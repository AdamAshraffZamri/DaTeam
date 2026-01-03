    @extends('layouts.staff')

@section('title', 'Reporting & Analysis')

@section('content')
<div class="row g-5 g-xl-8">
    
    <div class="col-12 flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Analytics Dashboard</h1>
        <form action="{{ route('staff.reports.export') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i class="fab fa-google-drive me-2"></i> Save Report to Drive
            </button>
        </form>
    </div>

    <div class="col-xl-6">
        <div class="card card-xl-stretch mb-xl-8">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-col">
                    <span class="card-label fw-bolder fs-3 text-dark">Monthly Revenue</span>
                    <span class="text-muted mt-1 fw-bold fs-7">Verified payments only</span>
                </h3>
            </div>
            <div class="card-body">
                <div id="revenueChart" style="height: 350px;"></div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card card-xl-stretch mb-xl-8">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-col">
                    <span class="card-label fw-bolder fs-3 text-dark">Booking Status</span>
                </h3>
            </div>
            <div class="card-body">
                <div id="statusChart" style="height: 350px;"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // --- 1. REVENUE CHART CONFIG ---
        var revenueOptions = {
            series: [{
                name: 'Revenue (RM)',
                data: @json($revenueData->pluck('total'))
            }],
            chart: { type: 'area', height: 350, toolbar: { show: false } },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth' },
            xaxis: {
                categories: @json($revenueData->pluck('month')),
            },
            colors: ['#009ef7'], // Metronic Blue
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.9, stops: [0, 90, 100] }
            }
        };
        new ApexCharts(document.querySelector("#revenueChart"), revenueOptions).render();

        // --- 2. STATUS CHART CONFIG ---
        var statusOptions = {
            series: @json($statusStats->pluck('total')),
            labels: @json($statusStats->pluck('bookingStatus')),
            chart: { type: 'donut', height: 350 },
            colors: ['#50cd89', '#f1416c', '#ffc700', '#7239ea', '#009ef7'], // Metronic Colors
            plotOptions: {
                pie: { donut: { labels: { show: true, total: { show: true, label: 'Total' } } } }
            }
        };
        new ApexCharts(document.querySelector("#statusChart"), statusOptions).render();
    });
</script>
@endsection