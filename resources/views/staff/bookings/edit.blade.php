@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-slate-100 p-6">
    <div class="max-w-6xl mx-auto">

        {{-- HEADER --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-black text-gray-900">Edit Booking</h1>
                <p class="text-sm text-gray-500 mt-1">Booking ID: <span class="font-bold">#{{ $booking->bookingID }}</span> | Vehicle: <span class="font-bold">{{ $booking->vehicle->plateNo }}</span></p>
            </div>
            <a href="{{ route('staff.bookings.show', $booking->bookingID) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-bold transition flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- LEFT COLUMN: FORM --}}
            <div class="lg:col-span-2 space-y-6">

        {{-- CUSTOMER & VEHICLE INFO --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            
            {{-- CUSTOMER INFO --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Customer Information</h3>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xl font-bold">
                        {{ substr($booking->customer->fullName ?? 'G', 0, 1) }}
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900">{{ $booking->customer->fullName ?? 'Guest User' }}</h4>
                        <p class="text-xs text-gray-500">{{ $booking->customer->email }}</p>
                        <p class="text-xs text-gray-500">{{ $booking->customer->phoneNo }}</p>
                    </div>
                </div>
            </div>

            {{-- VEHICLE INFO --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Vehicle Information</h3>
                <div class="flex items-center gap-4">
                    <img src="{{ Storage::disk('s3')->temporaryUrl($booking->vehicle->image, now()->addMinutes(60)) }}" alt="Car" class="w-20 h-20 object-cover rounded-lg bg-gray-50">
                    <div>
                        <h4 class="font-bold text-gray-900 text-lg">{{ $booking->vehicle->model }}</h4>
                        <p class="text-xs text-gray-500">{{ $booking->vehicle->plateNo }}</p>
                        <p class="text-xs text-gray-500">{{ $booking->vehicle->color }} • {{ $booking->vehicle->fuelType }}</p>
                    </div>
                </div>
            </div>

        </div>

        {{-- EDIT FORM --}}
        <form action="{{ route('staff.bookings.update', $booking->bookingID) }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            @csrf
            @method('PUT')

            {{-- VALIDATION ERROR ALERT --}}
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border-2 border-red-200 rounded-lg p-4">
                    <div class="flex gap-3">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl mt-0.5 flex-shrink-0"></i>
                        <div>
                            <h4 class="font-bold text-red-900 mb-2">Please fix the following errors:</h4>
                            <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- PICKUP SECTION --}}
            <div class="mb-8 pb-8 border-b border-gray-100">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 font-bold text-lg">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Pickup Information</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    {{-- PICKUP DATE --}}
                    <div>
                        <label for="originalDate" class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-calendar text-green-500 mr-2"></i>Pickup Date
                        </label>
                        <input type="date" id="originalDate" name="originalDate" 
                               value="{{ \Carbon\Carbon::parse($booking->originalDate)->format('Y-m-d') }}"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-green-500 focus:outline-none font-medium text-gray-900 transition"
                               required>
                        @error('originalDate')
                            <p class="mt-1 text-sm text-red-600 font-medium"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- PICKUP TIME --}}
                    <div>
                        <label for="bookingTime" class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-clock text-green-500 mr-2"></i>Pickup Time
                        </label>
                        <input type="time" id="bookingTime" name="bookingTime" 
                               value="{{ \Carbon\Carbon::parse($booking->bookingTime)->format('H:i') }}"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-green-500 focus:outline-none font-medium text-gray-900 transition"
                               required>
                        @error('bookingTime')
                            <p class="mt-1 text-sm text-red-600 font-medium"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- PICKUP LOCATION --}}
                    <div class="md:col-span-2">
                        <label for="pickupLocation" class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-map-marker-alt text-green-500 mr-2"></i>Pickup Location
                        </label>
                        <textarea id="pickupLocation" name="pickupLocation" rows="3"
                                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-green-500 focus:outline-none font-medium text-gray-900 transition resize-none"
                                  placeholder="Enter pickup location address..." required>{{ $booking->pickupLocation }}</textarea>
                        @error('pickupLocation')
                            <p class="mt-1 text-sm text-red-600 font-medium"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>

            {{-- RETURN SECTION --}}
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 font-bold text-lg">
                        <i class="fas fa-arrow-left"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Return Information</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    {{-- RETURN DATE --}}
                    <div>
                        <label for="returnDate" class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-calendar text-red-500 mr-2"></i>Return Date
                        </label>
                        <input type="date" id="returnDate" name="returnDate" 
                               value="{{ \Carbon\Carbon::parse($booking->returnDate)->format('Y-m-d') }}"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-red-500 focus:outline-none font-medium text-gray-900 transition"
                               required>
                        @error('returnDate')
                            <p class="mt-1 text-sm text-red-600 font-medium"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- RETURN TIME --}}
                    <div>
                        <label for="returnTime" class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-clock text-red-500 mr-2"></i>Return Time
                        </label>
                        <input type="time" id="returnTime" name="returnTime" 
                               value="{{ \Carbon\Carbon::parse($booking->returnTime)->format('H:i') }}"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-red-500 focus:outline-none font-medium text-gray-900 transition"
                               required>
                        @error('returnTime')
                            <p class="mt-1 text-sm text-red-600 font-medium"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- RETURN LOCATION --}}
                    <div class="md:col-span-2">
                        <label for="returnLocation" class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>Return Location
                        </label>
                        <textarea id="returnLocation" name="returnLocation" rows="3"
                                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-red-500 focus:outline-none font-medium text-gray-900 transition resize-none"
                                  placeholder="Enter return location address..." required>{{ $booking->returnLocation }}</textarea>
                        @error('returnLocation')
                            <p class="mt-1 text-sm text-red-600 font-medium"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="flex gap-3 justify-between">
                <a href="{{ route('staff.bookings.show', $booking->bookingID) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-900 px-6 py-3 rounded-lg font-bold transition flex items-center gap-2">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-lg font-bold transition flex items-center gap-2 shadow-lg shadow-indigo-600/30">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>

        </form>

            </div>

            {{-- RIGHT COLUMN: CALENDAR CHECKER --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-calendar text-indigo-600"></i> Availability Checker
                    </h3>

                    {{-- CURRENT SELECTION --}}
                    <div class="bg-indigo-50 rounded-xl p-4 mb-6 border border-indigo-100">
                        <p class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-3">Selected Dates</p>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 text-sm">
                                <i class="fas fa-arrow-right text-green-600 font-bold"></i>
                                <span class="text-gray-600">
                                    <strong id="pickupDisplay">Loading...</strong>
                                </span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <i class="fas fa-arrow-left text-red-600 font-bold"></i>
                                <span class="text-gray-600">
                                    <strong id="returnDisplay">Loading...</strong>
                                </span>
                            </div>
                            <div class="flex items-center gap-2 text-sm pt-2 border-t border-indigo-100">
                                <i class="fas fa-hourglass-half text-orange-600"></i>
                                <span class="text-gray-600">
                                    Duration: <strong id="durationDisplay">0 days</strong>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- CALENDAR MONTHS --}}
                    <div id="calendarContainer" class="space-y-6">
                        {{-- Calendar will be generated by JavaScript --}}
                    </div>

                    {{-- LEGEND --}}
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Legend</p>
                        <div class="space-y-2 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-indigo-600"></span>
                                <span class="text-gray-600">Your Booking Period</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                                <span class="text-gray-600">Booked by Other Customers</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-gray-300"></span>
                                <span class="text-gray-600">Available</span>
                            </div>
                        </div>
                    </div>

                    {{-- BOOKING INFO --}}
                    <div id="bookingInfo" class="mt-6 pt-6 border-t border-gray-100 text-xs hidden">
                        <p class="font-bold text-gray-700 mb-2 text-sm">✅ Dates Conflict Check</p>
                        <div id="bookingDetails" class="space-y-1 text-gray-600"></div>
                    </div>
                </div>
            </div>

        </div>

        {{-- INFO BOX --}}
        <div class="mt-8 bg-blue-50 border-2 border-blue-200 rounded-2xl p-6">
            <div class="flex gap-3">
                <i class="fas fa-info-circle text-blue-600 text-xl mt-0.5 flex-shrink-0"></i>
                <div>
                    <h3 class="font-bold text-blue-900 mb-2">How to Use the Calendar Checker</h3>
                    <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
                        <li>Select dates using the input fields on the left</li>
                        <li>The calendar on the right shows availability in real-time</li>
                        <li>Red dates are already booked by other customers</li>
                        <li>Your selected period is highlighted in blue</li>
                        <li>Make sure your dates don't conflict with other bookings</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    const vehicleId = {{ $booking->vehicleID }};
    const originalDateInput = document.getElementById('originalDate');
    const returnDateInput = document.getElementById('returnDate');
    const bookingTimeInput = document.getElementById('bookingTime');
    const returnTimeInput = document.getElementById('returnTime');

    // Update display and calendar when dates/times change
    originalDateInput.addEventListener('change', updateCalendar);
    returnDateInput.addEventListener('change', updateCalendar);
    bookingTimeInput.addEventListener('change', updateDisplay);
    returnTimeInput.addEventListener('change', updateDisplay);

    function updateDisplay() {
        const pickupDateStr = originalDateInput.value ? new Date(originalDateInput.value + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' }) : 'Not set';
        const returnDateStr = returnDateInput.value ? new Date(returnDateInput.value + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' }) : 'Not set';
        
        document.getElementById('pickupDisplay').textContent = pickupDateStr + (bookingTimeInput.value ? ' ' + bookingTimeInput.value : '');
        document.getElementById('returnDisplay').textContent = returnDateStr + (returnTimeInput.value ? ' ' + returnTimeInput.value : '');

        // Calculate duration and validate times
        if (originalDateInput.value && returnDateInput.value && bookingTimeInput.value && returnTimeInput.value) {
            const pickupDateTime = new Date(originalDateInput.value + 'T' + bookingTimeInput.value);
            const returnDateTime = new Date(returnDateInput.value + 'T' + returnTimeInput.value);
            
            const diffMs = returnDateTime - pickupDateTime;
            const diffMins = Math.floor(diffMs / (1000 * 60));
            const diffDays = Math.floor(diffMins / (24 * 60));
            const remainingHours = Math.floor((diffMins % (24 * 60)) / 60);
            
            // Show duration with validation
            const durationDisplay = document.getElementById('durationDisplay');
            if (diffMins < 0) {
                durationDisplay.textContent = '❌ Invalid: Return before pickup!';
                durationDisplay.className = 'text-red-600 font-bold';
            } else if (diffMins < 60) {
                durationDisplay.textContent = `⚠️ Only ${diffMins} minutes (min 60)`;
                durationDisplay.className = 'text-orange-600 font-bold';
            } else {
                const days = diffDays > 0 ? diffDays + 'd ' : '';
                durationDisplay.textContent = `✓ ${days}${remainingHours}h`;
                durationDisplay.className = 'text-green-600 font-bold';
            }
        } else {
            document.getElementById('durationDisplay').textContent = '0 days';
            document.getElementById('durationDisplay').className = 'text-gray-600';
        }
    }

    async function updateCalendar() {
        updateDisplay();

        try {
            const response = await fetch(`/staff/api/vehicle/${vehicleId}/booked-dates`);
            const data = await response.json();
            
            const bookedDates = data.booked_dates || [];
            const pickupDate = originalDateInput.value ? new Date(originalDateInput.value) : null;
            const returnDate = returnDateInput.value ? new Date(returnDateInput.value) : null;

            renderCalendar(bookedDates, pickupDate, returnDate);
        } catch (error) {
            console.error('Error fetching booked dates:', error);
        }
    }

    function renderCalendar(bookedDates, pickupDate, returnDate) {
        const container = document.getElementById('calendarContainer');
        container.innerHTML = '';

        // Show 3 months calendar
        const today = new Date();
        for (let i = 0; i < 3; i++) {
            const date = new Date(today.getFullYear(), today.getMonth() + i, 1);
            container.appendChild(createMonthCalendar(date, bookedDates, pickupDate, returnDate));
        }
    }

    function createMonthCalendar(date, bookedDates, pickupDate, returnDate) {
        const monthDiv = document.createElement('div');
        const monthName = date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        const daysInMonth = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
        const firstDay = new Date(date.getFullYear(), date.getMonth(), 1).getDay();

        monthDiv.innerHTML = `<h4 class="text-sm font-bold text-gray-700 mb-3">${monthName}</h4>`;

        // Days of week header
        const header = document.createElement('div');
        header.className = 'grid grid-cols-7 gap-1 mb-2';
        ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].forEach(day => {
            const dayDiv = document.createElement('div');
            dayDiv.className = 'text-center text-xs font-bold text-gray-400 py-1';
            dayDiv.textContent = day;
            header.appendChild(dayDiv);
        });
        monthDiv.appendChild(header);

        // Days grid
        const daysGrid = document.createElement('div');
        daysGrid.className = 'grid grid-cols-7 gap-1';

        // Empty cells before first day
        for (let i = 0; i < firstDay; i++) {
            const emptyDiv = document.createElement('div');
            daysGrid.appendChild(emptyDiv);
        }

        // Days of month
        for (let day = 1; day <= daysInMonth; day++) {
            const dayDiv = document.createElement('div');
            const currentDate = new Date(date.getFullYear(), date.getMonth(), day);
            const dateStr = currentDate.toISOString().split('T')[0];

            dayDiv.className = 'flex items-center justify-center text-xs py-2 rounded-lg font-medium cursor-pointer transition';

            const isBooked = bookedDates.includes(dateStr);
            const isInRange = pickupDate && returnDate && currentDate >= pickupDate && currentDate <= returnDate;

            if (isInRange) {
                dayDiv.className += ' bg-indigo-600 text-white';
            } else if (isBooked) {
                dayDiv.className += ' bg-red-500 text-white opacity-50 cursor-not-allowed';
                dayDiv.title = 'Already booked';
            } else {
                dayDiv.className += ' bg-gray-100 text-gray-700 hover:bg-gray-200';
            }

            dayDiv.textContent = day;
            daysGrid.appendChild(dayDiv);
        }

        monthDiv.appendChild(daysGrid);
        return monthDiv;
    }

    // Initial load
    updateCalendar();
</script>

@endsection
