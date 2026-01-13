<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\Feedback;
use App\Models\Vehicle;
use App\Models\Maintenance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
// Google API Imports
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use App\Services\GoogleDriveService;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // --- FILTER LOGIC ---
        $filterType = $request->input('filter_type', 'monthly'); // daily, weekly, monthly, yearly
        $selectedDate = $request->input('date', Carbon::today()->format('Y-m-d'));
        $selectedMonth = $request->input('month', Carbon::now()->month);
        $selectedYear = $request->input('year', Carbon::now()->year);

        $chartLabels = [];
        $chartData = [];
        $totalIncome = 0;
        $chartTitle = '';

        $query = Payment::query();

        switch ($filterType) {
            case 'daily':
                // Breakdown by Hour (00:00 - 23:00) for a specific date
                $date = Carbon::parse($selectedDate);
                $query->whereDate('created_at', $date);
                $totalIncome = $query->sum('amount');
                $chartTitle = "Income for " . $date->format('d M Y');

                // Group by Hour
                $hourlyData = Payment::select(DB::raw('HOUR(created_at) as hour'), DB::raw('sum(amount) as total'))
                    ->whereDate('created_at', $date)
                    ->groupBy('hour')
                    ->pluck('total', 'hour');

                for ($i = 0; $i < 24; $i++) {
                    $chartLabels[] = sprintf('%02d:00', $i);
                    $chartData[] = $hourlyData[$i] ?? 0;
                }
                break;

            case 'weekly':
                // Breakdown by Day (Monday - Sunday) for the selected date's week
                $date = Carbon::parse($selectedDate);
                $startOfWeek = $date->copy()->startOfWeek();
                $endOfWeek = $date->copy()->endOfWeek();
                
                $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
                $totalIncome = $query->sum('amount');
                $chartTitle = "Income: " . $startOfWeek->format('d M') . " - " . $endOfWeek->format('d M Y');

                $dailyData = Payment::select(DB::raw('DATE(created_at) as date'), DB::raw('sum(amount) as total'))
                    ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->groupBy('date')
                    ->pluck('total', 'date');

                for ($i = 0; $i < 7; $i++) {
                    $currentDay = $startOfWeek->copy()->addDays($i);
                    $chartLabels[] = $currentDay->format('D (d/m)');
                    $chartData[] = $dailyData[$currentDay->format('Y-m-d')] ?? 0;
                }
                break;

            case 'yearly':
                // Breakdown by Month (Jan - Dec) for a specific year
                $query->whereYear('created_at', $selectedYear);
                $totalIncome = $query->sum('amount');
                $chartTitle = "Income for Year " . $selectedYear;

                $monthlyData = Payment::select(DB::raw('MONTH(created_at) as month'), DB::raw('sum(amount) as total'))
                    ->whereYear('created_at', $selectedYear)
                    ->groupBy('month')
                    ->pluck('total', 'month');

                for ($i = 1; $i <= 12; $i++) {
                    $chartLabels[] = Carbon::create()->month($i)->format('M');
                    $chartData[] = $monthlyData[$i] ?? 0;
                }
                break;

            case 'monthly':
            default:
                // Breakdown by Day (1 - 31) for a specific month/year
                $query->whereMonth('created_at', $selectedMonth)->whereYear('created_at', $selectedYear);
                $totalIncome = $query->sum('amount');
                $dateObj = Carbon::createFromDate($selectedYear, $selectedMonth, 1);
                $chartTitle = "Income for " . $dateObj->format('F Y');

                $dailyData = Payment::select(DB::raw('DAY(created_at) as day'), DB::raw('sum(amount) as total'))
                    ->whereMonth('created_at', $selectedMonth)
                    ->whereYear('created_at', $selectedYear)
                    ->groupBy('day')
                    ->pluck('total', 'day');

                $daysInMonth = $dateObj->daysInMonth;
                for ($i = 1; $i <= $daysInMonth; $i++) {
                    $chartLabels[] = $i;
                    $chartData[] = $dailyData[$i] ?? 0;
                }
                break;
        }

        // --- 2. NEW BOOKING OVERVIEW LOGIC ---
        $bookingPeriod = $request->input('booking_period', '7days'); // Default to 7 days
        $bookingStartDate = Carbon::now();
        $bookingTitle = 'Recent Booking Activity';

        switch ($bookingPeriod) {
            case '30days':
                $bookingStartDate->subDays(29);
                $bookingTitle = 'Booking Activity (Last 30 Days)';
                break;
            case '7days':
            default:
                $bookingStartDate->subDays(6);
                $bookingTitle = 'Booking Activity (Last 7 Days)';
                break;
        }

        // [CHANGED] Fetch FULL Booking Models instead of just count
        // We eager load 'customer' and 'vehicle' for the modal details
        $rawBookings = Booking::with(['customer', 'vehicle'])
            ->where('created_at', '>=', $bookingStartDate)
            ->orderBy('created_at', 'ASC') // Chronological order
            ->get();
        
        // Group by Date (Y-m-d) so we can iterate days in the view
        $bookingOverview = $rawBookings->groupBy(function($booking) {
            return $booking->created_at->format('Y-m-d');
        });

        // --- 3. OTHER WIDGET DATA ---
        $statusPeriod = $request->input('status_period', '1month'); // 1month or 3months
        $statusStartDate = Carbon::now();

        switch ($statusPeriod) {
            case '3months':
                $statusStartDate->subMonths(3);
                break;
            case '1month':
            default:
                $statusStartDate->subMonth();
                break;
        }

        $statusRaw = Booking::select('bookingStatus', DB::raw('count(*) as count'))
            ->where('created_at', '>=', $statusStartDate)
            ->groupBy('bookingStatus')
            ->get();
        $bookingStatus = $statusRaw->pluck('count', 'bookingStatus'); 

        // --- TOP VEHICLES WITH PERIOD FILTER ---
        $vehiclePeriod = $request->input('vehicle_period', '1month');
        $vehicleStartDate = Carbon::now();

        switch ($vehiclePeriod) {
            case '3months':
                $vehicleStartDate->subMonths(3);
                break;
            case '1week':
                $vehicleStartDate->subDays(6);
                break;
            case '1month':
            default:
                $vehicleStartDate->subMonth();
                break;
        }

        $monthBookings = Booking::with('vehicle')
            ->where('created_at', '>=', $vehicleStartDate)
            ->get();

        $topVehicles = $monthBookings->groupBy(function ($booking) {
                return $booking->vehicle->model ?? 'Unknown Vehicle';
            })
            ->map(function ($group) {
                return [
                    'model' => $group->first()->vehicle->model ?? 'Unknown',
                    'total_bookings' => $group->count()
                ];
            })
            ->sortByDesc('total_bookings')
            ->take(7);

        $reviews = Feedback::with(['booking.customer', 'booking.vehicle'])
            ->latest()
            ->limit(15)
            ->get();

        // --- 4. BOOKINGS BY FACULTY ---
        $facultyPeriod = $request->input('faculty_period', '1month');
        $facultyStartDate = Carbon::now();

        switch ($facultyPeriod) {
            case '3months':
                $facultyStartDate->subMonths(3);
                break;
            case '1month':
            default:
                $facultyStartDate->subMonth();
                break;
        }

        $bookingsByFaculty = Booking::with('customer')
            ->where('created_at', '>=', $facultyStartDate)
            ->get()
            ->groupBy(function($booking) {
                return $booking->customer->faculty ?? 'Unspecified';
            })
            ->map(function($group) {
                return $group->count();
            })
            ->sortDesc()
            ->take(10);

        // --- 5. BOOKINGS BY COLLEGE ADDRESS ---
        $addressPeriod = $request->input('address_period', '1month');
        $addressStartDate = Carbon::now();

        switch ($addressPeriod) {
            case '3months':
                $addressStartDate->subMonths(3);
                break;
            case '1month':
            default:
                $addressStartDate->subMonth();
                break;
        }

        $bookingsByAddress = Booking::with('customer')
            ->where('created_at', '>=', $addressStartDate)
            ->get()
            ->groupBy(function($booking) {
                return $booking->customer->collegeAddress ?? 'Unspecified';
            })
            ->map(function($group) {
                return $group->count();
            })
            ->sortDesc()
            ->take(10);

        // --- 6. VEHICLE PROFITS CALCULATION (FIXED) ---
        $vehicleProfitFilter = $request->input('vehicle_profit_filter', 'all');
        $vehicleEarnings = 0;
        $vehicleCosts = 0;
        $vehicleNetProfit = 0;

        $allVehicles = Vehicle::all();

        if ($vehicleProfitFilter === 'all') {
            // Calculate for ALL vehicles
            $vehicleEarnings = Payment::sum('amount');
            $vehicleCosts = Maintenance::sum('cost') ?? 0;
        } else {
            // Calculate for SPECIFIC vehicle
            $vehicle = Vehicle::find($vehicleProfitFilter);
            
            if ($vehicle) {
                // Fix 1: Use 'vehicleID' (camelCase) to find bookings
                // Fix 2: Pluck 'bookingID' (not 'id') because Booking PK is bookingID
                $bookingIds = Booking::where('vehicleID', $vehicle->VehicleID)
                    ->pluck('bookingID');

                // Fix 3: Use 'bookingID' (camelCase) in Payment query
                $vehicleEarnings = Payment::whereIn('bookingID', $bookingIds)->sum('amount');

                // Fix 4: Use 'VehicleID' (PascalCase) for Maintenance query
                $vehicleCosts = Maintenance::where('VehicleID', $vehicle->VehicleID)->sum('cost') ?? 0;
            }
        }

        $vehicleNetProfit = $vehicleEarnings - $vehicleCosts;

        return view('staff.reports.index', compact(
            'totalIncome', 'chartLabels', 'chartData', 'chartTitle',
            'filterType', 'selectedDate', 'selectedMonth', 'selectedYear',
            'bookingOverview', 'bookingPeriod', 'bookingTitle',
            'bookingStatus', 'statusRaw', 'statusPeriod', 'topVehicles', 'vehiclePeriod', 'reviews',
            'bookingsByFaculty', 'facultyPeriod', 'bookingsByAddress', 'addressPeriod',
            'vehicleEarnings', 'vehicleCosts', 'vehicleNetProfit', 'vehicleProfitFilter', 'allVehicles'
        ));
    }

    public function exportToDrive(Request $request, GoogleDriveService $driveService)
    {
        try {
            // --- 1. GET USER SELECTION ---
            $reportType = $request->input('report_type', 'monthly');
            $selectedYear = $request->input('year', Carbon::now()->year);
            $selectedMonth = $request->input('month', Carbon::now()->month);

            if ($reportType === 'yearly') {
                $startDate = Carbon::createFromDate($selectedYear, 1, 1)->startOfYear();
                $endDate = Carbon::createFromDate($selectedYear, 12, 31)->endOfYear();
                $filenameLabel = "Annual_Report_{$selectedYear}";
                $periodLabel = "Year " . $selectedYear;
            } else {
                $startDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth();
                $endDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->endOfMonth();
                $filenameLabel = "Monthly_Report_" . $startDate->format('M_Y');
                $periodLabel = $startDate->format('F Y');
            }

            // --- 2. INCOME ANALYSIS (Graph Data) ---
            if ($reportType === 'yearly') {
                // For Year: Group by Month
                $incomeData = Payment::select(DB::raw('MONTH(created_at) as label_key'), DB::raw('sum(amount) as total'))
                    ->whereYear('created_at', $selectedYear)
                    ->groupBy('label_key')
                    ->orderBy('label_key', 'ASC')
                    ->get();
                
                // Map to Month Names
                $graphData = $incomeData->map(function($item) {
                    return [
                        'label' => Carbon::create()->month($item->label_key)->format('M'),
                        'value' => $item->total
                    ];
                });
            } else {
                // For Month: Group by Day
                $incomeData = Payment::select(DB::raw('DATE(created_at) as label_key'), DB::raw('sum(amount) as total'))
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->groupBy('label_key')
                    ->orderBy('label_key', 'ASC')
                    ->get();

                $graphData = $incomeData->map(function($item) {
                    return [
                        'label' => Carbon::parse($item->label_key)->format('d'),
                        'value' => $item->total
                    ];
                });
            }

            // Find max value for graph scaling
            $maxIncome = $graphData->max('value') ?? 1;

            // --- 3. VEHICLE PROFITS (All Vehicles + Individual Breakdown) ---
            $allVehicles = Vehicle::all();
            $vehicleProfits = [];
            $totalEarnings = 0;
            $totalCosts = 0;

            foreach ($allVehicles as $vehicle) {
                // Earnings: Payments for bookings of this vehicle in date range
                // Note: We filter bookings by created_at within range
                $bookings = Booking::where('vehicleID', $vehicle->VehicleID)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->pluck('bookingID');

                $earnings = Payment::whereIn('bookingID', $bookings)->sum('amount');

                // Costs: Maintenance for this vehicle in date range
                $costs = Maintenance::where('VehicleID', $vehicle->VehicleID)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('cost');

                $vehicleProfits[] = [
                    'plate' => $vehicle->plateNo,
                    'model' => $vehicle->model,
                    'earnings' => $earnings,
                    'costs' => $costs,
                    'profit' => $earnings - $costs
                ];

                $totalEarnings += $earnings;
                $totalCosts += $costs;
            }
            $totalNetProfit = $totalEarnings - $totalCosts;

            // --- 4. TOP VEHICLES ---
            $topVehicles = Booking::with('vehicle')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get()
                ->groupBy(function ($booking) { return $booking->vehicle->model ?? 'Unknown'; })
                ->map(function ($group) {
                    return [
                        'model' => $group->first()->vehicle->model ?? 'Unknown',
                        'count' => $group->count()
                    ];
                })
                ->sortByDesc('count')
                ->take(5);

            // --- 5. DEMOGRAPHICS (Faculty & College) ---
            $bookingsCollection = Booking::with('customer')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $byFaculty = $bookingsCollection->groupBy(function($b) { return $b->customer->faculty ?? 'Unknown'; })
                ->map(function($g) { return $g->count(); })->sortDesc();

            $byCollege = $bookingsCollection->groupBy(function($b) { return $b->customer->collegeAddress ?? 'Unknown'; })
                ->map(function($g) { return $g->count(); })->sortDesc();

            // --- 6. REVIEWS ---
            $reviews = Feedback::with(['booking.customer', 'booking.vehicle'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->latest()
                ->get();

            // --- GENERATE PDF ---
            $pdf = Pdf::loadView('staff.reports.pdf', compact(
                'periodLabel', 'graphData', 'maxIncome',
                'vehicleProfits', 'totalEarnings', 'totalCosts', 'totalNetProfit',
                'topVehicles', 'byFaculty', 'byCollege', 'reviews'
            ));
            
            // Allow PDF download stream for testing/debugging if needed, or upload to Drive
            $pdfContent = $pdf->output();
            $filename = "{$filenameLabel}_" . now()->format('His') . '.pdf';

            $folderId = env('GOOGLE_DRIVE_REPORTS');
            
            // Use the injected service to upload
            $link = $driveService->uploadFromString($pdfContent, $filename, $folderId);

            return back()->with('success', "Report generated and saved to Drive! Link: " . $link);

        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}