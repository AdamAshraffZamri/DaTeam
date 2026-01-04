<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\Feedback;;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Google\Client; 
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use GuzzleHttp\Client as GuzzleClient; 

class ReportController extends Controller
{
    public function index()
    {
        // 1. Daily Income
        $dailyIncome = Payment::whereDate('created_at', Carbon::today())->sum('amount');

        // 2. Monthly Income
        $monthlyIncome = Payment::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');

        // 3. Booking Status
        $statusRaw = Booking::select('bookingStatus', DB::raw('count(*) as count'))
            ->groupBy('bookingStatus')
            ->get();
        $bookingStatus = $statusRaw->pluck('count', 'bookingStatus'); 

        // 4. Booking Overview (Last 7 Days)
        $startDate = Carbon::now()->subDays(6);
        $bookingOverview = Booking::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        // 5. Revenue Data (for Chart)
        $revenueData = Payment::select(DB::raw('DATE(created_at) as date'), DB::raw('sum(amount) as total'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        // 6. (NEW) Top Rented Vehicles (Same logic as PDF)
        $monthBookings = Booking::with('vehicle')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
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
            ->take(5);

        // 7. Reviews
        $reviews = Feedback::with(['booking.customer', 'booking.vehicle'])
            ->latest()
            ->limit(5)
            ->get();

        return view('staff.reports.index', compact(
            'dailyIncome', 
            'monthlyIncome', 
            'bookingStatus', 
            'statusRaw', 
            'bookingOverview', 
            'revenueData', 
            'topVehicles', // <--- Pass the new data
            'reviews'
        ));
    }

    public function exportToDrive()
    {
        try {
            // --- 1. PREPARE DATA ---

            // A. KPI Cards
            $dailyIncome = Payment::whereDate('created_at', Carbon::today())->sum('amount');
            $monthlyIncome = Payment::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('amount');

            // B. Booking Status Summary
            $bookingStatus = Booking::select('bookingStatus', DB::raw('count(*) as count'))
                ->groupBy('bookingStatus')
                ->pluck('count', 'bookingStatus');

            // C. Booking Overview (Last 7 Days)
            $startDate = Carbon::now()->subDays(6);
            $bookingOverview = Booking::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->where('created_at', '>=', $startDate)
                ->groupBy('date')
                ->orderBy('date', 'ASC')
                ->get();

            // D. (FIXED) Top Rented Vehicles (Collection Method)
            // 1. Fetch all bookings for this month with the vehicle data
            $monthBookings = Booking::with('vehicle')
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->get();

            // 2. Group by Vehicle Model and Count in PHP (Bypasses the "Unknown Column" error)
            $topVehicles = $monthBookings->groupBy(function ($booking) {
                    // Get the vehicle model name safely
                    return $booking->vehicle->model ?? 'Unknown Vehicle';
                })
                ->map(function ($group) {
                    // Count how many bookings in this group
                    return [
                        'model' => $group->first()->vehicle->model ?? 'Unknown', // Car Name
                        'total_bookings' => $group->count() // Total Count
                    ];
                })
                ->sortByDesc('total_bookings') // Sort Highest to Lowest
                ->take(5); // Take Top 5

            // E. Detailed Breakdowns
            $dailyBreakdown = Payment::select(DB::raw('DATE(created_at) as date'), DB::raw('sum(amount) as total'))
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->groupBy('date')
                ->orderBy('date', 'ASC')
                ->get();

            $monthlyBreakdown = Payment::select(DB::raw('MONTH(created_at) as month'), DB::raw('sum(amount) as total'))
                ->whereYear('created_at', Carbon::now()->year)
                ->groupBy('month')
                ->orderBy('month', 'ASC')
                ->get();

            // F. Reviews
            $reviews = Feedback::with(['booking.customer', 'booking.vehicle'])
                ->latest()
                ->limit(10)
                ->get();

            // --- 2. GENERATE PDF ---
            $pdf = Pdf::loadView('staff.reports.pdf', compact(
                'dailyIncome', 
                'monthlyIncome', 
                'bookingStatus', 
                'bookingOverview',
                'topVehicles',    
                'dailyBreakdown', 
                'monthlyBreakdown', 
                'reviews'
            ));
            
            $pdfContent = $pdf->output();
            $filename = 'Management_Report_' . now()->format('Y-m-d_H-i') . '.pdf';

            // --- 3. GOOGLE DRIVE UPLOAD ---
            $client = new Client();
            $httpClient = new GuzzleClient([
                'verify' => false,
                'curl' => [
                    CURLOPT_CAINFO => __FILE__, 
                    CURLOPT_SSL_VERIFYPEER => false,
                ]
            ]);
            $client->setHttpClient($httpClient);
            $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
            $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
            $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
            
            $service = new Drive($client);
            $folderId = env('GOOGLE_DRIVE_FOLDER_ID');
            $fileMetadata = new DriveFile([
                'name' => $filename,
                'parents' => [$folderId] 
            ]);

            $service->files->create($fileMetadata, [
                'data' => $pdfContent,
                'mimeType' => 'application/pdf',
                'uploadType' => 'multipart'
            ]);

            return back()->with('success', 'Report successfully generated and saved to Google Drive!');

        } catch (\Exception $e) {
            return back()->with('error', 'Drive Upload Failed: ' . $e->getMessage());
        }
    }
}