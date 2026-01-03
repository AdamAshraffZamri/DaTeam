@extends('layouts.staff')

@section('content')
<style>
    /* Utility to hide number input arrows */
    .no-spin::-webkit-inner-spin-button, 
    .no-spin::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
    .no-spin { 
        -moz-appearance: textfield; 
    }
</style>

{{-- GREY BACKGROUND RECTANGLE --}}
<div class="min-h-screen bg-gray-100 rounded-2xl p-6">
    <div class="max-w-7xl mx-auto">

        {{-- BACK BUTTON --}}
        <a href="{{ route('staff.fleet.index') }}" class="inline-flex items-center text-gray-500 hover:text-gray-900 mb-6 transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Fleet
        </a>

        {{-- === 1. SUCCESS MESSAGE === --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-2xl p-4 flex items-center gap-4 shadow-sm animate-pulse">
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 shrink-0 border border-green-200">
                    <i class="fas fa-check text-sm"></i>
                </div>
                <div>
                    <h4 class="text-sm font-black text-green-900">Success</h4>
                    <p class="text-xs text-green-700 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- === 2. ERROR MESSAGE === --}}
        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded-2xl p-4 shadow-sm">
                <div class="flex items-center gap-4 mb-2">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 shrink-0 border border-red-200">
                        <i class="fas fa-exclamation text-sm"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-red-900">Unable to Save Vehicle</h4>
                        <p class="text-xs text-red-700 font-medium">Please check the following errors:</p>
                    </div>
                </div>
                <ul class="list-disc list-inside text-xs text-red-600 font-bold ml-14 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div x-data="fleetForm()">
            <form action="{{ route('staff.fleet.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="vehicle_category" :value="category">

                {{-- MAIN WHITE CARD --}}
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden flex flex-col lg:flex-row">
                    
                    {{-- LEFT SIDE: VISUALS --}}
                    <div class="w-full lg:w-1/3 bg-gray-50/50 p-10 border-r border-gray-100 flex flex-col gap-8">
                        <div>
                            <h1 class="text-3xl font-black text-gray-900 leading-none mb-2">Register<br><span class="text-orange-600">Fleet.</span></h1>
                            <p class="text-xs text-gray-400 font-medium">Upload vehicle image and select category.</p>
                        </div>

                        {{-- Image Upload --}}
                        <div class="space-y-4">
                            <label class="block w-full aspect-[4/3] border-2 border-dashed border-gray-300 rounded-[2rem] flex flex-col items-center justify-center cursor-pointer hover:border-orange-500 hover:bg-white transition-all group relative overflow-hidden bg-gray-100">
                                <template x-if="!imagePreview">
                                    <div class="flex flex-col items-center justify-center p-6 text-center">
                                        <div class="w-14 h-14 bg-white rounded-full flex items-center justify-center mb-3 shadow-sm group-hover:scale-110 transition-transform">
                                            <i class="fas fa-cloud-upload-alt text-gray-300 text-xl group-hover:text-orange-500"></i>
                                        </div>
                                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest group-hover:text-orange-600">Upload Photo</span>
                                    </div>
                                </template>
                                <template x-if="imagePreview">
                                    <img :src="imagePreview" class="w-full h-full object-cover">
                                </template>
                                <input type="file" name="image" @change="previewImage" class="hidden" accept="image/*">
                            </label>
                            <p x-show="fileName" x-text="fileName" class="text-[10px] text-orange-500 font-bold text-center uppercase tracking-widest truncate px-4"></p>
                        </div>

                        {{-- Category --}}
                        <div class="space-y-4">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block text-center">Vehicle Type</label>
                            <div class="flex bg-white p-1.5 rounded-2xl border border-gray-200 shadow-sm mx-auto w-full">
                                <button type="button" @click="category = 'car'" 
                                    :class="category === 'car' ? 'bg-gray-900 text-white shadow-md' : 'text-gray-400 hover:bg-gray-50'" 
                                    class="flex-1 h-12 rounded-xl transition-all duration-300 flex items-center justify-center gap-2">
                                    <i class="fas fa-car text-lg"></i>
                                    <span class="text-xs font-bold">Car</span>
                                </button>
                                <button type="button" @click="category = 'bike'" 
                                    :class="category === 'bike' ? 'bg-gray-900 text-white shadow-md' : 'text-gray-400 hover:bg-gray-50'" 
                                    class="flex-1 h-12 rounded-xl transition-all duration-300 flex items-center justify-center gap-2">
                                    <i class="fas fa-motorcycle text-lg"></i>
                                    <span class="text-xs font-bold">Bike</span>
                                </button>
                            </div>
                        </div>

                        {{-- Classification --}}
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2">Classification</label>
                            <div class="relative mb-6">
                                <select name="type" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold text-gray-700 shadow-sm outline-none focus:border-orange-500 appearance-none" required>
                                    <template x-if="category === 'car'">
                                        <optgroup label="Car Variants">
                                            <option value="Compact">Compact</option>
                                            <option value="Hatchback">Hatchback</option>
                                            <option value="Sedan">Sedan</option>
                                            <option value="SUV">SUV</option>
                                            <option value="MPV">MPV</option>
                                        </optgroup>
                                    </template>
                                    <template x-if="category === 'bike'">
                                        <optgroup label="Bike Variants">
                                            <option value="Scooter">Scooter</option>
                                            <option value="Moped">Moped</option>
                                            <option value="Superbike">Superbike</option>
                                        </optgroup>
                                    </template>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-400">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>

                            {{-- INFO FILLER --}}
                            <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4">
                                <div class="flex gap-3">
                                    <div class="mt-0.5"><i class="fas fa-info-circle text-blue-400 text-xs"></i></div>
                                    <div>
                                        <h4 class="text-[10px] font-bold text-blue-800 uppercase tracking-wide mb-1">Fleet Standard</h4>
                                        <p class="text-[10px] text-blue-600 leading-relaxed">
                                            Ensure the selected classification matches the vehicle's registration card. This affects automated pricing logic.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT SIDE: FORM DATA --}}
                    <div class="flex-1 p-10 lg:p-14 space-y-12">
                        
                        {{-- 1. Technical Profile --}}
                        <div class="space-y-6">
                            <div class="flex items-center gap-4 mb-2">
                                <div class="h-px bg-gray-200 flex-1"></div>
                                <h3 class="text-xs font-bold text-gray-900 uppercase tracking-[0.2em]">Technical Profile</h3>
                                <div class="h-px bg-gray-200 flex-1"></div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                <div class="col-span-full">
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1.5 ml-1">Plate Number</label>
                                    <input type="text" name="plateNo" placeholder="UTMXXXX" value="{{ old('plateNo') }}" class="w-full bg-gray-50 border border-gray-100 rounded-xl px-4 py-3 font-bold text-gray-900 tracking-wider uppercase outline-none focus:bg-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-all" required>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1.5 ml-1">Brand</label>
                                    <input type="text" name="brand" placeholder="Perodua" value="{{ old('brand') }}" class="w-full bg-gray-50 border border-gray-100 rounded-xl px-4 py-3 font-medium text-gray-900 outline-none focus:bg-white focus:border-orange-500 transition-all" required>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1.5 ml-1">Model</label>
                                    <input type="text" name="model" placeholder="Myvi" value="{{ old('model') }}" class="w-full bg-gray-50 border border-gray-100 rounded-xl px-4 py-3 font-medium text-gray-900 outline-none focus:bg-white focus:border-orange-500 transition-all" required>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1.5 ml-1">Year</label>
                                    <div class="relative">
                                        <select name="year" class="w-full bg-gray-50 border border-gray-100 rounded-xl px-4 py-3 font-medium text-gray-900 outline-none focus:bg-white focus:border-orange-500 appearance-none cursor-pointer">
                                            @for($i = date('Y'); $i >= 2010; $i--)
                                                <option value="{{ $i }}" {{ old('year') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1.5 ml-1">Color</label>
                                    <select name="color" class="w-full bg-gray-50 border border-gray-100 rounded-xl px-4 py-3 font-medium text-gray-900 outline-none focus:bg-white focus:border-orange-500 appearance-none cursor-pointer">
                                        @foreach(['Yellow','Orange','Blue','White','Green','Purple','Gold','Black','Red','Silver'] as $color)
                                            <option value="{{ $color }}" {{ old('color') == $color ? 'selected' : '' }}>{{ $color }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1.5 ml-1">Mileage</label>
                                    <input type="number" name="mileage" placeholder="0" value="{{ old('mileage') }}" class="w-full bg-gray-50 border border-gray-100 rounded-xl px-4 py-3 font-medium text-gray-900 outline-none focus:bg-white focus:border-orange-500 transition-all no-spin">
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1.5 ml-1">Fuel Type</label>
                                    <select name="fuelType" class="w-full bg-gray-50 border border-gray-100 rounded-xl px-4 py-3 font-medium text-gray-900 outline-none focus:bg-white focus:border-orange-500 appearance-none cursor-pointer">
                                        <option value="Petrol (RON95)" {{ old('fuelType') == 'Petrol (RON95)' ? 'selected' : '' }}>Petrol (RON95)</option>
                                        <option value="Petrol (RON97)" {{ old('fuelType') == 'Petrol (RON97)' ? 'selected' : '' }}>Petrol (RON97)</option>
                                        <option value="Diesel" {{ old('fuelType') == 'Diesel' ? 'selected' : '' }}>Diesel</option>
                                        <option value="Electric" {{ old('fuelType') == 'Electric' ? 'selected' : '' }}>Electric</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Ownership --}}
                        <div class="space-y-6">
                            <div class="flex items-center gap-4 mb-2">
                                <div class="h-px bg-gray-200 flex-1"></div>
                                <h3 class="text-xs font-bold text-blue-900 uppercase tracking-[0.2em]">Ownership</h3>
                                <div class="h-px bg-gray-200 flex-1"></div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                <div class="col-span-full">
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1.5 ml-1">Owner Name</label>
                                    <input type="text" name="owner_name" placeholder="Hasta Travel & Tours" value="{{ old('owner_name') }}" class="w-full bg-blue-50/50 border border-blue-100 rounded-xl px-4 py-3 font-medium text-gray-900 outline-none focus:bg-white focus:border-blue-500 transition-all">
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1.5 ml-1">Contact No.</label>
                                    <input type="text" name="owner_phone" placeholder="012-3456789" value="{{ old('owner_phone') }}" class="w-full bg-blue-50/50 border border-blue-100 rounded-xl px-4 py-3 font-medium text-gray-900 outline-none focus:bg-white focus:border-blue-500 transition-all">
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1.5 ml-1">NRIC / ID</label>
                                    <input type="text" name="owner_nric" placeholder="XXXXXX-XX-XXXX" value="{{ old('owner_nric') }}" class="w-full bg-blue-50/50 border border-blue-100 rounded-xl px-4 py-3 font-medium text-gray-900 outline-none focus:bg-white focus:border-blue-500 transition-all">
                                </div>
                                <div class="col-span-full">
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1.5 ml-1">Base Security Deposit (RM)</label>
                                    <input type="number" name="baseDepo" value="{{ old('baseDepo', 50) }}" class="w-full bg-orange-50/50 border border-orange-100 rounded-xl px-4 py-3 font-bold text-orange-600 outline-none focus:bg-white focus:border-orange-500 transition-all no-spin">
                                </div>
                            </div>
                        </div>

                        {{-- 3. Pricing --}}
                        <div class="bg-gray-50/50 rounded-[2rem] p-8 border border-gray-100">
                            <h4 class="text-xs font-bold text-gray-900 uppercase tracking-[0.2em] mb-6 text-center border-b border-gray-200 pb-4">Hourly Rates (RM)</h4>
                            <div class="space-y-3">
                                @foreach([1, 3, 5, 7, 9, 12, 24] as $h)
                                <div x-data="{ price: {{ old("rates.$h", 0) }} }" class="flex items-center justify-between bg-white p-3 pr-4 rounded-xl border border-gray-200 shadow-sm hover:border-orange-300 transition-all group">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-xs font-black text-gray-500 group-hover:bg-orange-50 group-hover:text-orange-600 transition-colors">
                                            {{ $h }}H
                                        </div>
                                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">{{ $h }} Hour Rate</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="price = Math.max(0, parseInt(price) - 5)" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-orange-600 transition-all">
                                            <i class="fas fa-minus text-xs"></i>
                                        </button>
                                        <div class="relative w-16">
                                            <span class="absolute left-2 top-1/2 -translate-y-1/2 text-[10px] font-bold text-gray-400">RM</span>
                                            <input type="number" name="rates[{{ $h }}]" x-model="price" class="w-full pl-6 pr-2 py-1 text-right font-black text-gray-900 bg-transparent outline-none border-b border-gray-200 focus:border-orange-500 transition-colors no-spin">
                                        </div>
                                        <button type="button" @click="price = parseInt(price) + 5" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-orange-600 transition-all">
                                            <i class="fas fa-plus text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- SUBMIT --}}
                        <div class="pt-4">
                            <button type="submit" class="w-full bg-gray-900 text-white py-5 rounded-2xl font-bold text-xs uppercase tracking-[0.2em] shadow-xl hover:bg-orange-600 hover:shadow-orange-500/30 transition-all transform active:scale-[0.98] flex items-center justify-center gap-3">
                                <span>Add & Save Vehicle</span>
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function fleetForm() {
        return {
            category: '{{ old('vehicle_category', 'car') }}',
            imagePreview: null,
            fileName: '',
            previewImage(event) {
                const file = event.target.files[0];
                if (file) {
                    this.fileName = file.name;
                    this.imagePreview = URL.createObjectURL(file);
                }
            }
        }
    }
</script>
@endsection