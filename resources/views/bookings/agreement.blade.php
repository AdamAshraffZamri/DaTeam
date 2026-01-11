@extends('layouts.app')

@section('content')
{{-- Print Styles --}}
<style>
    @media print {
        /* Hide everything by default */
        body * {
            visibility: hidden;
        }
        /* Hide navbar, footer, background, and other layout elements explicitly */
        nav, footer, .fixed, header, .z-40, .z-50, button {
            display: none !important;
        }
        
        /* Show only the agreement container */
        #printable-agreement, #printable-agreement * {
            visibility: visible;
        }

        /* Position the agreement at the very top */
        #printable-agreement {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            background: white;
            box-shadow: none !important;
        }

        /* Ensure background colors/borders print */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>

<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/90 via-black/80 to-black/95"></div>
</div>

<div class="relative z-10 py-6">
    <div class="container mx-auto px-2 max-w-4xl">
        
        {{-- Back Button --}}
        <button onclick="window.close()" class="inline-flex items-center text-gray-400 hover:text-white mb-4 transition print:hidden">
            <i class="fas fa-times mr-2"></i> Close Window
        </button>

        {{-- ID added here for Print targeting --}}
        <div id="printable-agreement" class="bg-white text-black shadow-2xl overflow-hidden relative print:shadow-none print:w-full">
            
            {{-- Print Button (Hidden when printing) --}}
            <div class="absolute top-4 right-4 print:hidden">
                <button onclick="window.print()" class="bg-blue-900 text-white px-4 py-2 rounded shadow hover:bg-blue-800 text-sm">
                    <i class="fas fa-print mr-2"></i> Print / Save PDF
                </button>
            </div>

            {{-- Compact Padding for Single Page --}}
            <div class="p-6 space-y-4 font-serif text-xs">
                
                {{-- Header Section --}}
                <div class="grid grid-cols-2 gap-4 border-b-2 border-black pb-2">
                    <div>
                        <h1 class="text-xl font-bold uppercase tracking-wider mb-1">AGREEMENT FORM</h1>
                        <h2 class="text-lg font-bold text-blue-900">HASTA</h2>
                        <div class="text-[10px] space-y-0 text-gray-700 mt-1 leading-tight">
                            <p class="font-bold">HASTA TRAVEL & TOURS SDN. BHD. (1359376-T)</p>
                            <p>7A, JALAN KEBUDAYAAN 1A, TAMAN UNIVERSITI, 81310 SKUDAI, JOHOR</p>
                            <p>Office: +6011-10900700</p>
                        </div>
                    </div>
                    <div class="text-right flex flex-col justify-between">
                        <div>
                            <p class="font-bold text-sm">HASTA TRAVEL & TOURS SDN.BHD.</p>
                        </div>
                        <div class="mt-1">
                            <p class="text-[10px] uppercase font-bold text-gray-500">Invoice Number</p>
                            <p class="text-base font-bold">
                                #{{ ($booking->created_at ?? now())->format('Y-m') }}-HASTA/{{ $booking->bookingID == 'PENDING' ? 'DRAFT' : 'INV'.str_pad($booking->id ?? 0, 6, '0', STR_PAD_LEFT) }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Top Section Grid: Usage, Customer, Car, Pricing --}}
                <div class="space-y-3">
                    {{-- Row 1: Usage Details (Full Width) --}}
                    <div>
                        <h3 class="font-bold uppercase border-b border-gray-300 mb-1 pb-0 text-[10px]">Usage Details</h3>
                        <table class="w-full border-collapse border border-gray-300 text-[10px]">
                            <tr class="bg-gray-50">
                                <td class="border border-gray-300 p-1 font-bold w-1/5">Vehicle</td>
                                <td class="border border-gray-300 p-1">{{ $booking->vehicle->model }} ({{ $booking->vehicle->plateNo }})</td>
                                <td class="border border-gray-300 p-1 font-bold w-1/5">Duration</td>
                                <td class="border border-gray-300 p-1">
                                    @php
                                        // Robust Time Calculation
                                        $pDateVal = $booking->originalDate ?? request('pickup_date') ?? now();
                                        $rDateVal = $booking->returnDate ?? request('return_date') ?? now();
                                        $pTimeVal = $booking->bookingTime ?? request('pickup_time') ?? '10:00';
                                        $rTimeVal = $booking->returnTime ?? request('return_time') ?? '10:00';
                                        
                                        $start = \Carbon\Carbon::parse(\Carbon\Carbon::parse($pDateVal)->format('Y-m-d') . ' ' . $pTimeVal);
                                        $end = \Carbon\Carbon::parse(\Carbon\Carbon::parse($rDateVal)->format('Y-m-d') . ' ' . $rTimeVal);
                                        $diff = $start->diff($end);
                                    @endphp
                                    {{ $diff->d }}d {{ $diff->h }}h {{ $diff->i }}m
                                </td>
                            </tr>
                            <tr>
                                <td class="border border-gray-300 p-1 font-bold">Pick Up</td>
                                <td class="border border-gray-300 p-1">
                                    {{ $start->format('d-m-Y') }} 
                                    @ {{ $start->format('H:i') }}
                                    <span class="text-gray-500 block text-[9px]">Loc: {{ $booking->pickupLocation }}</span>
                                </td>
                                <td class="border border-gray-300 p-1 font-bold">Return</td>
                                <td class="border border-gray-300 p-1">
                                    {{ $end->format('d-m-Y') }} 
                                    @ {{ $end->format('H:i') }}
                                    <span class="text-gray-500 block text-[9px]">Loc: {{ $booking->returnLocation }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="border border-gray-300 p-1 font-bold">Note</td>
                                <td class="border border-gray-300 p-1" colspan="3">{{ $booking->remarks ?? 'No additional notes.' }}</td>
                            </tr>
                        </table>
                    </div>

                    {{-- Row 2: 3 Columns for Details and Pricing --}}
                    <div class="grid grid-cols-3 gap-4 text-[10px]">
                        
                        {{-- Customer Details --}}
                        <div>
                            <h3 class="font-bold uppercase border-b border-gray-300 mb-1 pb-0">Customer</h3>
                            <table class="w-full">
                                {{-- FIX: Changed 'name' to 'fullName' --}}
                                <tr><td class="py-0 font-bold text-gray-600 w-1/3">Name:</td><td class="py-0 truncate">{{ $booking->customer->fullName }}</td></tr>
                                <tr><td class="py-0 font-bold text-gray-600">IC/Pass:</td><td class="py-0">{{ $booking->customer->ic_passport ?? 'N/A' }}</td></tr>
                                <tr><td class="py-0 font-bold text-gray-600">Mobile:</td><td class="py-0">{{ $booking->customer->phoneNo ?? 'N/A' }}</td></tr>
                            </table>
                        </div>

                        {{-- Car Information --}}
                        <div>
                            <h3 class="font-bold uppercase border-b border-gray-300 mb-1 pb-0">Vehicle Info</h3>
                            <table class="w-full">
                                <tr><td class="py-0 font-bold text-gray-600 w-1/3">Model:</td><td class="py-0">{{ $booking->vehicle->model }}</td></tr>
                                <tr><td class="py-0 font-bold text-gray-600">Plate:</td><td class="py-0 font-bold border border-black px-1 inline-block">{{ $booking->vehicle->plateNo }}</td></tr>
                                <tr><td class="py-0 font-bold text-gray-600">Color:</td><td class="py-0">{{ $booking->vehicle->color ?? 'N/A' }}</td></tr>
                            </table>
                        </div>

                        {{-- Pricing Table --}}
                        <div>
                            <h3 class="font-bold uppercase border-b border-gray-300 mb-1 pb-0">Payment</h3>
                            <table class="w-full border-collapse border border-gray-300">
                                <tr>
                                    <td class="border border-gray-300 p-1">Rental</td>
                                    <td class="border border-gray-300 p-1 text-right">{{ number_format($booking->totalCost ?? 0, 2) }}</td>
                                </tr>
                                <tr class="font-bold bg-gray-50">
                                    <td class="border border-gray-300 p-1">Total</td>
                                    <td class="border border-gray-300 p-1 text-right">{{ number_format($booking->totalCost ?? 0, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- T&C Section (No Page Break) --}}
                <div class="border-t border-black pt-2">
                    <div class="text-center mb-2">
                        <h3 class="text-sm font-bold uppercase underline">Rental Agreement Terms</h3>
                        <p class="text-[8px] font-bold">HASTA TRAVEL & TOURS SDN. BHD. (1359376T) | KPK/LN 10181</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-2">
                        {{-- Table 1: Price List --}}
                        <div>
                            <p class="text-[8px] font-bold text-center mb-0.5">Table 1: Overtime Rates (RM)</p>
                            <table class="w-full text-[8px] border-collapse border border-black text-center">
                                <tr class="bg-gray-200">
                                    <th class="border border-black p-0.5">HOUR</th>
                                    <th class="border border-black p-0.5">1</th>
                                    <th class="border border-black p-0.5">3</th>
                                    <th class="border border-black p-0.5">5</th>
                                    <th class="border border-black p-0.5">12</th>
                                    <th class="border border-black p-0.5">24</th>
                                </tr>
                                <tr>
                                    <td class="border border-black p-0.5 font-bold">AXIA</td>
                                    <td class="border border-black p-0.5">30</td>
                                    <td class="border border-black p-0.5">50</td>
                                    <td class="border border-black p-0.5">60</td>
                                    <td class="border border-black p-0.5">80</td>
                                    <td class="border border-black p-0.5">110</td>
                                </tr>
                                <tr>
                                    <td class="border border-black p-0.5 font-bold">SEDAN</td>
                                    <td class="border border-black p-0.5">35</td>
                                    <td class="border border-black p-0.5">55</td>
                                    <td class="border border-black p-0.5">65</td>
                                    <td class="border border-black p-0.5">85</td>
                                    <td class="border border-black p-0.5">130</td>
                                </tr>
                            </table>
                        </div>

                        {{-- Table 2: Excess Fee --}}
                        <div>
                            <p class="text-[8px] font-bold text-center mb-0.5">Table 2: Excess Fee</p>
                            <table class="w-full text-[8px] border-collapse border border-black">
                                <tr class="bg-gray-200">
                                    <th class="border border-black p-0.5 text-left">TYPE</th>
                                    <th class="border border-black p-0.5 text-right">FEE (RM)</th>
                                </tr>
                                <tr>
                                    <td class="border border-black p-0.5">AXIA</td>
                                    <td class="border border-black p-0.5 text-right">2,000</td>
                                </tr>
                                <tr>
                                    <td class="border border-black p-0.5">MYVI / BEZZA / SAGA</td>
                                    <td class="border border-black p-0.5 text-right">2,500</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- Full Terms Text (2 Columns) --}}
                    <div class="text-[8px] leading-tight text-justify grid grid-cols-2 gap-x-4 gap-y-1 font-serif">
                        <div>
                            <strong class="uppercase">Rates:</strong>
                            Min 1-hour RM30. >12 hours = 1 day. Rates include 300km/day limit and breakdown replacement only for maintenance issues.
                        </div>
                        <div>
                            <strong class="uppercase">Requirements:</strong>
                            Driver age 19-55. Valid License (No P license).
                        </div>
                        <div>
                            <strong class="uppercase">Payment & Deposit:</strong>
                            Deposit RM50 (max 5 days), RM150 (weekly). Online payment preferred. Refund depends on condition/fuel/summons.
                        </div>
                        <div>
                            <strong class="uppercase">Cancellation:</strong>
                            Non-refundable for paid rental and deposit.
                        </div>
                        <div class="col-span-2">
                            <strong class="uppercase">Excess Fee & Liability:</strong>
                            Renter responsible for excess fee (Table 2) for any damage/accident. Renter fully liable for negligence, tyres, battery, scratches, etc. Accident: Inform company first & Police report within 24hrs.
                        </div>
                        <div>
                            <strong class="uppercase">Fuel:</strong>
                            Return same level. Charge RM10/bar if less.
                        </div>
                        <div>
                            <strong class="uppercase">Fines:</strong>
                            Renter liable for all fines + RM20 admin fee.
                        </div>
                        <div>
                            <strong class="uppercase">Condition:</strong>
                            Return in original condition or liable for restoration costs + loss of sales.
                        </div>
                        <div>
                            <strong class="uppercase">Prohibited:</strong>
                            No strong odours (Durian/Salted Fish). No Smoking. No entering SG/Thai. No sea transport.
                        </div>
                    </div>
                </div>

                {{-- Signatures --}}
                <div class="mt-2 pt-2 border-t-2 border-black grid grid-cols-2 gap-8">
                    <div>
                        <p class="mb-4 font-bold text-[10px] uppercase">Signed by Lessor:</p>
                        <div class="h-6 flex items-end"><p class="font-bold text-sm">HASTA MANAGER</p></div>
                        <div class="border-t border-black pt-0.5"><p class="text-[8px] text-gray-500">Authorized Signature</p></div>
                    </div>
                    <div>
                        <p class="mb-2 font-bold text-[10px] uppercase">I agree to the terms above:</p>
                        @if($booking->bookingID == 'PENDING')
                            <div class="h-6 flex items-end border-b border-dashed border-gray-400 mb-0.5"></div>
                            <p class="text-[8px] text-gray-400 text-center">(Sign Here)</p>
                        @else
                            {{-- FIX: Changed 'name' to 'fullName' --}}
                            <div class="h-6 flex items-end"><p class="font-script text-lg text-white leading-none">{{ $booking->customer->fullName }}</p></div>
                        @endif
                        <div class="border-t border-black pt-0.5">
                            {{-- FIX: Changed 'name' to 'fullName' --}}
                            <p class="text-[9px] font-bold truncate">{{ strtoupper($booking->customer->fullName) }}</p>
                            <p class="text-[8px]">Date: {{ now()->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection