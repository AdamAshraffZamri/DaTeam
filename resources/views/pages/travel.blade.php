@extends('layouts.app')

@section('content')
<div class="pt-10 pb-20 bg-[#0a0a0a] min-h-screen">
    {{-- Decorative background glow --}}
    <div class="container mx-auto px-6">
        <div class="flex flex-col md:flex-row justify-between items-end mb-16">
            <div>
                <h1 class="text-6xl font-black text-white tracking-tighter uppercase mb-4">Travel <span class="text-orange-500">Guides</span></h1>
                <p class="text-gray-400">Discover the best hidden gems and seasonal events around Johor.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            {{-- Feature Card --}}
            <div class="group relative rounded-[3rem] overflow-hidden aspect-[4/3] cursor-pointer">
                <img src="https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&q=80&w=1200" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000">
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent p-12 flex flex-col justify-end">
                    <span class="bg-orange-500 text-white px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest w-fit mb-4">Seasonal Bazaar</span>
                    <h3 class="text-4xl font-bold text-white mb-4">Larkin Ramadan Market</h3>
                    <p class="text-gray-300 text-sm mb-6 max-w-sm">Experience the vibrant street food culture of Johor Bahru with over 200 stalls at the Larkin Sentral bazaar.</p>
                    <div class="flex items-center gap-2 text-orange-500 font-bold">
                        Navigate to Location <i class="fas fa-arrow-right"></i>
                    </div>
                </div>
            </div>

            <div class="group relative rounded-[3rem] overflow-hidden aspect-[4/3] cursor-pointer">
                <img src="https://images.unsplash.com/photo-1596422846543-75c6fc18a5ce?auto=format&fit=crop&q=80&w=1200" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000">
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent p-12 flex flex-col justify-end">
                    <span class="bg-blue-600 text-white px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest w-fit mb-4">Coastal Escape</span>
                    <h3 class="text-4xl font-bold text-white mb-4">Desaru Coast</h3>
                    <p class="text-gray-300 text-sm mb-6 max-w-sm">A world-class destination featuring pristine beaches and the Adventure Waterpark.</p>
                    <div class="flex items-center gap-2 text-orange-500 font-bold">
                        Read Travel Guide <i class="fas fa-arrow-right"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection