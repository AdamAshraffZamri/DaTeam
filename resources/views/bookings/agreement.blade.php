<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rental Agreement #{{ $booking->bookingID }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print { .no-print { display: none; } }
    </style>
</head>
<body class="bg-gray-100 py-10 font-serif">

    <div class="max-w-3xl mx-auto bg-white p-12 shadow-lg">
        
        <div class="border-b-2 border-gray-800 pb-8 mb-8 flex justify-between items-start">
            <div>
                <h1 class="text-4xl font-bold text-gray-900">RENTAL AGREEMENT</h1>
                <p class="text-gray-500 mt-2">Contract ID: HASTA-{{ str_pad($booking->bookingID, 6, '0', STR_PAD_LEFT) }}</p>
            </div>
            <div class="text-right">
                <h2 class="text-xl font-bold text-orange-600">HASTA CAR RENTAL</h2>
                <p class="text-sm text-gray-600">UTM Skudai, Johor Bahru</p>
                <p class="text-sm text-gray-600">Reg: 20240100987</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-8 mb-8 text-sm">
            <div>
                <h3 class="font-bold bg-gray-100 p-2 mb-2 uppercase">Renter Details</h3>
                <p><span class="font-bold">Name:</span> {{ $booking->customer->name ?? $booking->customer->fullName }}</p>
                <p><span class="font-bold">Email:</span> {{ $booking->customer->email }}</p>
                <p><span class="font-bold">Phone:</span> {{ $booking->customer->phone ?? $booking->customer->phoneNo }}</p>
            </div>
            <div>
                <h3 class="font-bold bg-gray-100 p-2 mb-2 uppercase">Vehicle Details</h3>
                <p><span class="font-bold">Vehicle:</span> {{ $booking->vehicle->brand }} {{ $booking->vehicle->model }}</p>
                <p><span class="font-bold">Plate No:</span> {{ $booking->vehicle->plateNo }}</p>
                <p><span class="font-bold">Color:</span> {{ $booking->vehicle->colour }}</p>
            </div>
        </div>

        <div class="mb-8 border border-gray-300 p-4">
            <h3 class="font-bold mb-4 uppercase">Rental Period & Fees</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <p><strong>Pickup:</strong> {{ \Carbon\Carbon::parse($booking->originalDate)->format('d M Y') }} at {{ $booking->pickupLocation }}</p>
                <p><strong>Return:</strong> {{ \Carbon\Carbon::parse($booking->returnDate)->format('d M Y') }} at {{ $booking->returnLocation }}</p>
                <p class="text-lg mt-2"><strong>Total Paid:</strong> RM {{ number_format($booking->totalCost, 2) }}</p>
            </div>
        </div>

        <div class="text-xs text-gray-500 text-justify mb-12 space-y-2">
            <p><strong>1. AGREEMENT:</strong> By digitally signing this agreement, the Renter acknowledges receipt of the vehicle described above in good working condition.</p>
            <p><strong>2. LIABILITY:</strong> The Renter agrees to be liable for any damage, loss, or theft of the vehicle during the rental period.</p>
            <p><strong>3. RETURN:</strong> The vehicle must be returned on the date and time specified. Late returns will incur a penalty of RM50/hour.</p>
        </div>

        <div class="grid grid-cols-2 gap-12 mt-12">
            <div>
                <div class="border-b border-black mb-2 h-12 flex items-end">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/e/e4/Signature_sample.svg" class="h-10 opacity-50"> 
                </div>
                <p class="font-bold">Authorized Signature</p>
                <p class="text-xs">HASTA Management</p>
            </div>
            <div>
                <div class="border-b border-black mb-2 h-12 flex items-end relative">
                    <p class="font-script text-2xl text-blue-900 italic absolute bottom-0 w-full text-center">
                        Digital Signed: {{ $booking->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
                <p class="font-bold">Renter Signature</p>
                <p class="text-xs">Digitally Verified by IP Address</p>
            </div>
        </div>

        <div class="mt-12 text-center no-print">
            <button onclick="window.print()" class="bg-gray-900 text-white px-6 py-3 rounded hover:bg-black font-bold">
                <i class="fas fa-print mr-2"></i> Print / Save as PDF
            </button>
            <a href="{{ route('book.index') }}" class="text-gray-600 hover:text-black ml-4 underline">Back to Bookings</a>
        </div>

    </div>
</body>
</html>