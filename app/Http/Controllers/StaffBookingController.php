<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Staff;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\BookingStatusUpdated;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Services\GoogleDriveService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * StaffBookingController
 * 
 * Manages staff-side booking operations including verification, payment processing, and operational tracking.
 * Provides dashboard analytics, booking management, inspection coordination, and revenue reporting.
 * 
 * Key Features:
 * - Dashboard with revenue metrics, charts, and operational overview
 * - Real-time analytics (active rentals, pending bookings, utilization rates)
 * - Booking listing and searching with status filtering
 * - Payment verification and dispute handling
 * - Inspection coordination (pickup/return inspections)
 * - PDF agreement and receipt generation
 * - Email notifications to customers
 * - Booking cancellation and modification
 * - Revenue reporting (daily, weekly, monthly)
 * 
 * Database Constraints:
 * - bookingStatus: max 50 characters (Pending, Confirmed, Active, Completed, Cancelled)
 * - reason: max 150 characters (cancellation or modification reason)
 * - totalCost: decimal(10,2)
 * - remarks: max 150 characters
 * 
 * Authentication:
 * - Staff guard required for all operations
 * - Only authorized staff can manage bookings
 * 
 * Key Workflows:
 * 1. Dashboard: View real-time metrics and upcoming tasks
 * 2. Verification: Approve bookings after payment confirmation
 * 3. Inspection: Coordinate vehicle inspections before pickup and after return
 * 4. Payment: Process deposits and balances
 * 5. Reporting: Generate revenue reports and performance metrics
 * 
 * Dependencies:
 * - GoogleDriveService: Document storage and backup
 * - Booking model: Booking records and relationships
 * - Payment model: Payment tracking and verification
 * - Customer notification system: Email and notification dispatch
 * - DomPDF: PDF document generation
 */
class StaffBookingController extends Controller
{
    protected $driveService;

    /**
     * __construct()
     * 
     * Inject Google Drive Service for document operations.
     * Enables file backup, storage, and retrieval functionality.
     * 
     * @param GoogleDriveService $driveService The Google Drive service instance
     */
    public function __construct(GoogleDriveService $driveService)
    {
        $this->driveService = $driveService;
    }

