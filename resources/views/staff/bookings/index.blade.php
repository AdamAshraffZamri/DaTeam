@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-gray-100 p-6">
    <div class="max-w-7xl mx-auto">

        <h1 class="text-3xl font-black text-gray-900 mb-8">Booking Management</h1>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-2xl font-bold text-gray-800">{{ $bookings->count() }}</h3>
                <p class="text-sm text-gray-400">Total Bookings</p>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-2xl font-bold text-orange-500">{{ $bookings->where('bookingStatus', 'Submitted')->count() }}</h3>
                <p class="text-sm text-gray-400">Pending Approval</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-bold text-gray-800">Recent Bookings</h3>
                </div>

            <table class="w-full text-left">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">Customer</th>
                        <th class="px-6 py-4">Vehicle</th>
                        <th class="px-6 py-4">Proof</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($bookings as $booking)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-mono text-sm">#{{ $booking->bookingID }}</td>
                        
                        <td class="px-6 py-4 font-bold">{{ $booking->customer->name ?? 'Guest' }}</td>
                        
                        <td class="px-6 py-4 text-sm">
                            {{ $booking->vehicle->model }} <br> 
                            <span class="text-gray-400">{{ $booking->vehicle->plateNo }}</span>
                        </td>

                        <td class="px-6 py-4 text-sm">
                            @if($booking->payment && $booking->payment->installmentDetails)
                                <a href="{{ asset('storage/' . $booking->payment->installmentDetails) }}" target="_blank" class="text-blue-600 underline text-xs font-bold">View Receipt</a>
                            @else
                                <span class="text-gray-400 text-xs">No File</span>
                            @endif
                        </td>

                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-bold 
                                {{ $booking->bookingStatus == 'Submitted' ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700' }}">
                                {{ $booking->bookingStatus }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-right space-x-2">
                            @if($booking->bookingStatus == 'Submitted')
                                <form action="{{ route('staff.bookings.approve', $booking->bookingID) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-green-100 text-green-600 hover:bg-green-200 p-2 rounded transition" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                            @elseif($booking->bookingStatus == 'Confirmed')
                                <form action="{{ route('staff.bookings.finalize', $booking->bookingID) }}" method="POST" class="inline" onsubmit="return confirm('Complete rental?');">
                                    @csrf
                                    <button type="submit" class="bg-blue-100 text-blue-600 hover:bg-blue-200 p-2 rounded transition" title="Complete & Return">
                                        <i class="fas fa-flag-checkered"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection