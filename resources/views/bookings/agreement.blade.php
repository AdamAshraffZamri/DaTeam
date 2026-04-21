@extends('layouts.app')

@section('content')
{{-- Print Styles --}}
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        nav, footer, .fixed, header, .z-40, .z-50, button {
            display: none !important;
        }
        
        #printable-agreement, #printable-agreement * {
            visibility: visible;
        }

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

        .page-break {
            page-break-before: always;
            margin-top: 2cm;
        }

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

        <div id="printable-agreement" class="bg-white text-black shadow-2xl overflow-hidden relative print:shadow-none print:w-full">
            
            {{-- Print Button --}}
            <div class="absolute top-4 right-4 print:hidden">
                <button onclick="window.print()" class="bg-blue-900 text-white px-4 py-2 rounded shadow hover:bg-blue-800 text-sm">
                    <i class="fas fa-print mr-2"></i> Print / Save PDF
                </button>
            </div>

            {{-- PAGE 1: BOOKING SUMMARY --}}
            <div class="p-8 space-y-6 font-serif">
                
                {{-- Header Section --}}
                <div class="grid grid-cols-2 gap-4 border-b-2 border-black pb-4">
                    <div>
                        <h1 class="text-2xl font-bold uppercase tracking-wider mb-1">AGREEMENT FORM</h1>
                        <img src="{{ asset('hasta.jpeg') }}" alt="HASTA Logo" class="h-8 md:h-10 w-auto object-contain drop-shadow-sm hover:scale-105 transition transform">
                        <div class="text-xs space-y-1 text-gray-700 mt-2 leading-tight">
                            <p class="font-bold">HASTA TRAVEL & TOURS SDN. BHD. (1359376-T)</p>
                            <p>7A, JALAN KEBUDAYAAN 1A, TAMAN UNIVERSITI, 81310 SKUDAI, JOHOR</p>
                            <p>Office: +6011-10900700</p>
                        </div>
                    </div>
                    <div class="text-right flex flex-col justify-between">
                        <div>
                            <p class="font-bold text-lg">VEHICLE RENTAL INVOICE</p>
                        </div>
                        <div class="mt-2">
                            <p class="text-xs uppercase font-bold text-gray-500">Invoice Number</p>
                            <p class="text-xl font-bold">
                                #{{ ($booking->created_at ?? now())->format('Y-m') }}-HASTA/{{ $booking->bookingID == 'PENDING' ? 'DRAFT' : 'INV'.str_pad($booking->id ?? 0, 6, '0', STR_PAD_LEFT) }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Usage Details --}}
                <div class="space-y-4">
                    <h3 class="font-bold uppercase border-b-2 border-gray-800 pb-1 text-sm">1. Rental Details</h3>
                    <table class="w-full border-collapse border border-gray-400 text-sm">
                        <tr class="bg-gray-50">
                            <td class="border border-gray-400 p-2 font-bold w-1/4">Vehicle Model</td>
                            <td class="border border-gray-400 p-2">{{ $booking->vehicle->model }}</td>
                            <td class="border border-gray-400 p-2 font-bold w-1/4">Rental Duration</td>
                            <td class="border border-gray-400 p-2">
                                @php
                                    $pDateVal = $booking->originalDate ?? request('pickup_date') ?? now();
                                    $rDateVal = $booking->returnDate ?? request('return_date') ?? now();
                                    $pTimeVal = $booking->bookingTime ?? request('pickup_time') ?? '10:00';
                                    $rTimeVal = $booking->returnTime ?? request('return_time') ?? '10:00';
                                    $start = \Carbon\Carbon::parse(\Carbon\Carbon::parse($pDateVal)->format('Y-m-d') . ' ' . $pTimeVal);
                                    $end = \Carbon\Carbon::parse(\Carbon\Carbon::parse($rDateVal)->format('Y-m-d') . ' ' . $rTimeVal);
                                    $diff = $start->diff($end);
                                @endphp
                                {{ $diff->d }} Days, {{ $diff->h }} Hours
                            </td>
                        </tr>
                        <tr>
                            <td class="border border-gray-400 p-2 font-bold">Pick Up</td>
                            <td class="border border-gray-400 p-2">
                                {{ $start->format('d-M-Y') }} @ {{ $start->format('H:i') }}<br>
                                <span class="text-gray-600 text-xs">Location: {{ $booking->pickupLocation }}</span>
                            </td>
                            <td class="border border-gray-400 p-2 font-bold">Return</td>
                            <td class="border border-gray-400 p-2">
                                {{ $end->format('d-M-Y') }} @ {{ $end->format('H:i') }}<br>
                                <span class="text-gray-600 text-xs">Location: {{ $booking->returnLocation }}</span>
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- Customer & Vehicle Grid --}}
                <div class="grid grid-cols-2 gap-8">
                    <div>
                        <h3 class="font-bold uppercase border-b-2 border-gray-800 pb-1 text-sm mb-2">2. Customer Information</h3>
                        <table class="w-full text-sm">
                            <tr><td class="py-1 font-bold text-gray-700 w-1/3">Full Name:</td><td class="py-1">{{ $booking->customer->fullName }}</td></tr>
                            <tr><td class="py-1 font-bold text-gray-700">IC/Passport:</td><td class="py-1">{{ $booking->customer->ic_passport ?? 'N/A' }}</td></tr>
                            <tr><td class="py-1 font-bold text-gray-700">Phone No:</td><td class="py-1">{{ $booking->customer->phoneNo ?? 'N/A' }}</td></tr>
                        </table>
                    </div>
                    <div>
                        <h3 class="font-bold uppercase border-b-2 border-gray-800 pb-1 text-sm mb-2">3. Vehicle Information</h3>
                        <table class="w-full text-sm">
                            <tr><td class="py-1 font-bold text-gray-700 w-1/3">Plate No:</td><td class="py-1">{{ $booking->vehicle->plate_no ?? 'TBA' }}</td></tr>
                            <tr><td class="py-1 font-bold text-gray-700">Color:</td><td class="py-1">{{ $booking->vehicle->color ?? 'N/A' }}</td></tr>
                        </table>
                    </div>
                </div>

                {{-- Financial Summary --}}
                <div class="pt-4">
                    <h3 class="font-bold uppercase border-b-2 border-gray-800 pb-1 text-sm mb-2">4. Payment Summary</h3>
                    <table class="w-1/2 ml-auto border-collapse border border-gray-400 text-sm">
                        <tr>
                            <td class="border border-gray-400 p-2 font-bold">Rental Subtotal</td>
                            <td class="border border-gray-400 p-2 text-right">RM {{ number_format($booking->totalCost ?? 0, 2) }}</td>
                        </tr>
                        <tr class="bg-gray-100 font-bold text-lg">
                            <td class="border border-gray-400 p-2">Total Amount</td>
                            <td class="border border-gray-400 p-2 text-right">RM {{ number_format($booking->totalCost ?? 0, 2) }}</td>
                        </tr>
                    </table>
                </div>

                <div class="mt-12 p-4 bg-gray-50 border border-dashed border-gray-300 rounded text-center">
                    <p class="text-sm text-gray-600">Please refer to <strong>Page 2</strong> for the full Rental Agreement Terms & Conditions.</p>
                </div>
            </div>

            {{-- PAGE 2: TERMS & SIGNATURES --}}
            <div class="page-break p-8 space-y-6 font-serif">
                <div class="text-center border-b-2 border-black pb-2">
                    <h3 class="text-lg font-bold uppercase underline">Rental Agreement Terms & Conditions</h3>
                    <p class="text-xs font-bold uppercase">HASTA TRAVEL & TOURS SDN. BHD. (1359376T) | KPK/LN 10181</p>
                </div>

                {{-- Tables Grid --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[10px] font-bold text-center mb-1 uppercase">Table 1: Price List Hasta (RM)</p>
                        <table class="w-full text-[9px] border-collapse border border-black text-center">
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
                                <td class="border border-black p-1">70</td>
                                <td class="border border-black p-1">80</td>
                                <td class="border border-black p-1">110</td>
                            </tr>
                            <tr>
                                <td class="border border-black p-1 font-bold">MYVI / BEZZA / SAGA</td>
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

                    <div>
                        <p class="text-[10px] font-bold text-center mb-1 uppercase">Table 2: Excess Fee Liability</p>
                        <table class="w-full text-[9px] border-collapse border border-black">
                            <tr class="bg-gray-200">
                                <th class="border border-black p-1 text-left uppercase">Vehicle Type</th>
                                <th class="border border-black p-1 text-right uppercase">Excess Fee (RM)</th>
                            </tr>
                            <tr>
                                <td class="border border-black p-1 uppercase">Perodua Axia</td>
                                <td class="border border-black p-1 text-right">2,000</td>
                            </tr>
                            <tr>
                                <td class="border border-black p-1 uppercase">Perodua Myvi / Bezza / Proton Saga</td>
                                <td class="border border-black p-1 text-right">2,500</td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- Detailed Terms from PDF --}}
                <div class="text-[9px] leading-[1.3] text-justify grid grid-cols-2 gap-x-6 gap-y-3 font-serif border-t border-black pt-4">
                    <div>
                        <strong class="uppercase">1. Rates:</strong>
                        Minimum 1-hour rental is RM30. Rental with more than 12 hours will be considered as 1-day rental. Extend hours will be calculated at fix rate based on Table 1. Rates include maximum mileage of 300 km per day and replace car breakdown (for maintenance issues only).
                    </div>
                    <div>
                        <strong class="uppercase">2. Driver Requirements:</strong>
                        The driver must be between 19 to 55 years old and in possession of a valid national or International Driving License. Probational (P) license holders will not be accepted.
                    </div>
                    <div>
                        <strong class="uppercase">3. Payment & Deposit:</strong>
                        Compulsory deposit: RM50.00 (up to 5 days) or RM150 (weekly). Refunds depend on return car condition (fuel, late return, extend and accident). Only online payment is accepted for deposits and rental.
                    </div>
                    <div>
                        <strong class="uppercase">4. Cancellation:</strong>
                        All paid rental and deposit cannot be cancelled, and payments made are non-refundable.
                    </div>
                    <div>
                        <strong class="uppercase">5. Excess Fee:</strong>
                        The renter shall be held responsible for accidental damage to third
                        party property and bodily injuries. However, the renter is always
                        responsible for an amount equivalent to the excess fee based on Table
                        2. A full responsible will be on the renter for damage as a result of
                        illegal, negligence, careless actions, tyre punctures, bust tyre,
                        scratches and dent, lack of battery power because of forgotten turned
                        off car electrical devices, loss or damage to the vehicle and vehicle
                        accessories and damages of windows, mirror and undercarriage. In the
                        event of any accident, the renter must agree to accept the Excess Fee
                        and inform our company first before taking any action and make a
                        police report within 24 hours from the time of the accident or theft.
                        Our company shall be entitled to charge the renter an excess fee which
                        is in accordance with the following Table 2. Upon the renter's
                        acceptance and subject to the terms and conditions stipulated in the
                        Rental Agreement, the renter's liability is limited to the Excess Fee.
                        Excess Fee is used to cover loss of company sales for that particular
                        car while repairing. Any extra charge of the repairing cost will be
                        added if needed by the company. Receipt of any additional cost will be
                        given to the customer.
                    </div>
                    <div>
                        <strong class="uppercase">6. Traffic Fines:</strong>
                        The renter is liable for all parking and traffic fines incurred for the
                        duration of the rental. An additional RM20 administration fee will be
                        charged to the renter over and above any fine and penalty cost for any
                        violation arising from the renter's use of vehicle. Our company retains
                        the right to charge against the renter's charge if and when payment is
                        due for traffic fines committed by the renter, upon receiving
                        notification from the government authorities.
                        
                    </div>
                    <div>
                        <strong class="uppercase">7. Fuel:</strong>
                        Vehicle must be returned with the same fuel level. Missing fuel will be charged based on 1 bar = RM10.
                    </div>
                    <div>
                        <strong class="uppercase">8. Vehicle Condition:</strong>
                        Car must be returned in the same condition. Renter is liable for restoration costs and loss of company sales.
                    </div>
                    <div>
                        <strong class="uppercase">9. Surcharge:</strong>
                        A fee of RM10 applies for meetings/reservations after operating hours.
                    </div>
                    <div>
                        <strong class="uppercase">10. Restricted Entry:</strong>
                        Vehicles cannot be driven into Singapore, Thailand, Brunei, or Indonesia. Prohibited from being loaded onto sea, river, or air transportation (Islands).
                    </div>
                    <div>
                        <strong class="uppercase">11. Prohibited Odours:</strong>
                        Strong odours (Durians, salted fish, etc.) are strictly forbidden. Renter is liable for air-con servicing costs and loss of rental days. Smoking is prohibited.
                    </div>
                    <div>
                        <strong class="uppercase">12. Destination Limits:</strong>
                        Johor state only for 1-day rental. Minimum 2 days for travel outside Johor. Failure to comply results in 1-day rental penalty and burnt deposit.
                    </div>
                </div>

                {{-- Signatures --}}
                <div class="mt-8 pt-4 border-t-2 border-black grid grid-cols-2 gap-12">
                    <div>
                        <p class="mb-4 font-bold text-xs uppercase">Signed by Lessor:</p>
                        <div class="h-10 flex items-end"><p class="font-bold text-sm">HASTA MANAGER</p></div>
                        <div class="border-t border-black pt-1"><p class="text-[9px] text-gray-500 uppercase tracking-widest">Authorized Signature</p></div>
                    </div>
                    <div>
                        <p class="mb-4 font-bold text-xs uppercase">Lessee Acknowledgement:</p>
                        <div class="h-10 flex items-end"></div>
                        <div class="border-t border-black pt-1">
                            <p class="text-xs font-bold uppercase">{{ $booking->customer->fullName }}</p>
                            <p class="text-[9px]">Date: {{ now()->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection