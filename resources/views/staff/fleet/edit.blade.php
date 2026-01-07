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
<div class="min-h-screen bg-slate-100 rounded-2xl p-6">
    <div class="max-w-7xl mx-auto">

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
                        <h4 class="text-sm font-black text-red-900">Unable to Update Vehicle</h4>
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
            {{-- UPDATE FORM --}}
            <form x-ref="form" @submit.prevent="submitForm" action="{{ route('staff.fleet.update', $vehicle->VehicleID) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="vehicle_category" :value="category">

                {{-- MAIN WHITE CARD --}}
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden flex flex-col lg:flex-row">
                    
                    {{-- === LEFT SIDE: VISUALS & SETTINGS === --}}
                    <div class="w-full lg:w-1/3 bg-gray-50/50 p-8 lg:p-10 border-r border-gray-100 flex flex-col gap-8">
                        <div>
                            <h1 class="text-3xl font-black text-gray-900 leading-none mb-2">Edit<br><span class="text-orange-600">Vehicle.</span></h1>
                            <p class="text-xs text-gray-400 font-medium">Update vehicle details and documents.</p>
                        </div>

                        {{-- 1. Main Image Upload --}}
                        <div class="space-y-4">
                            <label class="block w-full aspect-[4/3] border-2 border-dashed border-gray-300 rounded-[2rem] flex flex-col items-center justify-center cursor-pointer hover:border-orange-500 hover:bg-white transition-all group relative overflow-hidden bg-gray-100">
                                <template x-if="!imagePreview">
                                    <div class="flex flex-col items-center justify-center p-6 text-center">
                                        <div class="w-14 h-14 bg-white rounded-full flex items-center justify-center mb-3 shadow-sm group-hover:scale-110 transition-transform">
                                            <i class="fas fa-cloud-upload-alt text-gray-300 text-xl group-hover:text-orange-500"></i>
                                        </div>
                                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest group-hover:text-orange-600">Change Photo</span>
                                    </div>
                                </template>
                                <template x-if="imagePreview">
                                    <img :src="imagePreview" class="w-full h-full object-cover">
                                </template>
                                <input type="file" name="image" @change="previewImage" class="hidden" accept="image/*">
                            </label>
                            <p x-show="fileName" x-text="fileName" class="text-[10px] text-orange-500 font-bold text-center uppercase tracking-widest truncate px-4"></p>
                        </div>

                        {{-- 2. Category --}}
                        <div class="space-y-4">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block text-center">Vehicle Type <span class="text-red-500">*</span></label>
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

                        {{-- 3. Classification --}}
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2">Classification <span class="text-red-500">*</span></label>
                            <div class="relative mb-6">
                                <select name="type" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold text-gray-700 shadow-sm outline-none focus:border-orange-500 appearance-none" required>
                                    <template x-if="category === 'car'">
                                        <optgroup label="Car Variants">
                                            @foreach(['Compact', 'Hatchback', 'Sedan', 'SUV', 'MPV'] as $type)
                                                <option value="{{ $type }}" {{ old('type', $vehicle->type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                                            @endforeach
                                        </optgroup>
                                    </template>
                                    <template x-if="category === 'bike'">
                                        <optgroup label="Bike Variants">
                                            @foreach(['Scooter', 'Moped', 'Superbike'] as $type)
                                                <option value="{{ $type }}" {{ old('type', $vehicle->type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                                            @endforeach
                                        </optgroup>
                                    </template>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-400">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        {{-- 4. Hourly Rates --}}
                        <div>
                            <div class="flex items-center gap-2 mb-4">
                                <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Hourly Rates (RM) <span class="text-red-500">*</span></h4>
                                <div class="h-px bg-gray-200 flex-1"></div>
                            </div>
                            
                            <div class="space-y-2">
                                @php
                                    $rates = $vehicle->hourly_rates ?? [];
                                @endphp
                                @foreach([1, 3, 5, 7, 9, 12, 24] as $h)
                                <div x-data="{ price: {{ old("rates.$h", $rates[$h] ?? 0) }} }" class="flex items-center justify-between bg-white p-2 rounded-xl border border-gray-200 shadow-sm hover:border-orange-300 transition-all group">
                                    <div class="flex items-center gap-3 pl-1">
                                        <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-[10px] font-black text-gray-500 group-hover:bg-orange-50 group-hover:text-orange-600 transition-colors">
                                            {{ $h }}H
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 pr-1">
                                        <button type="button" @click="price = Math.max(0, parseInt(price) - 5)" class="w-6 h-6 rounded-full flex items-center justify-center text-gray-300 hover:bg-gray-100 hover:text-orange-600">
                                            <i class="fas fa-minus text-[10px]"></i>
                                        </button>
                                        <div class="relative w-12 text-center">
                                            <input type="number" name="rates[{{ $h }}]" x-model="price" min="0" class="w-full text-center py-1 text-sm font-black text-gray-900 bg-transparent outline-none focus:text-orange-600 transition-colors no-spin" required>
                                        </div>
                                        <button type="button" @click="price = parseInt(price) + 5" class="w-6 h-6 rounded-full flex items-center justify-center text-gray-300 hover:bg-gray-100 hover:text-orange-600">
                                            <i class="fas fa-plus text-[10px]"></i>
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Info Filler --}}
                        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 mt-auto">
                            <div class="flex gap-3">
                                <div class="mt-0.5"><i class="fas fa-info-circle text-blue-400 text-xs"></i></div>
                                <div>
                                    <h4 class="text-[10px] font-bold text-blue-800 uppercase tracking-wide mb-1">Fleet Standard</h4>
                                    <p class="text-[10px] text-blue-600 leading-relaxed">
                                        Rates are calculated automatically based on base price, but you can override specific tiers here.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- === RIGHT SIDE: FORM DATA === --}}
                    <div class="flex-1 p-10 lg:p-14 space-y-12">
                        
                        {{-- 1. Technical Profile --}}
                        <div class="space-y-6">
                            <div class="flex items-center gap-4 mb-2">
                                <div class="h-px bg-gray-200 flex-1"></div>
                                <h3 class="text-xs font-bold text-orange-900 uppercase tracking-[0.2em]">Technical Profile</h3>
                                <div class="h-px bg-gray-200 flex-1"></div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                <div class="col-span-full">
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1.5 ml-1">Plate Number <span class="text-red-500">*</span></label>
                                    <input type="text" name="plateNo" placeholder="UTMXXXX" value="{{ old('plateNo', $vehicle->plateNo) }}" 
                                           class="w-full bg-orange-50/50 border border-orange-100 rounded-xl px-4 py-3 font-bold text-gray-900 tracking-wider uppercase outline-none focus:bg-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-all" 
                                           required x-on:input="$el.value = $el.value.toUpperCase().replace(/[^A-Z0-9\s]/g, '')">
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1.5 ml-1">Brand <span class="text-red-500">*</span></label>
                                    <input type="text" name="brand" placeholder="Perodua" value="{{ old('brand', $vehicle->brand) }}" class="w-full bg-orange-50/50 border border-orange-100 rounded-xl px-4 py-3 font-medium text-gray-900 outline-none focus:bg-white focus:border-orange-500 transition-all" required>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1.5 ml-1">Model <span class="text-red-500">*</span></label>
                                    <input type="text" name="model" placeholder="Myvi" value="{{ old('model', $vehicle->model) }}" class="w-full bg-orange-50/50 border border-orange-100 rounded-xl px-4 py-3 font-medium text-gray-900 outline-none focus:bg-white focus:border-orange-500 transition-all" required>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1.5 ml-1">Year <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <select name="year" class="w-full bg-orange-50/50 border border-orange-100 rounded-xl px-4 py-3 font-medium text-gray-900 outline-none focus:bg-white focus:border-orange-500 appearance-none cursor-pointer" required>
                                            @for($i = date('Y'); $i >= 2010; $i--)
                                                <option value="{{ $i }}" {{ old('year', $vehicle->year) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1.5 ml-1">Color <span class="text-red-500">*</span></label>
                                    <select name="color" class="w-full bg-orange-50/50 border border-orange-100 rounded-xl px-4 py-3 font-medium text-gray-900 outline-none focus:bg-white focus:border-orange-500 appearance-none cursor-pointer" required>
                                        @foreach(['Yellow','Orange','Blue','White','Green','Purple','Gold','Black','Red','Silver'] as $color)
                                            <option value="{{ $color }}" {{ old('color', $vehicle->color) == $color ? 'selected' : '' }}>{{ $color }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- Optional Fields --}}
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1.5 ml-1">Mileage</label>
                                    <input type="number" name="mileage" placeholder="0" value="{{ old('mileage', $vehicle->mileage) }}" class="w-full bg-orange-50/50 border border-orange-100 rounded-xl px-4 py-3 font-medium text-gray-900 outline-none focus:bg-white focus:border-orange-500 transition-all no-spin">
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1.5 ml-1">Fuel Type</label>
                                    <select name="fuelType" class="w-full bg-orange-50/50 border border-orange-100 rounded-xl px-4 py-3 font-medium text-gray-900 outline-none focus:bg-white focus:border-orange-500 appearance-none cursor-pointer">
                                        <option value="Petrol (RON95)" {{ old('fuelType', $vehicle->fuelType) == 'Petrol (RON95)' ? 'selected' : '' }}>Petrol (RON95)</option>
                                        <option value="Petrol (RON97)" {{ old('fuelType', $vehicle->fuelType) == 'Petrol (RON97)' ? 'selected' : '' }}>Petrol (RON97)</option>
                                        <option value="Diesel" {{ old('fuelType', $vehicle->fuelType) == 'Diesel' ? 'selected' : '' }}>Diesel</option>
                                        <option value="Electric" {{ old('fuelType', $vehicle->fuelType) == 'Electric' ? 'selected' : '' }}>Electric</option>
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
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1.5 ml-1">Owner Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="owner_name" placeholder="Hasta Travel & Tours" value="{{ old('owner_name', $vehicle->owner_name) }}" 
                                           class="w-full bg-blue-50/50 border border-blue-100 rounded-xl px-4 py-3 font-medium text-gray-900 outline-none focus:bg-white focus:border-blue-500 transition-all" 
                                           required x-on:input="$el.value = $el.value.replace(/[^a-zA-Z\s\.]/g, '')">
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1.5 ml-1">Contact No. <span class="text-red-500">*</span></label>
                                    <input type="text" name="owner_phone" placeholder="012-3456789" value="{{ old('owner_phone', $vehicle->owner_phone) }}" 
                                           class="w-full bg-blue-50/50 border border-blue-100 rounded-xl px-4 py-3 font-medium text-gray-900 outline-none focus:bg-white focus:border-blue-500 transition-all" 
                                           required x-on:input="$el.value = $el.value.replace(/[^0-9\-\+\s]/g, '')">
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1.5 ml-1">NRIC / ID</label>
                                    <input type="text" name="owner_nric" placeholder="XXXXXX-XX-XXXX" value="{{ old('owner_nric', $vehicle->owner_nric) }}" 
                                           class="w-full bg-blue-50/50 border border-blue-100 rounded-xl px-4 py-3 font-medium text-gray-900 outline-none focus:bg-white focus:border-blue-500 transition-all"
                                           x-on:input="$el.value = $el.value.replace(/[^0-9\-]/g, '')">
                                </div>

                                {{-- === DOCUMENTS (Optional) === --}}
                                <div class="col-span-full space-y-4 pt-4">
                                    <label class="text-[10px] font-bold text-blue-900 uppercase tracking-widest block text-center">Legal Documents</label>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        
                                        {{-- 1. Road Tax --}}
                                        <div>
                                            <input type="hidden" name="delete_road_tax" x-model="deleteRoadTax">
                                            <div x-data="{ isDragging: false }"
                                                 @dragover.prevent="isDragging = true"
                                                 @dragleave.prevent="isDragging = false"
                                                 @drop.prevent="isDragging = false; handleDrop($event, $refs.roadTaxInput, 'roadTaxPreview', 'roadTaxName', 'deleteRoadTax')"
                                                 :class="isDragging ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200 scale-[1.02]' : 'border-blue-200 bg-white'"
                                                 class="relative block border-2 border-dashed rounded-xl p-1 h-32 flex flex-col items-center justify-center hover:border-blue-500 transition-all duration-200 overflow-hidden group">
                                                
                                                <button type="button" x-show="roadTaxName" @click.prevent="removeDoc('roadTax')" class="absolute top-2 right-2 z-20 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-600 transition-transform hover:scale-110">
                                                    <i class="fas fa-times text-[10px]"></i>
                                                </button>

                                                <label class="w-full h-full flex flex-col items-center justify-center cursor-pointer">
                                                    <div x-show="!roadTaxName" class="text-center pointer-events-none">
                                                        <i class="fas fa-file-invoice text-blue-200 text-2xl mb-2 group-hover:text-blue-500 transition-colors"></i>
                                                        <span class="block text-[9px] font-bold text-gray-400 uppercase group-hover:text-blue-600">
                                                            <span x-show="!isDragging">Upload or Drag</span>
                                                            <span x-show="isDragging" class="text-blue-600">Drop Here</span>
                                                        </span>
                                                    </div>
                                                    <template x-if="roadTaxName">
                                                        <div class="w-full h-full pointer-events-none">
                                                            <template x-if="roadTaxPreview">
                                                                <img :src="roadTaxPreview" class="absolute inset-0 w-full h-full object-cover rounded-lg">
                                                            </template>
                                                            <template x-if="!roadTaxPreview">
                                                                <div class="w-full h-full flex flex-col items-center justify-center bg-blue-50 text-blue-500 rounded-lg">
                                                                    <i class="fas fa-file-pdf text-2xl mb-1"></i>
                                                                    <span class="text-[8px] font-bold uppercase tracking-wider">PDF / File</span>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </template>
                                                    <input x-ref="roadTaxInput" type="file" name="road_tax_image" @change="previewDoc($event, 'roadTaxPreview', 'roadTaxName', 'deleteRoadTax')" accept=".pdf,image/*" class="hidden">
                                                </label>
                                            </div>
                                            <p x-show="roadTaxName" x-text="roadTaxName" class="text-[9px] text-blue-600 font-bold text-center uppercase tracking-widest truncate px-2 mt-2"></p>
                                            <p x-show="!roadTaxName" class="text-[9px] text-gray-400 font-bold text-center uppercase tracking-widest mt-2">Road Tax</p>
                                        </div>

                                        {{-- 2. Grant --}}
                                        <div>
                                            <input type="hidden" name="delete_grant" x-model="deleteGrant">
                                            <div x-data="{ isDragging: false }"
                                                 @dragover.prevent="isDragging = true"
                                                 @dragleave.prevent="isDragging = false"
                                                 @drop.prevent="isDragging = false; handleDrop($event, $refs.grantInput, 'grantPreview', 'grantName', 'deleteGrant')"
                                                 :class="isDragging ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200 scale-[1.02]' : 'border-blue-200 bg-white'"
                                                 class="relative block border-2 border-dashed rounded-xl p-1 h-32 flex flex-col items-center justify-center hover:border-blue-500 transition-all duration-200 overflow-hidden group">
                                                
                                                <button type="button" x-show="grantName" @click.prevent="removeDoc('grant')" class="absolute top-2 right-2 z-20 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-600 transition-transform hover:scale-110">
                                                    <i class="fas fa-times text-[10px]"></i>
                                                </button>

                                                <label class="w-full h-full flex flex-col items-center justify-center cursor-pointer">
                                                    <div x-show="!grantName" class="text-center pointer-events-none">
                                                        <i class="fas fa-scroll text-blue-200 text-2xl mb-2 group-hover:text-blue-500 transition-colors"></i>
                                                        <span class="block text-[9px] font-bold text-gray-400 uppercase group-hover:text-blue-600">
                                                            <span x-show="!isDragging">Upload or Drag</span>
                                                            <span x-show="isDragging" class="text-blue-600">Drop Here</span>
                                                        </span>
                                                    </div>
                                                    <template x-if="grantName">
                                                        <div class="w-full h-full pointer-events-none">
                                                            <template x-if="grantPreview">
                                                                <img :src="grantPreview" class="absolute inset-0 w-full h-full object-cover rounded-lg">
                                                            </template>
                                                            <template x-if="!grantPreview">
                                                                <div class="w-full h-full flex flex-col items-center justify-center bg-blue-50 text-blue-500 rounded-lg">
                                                                    <i class="fas fa-file-pdf text-2xl mb-1"></i>
                                                                    <span class="text-[8px] font-bold uppercase tracking-wider">PDF / File</span>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </template>
                                                    <input x-ref="grantInput" type="file" name="grant_image" @change="previewDoc($event, 'grantPreview', 'grantName', 'deleteGrant')" accept=".pdf,image/*" class="hidden">
                                                </label>
                                            </div>
                                            <p x-show="grantName" x-text="grantName" class="text-[9px] text-blue-600 font-bold text-center uppercase tracking-widest truncate px-2 mt-2"></p>
                                            <p x-show="!grantName" class="text-[9px] text-gray-400 font-bold text-center uppercase tracking-widest mt-2">Grant</p>
                                        </div>

                                        {{-- 3. Insurance --}}
                                        <div>
                                            <input type="hidden" name="delete_insurance" x-model="deleteInsurance">
                                            <div x-data="{ isDragging: false }"
                                                 @dragover.prevent="isDragging = true"
                                                 @dragleave.prevent="isDragging = false"
                                                 @drop.prevent="isDragging = false; handleDrop($event, $refs.insuranceInput, 'insurancePreview', 'insuranceName', 'deleteInsurance')"
                                                 :class="isDragging ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200 scale-[1.02]' : 'border-blue-200 bg-white'"
                                                 class="relative block border-2 border-dashed rounded-xl p-1 h-32 flex flex-col items-center justify-center hover:border-blue-500 transition-all duration-200 overflow-hidden group">
                                                
                                                <button type="button" x-show="insuranceName" @click.prevent="removeDoc('insurance')" class="absolute top-2 right-2 z-20 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-600 transition-transform hover:scale-110">
                                                    <i class="fas fa-times text-[10px]"></i>
                                                </button>

                                                <label class="w-full h-full flex flex-col items-center justify-center cursor-pointer">
                                                    <div x-show="!insuranceName" class="text-center pointer-events-none">
                                                        <i class="fas fa-shield-alt text-blue-200 text-2xl mb-2 group-hover:text-blue-500 transition-colors"></i>
                                                        <span class="block text-[9px] font-bold text-gray-400 uppercase group-hover:text-blue-600">
                                                            <span x-show="!isDragging">Upload or Drag</span>
                                                            <span x-show="isDragging" class="text-blue-600">Drop Here</span>
                                                        </span>
                                                    </div>
                                                    <template x-if="insuranceName">
                                                        <div class="w-full h-full pointer-events-none">
                                                            <template x-if="insurancePreview">
                                                                <img :src="insurancePreview" class="absolute inset-0 w-full h-full object-cover rounded-lg">
                                                            </template>
                                                            <template x-if="!insurancePreview">
                                                                <div class="w-full h-full flex flex-col items-center justify-center bg-blue-50 text-blue-500 rounded-lg">
                                                                    <i class="fas fa-file-pdf text-2xl mb-1"></i>
                                                                    <span class="text-[8px] font-bold uppercase tracking-wider">PDF / File</span>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </template>
                                                    <input x-ref="insuranceInput" type="file" name="insurance_image" @change="previewDoc($event, 'insurancePreview', 'insuranceName', 'deleteInsurance')" accept=".pdf,image/*" class="hidden">
                                                </label>
                                            </div>
                                            <p x-show="insuranceName" x-text="insuranceName" class="text-[9px] text-blue-600 font-bold text-center uppercase tracking-widest truncate px-2 mt-2"></p>
                                            <p x-show="!insuranceName" class="text-[9px] text-gray-400 font-bold text-center uppercase tracking-widest mt-2">Insurance</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-span-full">
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1.5 ml-1">Base Security Deposit (RM)</label>
                                    <input type="number" name="baseDepo" value="{{ old('baseDepo', $vehicle->baseDepo) }}" min="0" class="w-full bg-orange-50/50 border border-orange-100 rounded-xl px-4 py-3 font-bold text-orange-600 outline-none focus:bg-white focus:border-orange-500 transition-all no-spin">
                                </div>
                            </div>
                        </div>

                        {{-- SUBMIT --}}
                        <div class="pt-4">
                            <button type="submit" class="w-full bg-gray-900 text-white py-5 rounded-2xl font-bold text-xs uppercase tracking-[0.2em] shadow-xl hover:bg-orange-600 hover:shadow-orange-500/30 transition-all transform active:scale-[0.98] flex items-center justify-center gap-3">
                                <span>Update Vehicle</span>
                                <i class="fas fa-check"></i>
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
            category: '{{ old('vehicle_category', $vehicle->vehicle_category) }}',
            
            // Main Vehicle Image
            imagePreview: '{{ $vehicle->image ? asset("storage/" . $vehicle->image) : null }}',
            fileName: '{{ $vehicle->image ? basename($vehicle->image) : "" }}',
            
            // Delete Flags (0 = keep, 1 = delete)
            deleteRoadTax: 0,
            deleteGrant: 0,
            deleteInsurance: 0,

            // Document Data
            roadTaxPreview: '{{ $vehicle->road_tax_image && pathinfo($vehicle->road_tax_image, PATHINFO_EXTENSION) != "pdf" ? asset("storage/" . $vehicle->road_tax_image) : null }}',
            roadTaxName: '{{ $vehicle->road_tax_image ? basename($vehicle->road_tax_image) : "" }}',
            
            grantPreview: '{{ $vehicle->grant_image && pathinfo($vehicle->grant_image, PATHINFO_EXTENSION) != "pdf" ? asset("storage/" . $vehicle->grant_image) : null }}',
            grantName: '{{ $vehicle->grant_image ? basename($vehicle->grant_image) : "" }}',
            
            insurancePreview: '{{ $vehicle->insurance_image && pathinfo($vehicle->insurance_image, PATHINFO_EXTENSION) != "pdf" ? asset("storage/" . $vehicle->insurance_image) : null }}',
            insuranceName: '{{ $vehicle->insurance_image ? basename($vehicle->insurance_image) : "" }}',

            previewImage(event) {
                const file = event.target.files[0];
                if (file) {
                    this.fileName = file.name;
                    this.imagePreview = URL.createObjectURL(file);
                }
            },

            // Handle Drag & Drop
            handleDrop(event, inputElement, previewVar, nameVar, deleteVar) {
                const files = event.dataTransfer.files;
                if (files.length > 0) {
                    inputElement.files = files;
                    this.previewDoc({ target: { files: files } }, previewVar, nameVar, deleteVar);
                }
            },

            // Handle Preview and reset delete flag if user uploads new file
            previewDoc(event, previewVar, nameVar, deleteVar) {
                const file = event.target.files[0];
                if (file) {
                    this[nameVar] = file.name;
                    this[deleteVar] = 0; // Reset delete flag because user is uploading new file
                    
                    if (file.type.startsWith('image/')) {
                        this[previewVar] = URL.createObjectURL(file);
                    } else {
                        this[previewVar] = null; // PDF icon state
                    }
                }
            },

            // Handle Deletion
            removeDoc(type) {
                this[type + 'Name'] = '';
                this[type + 'Preview'] = null;
                let deleteVar = 'delete' + type.charAt(0).toUpperCase() + type.slice(1);
                this[deleteVar] = 1;
            },

            // Submit with Confirmation
            submitForm(event) {
                // Check HTML5 validity (required fields)
                if (!this.$refs.form.checkValidity()) {
                    this.$refs.form.reportValidity();
                    return;
                }

                if (confirm('Are you sure you want to update this vehicle?\n\nPlease confirm that all details are correct.')) {
                    this.$refs.form.submit();
                }
            }
        }
    }
</script>
@endsection