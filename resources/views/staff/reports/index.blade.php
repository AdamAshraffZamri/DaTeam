@extends('layouts.staff')

@section('title', 'Reporting & Analysis')

@section('content')

{{-- START: Alert Block --}}
    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
            <span class="font-medium">Success!</span> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
            <span class="font-medium">Error!</span> {{ session('error') }}
        </div>
    @endif
{{-- END: Alert Block --}}

<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Reporting & Analysis</h1>
        
        <form action="{{ route('staff.reports.export') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i class="fab fa-google-drive me-2"></i> Save Report to Drive
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-gray-500 text-sm font-medium uppercase">Daily Income</p>
                    <p class="text-3xl font-bold text-gray-800">${{ number_format($dailyIncome, 2) }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-dollar-sign text-green-500 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-gray-400 mt-2">Total for {{ now()->toFormattedDateString() }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-gray-500 text-sm font-medium uppercase">Monthly Income</p>
                    <p class="text-3xl font-bold text-gray-800">${{ number_format($monthlyIncome, 2) }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-chart-line text-blue-500 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-gray-400 mt-2">Revenue for {{ now()->format('F Y') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6 lg:col-span-1">
            <h3 class="text-lg font-semibold mb-4">Booking Status</h3>
            <div id="statusChart" class="h-64"></div> 
            <div class="mt-4 space-y-2">
                @foreach($bookingStatus as $status => $count)
                    <div class="flex justify-between text-sm">
                        <span class="capitalize text-gray-600">{{ $status }}</span>
                        <span class="font-bold">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 lg:col-span-2">
            <h3 class="text-lg font-semibold mb-4">Income Overview (Last 7 Days)</h3>
            <div id="revenueChart" class="h-80"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        
        <div class="bg-white rounded-lg shadow overflow-hidden lg:col-span-2">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800">Booking Overview (7 Days)</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-4 sm:grid-cols-7 gap-2 text-center">
                    @foreach($bookingOverview as $data)
                        <div class="flex flex-col items-center p-2 bg-indigo-50 rounded-lg">
                            <span class="text-[11px] font-bold text-gray-500 uppercase">
                                {{ \Carbon\Carbon::parse($data->date)->format('D, d M') }}
                            </span>
                            
                            <span class="text-xl font-bold text-indigo-600 mt-1">{{ $data->count }}</span>
                        </div>
                    @endforeach
                    
                    @if($bookingOverview->isEmpty())
                        <div class="col-span-7 text-gray-500 py-4 text-sm">No bookings recorded.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden lg:col-span-1">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800">Top Vehicles</h3>
            </div>
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Model</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($topVehicles as $vehicle)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 truncate max-w-[120px]" title="{{ $vehicle['model'] }}">
                                {{ $vehicle['model'] }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-600">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $vehicle['total_bookings'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-4 text-center text-sm text-gray-500">No bookings.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold">Latest Customer Reviews</h3>
        </div>
        <table class="min-w-full">
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($reviews as $review)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $review->booking->customer->fullName ?? 'Guest' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-500">{{ $review->booking->vehicle->model ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex text-yellow-400">
                                @for($i = 0; $i < 5; $i++)
                                    <span>{{ $i < $review->rating ? '★' : '☆' }}</span>
                                @endfor
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            "{{ Str::limit($review->comment, 60) }}"
                        </td>
                        <td class="px-6 py-4 text-right text-sm text-gray-500">
                            {{ $review->created_at->diffForHumans() }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No reviews found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // --- REVENUE CHART ---
        var revenueOptions = {
            series: [{
                name: 'Revenue (RM)',
                data: @json($revenueData->pluck('total'))
            }],
            chart: { 
                type: 'area', 
                height: 320, 
                toolbar: { show: false } 
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            xaxis: {
                categories: @json($revenueData->pluck('date')),
                labels: { format: 'dd/MM' }
            },
            colors: ['#3B82F6'],
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.2, stops: [0, 90, 100] }
            },
            tooltip: { y: { formatter: function (val) { return "$" + val.toFixed(2) } } }
        };
        
        if(document.querySelector("#revenueChart")) {
            new ApexCharts(document.querySelector("#revenueChart"), revenueOptions).render();
        }

        // --- STATUS CHART ---
        var statusOptions = {
            series: @json($statusRaw->pluck('count')),
            labels: @json($statusRaw->pluck('bookingStatus')),
            chart: { type: 'donut', height: 280 },
            colors: ['#10B981', '#F59E0B', '#EF4444', '#3B82F6'],
            plotOptions: {
                pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total' } } } }
            },
            legend: { position: 'bottom' }
        };

        if(document.querySelector("#statusChart")) {
            new ApexCharts(document.querySelector("#statusChart"), statusOptions).render();
        }
    });
</script>
@endsection