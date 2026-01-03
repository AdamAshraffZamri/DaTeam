<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf; // Requires 'composer require barryvdh/laravel-dompdf'

class ReportController extends Controller
{
    public function index()
    {
        // 1. REVENUE (Last 6 Months) - Grouped by Month
        $revenueData = Payment::select(
            DB::raw("DATE_FORMAT(transactionDate, '%Y-%m') as month"), 
            DB::raw('SUM(amount) as total')
        )
        ->where('paymentStatus', 'Verified') // Only counted verified payments
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

            // 2. Generate PDF (Now inside the try block to catch errors)
            $pdf = Pdf::loadView('staff.reports.pdf', $data);
            
            // 3. Define Filename
            $filename = 'Monthly_Report_' . now()->format('F_Y') . '.pdf';

            // 4. Upload to Google Drive
            Storage::disk('google')->put($filename, $pdf->output());

            return back()->with('success', 'Report successfully uploaded to Google Drive!');

        } catch (\Exception $e) {
            // This will now catch PDF errors AND Google Drive errors
            return back()->with('error', 'Export Failed: ' . $e->getMessage());
        }
    }
}