    public function dashboard(Request $request)
    {
        // 1. === GLOBAL METRICS ===
        $totalRevenue = Booking::where('bookingStatus', '!=', 'Cancelled')->where('bookingStatus', '!=', 'Deleted')->sum('totalCost');
        
        // Revenue Growth
        $thisMonthRev = Booking::where('bookingStatus', '!=', 'Cancelled')
            ->where('bookingStatus', '!=', 'Deleted')
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('totalCost');
            
        $lastMonthRev = Booking::where('bookingStatus', '!=', 'Cancelled')
            ->where('bookingStatus', '!=', 'Deleted')
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->sum('totalCost');

        $revenueGrowth = ($lastMonthRev > 0) ? round((($thisMonthRev - $lastMonthRev) / $lastMonthRev) * 100, 1) : ($thisMonthRev > 0 ? 100 : 0);

        // Counts
        $activeRentalsCount = Booking::whereIn('bookingStatus', ['Active', 'Ongoing', 'Picked Up'])->count();
        $pendingBookingsCount = Booking::whereIn('bookingStatus', ['Pending', 'Submitted'])->count();
        $totalCustomers = \App\Models\Customer::count();
        $pendingCustomersCount = \App\Models\Customer::where('accountStat', 'pending')->count();
        $fullyPaidCount = Booking::where('bookingStatus', 'Confirmed')->count();
        $depositPaidCount = Booking::where('bookingStatus', 'Deposit Paid')->count();

        // 2. === CHART DATA ===
        $period = $request->input('chart_period', 'daily'); 
        $chartLabels = [];
        $chartRevenue = [];
        $chartBookings = [];

        if ($period === 'monthly') {
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $chartLabels[] = $date->format('M Y');
                $stats = Booking::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->where('bookingStatus', '!=', 'Cancelled')->where('bookingStatus', '!=', 'Deleted')
                    ->selectRaw('sum(totalCost) as total_money, count(*) as total_count')->first();
                $chartRevenue[] = $stats->total_money ?? 0;
                $chartBookings[] = $stats->total_count ?? 0;
            }
        } elseif ($period === 'weekly') {
            for ($i = 7; $i >= 0; $i--) {
                $date = Carbon::now()->subWeeks($i);
                $startOfWeek = $date->copy()->startOfWeek();
                $endOfWeek = $date->copy()->endOfWeek();
                $chartLabels[] = $startOfWeek->format('d M') . ' - ' . $endOfWeek->format('d M');
                $stats = Booking::whereBetween('created_at', [$startOfWeek, $endOfWeek])->where('bookingStatus', '!=', 'Cancelled')->where('bookingStatus', '!=', 'Deleted')
                    ->selectRaw('sum(totalCost) as total_money, count(*) as total_count')->first();
                $chartRevenue[] = $stats->total_money ?? 0;
                $chartBookings[] = $stats->total_count ?? 0;
            }
        } else {
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $chartLabels[] = $date->format('d M');
                $stats = Booking::whereDate('created_at', $date)->where('bookingStatus', '!=', 'Cancelled')->where('bookingStatus', '!=', 'Deleted')
                    ->selectRaw('sum(totalCost) as total_money, count(*) as total_count')->first();
                $chartRevenue[] = $stats->total_money ?? 0;
                $chartBookings[] = $stats->total_count ?? 0;
            }
        }

        // 3. === OPERATIONAL LISTS ===
        $today = Carbon::today();
        $pickupsToday = Booking::whereDate('originalDate', $today)->where('bookingStatus', '!=', 'Cancelled')->where('bookingStatus', '!=', 'Deleted')->get();
        $returnsToday = Booking::whereDate('returnDate', $today)->where('bookingStatus', '!=', 'Cancelled')->where('bookingStatus', '!=', 'Deleted')->get();
        $recentBookings = Booking::latest()->take(5)->get();
        
        // 4. === FLEET PULSE ===
        $totalVehicles = Vehicle::count();
        $activeVehicles = Booking::whereIn('bookingStatus', ['Active', 'Ongoing'])->count();
        $utilizationRate = $totalVehicles > 0 ? ($activeVehicles / $totalVehicles) * 100 : 0;
        $maintenanceRate = 5; 
        $todayRevenue = Booking::whereDate('created_at', $today)->where('bookingStatus', '!=', 'Cancelled')->where('bookingStatus', '!=', 'Deleted')->sum('totalCost');
        $overdueCount = Booking::where('returnDate', '<', $today)->whereIn('bookingStatus', ['Active', 'Ongoing'])->count();

        // 5. === CALENDAR EVENTS ===
        $calendarEvents = [];
        $allBookings = Booking::with(['vehicle', 'customer'])
            ->where('bookingStatus', '!=', 'Cancelled')
            ->where('bookingStatus', '!=', 'Deleted')
            ->get();

        foreach ($allBookings as $b) {
            $calendarEvents[] = [
                'id' => $b->bookingID,
                'title' => $b->vehicle->plateNo ?? 'Unknown', // <--- JUST PLATE NO
                'start' => $b->originalDate . 'T' . $b->bookingTime,
                'end' => $b->returnDate . 'T' . $b->returnTime,
                'extendedProps' => [
                    'vID' => $b->vehicleID,
                    'type' => 'booking',
                    'model' => $b->vehicle->model ?? 'Unknown Model',
                    'plate' => $b->vehicle->plateNo ?? 'Unknown',
                    'status' => $b->bookingStatus,
                    'source' => $b->external_company ? 'External' : 'Customer',
                    'customer_name' => $b->customer->fullName ?? 'Guest' // Store Name Here for Popup
                ]
            ];
        }

        if ($request->ajax()) {
            $pDate = $request->pickup_date . ' ' . ($request->pickup_time ?? '09:00:00');
            $rDate = $request->return_date . ' ' . ($request->return_time ?? '09:00:00');

            // 1. Fetch ALL vehicles
            $vehicles = Vehicle::where('status', '!=', 'inactive')
                ->with(['bookings' => function($q) use ($pDate, $rDate) {
                    $q->whereNotIn('bookingStatus', ['Cancelled', 'Rejected', 'Deleted'])
                    ->where(function($query) use ($pDate, $rDate) {
                        // Merge Date and Time columns into one for an accurate comparison
                        $query->where(DB::raw("CONCAT(originalDate, ' ', bookingTime)"), '<', $rDate)
                                ->where(DB::raw("CONCAT(returnDate, ' ', returnTime)"), '>', $pDate);
                    });
                }])->get();

            // 2. Map results (Fixing the variable name to $searchResults)
            $searchResults = $vehicles->map(function($v) {
                $clash = $v->bookings->first();
                $busyTime = null;

                if ($clash) {
                    // Force string conversion to prevent Carbon formatting errors
                    $sD = is_object($clash->originalDate) ? $clash->originalDate->format('Y-m-d') : substr($clash->originalDate, 0, 10);
                    $eD = is_object($clash->returnDate) ? $clash->returnDate->format('Y-m-d') : substr($clash->returnDate, 0, 10);
                    
                    $start = \Carbon\Carbon::parse($sD . ' ' . $clash->bookingTime)->format('d/m h:i a');
                    $end = \Carbon\Carbon::parse($eD . ' ' . $clash->returnTime)->format('d/m h:i a');
                    $busyTime = $start . ' - ' . $end;
                }

                return [
                    'plateNo' => $v->plateNo,
                    'model' => $v->model,
                    'is_available' => $v->bookings->isEmpty(),
                    'busy_time' => $busyTime
                ];
            })->sortByDesc('is_available')->values();

            // 3. RETURN the correct variable
            return response()->json([
                'searchResults' => $searchResults
            ]);
        }

        $notUpdatedDeposits = \App\Models\Booking::whereHas('payments', function($q) {
            $q->where('depoAmount', '>', 0)
            ->where(function($sub) {
                // 1. Capture all that are Requested, Pending, or Holding
                $sub->whereIn('depoStatus', ['Requested', 'Pending', 'Holding'])
                    // 2. OR capture Processed ones that have NO remarks (not fully settled yet)
                    ->orWhere(function($inner) {
                        $inner->where('depoStatus', 'Processed')
                                ->where(function($rem) {
                                    $rem->whereNull('remarks')
                                        ->orWhere('remarks', '');
                                });
                    });
            });
        })->count();

        $counts = [
            'not_updated' => Booking::whereHas('payments', function($q) {
                $q->whereIn('depoStatus', ['Requested', 'Pending', 'Holding'])
                ->orWhere(function($inner) {
                    $inner->where('depoStatus', 'Processed')
                            ->where(function($rem) {
                                $rem->whereNull('remarks')->orWhere('remarks', '');
                            });
                });
            })->count(),
            'updated' => Booking::whereHas('payments', function($q) {
                $q->where('depoStatus', 'Processed')
                ->whereNotNull('remarks')
                ->where('remarks', '!=', '');
            })->count(),
        ];

        return view('staff.dashboard', compact(
            'totalRevenue', 'revenueGrowth', 'activeRentalsCount', 'pendingBookingsCount', 'fullyPaidCount', 'depositPaidCount',
            'totalCustomers', 'pendingCustomersCount', 'chartLabels', 'chartRevenue', 'chartBookings',
            'pickupsToday', 'returnsToday', 'recentBookings', 
            'totalVehicles', 'utilizationRate', 'maintenanceRate', 'todayRevenue', 'overdueCount',
            'calendarEvents', 'counts'
        ));
    }

    // --- 2. LIST ALL BOOKINGS ---
    public function index(Request $request, )
    {
        $query = Booking::with(['customer', 'vehicle', 'payment', 'payments']) 
                           ->orderBy('bookingDate', 'desc')
                           ->where('bookingStatus', '!=', 'Deleted');
        
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('bookingStatus', $request->status);
        }

        // Search Logic
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function($q) use ($term) {
                // Search by Booking ID
                $q->where('bookingID', 'like', "%$term%")
                  // Search by Customer Name or Email
                  ->orWhereHas('customer', function($c) use ($term) {
                      $c->where('fullName', 'like', "%$term%")
                        ->orWhere('email', 'like', "%$term%");
                  })
                  // Search by Vehicle Model or Plate
                  ->orWhereHas('vehicle', function($v) use ($term) {
                      $v->where('model', 'like', "%$term%")
                        ->orWhere('plateNo', 'like', "%$term%");
                  });
            });
        }

        // 2. FILTER BY BOOKING TYPE
        if ($request->filled('type') && $request->type !== 'all') {
            if ($request->type === 'external') {
                // Adjust this condition based on how you identify external bookings
                $query->whereNotNull('external_company'); 
            } elseif ($request->type === 'customer') {
                $query->whereNull('external_company');
            }
        }

        // 3. FILTER BY PICKUP DATE
        if ($request->filled('pickup_date')) {
            $pickupDate = $request->pickup_date;
            $query->whereDate('originalDate', $pickupDate);
        }

        // $bookings = $query->latest()->get();
        // $bookings = $query->orderByRaw("FIELD(bookingStatus, 'Submitted', 'Paid', 'Deposit Paid', 'Confirmed', 'Active', 'Completed', 'Cancelled', 'Rejected') ASC")
        //           ->orderBy('originalDate', 'desc') 
        //           ->get();
        // 1. Get the data from SQL without sorting first
        $bookings = $query->get();

        // 2. Define your custom order priority
        $priority = [
            'Submitted'    => 1,
            'Paid'         => 2,
            'Deposit Paid' => 3,
            'Confirmed'    => 4,
            'Active'       => 5,
            'Completed'    => 6,
            'Cancelled'    => 7,
            'Rejected'     => 8,
        ];

        // 3. Sort the collection manually
        $bookings = $bookings->sort(function ($a, $b) use ($priority) {
            $aStatus = trim($a->bookingStatus);
            $bStatus = trim($b->bookingStatus);

            $aPrio = $priority[$aStatus] ?? 9;
            $bPrio = $priority[$bStatus] ?? 9;

            // If statuses are different, sort by priority
            if ($aPrio !== $bPrio) {
                return $aPrio <=> $bPrio;
            }

            // if status is Completed, sort by returnDate + returnTime
            if ($aStatus === 'Completed' && $bStatus === 'Completed') {
                return Carbon::parse($b->originalDate . ' ' . $b->bookingTime) <=> 
                Carbon::parse($a->originalDate . ' ' . $a->bookingTime);
            }

            if ($aStatus === 'Active' && $bStatus === 'Active') {
                return Carbon::parse($a->returnDate . ' ' . $a->returnTime) <=> 
                Carbon::parse($b->returnDate . ' ' . $b->returnTime);
            }

            // If statuses are the same, sort by originalDate + bookingTime
            return Carbon::parse($a->originalDate . ' ' . $a->bookingTime) <=> 
                Carbon::parse($b->originalDate . ' ' . $b->bookingTime);
        });

        $pendingActivations = Booking::where('bookingStatus', 'Confirmed')
            ->where('originalDate', '<=', now()->toDateString())
            ->get();

        foreach ($pendingActivations as $booking) {
            // Combine Date and Time for a precise comparison
            $pickupDateTime = \Carbon\Carbon::parse($booking->originalDate . ' ' . $booking->bookingTime);

            // CRITICAL: Only update if the current time is >= the scheduled pickup time
            if (now()->greaterThanOrEqualTo($pickupDateTime)) {
                $booking->update(['bookingStatus' => 'Active']);

                // Sync Vehicle to 'Rented'
                if ($booking->vehicle) {
                    $booking->vehicle->update([
                        'status' => 'rented',
                        'availability' => false
                    ]);
                }
                
                \Log::info("Booking #{$booking->bookingID} automatically shifted to Active.");
            }
        }

        return view('staff.bookings.index', compact('bookings'));
    }

    // [NEW] CREATE BOOKING (Staff Input)
    public function store(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'booking_type'     => 'required|in:customer,external',
            'customer_id'      => 'required_if:booking_type,customer|exists:customers,customerID',
            'external_company' => 'required_if:booking_type,external|string',
            'contact_person'   => 'nullable|string|max:100',
            'company_email'    => 'nullable|email',
            'company_phone'    => 'nullable|string|max:20',
            'vehicle_id'       => 'required|exists:vehicles,VehicleID',
            'pickup_date'      => 'required|date',
            'pickup_time'      => 'required|date_format:H:i',
            'return_date'      => 'required|date|after_or_equal:pickup_date',
            'return_time'      => 'required|date_format:H:i',
            'pickup_location'  => 'nullable|string|max:255',
            'return_location'  => 'nullable|string|max:255',
            'total_amount'     => 'required_if:booking_type,external|numeric|min:0',
            'additional_fees'  => 'nullable|numeric|min:0',
            'receipt_image'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'agreement_image'  => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'remarks'          => 'nullable|string|max:150'
        ]);

        try {
            // For external bookings, create a temporary guest customer
            if ($validated['booking_type'] === 'external') {
                try {
                    $customer = Customer::create([
                        'fullName'       => $validated['contact_person'] ?? $validated['external_company'],
                        'email'          => $validated['company_email'] ?? 'external-' . strtolower($validated['external_company']) . '-' . time() . '@company.com',
                        'phoneNo'        => $validated['company_phone'] ?? '000-0000000',
                        'password'       => bcrypt('temp-external-' . time()),
                        'accountStat'    => 'Confirmed', // Auto-approve external bookings
                        'ic_passport'    => 'External: ' . $validated['external_company'],
                        'stustaffID'     => 'EXT-' . strtoupper($validated['external_company'][0]) . date('Ymd'),
                        'driving_license_expiry' => date('Y-m-d', strtotime('+5 years')),
                    ]);
                } catch (\Exception $e) {
                    // If customer creation fails, log but continue (e.g., Google Drive errors)
                    Log::warning("Guest customer creation warning", ['error' => $e->getMessage()]);
                    // Try to find existing customer with similar email
                    $baseEmail = strtolower($validated['external_company']) . '-' . time();
                    $customer = Customer::firstOrCreate(
                        ['email' => 'external-' . $baseEmail . '@company.com'],
                        [
                            'fullName'       => $validated['contact_person'] ?? $validated['external_company'],
                            'phoneNo'        => $validated['company_phone'] ?? '000-0000000',
                            'password'       => bcrypt('temp-external-' . time()),
                            'accountStat'    => 'Confirmed',
                            'ic_passport'    => 'External: ' . $validated['external_company'],
                            'stustaffID'     => 'EXT-' . strtoupper($validated['external_company'][0]) . date('Ymd'),
                            'driving_license_expiry' => date('Y-m-d', strtotime('+5 years')),
                        ]
                    );
                }
            } else {
                $customer = Customer::findOrFail($validated['customer_id']);
            }

            // Calculate booking dates
            $pickupDateTime = $validated['pickup_date'] . ' ' . $validated['pickup_time'];
            $returnDateTime = $validated['return_date'] . ' ' . $validated['return_time'];

            // Calculate cost using tiered pricing system
            $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
            $pickupCarbon = Carbon::parse($pickupDateTime);
            $returnCarbon = Carbon::parse($returnDateTime);
            
            // Calculate hours (ceil to next full hour)
            $hoursDiff = $pickupCarbon->floatDiffInHours($returnCarbon);
            $totalHours = ceil(abs($hoursDiff));
            if ($totalHours < 1) $totalHours = 1;
            
            // Get tiered rates from vehicle
            $hourlyRates = $vehicle->hourly_rates ?? [
                '1' => 10, '3' => 18, '5' => 25, '7' => 31, '9' => 36, '12' => 40, '24' => 43
            ];
            
            // Calculate rental cost based on tiers
            $rentalCost = 0;
            $remainingHours = $totalHours;
            
            // Full days (24 hours)
            if ($remainingHours >= 24) {
                $fullDays = intdiv($remainingHours, 24);
                $rentalCost += $fullDays * ($hourlyRates['24'] ?? 43);
                $remainingHours = $remainingHours % 24;
            }
            
            // Remaining hours tier
            if ($remainingHours > 0) {
                if ($remainingHours <= 1) {
                    $rentalCost += $hourlyRates['1'] ?? 10;
                } elseif ($remainingHours <= 3) {
                    $rentalCost += $hourlyRates['3'] ?? 18;
                } elseif ($remainingHours <= 5) {
                    $rentalCost += $hourlyRates['5'] ?? 25;
                } elseif ($remainingHours <= 7) {
                    $rentalCost += $hourlyRates['7'] ?? 31;
                } elseif ($remainingHours <= 9) {
                    $rentalCost += $hourlyRates['9'] ?? 36;
                } elseif ($remainingHours <= 12) {
                    $rentalCost += $hourlyRates['12'] ?? 40;
                } else {
                    $rentalCost += $hourlyRates['24'] ?? 43;
                }
            }
            
            // For external bookings, use the manually entered total amount
            // For customer bookings, calculate the cost
            if ($validated['booking_type'] === 'external') {
                $totalCost = floatval($validated['total_amount'] ?? 0);
                Log::info("External Booking Cost (Manual Entry)", [
                    'booking_type' => 'external',
                    'total_amount_entered' => $totalCost,
                    'company' => $validated['external_company'],
                ]);
            } else {
                $additionalFees = floatval($validated['additional_fees'] ?? 0);
                $totalCost = $rentalCost + $additionalFees;
                Log::info("Booking Cost Calculation (Tiered)", [
                    'pickup_datetime' => $pickupDateTime,
                    'return_datetime' => $returnDateTime,
                    'hours_diff' => $hoursDiff,
                    'hours_rounded' => $totalHours,
                    'hourly_rates' => $hourlyRates,
                    'rental_cost' => $rentalCost,
                    'additional_fees' => $additionalFees,
                    'total_cost_calculated' => $totalCost,
                ]);
            }

            // Handle file uploads to S3
            $receiptPath = null;
            $agreementPath = null;
            
            if ($request->hasFile('receipt_image')) {
                try {
                    $receiptPath = Storage::disk('s3')->putFile('receipts', $request->file('receipt_image'));
                    if (!$receiptPath) {
                        throw new \Exception('Failed to upload receipt.');
                    }
                } catch (\Exception $e) {
                    Log::error('Receipt Upload Error: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to upload receipt: ' . $e->getMessage(),
                        'error' => $e->getMessage()
                    ], 400);
                }
            }
            
            if ($request->hasFile('agreement_image')) {
                try {
                    $agreementPath = Storage::disk('s3')->putFile('agreements', $request->file('agreement_image'));
                    if (!$agreementPath) {
                        throw new \Exception('Failed to upload agreement.');
                    }
                } catch (\Exception $e) {
                    Log::error('Agreement Upload Error: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to upload agreement: ' . $e->getMessage(),
                        'error' => $e->getMessage()
                    ], 400);
                }
            }
            
            // Create booking
            $booking = Booking::create([
                'customerID'      => $customer->customerID,
                'vehicleID'       => $validated['vehicle_id'],
                'staffID'         => Auth::id(),
                'bookingDate'     => now()->format('Y-m-d'),
                'originalDate'    => $validated['pickup_date'],
                'bookingTime'     => $validated['pickup_time'],
                'returnDate'      => $validated['return_date'],
                'returnTime'      => $validated['return_time'],
                'pickupLocation'  => $validated['pickup_location'],
                'returnLocation'  => $validated['return_location'],
                'totalCost'       => $totalCost,
                'aggreementLink'  => $agreementPath,
                'receipt'         => $receiptPath,
                'bookingStatus'   => 'Confirmed', // Auto-confirm staff-created bookings
                'remarks'         => ($validated['remarks'] ?? '') . 
                                    ($validated['booking_type'] === 'external' 
                                        ? "\n[EXTERNAL] Company: " . $validated['external_company'] 
                                        : ''),
                'external_company' => $validated['booking_type'] === 'external' ? $validated['external_company'] : null,
            ]);

            // Create payment record
            Payment::create([
                'bookingID'       => $booking->bookingID,
                'amount'          => $totalCost,
                'depoAmount'      => $totalCost, // Full amount as deposit for staff-created bookings
                'transactionDate' => now()->format('Y-m-d'),
                'paymentStatus'   => 'Verified', // Auto-verified for staff-created
                'depoStatus'      => 'Received',
                'paymentMethod'   => 'Manual Entry',
                'installmentDetails' => 'Staff-created booking - ' . date('Y-m-d H:i:s'),
            ]);

            // Log the action
            Log::info("Booking created by staff", [
                'booking_id' => $booking->bookingID,
                'type'       => $validated['booking_type'],
                'customer'   => $customer->fullName,
                'vehicle'    => $vehicle->model,
                'amount'     => $totalCost,
                'staff_id'   => Auth::id(),
            ]);

            // Notify customer
            $customer->notify(new BookingStatusUpdated(
                $booking,
                "Your new booking #{$booking->bookingID} has been created by our staff. Total cost: RM " . number_format($totalCost, 2)
            ));

            // Return JSON for AJAX requests, redirect for normal requests
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Booking created successfully!',
                    'booking_id' => $booking->bookingID,
                    'total' => 'RM ' . number_format($totalCost, 2)
                ]);
            }

            return redirect()->route('staff.bookings.show', $booking->bookingID)
                           ->with('success', 'Booking created successfully! Total: RM ' . number_format($totalCost, 2));

        } catch (\Exception $e) {
            Log::error("Error creating booking", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['agreement_image', 'receipt_image'])
            ]);
            
            // Return JSON for AJAX requests, redirect for normal requests
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating booking: ' . $e->getMessage(),
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->withError('Error creating booking: ' . $e->getMessage())->withInput();
        }
    }

    // Get booked dates for a vehicle (for calendar availability)
    public function getBookedDates($vehicleId)
    {
        // Get all active bookings for this vehicle
        $bookings = Booking::where('vehicleID', $vehicleId)
            ->whereIn('bookingStatus', ['Pending', 'Confirmed', 'Active'])
            ->with('customer')
            ->select('originalDate', 'returnDate', 'bookingStatus', 'customerID')
            ->get();

        // Convert to date array and build date info
        $bookedDates = [];
        $dateInfo = [];
        
        foreach ($bookings as $booking) {
            $start = Carbon::parse($booking->originalDate);
            $end = Carbon::parse($booking->returnDate);
            $duration = $start->diffInDays($end);
            $customerName = $booking->customer ? $booking->customer->fullName : 'Unknown';
            
            while ($start->lte($end)) {
                $dateStr = $start->format('Y-m-d');
                $bookedDates[] = $dateStr;
                
                // Store booking info for this date
                if (!isset($dateInfo[$dateStr])) {
                    $dateInfo[$dateStr] = [
                        'customer' => $customerName,
                        'duration' => $duration . ' day(s)',
                        'status' => $booking->bookingStatus
                    ];
                }
                
                $start->addDay();
            }
        }

        return response()->json([
            'dates' => array_unique($bookedDates),
            'dateInfo' => $dateInfo
        ]);
    }

    // --- 3. VERIFY PAYMENT (Handles Balance Payment too) ---
    public function verifyPayment($id)
    {
        $booking = Booking::findOrFail($id);
        
        // Find the latest pending payment (This catches the new balance receipt)
        $payment = Payment::where('bookingID', $id)
                          ->where('paymentStatus', 'Pending Verification')
                          ->latest()
                          ->first();

        if ($payment) {
            $payment->update(['paymentStatus' => 'Verified']);
            return back()->with('success', 'Payment verified. You can now Approve/Confirm the booking.');
        }

        return back()->with('error', 'No pending payment found.');
    }

    // --- 4. APPROVE / CONFIRM ---
    public function approveAgreement($id)
    {
        $booking = Booking::with('customer', 'vehicle')->findOrFail($id);
        // Check if there are any unverified payments
        $pendingPayments = Payment::where('bookingID', $id)
                                  ->where('paymentStatus', 'Pending Verification')
                                  ->exists();

        if ($pendingPayments) {
            return back()->with('error', 'Please verify all pending payments first.');
        }

        // Set status to Confirmed (This is what you wanted)
        $booking->update([
            'bookingStatus' => 'Confirmed', 
            'staffID' => $booking->staffID ?? Auth::guard('staff')->id(), 
            'aggreementDate' => now(),
        ]);
        // Notify Customer
        try {
            $booking->customer->notify(new BookingStatusUpdated(
                $booking, 
                "Great news! Your booking #{$booking->bookingID} has been CONFIRMED. Please arrive on time for pickup."
            ));
        } catch (\Exception $e) {
            \Log::error("Confirmation Email Failed: " . $e->getMessage());
        }
        
        return back()->with('success', 'Booking is CONFIRMED and email sent.');
    }

    // ... pickup, processReturn, processRefund, storeInspection, assignStaff, show ...
    public function show($id) {
        // FIX: Added 'feedback' to the with() list
        $booking = Booking::with(['customer', 'vehicle', 'payment', 'inspections', 'staff', 'feedback'])
                          ->findOrFail($id);

    // 2. [NEW] Find Available Alternatives (Same Model, Different Plate)
    // Only needed if booking is not yet completed
    $availableCars = collect(); // Default empty collection

    if (in_array($booking->bookingStatus, ['Submitted', 'Deposit Paid', 'Paid', 'Confirmed'])) {
        
        // Get Booking Dates
        $reqStart = \Carbon\Carbon::parse($booking->originalDate . ' ' . $booking->bookingTime);
        $reqEnd   = \Carbon\Carbon::parse($booking->returnDate . ' ' . $booking->returnTime);

        // Find cars: Same Model + Same Brand + Not the current one + Available
        $availableCars = \App\Models\Vehicle::where('model', $booking->vehicle->model)
            ->where('brand', $booking->vehicle->brand)
            ->where('availability', true)
            ->where('VehicleID', '!=', $booking->vehicleID) // Don't show the one already assigned
            ->get()
            ->filter(function($v) use ($reqStart, $reqEnd) {
                // Check if this specific car is free during the booking time
                return $this->isVehicleAvailable($v, $reqStart, $reqEnd);
            });
    }

        $allStaff = Staff::all();
        return view('staff.bookings.show', compact('booking', 'allStaff', 'availableCars'));
    }

    private function isVehicleAvailable($vehicle, $start, $end) {
    $endBuffer = $end->copy()->addHours(3); // 3 Hour Buffer
    
    foreach ($vehicle->bookings as $b) {
        if (in_array($b->bookingStatus, ['Cancelled', 'Rejected'])) continue;
        
        $bStart = \Carbon\Carbon::parse($b->originalDate . ' ' . $b->bookingTime);
        $bEnd = \Carbon\Carbon::parse($b->returnDate . ' ' . $b->returnTime)->addHours(3);
        
        // Check overlap
        if ($start->lt($bEnd) && $endBuffer->gt($bStart)) return false;
    }
    return true;
}

