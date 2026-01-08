<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Management Report - {{ $periodLabel }}</title>
    <style>
        /* PAGE SETUP */
        @page { margin: 30px 40px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; font-size: 10px; margin: 0; padding: 0; line-height: 1.1; }

        /* HEADER */
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #4F46E5; padding-bottom: 8px; }
        .header h1 { margin: 0; font-size: 16px; color: #1F2937; text-transform: uppercase; }
        .header p { margin: 2px 0 0; color: #6B7280; font-size: 9px; }

        /* HEADINGS */
        h3 { 
            background-color: #EEF2FF; 
            color: #4F46E5; 
            padding: 4px 8px; 
            margin: 15px 0 10px 0; 
            border-radius: 4px; 
            font-size: 11px; 
            text-transform: uppercase; 
            page-break-after: avoid;
        }
        h4 { margin: 8px 0 4px; font-size: 10px; color: #374151; }

        /* UTILITY */
        .keep-together { page-break-inside: avoid; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-green { color: #10B981; }
        .text-red { color: #EF4444; }

        /* STANDARD TABLES */
        .std-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .std-table th { background-color: #F9FAFB; text-align: left; padding: 5px; border-bottom: 1px solid #D1D5DB; font-size: 8px; color: #4B5563; text-transform: uppercase; }
        .std-table td { padding: 5px; border-bottom: 1px solid #E5E7EB; vertical-align: middle; font-size: 9px; }

        /* --- HORIZONTAL GRAPH CSS --- */
        .graph-container {
            width: 100%;
            margin-bottom: 20px;
            page-break-inside: avoid; /* Keep graph together */
        }
        
        .h-graph-table { 
            width: 100%; 
            border-collapse: collapse; 
            border: none;
        }
        
        /* Label Column (Date) */
        .h-col-label { 
            width: 10%; 
            text-align: right; 
            padding-right: 8px; 
            font-size: 8px; 
            color: #6B7280; 
            border-right: 1px solid #9CA3AF; /* Axis Line */
            vertical-align: middle;
            height: 12px;
        }

        /* Bar Column */
        .h-col-bar { 
            width: 80%; 
            padding: 2px 0; 
            vertical-align: middle;
        }
        
        /* The Bar Itself */
        .h-bar { 
            background-color: #4F46E5; 
            height: 8px; /* Slim bars */
            border-radius: 0 2px 2px 0; 
            display: block;
        }

        /* Value Column */
        .h-col-value { 
            width: 10%; 
            text-align: left; 
            padding-left: 5px; 
            font-size: 8px; 
            font-weight: bold; 
            color: #111827; 
            vertical-align: middle;
        }

        .graph-meta { text-align: center; font-size: 8px; color: #9CA3AF; margin-top: 5px; font-style: italic; }

        /* KPI BOXES */
        .kpi-row { width: 100%; margin-bottom: 10px; font-size: 0; text-align: center; }
        .kpi-box { 
            width: 30%; 
            display: inline-block; 
            background: #F9FAFB; 
            border: 1px solid #E5E7EB; 
            border-radius: 4px; 
            padding: 6px 4px; 
            text-align: center; 
            vertical-align: top;
            margin: 0 1.5%; 
        }
        .kpi-val { font-size: 11px; font-weight: bold; color: #111827; margin-bottom: 1px; }
        .kpi-lbl { font-size: 7px; text-transform: uppercase; color: #6B7280; }

        /* COLUMNS */
        .row-container { width: 100%; margin-top: 5px; }
        .col-half { width: 49%; display: inline-block; vertical-align: top; }
        
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 8px; color: #9CA3AF; padding: 5px 0; border-top: 1px solid #E5E7EB; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Hasta Travel & Tours - Management Report</h1>
        <p>Period: <strong>{{ $periodLabel }}</strong> | Generated: {{ now()->toDayDateTimeString() }}</p>
    </div>

    <div class="keep-together">
        <h3>Income Analysis</h3>
        
        <div class="graph-container">
            <table class="h-graph-table">
                @foreach($graphData as $data)
                @php 
                    $pct = $maxIncome > 0 ? ($data['value'] / $maxIncome) * 100 : 0;
                    // Ensure bar shows at least a sliver if value > 0
                    if($data['value'] > 0 && $pct < 1) $pct = 1;
                @endphp
                <tr>
                    <td class="h-col-label">{{ $data['label'] }}</td>
                    <td class="h-col-bar">
                        <div class="h-bar" style="width: {{ $pct }}%;"></div>
                    </td>
                    <td class="h-col-value">
                        @if($data['value'] > 0)
                            {{ number_format($data['value']) }}
                        @else
                            <span style="color: #ccc;">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </table>
            <div class="graph-meta">Total Revenue Peak: RM {{ number_format($maxIncome, 2) }}</div>
        </div>
    </div>

    <div class="keep-together">
        <h3>Vehicle Profits Overview</h3>
        <div class="kpi-row">
            <div class="kpi-box">
                <div class="kpi-val text-green">RM {{ number_format($totalEarnings, 2) }}</div>
                <div class="kpi-lbl">Total Earnings</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-val text-red">RM {{ number_format($totalCosts, 2) }}</div>
                <div class="kpi-lbl">Total Maintenance</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-val" style="color: {{ $totalNetProfit >= 0 ? '#3B82F6' : '#EF4444' }}">
                    RM {{ number_format($totalNetProfit, 2) }}
                </div>
                <div class="kpi-lbl">Net Profit</div>
            </div>
        </div>

        <h4>Individual Vehicle Performance</h4>
        <table class="std-table">
            <thead>
                <tr>
                    <th style="width: 25%">Model</th>
                    <th style="width: 15%">Plate</th>
                    <th class="text-right" style="width: 20%">Earn</th>
                    <th class="text-right" style="width: 20%">Cost</th>
                    <th class="text-right" style="width: 20%">Net</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vehicleProfits as $v)
                <tr>
                    <td>{{ $v['model'] }}</td>
                    <td>{{ $v['plate'] }}</td>
                    <td class="text-right text-green">{{ number_format($v['earnings'], 2) }}</td>
                    <td class="text-right text-red">{{ number_format($v['costs'], 2) }}</td>
                    <td class="text-right font-bold" style="color: {{ $v['profit'] >= 0 ? '#111827' : '#EF4444' }}">
                        {{ number_format($v['profit'], 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="row-container">
        <div class="col-half" style="margin-right: 1%">
            <h3>Top Vehicles</h3>
            <table class="std-table">
                <thead>
                    <tr>
                        <th>Model</th>
                        <th class="text-right">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topVehicles as $data)
                    <tr>
                        <td>{{ $data['model'] }}</td>
                        <td class="text-right font-bold">{{ $data['count'] }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="text-center">No data.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <h3>College</h3>
            <table class="std-table">
                <thead>
                    <tr>
                        <th>Location</th>
                        <th class="text-right">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($byCollege as $col => $count)
                    <tr>
                        <td>{{ Str::limit($col, 18) }}</td>
                        <td class="text-right font-bold">{{ $count }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="text-center">No data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="col-half">
            <h3>Faculty</h3>
            <table class="std-table">
                <thead>
                    <tr>
                        <th>Faculty</th>
                        <th class="text-right">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($byFaculty as $fac => $count)
                    <tr>
                        <td>{{ Str::limit($fac, 22) }}</td>
                        <td class="text-right">{{ $count }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="text-center">No data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <h3>Recent Reviews</h3>
    <table class="std-table">
        <thead>
            <tr>
                <th style="width: 15%">Date</th>
                <th style="width: 20%">Customer</th>
                <th style="width: 15%">Car</th>
                <th style="width: 10%">Star</th>
                <th style="width: 40%">Comment</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reviews as $review)
            <tr>
                <td>{{ $review->created_at->format('d/m') }}</td>
                <td>{{ Str::limit($review->booking->customer->fullName ?? 'Guest', 15) }}</td>
                <td>{{ Str::limit($review->booking->vehicle->model ?? 'N/A', 10) }}</td>
                <td>{{ $review->rating }}</td>
                <td style="font-style: italic; color: #555;">"{{ Str::limit($review->comment, 50) }}"</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center" style="padding: 10px;">No reviews.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated by System | Hasta Travel & Tours
    </div>

</body>
</html>