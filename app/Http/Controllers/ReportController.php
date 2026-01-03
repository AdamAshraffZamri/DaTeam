<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Booking;
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
        // 1. REVENUE (Last 6 Months)
        $revenueData = Payment::select(
            DB::raw("DATE_FORMAT(transactionDate, '%Y-%m') as month"), 
            DB::raw('SUM(amount) as total')
        )
        ->where('paymentStatus', 'Verified') 
        ->where('transactionDate', '>=', Carbon::now()->subMonths(6))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        // 2. FLEET POPULARITY (Top 5 Vehicles)
        $popularVehicles = Booking::select('vehicleID', DB::raw('count(*) as count'))
            ->whereIn('bookingStatus', ['Completed', 'Active'])
            ->with('vehicle')
            ->groupBy('vehicleID')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // 3. BOOKING STATUS (Pie Chart Data)
        $statusStats = Booking::select('bookingStatus', DB::raw('count(*) as total'))
            ->groupBy('bookingStatus')
            ->get();

        return view('staff.reports.index', compact('revenueData', 'popularVehicles', 'statusStats'));
    }

    public function exportToDrive()
    {
        try {
            // 1. Prepare Data
            $data = [
                'date' => now()->format('d M Y'),
                'total_revenue' => Payment::where('paymentStatus', 'Verified')->sum('amount'),
                'total_bookings' => Booking::count(),
                'bookings' => Booking::with(['customer', 'vehicle'])->latest()->take(20)->get()
            ];

            // 2. Generate PDF
            $pdf = Pdf::loadView('staff.reports.pdf', $data);
            $pdfContent = $pdf->output();
            
            $filename = 'Monthly_Report_' . now()->format('F_Y') . '_' . time() . '.pdf';

            // ---------------------------------------------------------
            // DIRECT GOOGLE UPLOAD (OAuth + SSL Fix)
            // ---------------------------------------------------------
            
            // A. Setup Client using REFRESH TOKEN from .env
            $client = new Client();
            $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
            $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
            $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
            
            // B. FIX: Disable SSL Verify to prevent cURL Error 77
            // This tells the system to ignore the missing Laragon certificate
            $httpClient = new GuzzleClient(['verify' => false]);
            $client->setHttpClient($httpClient);

            $service = new Drive($client);

            // C. Define File Metadata
            $folderId = env('GOOGLE_DRIVE_FOLDER_ID');
            $fileMetadata = new DriveFile([
                'name' => $filename,
                'parents' => [$folderId] 
            ]);

            // D. Upload File
            $service->files->create($fileMetadata, [
                'data' => $pdfContent,
                'mimeType' => 'application/pdf',
                'uploadType' => 'multipart'
            ]);

            return back()->with('success', 'Report uploaded successfully! (User Mode)');

        } catch (\Exception $e) {
            return back()->with('error', 'Upload Failed: ' . $e->getMessage());
        }
    }
}