// [NEW] EDIT BOOKING - Pickup and Return Information
    public function edit($id)
    {
        $booking = Booking::with(['customer', 'vehicle'])
                          ->findOrFail($id);

        return view('staff.bookings.edit', compact('booking'));
    }

    // [NEW] UPDATE BOOKING - Pickup and Return Information
    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        // Validate the input
        $validated = $request->validate([
            'originalDate' => 'required|date',
            'bookingTime' => 'required|date_format:H:i',
            'returnDate' => 'required|date|after_or_equal:originalDate',
            'returnTime' => 'required|date_format:H:i',
            'pickupLocation' => 'required|string|max:255',
            'returnLocation' => 'required|string|max:255',
        ]);

        // Additional validation: Check time interval logic
        $pickupDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $validated['originalDate'] . ' ' . $validated['bookingTime']);
        $returnDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $validated['returnDate'] . ' ' . $validated['returnTime']);

        // Return must be after or equal to pickup
        if ($returnDateTime->lte($pickupDateTime)) {
            return back()->withInput()
                        ->withErrors(['returnTime' => 'Return date/time must be after pickup date/time. Please ensure return is at least 1 hour after pickup.']);
        }

        // Ensure minimum booking duration (at least 1 hour)
        $minutes = $pickupDateTime->diffInMinutes($returnDateTime);
        if ($minutes < 60) {
            return back()->withInput()
                        ->withErrors(['returnTime' => 'Minimum booking duration is 1 hour. Current duration is only ' . $minutes . ' minutes.']);
        }

        try {
            // Update the booking with new pickup and return information
            $booking->update([
                'originalDate' => $validated['originalDate'],
                'bookingTime' => $validated['bookingTime'],
                'returnDate' => $validated['returnDate'],
                'returnTime' => $validated['returnTime'],
                'pickupLocation' => $validated['pickupLocation'],
                'returnLocation' => $validated['returnLocation'],
            ]);

            return redirect()->route('staff.bookings.show', $booking->bookingID)
                           ->with('success', 'Booking updated successfully! Pickup and return information has been modified.');
        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'Failed to update booking. Please try again.');
        }
    }

