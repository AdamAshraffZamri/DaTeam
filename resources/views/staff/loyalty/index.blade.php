@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-gray-100 rounded-2xl p-6">
    <div class="max-w-7xl mx-auto">
        
        {{-- PAGE HEADER --}}
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">
                <i class="fas fa-star text-yellow-500 mr-3"></i> Loyalty & Rewards Management
            </h1>
            <p class="text-gray-600">Monitor customer loyalty points, rewards activities, and manage vouchers</p>
        </div>

        {{-- KEY METRICS --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            {{-- Total Points Distributed --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Points Distributed</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($totalPointsDistributed) }}</h3>
                    </div>
                    <div class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-gift text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            {{-- Total Points Redeemed --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Points Redeemed</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($totalPointsRedeemed) }}</h3>
                    </div>
                    <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            {{-- Rental Vouchers --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Rental Vouchers</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $rentalVouchers->count() }}</h3>
                    </div>
                    <div class="w-14 h-14 rounded-full bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-car text-purple-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            {{-- Total Members --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Loyalty Members</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $loyaltyStats->count() }}</h3>
                    </div>
                    <div class="w-14 h-14 rounded-full bg-orange-100 flex items-center justify-center">
                        <i class="fas fa-users text-orange-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABS NAVIGATION --}}
        <div class="mb-6">
            <div class="flex gap-4 border-b border-gray-200">
                <button onclick="showTab('customers')" class="tab-btn active px-6 py-3 font-bold text-gray-900 border-b-2 border-orange-500 transition">
                    <i class="fas fa-list mr-2"></i> Customer Loyalty List
                </button>
                <button onclick="showTab('tier')" class="tab-btn px-6 py-3 font-bold text-gray-500 border-b-2 border-transparent hover:text-gray-900 transition">
                    <i class="fas fa-layer-group mr-2"></i> Tier Breakdown
                </button>
                <button onclick="showTab('vouchers')" class="tab-btn px-6 py-3 font-bold text-gray-500 border-b-2 border-transparent hover:text-gray-900 transition">
                    <i class="fas fa-ticket-alt mr-2"></i> Manage Vouchers
                </button>
                <button onclick="showTab('activities')" class="tab-btn px-6 py-3 font-bold text-gray-500 border-b-2 border-transparent hover:text-gray-900 transition">
                    <i class="fas fa-history mr-2"></i> Recent Activities
                </button>
            </div>
        </div>

        {{-- TAB 1: CUSTOMER LOYALTY LIST --}}
        <div id="customers-tab" class="tab-content">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-users text-indigo-600 mr-3"></i> All Customers Loyalty Points
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th class="px-6 py-3 text-left font-bold text-gray-700">#</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-700">Customer Name</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-700">Loyalty Points</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-700">Tier</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-700">Vouchers</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-700">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($loyaltyStats as $index => $loyalty)
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 text-gray-700 font-medium">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center font-bold">
                                                {{ substr($loyalty->customer->name ?? 'G', 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $loyalty->customer->name ?? 'N/A' }}</p>
                                                <p class="text-xs text-gray-500">{{ $loyalty->customer->email ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-xl font-bold text-indigo-600">{{ number_format($loyalty->points) }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold
                                            {{ $loyalty->tier == 'Platinum' ? 'bg-cyan-100 text-cyan-700' : ($loyalty->tier == 'Gold' ? 'bg-yellow-100 text-yellow-700' : ($loyalty->tier == 'Silver' ? 'bg-slate-100 text-slate-700' : 'bg-amber-100 text-amber-700')) }}">
                                            {{ $loyalty->tier }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @php $voucherCount = \App\Models\Voucher::where('customerID', $loyalty->user_id)->count(); @endphp
                                        <span class="font-bold text-gray-700">{{ $voucherCount }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('staff.loyalty.show_customer', $loyalty->user_id) }}" class="text-indigo-600 hover:text-indigo-800 font-bold transition">
                                            <i class="fas fa-eye mr-1"></i> View Details
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                        <i class="fas fa-inbox text-3xl mb-2 block"></i>
                                        <p>No loyalty data yet</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 2: TIER BREAKDOWN --}}
        <div id="tier-tab" class="tab-content hidden">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-layer-group text-indigo-600 mr-3"></i> Member Tier Distribution
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    {{-- Bronze --}}
                    <div class="bg-gradient-to-br from-amber-200 to-amber-600 rounded-xl p-6 border border-amber-200">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-bold text-gray-900 text-lg">Bronze</h4>
                            <i class="fas fa-medal text-amber-700 text-3xl"></i>
                        </div>
                        <p class="text-4xl font-bold text-amber-900 mb-2">{{ $tierBreakdown['Bronze'] }}</p>
                        <p class="text-xs text-amber-700">0 - 999 points</p>
                    </div>

                    {{-- Silver --}}
                    <div class="bg-gradient-to-br from-slate-200 to-slate-600 rounded-xl p-6 border border-slate-200">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-bold text-gray-900 text-lg">Silver</h4>
                            <i class="fas fa-medal text-slate-600 text-3xl"></i>
                        </div>
                        <p class="text-4xl font-bold text-slate-900 mb-2">{{ $tierBreakdown['Silver'] }}</p>
                        <p class="text-xs text-slate-700">1000 - 2499 points</p>
                    </div>

                    {{-- Gold --}}
                    <div class="bg-gradient-to-br from-yellow-200 to-yellow-600 rounded-xl p-6 border border-yellow-300">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-bold text-gray-900 text-lg">Gold</h4>
                            <i class="fas fa-medal text-yellow-600 text-3xl"></i>
                        </div>
                        <p class="text-4xl font-bold text-yellow-900 mb-2">{{ $tierBreakdown['Gold'] }}</p>
                        <p class="text-xs text-yellow-700">2500 - 4999 points</p>
                    </div>

                    {{-- Platinum --}}
                    <div class="bg-gradient-to-br from-cyan-200 to-cyan-600 rounded-xl p-6 border border-cyan-300">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-bold text-gray-900 text-lg">Platinum</h4>
                            <i class="fas fa-crown text-cyan-600 text-3xl"></i>
                        </div>
                        <p class="text-4xl font-bold text-cyan-900 mb-2">{{ $tierBreakdown['Platinum'] }}</p>
                        <p class="text-xs text-cyan-700">5000+ points</p>
                    </div>
                </div>

                {{-- Top Performers --}}
                <div>
                    <h4 class="font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-trophy text-yellow-500 mr-2"></i> Top 10 Performers
                    </h4>
                    <div class="space-y-2">
                        @foreach($topPerformers as $index => $performer)
                            <div class="flex items-center justify-between p-3 rounded-lg {{ $index == 0 ? 'bg-yellow-50 border border-yellow-200' : ($index == 1 ? 'bg-slate-50 border border-slate-200' : ($index == 2 ? 'bg-orange-50 border border-orange-200' : 'bg-gray-50 border border-gray-200')) }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center font-bold text-sm">
                                        {{ $index + 1 }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $performer->customer->name ?? 'N/A' }}</p>
                                        <p class="text-xs text-gray-500">{{ $performer->tier }}</p>
                                    </div>
                                </div>
                                <span class="font-bold text-indigo-600 text-lg">{{ number_format($performer->points) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 3: MANAGE VOUCHERS --}}
        <div id="vouchers-tab" class="tab-content hidden space-y-6">
            {{-- ADD NEW VOUCHER FORM --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-plus text-green-600 mr-3"></i> Add New Voucher
                </h3>

                <form action="{{ route('staff.loyalty.store_voucher') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">Voucher Code</label>
                        <input type="text" name="code" class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., HASTA10-ABC123" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">Discount Amount (RM)</label>
                        <input type="number" name="amount" step="0.01" class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500" placeholder="50.00" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">Type</label>
                        <select name="type" class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500" required>
                            <option value="Rental Discount">Rental Discount</option>
                            <option value="Merchant Reward">Merchant Reward</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">Valid From</label>
                        <input type="date" name="valid_from" class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">Valid Until</label>
                        <input type="date" name="valid_until" class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">Description</label>
                        <input type="text" name="description" class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Optional" maxlength="255">
                    </div>
                    <div class="md:col-span-3">
                        <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-lg transition">
                            <i class="fas fa-plus mr-2"></i> Create Voucher
                        </button>
                    </div>
                </form>
            </div>

            {{-- RENTAL DISCOUNT VOUCHERS --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-car text-purple-600 mr-3"></i> Car Rental Discount Vouchers ({{ $rentalVouchers->count() }})
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th class="px-6 py-3 text-left font-bold text-gray-700">Code</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-700">Amount (RM)</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-700">Valid From</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-700">Valid Until</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-700">Status</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rentalVouchers as $voucher)
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-bold text-gray-900">{{ $voucher->code }}</td>
                                    <td class="px-6 py-4 text-center font-bold text-indigo-600">RM {{ number_format($voucher->voucherAmount, 2) }}</td>
                                    <td class="px-6 py-4 text-center text-gray-700">{{ $voucher->validFrom?->format('M d, Y') ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-center text-gray-700">{{ $voucher->validUntil?->format('M d, Y') ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $voucher->isUsed ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                            {{ $voucher->isUsed ? 'Used' : 'Active' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center space-x-2">
                                        <button onclick="editVoucher({{ $voucher->voucherID }})" class="text-blue-600 hover:text-blue-800 font-bold transition">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form action="{{ route('staff.loyalty.delete_voucher', $voucher->voucherID) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 font-bold transition" onclick="return confirm('Delete this voucher?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                        <p>No rental vouchers yet</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- MERCHANT REWARD VOUCHERS --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-tag text-purple-600 mr-3"></i> Merchant Reward Vouchers ({{ $merchantVouchers->count() }})
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th class="px-6 py-3 text-left font-bold text-gray-700">Code</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-700">Merchant</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-700">Amount (RM)</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-700">Valid Until</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-700">Status</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($merchantVouchers as $voucher)
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-bold text-gray-900">{{ $voucher->code }}</td>
                                    <td class="px-6 py-4 text-gray-700">{{ $voucher->redeem_place ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-center font-bold text-indigo-600">RM {{ number_format($voucher->voucherAmount, 2) }}</td>
                                    <td class="px-6 py-4 text-center text-gray-700">{{ $voucher->validUntil?->format('M d, Y') ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $voucher->isUsed ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                            {{ $voucher->isUsed ? 'Used' : 'Active' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center space-x-2">
                                        <button onclick="editVoucher({{ $voucher->voucherID }})" class="text-blue-600 hover:text-blue-800 font-bold transition">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form action="{{ route('staff.loyalty.delete_voucher', $voucher->voucherID) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 font-bold transition" onclick="return confirm('Delete this voucher?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                        <p>No merchant vouchers yet</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 4: RECENT ACTIVITIES --}}
        <div id="activities-tab" class="tab-content hidden">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-history text-blue-600 mr-3"></i> Recent Loyalty Activities
                </h3>

                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @forelse($recentActivities as $activity)
                        <div class="flex items-start justify-between p-4 rounded-lg hover:bg-gray-50 transition border border-gray-100">
                            <div class="flex-1">
                                <p class="text-sm font-bold text-gray-900">{{ $activity->reason }}</p>
                                <p class="text-xs text-gray-600 mt-1">
                                    <i class="fas fa-user mr-1"></i> {{ $activity->customer->name ?? 'Unknown' }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">{{ $activity->created_at->format('M d, Y H:i') }}</p>
                            </div>
                            <span class="font-bold text-lg {{ $activity->points_change > 0 ? 'text-green-600' : 'text-red-600' }} ml-4">
                                {{ $activity->points_change > 0 ? '+' : '' }}{{ number_format($activity->points_change) }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400">
                            <p class="text-sm">No recent activities</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPTS --}}
<script>
    function showTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.add('hidden');
        });
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-orange-500', 'text-gray-900');
            btn.classList.add('border-transparent', 'text-gray-500');
        });

        // Show selected tab
        document.getElementById(tabName + '-tab').classList.remove('hidden');
        event.target.closest('.tab-btn').classList.remove('border-transparent', 'text-gray-500');
        event.target.closest('.tab-btn').classList.add('border-orange-500', 'text-gray-900');
    }

    function editVoucher(voucherId) {
        // Fetch voucher data via AJAX
        fetch(`/staff/loyalty/voucher/${voucherId}/edit`)
            .then(response => response.json())
            .then(data => {
                // Populate modal with voucher data
                document.getElementById('editVoucherId').value = data.voucherID;
                document.getElementById('editVoucherCode').value = data.voucherCode;
                document.getElementById('editVoucherAmount').value = data.voucherAmount;
                document.getElementById('editVoucherType').value = data.voucherType;
                document.getElementById('editValidFrom').value = data.validFrom.split(' ')[0];
                document.getElementById('editValidUntil').value = data.validUntil.split(' ')[0];
                document.getElementById('editVoucherDescription').value = data.conditions || '';
                
                // Show modal
                document.getElementById('editModal').classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to load voucher data');
            });
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('editModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditModal();
        }
    });
</script>

{{-- EDIT VOUCHER MODAL --}}
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900">Edit Voucher</h3>
            <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="editVoucherForm" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            
            <input type="hidden" id="editVoucherId" name="id">

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Voucher Code</label>
                <input type="text" id="editVoucherCode" name="code" required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Type</label>
                <select id="editVoucherType" name="type" required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    <option value="Rental Discount">Rental Discount</option>
                    <option value="Merchant Reward">Merchant Reward</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Amount (RM)</label>
                <input type="number" id="editVoucherAmount" name="amount" step="0.01" required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Valid From</label>
                    <input type="date" id="editValidFrom" name="valid_from" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Valid Until</label>
                    <input type="date" id="editValidUntil" name="valid_until" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Description (Optional)</label>
                <textarea id="editVoucherDescription" name="description" rows="3" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"></textarea>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeEditModal()" 
                    class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-semibold">
                    Cancel
                </button>
                <button type="submit" 
                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                    Update Voucher
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Update form action when editing
    document.getElementById('editVoucherForm')?.addEventListener('submit', function(e) {
        const voucherId = document.getElementById('editVoucherId').value;
        this.action = `/staff/loyalty/voucher/${voucherId}`;
    });
</script>
@endsection
