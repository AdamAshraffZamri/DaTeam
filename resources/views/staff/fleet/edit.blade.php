@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-gray-100 p-8 flex justify-center items-start" 
     x-data="fleetForm({
        category: '{{ $vehicle->vehicle_category }}',
        imagePreview: '{{ $vehicle->image ? asset('storage/' . $vehicle->image) : '' }}',
        type: '{{ $vehicle->type }}',
        color: '{{ $vehicle->color }}',
        fuelType: '{{ $vehicle->fuelType }}',
        year: '{{ $vehicle->year }}'
     })">
    
    <form action="{{ route('staff.fleet.update', $vehicle->VehicleID) }}" method="POST" enctype="multipart/form-data" class="w-full max-w-7xl">
        @csrf
        @method('PUT')
        
        <input type="hidden" name="vehicle_category" :value="category">

        <div class="bg-white/80 backdrop-blur-xl rounded-[3rem] shadow-sm border border-white overflow-hidden flex flex-col md:flex-row">
            
            <div class="w-full md:w-1/3 bg-gray-50/50 p-12 border-r border-gray-100 space-y-10">
                <div>
                    <div class="flex items-center justify-between mb-10">
                        <h1 class="text-3xl font-black text-gray-900 leading-none">Modify<br><span class="text-orange-500">Asset.</span></h1>
                        <a href="{{ route('staff.fleet.index') }}" class="text-gray-400 hover:text-gray-900 transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </a>
                    </div>
                    
                    <div class="space-y-4">
                        <label class="block w-full aspect-square border-2 border-dashed border-gray-200 rounded-[2.5rem] flex flex-col items-center justify-center cursor-pointer hover:border-orange-500 hover:bg-orange-50/50 transition-all group relative overflow-hidden bg-white shadow-inner">
                            <template x-if="!imagePreview">
                                <div class="flex flex-col items-center justify-center p-6 text-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                        <i class="fas fa-cloud-upload-alt text-gray-300 text-2xl group-hover:text-orange-500"></i>
                                    </div>
                                    <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Replace Photo</span>
                                </div>
                            </template>
                            
                            <template x-if="imagePreview">
                                <img :src="imagePreview" class="w-full h-full object-cover">
                            </template>

                            <input type="file" name="image" @change="previewImage" class="hidden" accept="image/*">
                        </label>
                        <p x-show="fileName" x-text="fileName" class="text-[10px] text-orange-500 font-black text-center uppercase tracking-widest"></p>
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block text-center">Vehicle Category</label>
                    <div class="flex bg-gray-100 p-1.5 rounded-2xl border border-gray-200 shadow-inner max-w-[200px] mx-auto">
                        <button type="button" @click="category = 'car'" 
                            :class="category === 'car' ? 'bg-white text-orange-600 shadow-md ring-1 ring-black/5' : 'text-gray-400'" 
                            class="flex-1 h-14 rounded-xl transition-all duration-300 flex items-center justify-center">
                            <i class="fas fa-car text-xl"></i>
                        </button>
                        <button type="button" @click="category = 'bike'" 
                            :class="category === 'bike' ? 'bg-white text-orange-600 shadow-md ring-1 ring-black/5' : 'text-gray-400'" 
                            class="flex-1 h-14 rounded-xl transition-all duration-300 flex items-center justify-center">
                            <i class="fas fa-motorcycle text-xl"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Classification</label>
                    <select name="type" x-model="type" class="w-full bg-white border border-gray-100 rounded-2xl p-4 text-sm font-bold shadow-sm outline-none" required>
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
                </div>
            </div>

            <div class="flex-1 p-12 md:p-16">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                    <div class="space-y-8">
                        <h3 class="text-[11px] font-black text-orange-500 uppercase tracking-[0.4em] border-b border-orange-100 pb-2">Technical Profile</h3>
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Plate Number</label>
                            <input type="text" name="plateNo" value="{{ $vehicle->plateNo }}" class="w-full bg-transparent border-b-2 border-gray-100 py-2 font-black text-gray-900 tracking-widest uppercase outline-none focus:border-orange-500" required>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Brand</label>
                                <input type="text" name="brand" value="{{ $vehicle->brand }}" class="w-full bg-transparent border-b-2 border-gray-100 py-2 font-bold outline-none focus:border-orange-500" required>
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Model</label>
                                <input type="text" name="model" value="{{ $vehicle->model }}" class="w-full bg-transparent border-b-2 border-gray-100 py-2 font-bold outline-none focus:border-orange-500" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Year</label>
                                <select name="year" x-model="year" class="w-full bg-transparent border-b-2 border-gray-100 py-2 font-bold outline-none">
                                    @for($i = date('Y'); $i >= 2010; $i--)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Color</label>
                                <select name="color" x-model="color" class="w-full bg-transparent border-b-2 border-gray-100 py-2 font-bold outline-none">
                                    <option value="Ivory White">Ivory White</option>
                                    <option value="Granite Grey">Granite Grey</option>
                                    <option value="Glittering Silver">Glittering Silver</option>
                                    <option value="Ocean Blue">Ocean Blue</option>
                                    <option value="Lava Red">Lava Red</option>
                                    <option value="Midnight Black">Midnight Black</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Mileage (Odometer)</label>
                                <input type="number" name="mileage" value="{{ $vehicle->mileage }}" class="w-full bg-transparent border-b-2 border-gray-100 py-2 font-bold outline-none">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Fuel Type</label>
                                <select name="fuelType" x-model="fuelType" class="w-full bg-transparent border-b-2 border-gray-100 py-2 font-bold outline-none">
                                    <option value="Petrol (RON95)">Petrol (RON95)</option>
                                    <option value="Petrol (RON97)">Petrol (RON97)</option>
                                    <option value="Diesel">Diesel</option>
                                    <option value="Electric">Electric</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-8">
                        <h3 class="text-[11px] font-black text-blue-500 uppercase tracking-[0.4em] border-b border-blue-50 pb-2">Legal & Ownership</h3>
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Owner Full Name</label>
                            <input type="text" name="owner_name" value="{{ $vehicle->owner_name }}" class="w-full bg-transparent border-b-2 border-gray-100 py-2 font-bold outline-none">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Contact No.</label>
                                <input type="text" name="owner_phone" value="{{ $vehicle->owner_phone }}" class="w-full bg-transparent border-b-2 border-gray-100 py-2 font-bold outline-none">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Owner NRIC/ID</label>
                                <input type="text" name="owner_nric" value="{{ $vehicle->owner_nric }}" class="w-full bg-transparent border-b-2 border-gray-100 py-2 font-bold outline-none">
                            </div>
                        </div>
                        <div class="pt-4">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Security Deposit (RM)</label>
                            <input type="number" name="baseDepo" value="{{ $vehicle->baseDepo }}" class="w-full bg-transparent border-b-2 border-orange-200 py-2 font-black text-orange-600 outline-none">
                        </div>
                    </div>
                </div>

                <div class="mt-16 bg-gray-50/80 p-10 rounded-[2.5rem] border border-gray-100 shadow-inner">
                    <h4 class="text-[11px] font-black text-gray-900 uppercase tracking-[0.4em] mb-10 text-center">Update Hourly Rate Architecture (RM)</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-4">
                        @foreach([1, 3, 5, 7, 9, 12, 24] as $h)
                        <div x-data="{ price: {{ $vehicle->hourly_rates[$h] ?? 0 }} }" class="text-center group">
                            <span class="text-[8px] font-black text-gray-400 uppercase mb-3 block tracking-tighter">{{ $h }} Hour</span>
                            <div class="flex items-center justify-between bg-white rounded-2xl p-1 border border-gray-200 shadow-sm group-hover:border-orange-300 transition-all">
                                <button type="button" @click="price = Math.max(0, parseInt(price) - 5)" class="w-6 h-6 text-gray-300 hover:text-orange-500 font-black">-</button>
                                <input type="number" name="rates[{{ $h }}]" x-model="price" class="w-8 bg-transparent text-center font-black text-gray-900 outline-none text-[10px]">
                                <button type="button" @click="price = parseInt(price) + 5" class="w-6 h-6 text-gray-300 hover:text-orange-500 font-black">+</button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="w-full mt-12 bg-gray-900 text-white py-6 rounded-3xl font-black text-[11px] uppercase tracking-[0.3em] shadow-xl hover:bg-orange-600 transition-all transform active:scale-95">
                    Update Fleet Record
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function fleetForm(initialData) {
        return {
            category: initialData.category || 'car',
            imagePreview: initialData.imagePreview || null,
            type: initialData.type || '',
            color: initialData.color || 'Ivory White',
            fuelType: initialData.fuelType || 'Petrol (RON95)',
            year: initialData.year || new Date().getFullYear(),
            fileName: '',
            previewImage(event) {
                const file = event.target.files[0];
                if (file) {
                    this.fileName = 'Selected: ' + file.name;
                    this.imagePreview = URL.createObjectURL(file);
                }
            }
        }
    }
</script>
@endsection