// [NEW] APPROVE
    public function approve($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->bookingStatus = 'Confirmed';
        $booking->save();
        
        // Notification logic here...

        return back()->with('success', 'Booking Confirmed!');
    }

    public function pickup(Request $request, $id) {
        $booking = Booking::findOrFail($id);
        $booking->update(['bookingStatus' => 'Active']);

        // Sync Vehicle Status to Database
        $vehicle = $booking->vehicle;
        $vehicle->update([
            'status' => 'rented',
            'availability' => false
        ]);

        return back()->with('success', 'Handover complete. Vehicle status updated to Rented.');
    }

    public function processReturn(Request $request, $id) {
        $booking = Booking::findOrFail($id);
        
        $isExternal = !empty($booking->external_company);

        // 1. Update Status to Completed
        $booking->update([
            'bookingStatus' => 'Completed',
            'actualReturnDate' => now()->toDateString(),
            'actualReturnTime' => now()->toTimeString(),
            'remarks' => $booking->remarks . ($isExternal ? " --- External Booking Completed - No Refund " : "")
        ]);

        // 2. Refund Deposit & Complete Payment
        if ($booking->payment) {
            if ($isExternal) {
                $booking->payment->update([
                    'depoStatus' => 'Completed', 
                    'paymentStatus' => 'Completed',
                ]);
            } else {
                $booking->payment->update(['depoStatus' => 'Pending', 'paymentStatus' => 'Completed']);
            }
        }

        // 3. Trigger Loyalty Points
        $loyaltyController = new \App\Http\Controllers\LoyaltyController();
        $loyaltyController->bookingCompleted($id);

        try {
            // A. Invoice Generation & Email
            $booking->loadMissing(['customer', 'vehicle', 'payment']);
            $pdf = Pdf::loadView('pdf.invoice', compact('booking'));
            $pdfContent = $pdf->output(); 

            // Send Invoice via System Mailer
            Mail::send([], [], function ($message) use ($booking, $pdfContent) {
                $message->to($booking->customer->email)
                        ->subject('Invoice for Booking #' . $booking->bookingID)
                        ->attachData($pdfContent, 'Invoice-'.$booking->bookingID.'.pdf', [
                            'mime' => 'application/pdf',
                        ]);
            });

            // B. Send Explicit Notification about Completion & Deposit Refund
            // This triggers the BookingStatusUpdated notification email
            $booking->customer->notify(new BookingStatusUpdated(
                $booking, 
                "Your vehicle return is verified. Booking #{$booking->bookingID} is COMPLETED and your deposit is being processed."
            ));

            // C. Upload to Google Drive & Save Link (NEW)
            // Use app() to resolve the service without constructor injection
            $driveService = app(\App\Services\GoogleDriveService::class);
            
            $timestamp = now()->format('Ymd_Hi');
            $safeName = preg_replace('/[^A-Za-z0-9 ]/', '', $booking->customer->fullName);
            $fileName = "Invoice_{$booking->bookingID}_{$safeName}_{$timestamp}.pdf";

            // Upload using the raw content method
            // Uses GOOGLE_DRIVE_INVOICES from your .env
            $invoiceLink = $driveService->uploadFromString(
                $pdfContent, 
                $fileName, 
                env('GOOGLE_DRIVE_INVOICES') 
            );

            // D. Save Link to Database for Customer Dashboard
            if ($invoiceLink) {
                $booking->invoiceLink = $invoiceLink;
                $booking->save();
            }

            $vehicle = $booking->vehicle;
            $vehicle->update([
                'status' => 'available',
                'availability' => true
            ]);
            
        } catch (\Exception $e) {
            // Log error but allow the return process to finish
            \Log::error("Invoice Generation/Upload Failed: " . $e->getMessage());
        }

        return back()->with('success', $isExternal 
            ? 'Vehicle returned. External booking completed (No refund required).' 
            : 'Vehicle returned. Loyalty Points Awarded & Invoice Sent.');
    }

    public function processRefund(Request $request, $id) {
        $booking = Booking::findOrFail($id);
        
        if ($booking->payment && $booking->payment->depoStatus == 'Requested') {
            
            // 1. Handle Remarks (if provided)
            if ($request->filled('refund_remarks')) {
                $oldRemarks = $booking->remarks ? $booking->remarks . "\n\n" : "";
                // Add a timestamped note
                $refundNote = "[REFUND " . now()->format('d/m/y H:i') . "]: " . $request->refund_remarks;
                $booking->update(['remarks' => $oldRemarks . $refundNote]);
            }

            // 2. Update Status
            $booking->payment->update([
                'depoStatus' => 'Refunded', 
                'paymentStatus' => 'Refund Completed',
                'depoRefundedDate' => now()
            ]);
            
            // 3. Notify Customer (Optional: Pass remarks to notification if your notification class supports it)
            try {
                $booking->customer->notify(new BookingStatusUpdated(
                    $booking, 
                    "Your refund request for booking #{$booking->bookingID} has been processed successfully."
                ));
            } catch (\Exception $e) {
                \Log::error("Refund Email Failed: " . $e->getMessage());
            }
            
            return back()->with('success', 'Refund issued successfully.');
        }
        
        return back()->with('error', 'Error processing refund.');
    }

    // INSPECTION UPLOAD
    public function storeInspection(Request $request, $id) {
        $booking = Booking::with('vehicle')->findOrFail($id);
        $type = $request->input('type');

        // 1. Determine photo requirements
        $requiredCount = ($type == 'Pickup') ? 5 : 6;
        
        // 2. Validate inputs (Matches your Modal fields)
        $request->validate([
            'type' => 'required',
            'photos' => "required|array|size:$requiredCount",
            'photos.*' => 'image|max:10240',
            'mileage' => 'required|numeric',
            'fuel_level' => 'required',
        ], [
            'photos.size' => "Exactly $requiredCount photos are required for $type inspection."
        ]);

        // 3. Handle File Uploads to S3
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                try {
                    $photoPath = Storage::disk('s3')->putFile('inspections', $photo);
                    if (!$photoPath) {
                        throw new \Exception('Failed to upload inspection photo to S3.');
                    }
                    $photoPaths[] = $photoPath;
                } catch (\Exception $e) {
                    Log::error('Inspection Photo Upload Error: ' . $e->getMessage());
                    return back()->with('error', 'Failed to upload inspection photos: ' . $e->getMessage());
                }
            }
        }
        $photoString = json_encode($photoPaths);

        // 4. PREPARE REMARKS & UPDATE FLEET
        $remarks = "";
        
        // Only update vehicle mileage if it is a "Return" inspection
        if ($type == 'Return') {
            // A. Update the Master Vehicle Record
            $booking->vehicle->update([
                'mileage' => $request->mileage
            ]);

            // B. Calculate Mileage Used (History Lookup)
            // We look for the "Pickup" inspection for this specific booking
            $pickupInspection = \App\Models\Inspection::where('bookingID', $booking->bookingID)
                ->where('inspectionType', 'Pickup')
                ->latest()
                ->first();

            // Determine start mileage: usage from Pickup Inspection, fallback to 0 if missing
            $startMileage = $pickupInspection ? ($pickupInspection->mileageBefore ?? $pickupInspection->mileageAfter) : 0;

            if ($startMileage > 0) {
                $diff = $request->mileage - $startMileage;
                $remarks = "[MILEAGE REPORT]\nStart: {$startMileage} km\nEnd: {$request->mileage} km\nTotal Used: {$diff} km";
            }
        }

        // 5. Create Inspection Record
        \App\Models\Inspection::create([
            'bookingID' => $booking->bookingID,
            'staffID' => Auth::guard('staff')->id(), 
            'inspectionType' => $type,
            'inspectionDate' => now(),
            
            // Map the new fields
            'fuelBefore' => ($type == 'Pickup') ? $request->fuel_level : null,
            'fuelAfter' => ($type == 'Return') ? $request->fuel_level : null,
            'mileageBefore' => ($type == 'Pickup') ? $request->mileage : null,
            'mileageAfter' => ($type == 'Return') ? $request->mileage : null,
            
            'photosBefore' => ($type == 'Pickup') ? $photoString : null,
            'photosAfter' => ($type == 'Return') ? $photoString : null,

            'remarks' => $remarks, // Save the calculated mileage text here
        ]);

        return back()->with('success', 'Inspection uploaded & vehicle mileage updated.');
    }

    // ASSIGN STAFF TO BOOKING
    public function assignStaff(Request $request, $id) {
        $request->validate(['staff_id' => 'required']);
        Booking::findOrFail($id)->update(['staffID' => $request->staff_id]);
        return back()->with('success', "Agent assigned.");
    }

    public function storeDynamicPricing(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'percentage' => 'required|numeric|min:-100|max:100', // [CHANGED] Allow negative for discounts
            'reason' => 'nullable|string|max:150',
        ]);

        $rules = Cache::get('dynamic_pricing_rules', []);

        $rules[] = [
            'start' => $request->start_date,
            'end'   => $request->end_date,
            'percent' => $request->percentage,
            'created_at' => now(),
            'author' => Auth::guard('staff')->user()->name ?? 'Staff'
        ];

        Cache::put('dynamic_pricing_rules', $rules);

        $type = $request->percentage > 0 ? "Surcharge (+{$request->percentage}%)" : "Discount ({$request->percentage}%)";
        return back()->with('success', "Rule Added: $type from {$request->start_date} to {$request->end_date}.");
    }

    public function deleteDynamicPricingRule($index)
    {
        $rules = Cache::get('dynamic_pricing_rules', []);
        
        if (isset($rules[$index])) {
            unset($rules[$index]);
            Cache::put('dynamic_pricing_rules', array_values($rules));
            return back()->with('success', 'Pricing rule removed.');
        }
        
        return back()->with('error', 'Rule not found.');
    }

    public function clearDynamicPricing()
    {
        Cache::forget('dynamic_pricing_rules');
        return back()->with('success', 'All dynamic pricing rules have been reset.');
    }

    // [NEW] UPDATE VEHICLE (Swap Plate No)
    public function updateVehicle(Request $request, $id)
    {
        // 1. Find the booking
        $booking = Booking::findOrFail($id);
        
        // 2. Validate the new Vehicle ID exists in your database
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,VehicleID'
        ]);

        // 3. FORCE UPDATE the vehicle ID
        // We set it directly and call save() to ensure it persists
        $booking->vehicleID = $request->vehicle_id;
        
        // Optional: If you want this action to also Confirm the booking immediately
        // Uncomment the line below. Otherwise, it just swaps the car.
        // $booking->bookingStatus = 'Confirmed'; 

        $booking->save();

        // 4. (Optional) Notify Customer about the change
        try {
            // Reload the vehicle relation so the email shows the NEW plate
            $booking->refresh(); 
            
            $booking->customer->notify(new \App\Notifications\BookingStatusUpdated(
                $booking, 
                "Vehicle update: Your booking #{$booking->bookingID} is now assigned to plate {$booking->vehicle->plateNo}."
            ));
        } catch (\Exception $e) {
            // Ignore notification errors
        }

        return back()->with('success', 'Vehicle successfully swapped to ' . $booking->vehicle->plateNo);
    }

    // [NEW] REJECT BOOKING (Handle Fraud vs Refund)
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reject_action' => 'required|in:fraud,refund',
            'reason' => 'required|string|max:150',
        ]);

        $booking = Booking::with('payment')->findOrFail($id);
        $payment = $booking->payment;

        // 1. Prepare the new Remarks string
        // We keep the old remarks (if any) and add the Rejection Reason on a new line
        $oldRemarks = $booking->remarks ? $booking->remarks . "\n\n" : "";
        $rejectionNote = "[REJECTED]: " . $request->reason;
        
        $finalRemarks = $oldRemarks . $rejectionNote;

        $statusMessage = "";

        if ($request->reject_action === 'fraud') {
            // CASE 1: FRAUD
            $booking->update([
                'bookingStatus' => 'Rejected',
                'remarks' => $finalRemarks // <--- Uses the combined string
            ]);

            if ($payment) {
                $payment->update([
                    'paymentStatus' => 'Rejected',
                    'depoStatus' => 'Void',
                ]);
            }
            $statusMessage = "Booking rejected as Fraud.";

        } elseif ($request->reject_action === 'refund') {
            // CASE 2: REFUND
            $booking->update([
                'bookingStatus' => 'Rejected',
                'remarks' => $finalRemarks // <--- Uses the combined string
            ]);

            if ($payment) {
                $payment->update([
                    'paymentStatus' => 'Refund Completed',
                    'depoStatus' => 'Refunded',
                    'depoRefundedDate' => now(),
                ]);
            }
            $statusMessage = "Booking rejected & Refunded.";
        }

        // Notify Customer
        try {
            $booking->customer->notify(new BookingStatusUpdated(
                $booking, 
                "Your booking #{$booking->bookingID} was REJECTED. Reason: " . $request->reason
            ));
        } catch (\Exception $e) {
            \Log::error("Rejection Email Failed: " . $e->getMessage());
        }
        return back()->with('success', $statusMessage);
    }

    // mark notification as read
    public function markAsRead($id) {
        $notification = auth()->guard('staff')->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return back();
    }

    // STREAM INVOICE AS PDF
    public function streamInvoice($id)
    {
        // 1. Find booking (No customerID check needed for staff)
        $booking = Booking::with(['customer', 'vehicle', 'payment', 'voucher'])
                    ->findOrFail($id);

        // 2. Generate and Stream
        // Staff can preview it even if not strictly "Completed" yet if needed, 
        // but typically invoice is for completed/paid jobs.
        $pdf = Pdf::loadView('pdf.invoice', compact('booking'));
        return $pdf->stream('Invoice-' . $booking->bookingID . '.pdf');
    }

    /**
     * DELETE BOOKING
     * 
     * Deletes a booking (marks as Deleted status).
     * Only allows deletion of bookings in specific statuses:
     * - Pending, Submitted, Rejected
     * 
     * Additional logic:
     * - Void any associated deposits/payments
     * - Add deletion note to remarks
     * - Notify customer about deletion
     * - Return success message
     * 
     * @param int $id Booking ID
     * @param Request $request Request object (contains optional deletion reason)
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function destroy(Request $request, $id)
    {
        // Validate deletion reason
        $request->validate([
            'deletion_reason' => 'nullable|string|max:150',
        ]);

        // Find booking with related data
        $booking = Booking::with(['payment', 'customer', 'vehicle'])->findOrFail($id);

        // Check if booking can be deleted (only certain statuses allowed)
        $deletableStatuses = ['Pending', 'Submitted', 'Confirmed', 'Rejected', 'Cancelled'];
        if (!in_array($booking->bookingStatus, $deletableStatuses)) {
            return back()->with('error', "Cannot delete booking with status '{$booking->bookingStatus}'. Only Pending, Submitted, Rejected, or Cancelled bookings can be deleted.");
        }

        try {
            // 1. Prepare deletion note
            $oldRemarks = $booking->remarks ? $booking->remarks . "\n\n" : "";
            $deletionReason = $request->deletion_reason ?? "No reason provided";
            $deletionNote = "[DELETED on " . now()->format('d-m-Y H:i:s') . "]: " . $deletionReason;
            $finalRemarks = $oldRemarks . $deletionNote;

            // 2. Update booking status to Deleted
            $booking->update([
                'bookingStatus' => 'Deleted',
                'remarks' => $finalRemarks,
            ]);

            // 3. Handle associated payment
            $payment = $booking->payment;
            if ($payment) {
                $payment->update([
                    'paymentStatus' => 'Void',
                    'depoStatus' => 'Void',
                ]);
            }

            // make deposit amount 0
            if ($payment) {
                $payment->update([
                    'depoAmount' => 0,
                    'amount' => 0,
                    'depoStatus' => 'Void',
                    'paymentStatus' => 'Void',
                ]);
            }

            // 4. Log the deletion
            Log::info("Booking deleted", [
                'bookingID' => $booking->bookingID,
                'customerID' => $booking->customerID,
                'vehicleID' => $booking->vehicleID,
                'previousStatus' => $booking->bookingStatus,
                'deletedAt' => now(),
            ]);

            // 5. Notify customer about deletion
            try {
                $booking->customer->notify(new BookingStatusUpdated(
                    $booking,
                    "Your booking #{$booking->bookingID} has been deleted. Reason: " . $deletionReason
                ));
            } catch (\Exception $e) {
                Log::error("Deletion Notification Failed: " . $e->getMessage());
            }

            return redirect()->route('staff.bookings.index')->with('success', "Booking #{$booking->bookingID} has been successfully deleted.");

        } catch (\Exception $e) {
            Log::error("Error deleting booking", [
                'bookingID' => $id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', "An error occurred while deleting the booking. " . $e->getMessage());
        }
    }
}