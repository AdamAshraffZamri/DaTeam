<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Management Report</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; font-size: 12px; }
        .header { margin-bottom: 25px; border-bottom: 2px solid #3B82F6; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #111827; font-size: 24px; }
        .header p { margin: 5px 0 0; color: #6B7280; }

        /* KPI Cards */
        .kpi-container { width: 100%; margin-bottom: 30px; overflow: hidden; }
        .kpi-card { width: 48%; float: left; padding: 15px; background-color: #F3F4F6; border-radius: 5px; box-sizing: border-box; }
        .kpi-card.green { border-left: 5px solid #10B981; margin-right: 4%; }
        .kpi-card.blue { border-left: 5px solid #3B82F6; }
        .kpi-title { font-size: 10px; text-transform: uppercase; color: #6B7280; font-weight: bold; }
        .kpi-value { font-size: 20px; font-weight: bold; color: #1F2937; margin-top: 5px; }
        .kpi-sub { font-size: 10px; color: #9CA3AF; margin-top: 5px; }

        /* Tables */
        h3 { color: #374151; border-bottom: 1px solid #E5E7EB; padding-bottom: 5px; margin-top: 25px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background-color: #F9FAFB; text-align: left; padding: 8px; border-bottom: 1px solid #E5E7EB; color: #6B7280; text-transform: uppercase; font-size: 10px; }
        td { padding: 8px; border-bottom: 1px solid #E5E7EB; color: #4B5563; }
        .text-right { text-align: right; }
        .clear { clear: both; }
        .badge { font-weight: bold; text-transform: capitalize; }
        
        .trend-table th { background-color: #EEF2FF; color: #4F46E5; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporting & Analysis</h1>
        <p>Generated on: {{ now()->toDayDateTimeString() }} | Staff: {{ auth()->user()->name ?? 'System' }}</p>
    </div>

    <div class="kpi-container">
        <div class="kpi-card green">
            <div class="kpi-title">Daily Income</div>
            <div class="kpi-value">RM {{ number_format($dailyIncome, 2) }}</div>
            <div class="kpi-sub">Date: {{ now()->toFormattedDateString() }}</div>
        </div>
        <div class="kpi-card blue">
            <div class="kpi-title">Monthly Income</div>
            <div class="kpi-value">RM {{ number_format($monthlyIncome, 2) }}</div>
            <div class="kpi-sub">Month: {{ now()->format('F Y') }}</div>
        </div>
    </div>
    <div class="clear"></div>

    <h3>Booking Status Summary</h3>
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th>Description</th>
                <th class="text-right">Total Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookingStatus as $status => $count)
            <tr>
                <td class="badge">{{ $status }}</td>
                <td style="color: #999; font-size: 10px;">Current active {{ $status }} bookings</td>
                <td class="text-right"><strong>{{ $count }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Monthly Performance Overview</h3>
    <table style="width: 100%; border: none;">
        <tr style="vertical-align: top;">
            
            <td style="width: 48%; padding-right: 15px; border: none;">
                <h4 style="margin-top: 0; margin-bottom: 5px; color: #374151;">Booking Trend (7 Days)</h4>
                <table class="trend-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th class="text-right">New Bookings</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookingOverview as $data)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($data->date)->format('D, M d') }}</td>
                            <td class="text-right"><strong>{{ $data->count }}</strong></td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center">No bookings last 7 days</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </td>

            <td style="width: 48%; padding-left: 15px; border: none;">
                <h4 style="margin-top: 0; margin-bottom: 5px; color: #374151;">Top Rented Vehicles</h4>
                <table class="trend-table">
                    <thead>
                        <tr>
                            <th>Vehicle Model</th>
                            <th class="text-right">Bookings</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Loop through the PHP Collection --}}
                        @forelse($topVehicles as $vehicleData)
                        <tr>
                            <td>{{ $vehicleData['model'] }}</td>
                            <td class="text-right"><strong>{{ $vehicleData['total_bookings'] }}</strong></td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center">No bookings found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <h3>Detailed Financial Breakdown</h3>
    <table style="width: 100%; border: none;">
        <tr style="vertical-align: top;">
            <td style="width: 48%; padding-right: 15px; border: none;">
                <h4 style="margin-top: 0; color: #6B7280; font-size: 11px;">Daily Income ({{ now()->format('F') }})</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dailyBreakdown as $day)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($day->date)->format('d M') }}</td>
                            <td class="text-right">RM {{ number_format($day->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
            <td style="width: 48%; padding-left: 15px; border: none;">
                <h4 style="margin-top: 0; color: #6B7280; font-size: 11px;">Monthly Income ({{ now()->year }})</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthlyBreakdown as $month)
                        <tr>
                            <td>{{ \Carbon\Carbon::create()->month($month->month)->format('F') }}</td>
                            <td class="text-right">RM {{ number_format($month->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <h3>Latest Customer Reviews</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Customer</th>
                <th style="width: 15%;">Car</th>
                <th style="width: 10%;">Rating</th>
                <th style="width: 40%;">Comment</th>
                <th style="width: 15%; text-align: right;">Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reviews as $review)
            <tr>
                <td>{{ $review->booking->customer->fullName ?? 'Guest' }}</td>
                <td>{{ $review->booking->vehicle->model ?? 'N/A' }}</td>
                <td><strong>{{ $review->rating }}/5</strong></td>
                <td>"{{ \Illuminate\Support\Str::limit($review->comment, 60) }}"</td>
                <td class="text-right">{{ $review->created_at->format('d M, Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align: center; padding: 20px;">No reviews found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 40px; border-top: 1px solid #ddd; padding-top: 10px; text-align: center; color: #999; font-size: 10px;">
        &copy; {{ date('Y') }} Hasta Travel & Tours. This is a computer-generated document.
    </div>
</body>
</html>