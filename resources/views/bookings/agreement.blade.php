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
        
        /* Page break handling */
        .print\:break-before-page {
            break-before: page;
        }
    }
</style>

<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/90 via-black/80 to-black/95"></div>
</div>

<div class="relative z-10 py-12">
    <div class="container mx-auto px-4 max-w-4xl">
        
        {{-- Back Button --}}
        <button onclick="window.close()" class="inline-flex items-center text-gray-400 hover:text-white mb-8 transition print:hidden">
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

            <div class="p-8 md:p-12 space-y-8 font-serif text-sm">
                
                {{-- PAGE 1 START --}}
                
                {{-- Header Section --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 border-b-2 border-black pb-6">
                    <div>
                        <h1 class="text-2xl font-bold uppercase tracking-wider mb-2">AGREEMENT FORM</h1>
                        <h2 class="text-xl font-bold text-blue-900">HASTA</h2>
                        <div class="text-xs space-y-1 text-gray-700 mt-2">
                            <p class="font-bold">HASTA TRAVEL & TOURS SDN. BHD. (1359376-T)</p>
                            <p>7A, JALAN KEBUDAYAAN 1A,</p>
                            <p>TAMAN UNIVERSITI,</p>
                            <p>81310 SKUDAI, JOHOR</p>
                            <p>Office: +6011-10900700</p>
                        </div>
                    </div>
                    <div class="text-right flex flex-col justify-between">
                        <div>
                            <p class="font-bold text-lg">HASTA TRAVEL & TOURS SDN.BHD. JOHOR BAHRU</p>
                        </div>
                        <div class="mt-4">
                            <p class="text-xs uppercase font-bold text-gray-500">Invoice Number</p>
                            <p class="text-lg font-bold">
                                {{-- Use now() fallback for preview mode to prevent crash --}}
                                #{{ ($booking->created_at ?? now())->format('Y-m') }}-HASTA/{{ $booking->bookingID == 'PENDING' ? 'DRAFT' : 'INV'.str_pad($booking->id ?? 0, 6, '0', STR_PAD_LEFT) }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Usage Details Table --}}
                <div>
                    <h3 class="font-bold uppercase border-b border-gray-300 mb-2 pb-1">Usage Details</h3>
                    <table class="w-full border-collapse border border-gray-300 text-xs">
                        <tr class="bg-gray-50">
                            <td class="border border-gray-300 p-2 font-bold w-1/4">Vehicle</td>
                            <td class="border border-gray-300 p-2">{{ $booking->vehicle->model }} ({{ $booking->vehicle->plateNo }})</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-bold">Pick Up</td>
                            <td class="border border-gray-300 p-2">
                                {{ \Carbon\Carbon::parse($booking->originalDate ?? request('pickup_date') ?? now())->format('d-m-Y') }} 
                                @ {{ $booking->bookingTime ?? request('pickup_time') ?? '00:00' }}
                                <br><span class="text-gray-500">Loc: {{ $booking->pickupLocation }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-bold">Return</td>
                            <td class="border border-gray-300 p-2">
                                {{ \Carbon\Carbon::parse($booking->returnDate ?? request('return_date') ?? now())->format('d-m-Y') }} 
                                @ {{ $booking->returnTime ?? request('return_time') ?? '00:00' }}
                                <br><span class="text-gray-500">Loc: {{ $booking->returnLocation }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-bold">Duration</td>
                            <td class="border border-gray-300 p-2">
                                @php
                                    // Robust Duration Calculation
                                    $pDateVal = $booking->originalDate ?? request('pickup_date') ?? now();
                                    $rDateVal = $booking->returnDate ?? request('return_date') ?? now();
                                    $pTimeVal = $booking->bookingTime ?? request('pickup_time') ?? '00:00';
                                    $rTimeVal = $booking->returnTime ?? request('return_time') ?? '00:00';
                                    
                                    // Format dates safely to Y-m-d first
                                    $pDateSafe = \Carbon\Carbon::parse($pDateVal)->format('Y-m-d');
                                    $rDateSafe = \Carbon\Carbon::parse($rDateVal)->format('Y-m-d');
                                    
                                    // Combine and Parse
                                    $start = \Carbon\Carbon::parse($pDateSafe . ' ' . $pTimeVal);
                                    $end = \Carbon\Carbon::parse($rDateSafe . ' ' . $rTimeVal);
                                    
                                    $diff = $start->diff($end);
                                @endphp
                                {{ $diff->d }} day(s) {{ $diff->h }} hour(s) {{ $diff->i }} minute(s)
                            </td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-bold">Note</td>
                            <td class="border border-gray-300 p-2 whitespace-pre-line">{{ $booking->remarks ?? 'No additional notes.' }}</td>
                        </tr>
                    </table>
                </div>

                {{-- Customer & Car Info Grid --}}
                <div class="grid grid-cols-2 gap-8">
                    {{-- Customer Details --}}
                    <div>
                        <h3 class="font-bold uppercase border-b border-gray-300 mb-2 pb-1">Customer Details</h3>
                        <table class="w-full text-xs">
                            <tr><td class="py-1 font-bold text-gray-600 w-1/3">Name:</td><td class="py-1">{{ $booking->customer->name }}</td></tr>
                            <tr><td class="py-1 font-bold text-gray-600">IC/Passport:</td><td class="py-1">{{ $booking->customer->ic_passport ?? 'N/A' }}</td></tr>
                            <tr><td class="py-1 font-bold text-gray-600">Mobile:</td><td class="py-1">{{ $booking->customer->phone_number ?? $booking->customer->phoneNo ?? 'N/A' }}</td></tr>
                        </table>
                    </div>
                    
                    {{-- Car Information --}}
                    <div>
                        <h3 class="font-bold uppercase border-b border-gray-300 mb-2 pb-1">Car Information</h3>
                        <table class="w-full text-xs">
                            <tr><td class="py-1 font-bold text-gray-600 w-1/3">Model:</td><td class="py-1">{{ $booking->vehicle->model }}</td></tr>
                            <tr><td class="py-1 font-bold text-gray-600">Plate No:</td><td class="py-1 font-bold border-2 border-black px-2 inline-block">{{ $booking->vehicle->plateNo }}</td></tr>
                            <tr><td class="py-1 font-bold text-gray-600">Color:</td><td class="py-1">{{ $booking->vehicle->color ?? 'N/A' }}</td></tr>
                        </table>
                    </div>
                </div>

                {{-- Pricing Table --}}
                <div class="mt-4">
                    <table class="w-full border-collapse border border-gray-300 text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-300 p-2 text-left">Description</th>
                                <th class="border border-gray-300 p-2 text-right">Amount (MYR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border border-gray-300 p-2">{{ $booking->vehicle->model }} Rental</td>
                                {{-- Use totalCost variable --}}
                                <td class="border border-gray-300 p-2 text-right">{{ number_format($booking->totalCost ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-300 p-2">Discount</td>
                                <td class="border border-gray-300 p-2 text-right">-0.00</td>
                            </tr>
                            <tr class="font-bold bg-gray-50">
                                <td class="border border-gray-300 p-2 text-right">Grand Total</td>
                                <td class="border border-gray-300 p-2 text-right">{{ number_format($booking->totalCost ?? 0, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Page Break Visual --}}
                <div class="border-t-4 border-dashed border-gray-300 my-8 py-4 text-center text-gray-400 print:hidden">
                    --- PAGE 2 (TERMS & CONDITIONS) ---
                </div>
                <div class="print:break-before-page"></div>

                {{-- PAGE 2 START --}}
                
                {{-- T&C Header --}}
                <div class="text-center mb-6">
                    <h3 class="text-xl font-bold uppercase underline">Rental Agreement</h3>
                    <p class="text-xs font-bold">HASTA TRAVEL & TOURS SDN. BHD. 202001003057 (1359376T)</p>
                    <p class="text-xs">KPK/LN 10181</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    {{-- Table 1: Price List --}}
                    <div>
                        <p class="text-xs font-bold text-center mb-1">Table 1: Price List Hasta (Overtime/Hourly)</p>
                        <table class="w-full text-[10px] border-collapse border border-black text-center">
                            <tr class="bg-gray-200">
                                <th class="border border-black p-1">HOUR</th>
                                <th class="border border-black p-1">1</th>
                                <th class="border border-black p-1">3</th>
                                <th class="border border-black p-1">5</th>
                                <th class="border border-black p-1">7</th>
                                <th class="border border-black p-1">9</th>
                                <th class="border border-black p-1">12</th>
                                <th class="border border-black p-1">24</th>
                            </tr>
                            <tr>
                                <td class="border border-black p-1 font-bold">AXIA</td>
                                <td class="border border-black p-1">30</td>
                                <td class="border border-black p-1">50</td>
                                <td class="border border-black p-1">60</td>
                                <td class="border border-black p-1">65</td>
                                <td class="border border-black p-1">75</td>
                                <td class="border border-black p-1">80</td>
                                <td class="border border-black p-1">110</td>
                            </tr>
                            <tr>
                                <td class="border border-black p-1 font-bold">MYVI/BEZZA/SAGA</td>
                                <td class="border border-black p-1">35</td>
                                <td class="border border-black p-1">55</td>
                                <td class="border border-black p-1">65</td>
                                <td class="border border-black p-1">70</td>
                                <td class="border border-black p-1">75</td>
                                <td class="border border-black p-1">85</td>
                                <td class="border border-black p-1">130</td>
                            </tr>
                        </table>
                    </div>

                    {{-- Table 2: Excess Fee --}}
                    <div>
                        <p class="text-xs font-bold text-center mb-1">Table 2: Excess Fee</p>
                        <table class="w-full text-[10px] border-collapse border border-black">
                            <tr class="bg-gray-200">
                                <th class="border border-black p-1 text-left">TYPES OF CAR</th>
                                <th class="border border-black p-1 text-right">EXCESS FEE (RM)</th>
                            </tr>
                            <tr>
                                <td class="border border-black p-1">PERODUA AXIA</td>
                                <td class="border border-black p-1 text-right">2,000</td>
                            </tr>
                            <tr>
                                <td class="border border-black p-1">PERODUA MYVI / BEZZA / PROTON SAGA</td>
                                <td class="border border-black p-1 text-right">2,500</td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- Full Terms Text --}}
                <div class="text-[11px] leading-tight text-justify space-y-3 font-serif">
                    <div>
                        <strong class="uppercase">Rates:</strong>
                        Rental rates are charged for minimum of 1-hour RM30. Rental with more than 12 hours will be considered as 1-day rental. Extend hours will be calculated at fix rate based on Table 1. Rates include maximum mileage of 300 km per day and replace car breakdown (if car got problem on road because of car maintenance only). Rates are in Ringgit Malaysia (RM).
                    </div>
                    
                    <div>
                        <strong class="uppercase">Driver's Age and License Requirements:</strong>
                        The driver must be between 19 to 55 years old for all car category vehicles and in possession of a valid national or International Driving License. Probational license holders will not be accepted.
                    </div>

                    <div>
                        <strong class="uppercase">Terms of Payment & Deposit:</strong>
                        All rentals are subjected to a compulsory deposit of RM50.00 per car with maximum rental of 5 days. For weekly rental deposit will be RM150 and for one month is equal to one month rental. Our company only accepts the online payment for deposits and rental. Cash is accepted as mode of payment at the counter. Refundable deposit depends on return car condition (fuel, late return, extend and accident).
                    </div>

                    <div>
                        <strong class="uppercase">Cancellation Policy:</strong>
                        All paid rental and deposit cannot be cancelled, and payment made are non-refundable.
                    </div>

                    <div>
                        <strong class="uppercase">Excess Fee & Liability:</strong>
                        The renter shall be held responsible for accidental damage to third party property and bodily injuries. However, the renter is always responsible for an amount equivalent to the excess fee based on Table 2. A full responsible will be on the renter for damage as a result of illegal, negligence, careless actions, tyre punctures, burst tyre, scratches and dent, lack of battery power because of forgotten turned off car electrical devices, loss or damage to the vehicle and vehicle accessories and damages of windows, mirror and undercarriage. In the event of any accident, the renter must agree to accept the Excess Fee and inform our company first before taking any action and make a police report within 24 hours.
                    </div>

                    <div>
                        <strong class="uppercase">Fuel:</strong>
                        Our company does not provide full tank unless requested by the renter and must be returned the same fuel level. Otherwise the renter will be charged based on 1 bar RM10.
                    </div>

                    <div>
                        <strong class="uppercase">Parking Fees and Traffic Fines:</strong>
                        The renter is liable for all parking and traffic fines incurred for the duration of the rental. An additional RM20 administration fee will be charged to the renter over and above any fine and penalty cost for any violation arising from the renter's use of vehicle.
                    </div>

                    <div>
                        <strong class="uppercase">Vehicle Condition:</strong>
                        Upon return, the car must be in the same condition as when it was rented. Failing which, the renter will be liable for the cost of restoring the vehicle to its original condition and loss of company sales for that particular car.
                    </div>

                    <div>
                        <strong class="uppercase">Prohibited Odours & Smoking:</strong>
                        All items and goods discharging unpleasant odours are strictly forbidden (e.g. Durians, salted fish etc). The renter will be liable to reimburse costs of eliminating such odours. Smoking in vehicles is strictly prohibited.
                    </div>

                    <div>
                        <strong class="uppercase">Restrictions:</strong>
                        The vehicles cannot be driven into Singapore, Thailand, Brunei and Indonesia. Vehicles are prohibited from being loaded onto sea/air transportation to islands (Langkawi, Tioman, etc).
                    </div>
                </div>

                {{-- Signatures --}}
                <div class="mt-8 pt-6 border-t-2 border-black grid grid-cols-2 gap-12">
                    <div>
                        <p class="mb-8 font-bold text-xs uppercase">Signed by Lessor:</p>
                        <div class="h-12 flex items-end"><p class="font-bold text-lg">HASTA MANAGER</p></div>
                        <div class="border-t border-black pt-1"><p class="text-[10px] text-gray-500">Authorized Signature</p></div>
                    </div>
                    <div>
                        <p class="mb-4 font-bold text-xs uppercase">I have read Terms & Conditions of this agreement and agree here to:</p>
                        {{-- IF PREVIEW, SHOW BLANK SPACE FOR SIGNATURE --}}
                        @if($booking->bookingID == 'PENDING')
                            <div class="h-12 flex items-end border-b border-dashed border-gray-400 mb-1"></div>
                            <p class="text-[10px] text-gray-400 text-center">(Sign Here)</p>
                        @else
                            <div class="h-12 flex items-end"><p class="font-script text-xl text-blue-900">{{ $booking->customer->name }}</p></div>
                        @endif
                        <div class="border-t border-black pt-1">
                            <p class="text-xs font-bold">{{ strtoupper($booking->customer->name) }}</p>
                            <p class="text-[10px]">Date: {{ now()->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection