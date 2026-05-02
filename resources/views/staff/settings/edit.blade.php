@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900">Settings</h1>
            <p class="mt-2 text-gray-600">Manage current deals and Johor highlights for your staff site</p>
        </div>

        <!-- Alert Messages -->
        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                <p class="font-semibold mb-2">Please fix the following errors:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-8">

            <!-- Current Deals Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-tag text-blue-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Current Deals</h2>
                </div>

                <!-- Existing Deals List -->
                @if ($deals->count() > 0)
                    <div class="mb-6 space-y-4">
                        <p class="text-sm text-gray-600 font-medium">Your Current Deals ({{ $deals->count() }})</p>
                        <div class="space-y-3">
                            @foreach ($deals as $deal)
                                <div class="border border-gray-200 rounded-lg p-4 flex items-start gap-4 bg-gray-50">
                                    @if ($deal->image_path)
                                        <img src="{{ Storage::disk('public')->url($deal->image_path) }}" alt="Deal" class="w-20 h-20 rounded object-cover">
                                    @else
                                        <div class="w-20 h-20 bg-gray-200 rounded flex items-center justify-center">
                                            <i class="fas fa-image text-gray-400"></i>
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <h4 class="font-bold text-gray-900">{{ $deal->title ?? 'Untitled Deal' }}</h4>
                                        @if ($deal->description)
                                            <p class="text-sm text-gray-600 mt-1">{{ Str::limit($deal->description, 100) }}</p>
                                        @endif
                                        <div class="flex gap-2 mt-3">
                                            <a href="{{ route('staff.deals.edit', $deal->id) }}" 
                                               class="text-xs px-3 py-1 bg-orange-100 text-orange-700 rounded hover:bg-orange-200 transition">
                                                Edit
                                            </a>
                                            <form action="{{ route('staff.deals.delete', $deal->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 transition"
                                                        onclick="return confirm('Delete this deal?')">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <p class="text-gray-500 italic mb-6">No deals created yet. Add your first deal below.</p>
                @endif

                <!-- Add New Deal Form -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Add New Deal</h3>
                    <form action="{{ route('staff.deals.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <!-- Deal Image Upload -->
                        <div>
                            <label for="deal_image" class="block text-sm font-semibold text-gray-700 mb-3">
                                Deal Image <span class="text-red-600">*</span>
                            </label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 hover:border-blue-400 transition text-center">
                                <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl mb-2"></i>
                                <p class="text-gray-700 font-medium">Click to upload or drag and drop</p>
                                <p class="text-gray-500 text-sm">PNG, JPG, GIF, WebP up to 5MB</p>
                                <input 
                                    type="file" 
                                    id="deal_image" 
                                    name="deal_image" 
                                    accept="image/*"
                                    required
                                    class="hidden"
                                    onchange="previewNewDeal()"
                                >
                                <button 
                                    type="button" 
                                    onclick="document.getElementById('deal_image').click()"
                                    class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                                >
                                    Select File
                                </button>
                            </div>
                            <img id="deal_preview" src="" alt="Preview" class="hidden mt-4 h-32 rounded-lg object-cover border border-gray-200">
                        </div>

                        <!-- Deal Title -->
                        <div>
                            <label for="deal_title" class="block text-sm font-semibold text-gray-700 mb-2">
                                Deal Title
                            </label>
                            <input 
                                type="text" 
                                id="deal_title" 
                                name="deal_title" 
                                placeholder="e.g., Summer Sale 50% Off"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                maxlength="100"
                            >
                        </div>

                        <!-- Deal Description -->
                        <div>
                            <label for="deal_description" class="block text-sm font-semibold text-gray-700 mb-2">
                                Deal Description
                            </label>
                            <textarea 
                                id="deal_description" 
                                name="deal_description" 
                                rows="3"
                                placeholder="Describe this deal..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                                maxlength="500"
                            ></textarea>
                            <p class="mt-1 text-xs text-gray-500">Maximum 500 characters</p>
                        </div>

                        <button 
                            type="submit" 
                            class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition flex items-center justify-center gap-2"
                        >
                            <i class="fas fa-plus"></i>
                            Add Deal
                        </button>
                    </form>
                </div>
            </div>

            <!-- Johor Highlights Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-map-marker-alt text-purple-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Johor Highlights</h2>
                </div>

                <form action="{{ route('staff.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <!-- Johor Highlights Image Upload -->
                    <div>
                        <label for="johor_highlights_image" class="block text-sm font-semibold text-gray-700 mb-3">
                            Johor Highlights Image
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 hover:border-purple-400 transition text-center">
                            <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl mb-2"></i>
                            <p class="text-gray-700 font-medium">Click to upload or drag and drop</p>
                            <p class="text-gray-500 text-sm">PNG, JPG, GIF, WebP up to 5MB</p>
                            <input 
                                type="file" 
                                id="johor_highlights_image" 
                                name="johor_highlights_image" 
                                accept="image/*"
                                class="hidden"
                                onchange="previewJohorImage()"
                            >
                            <button 
                                type="button" 
                                onclick="document.getElementById('johor_highlights_image').click()"
                                class="mt-4 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition"
                            >
                                Select File
                            </button>
                        </div>

                        @if ($settings->johor_highlights_image_path)
                            <div class="mt-4">
                                <p class="text-sm text-gray-600 mb-2">Current image:</p>
                                <img 
                                    src="{{ Storage::disk('public')->url($settings->johor_highlights_image_path) }}" 
                                    alt="Current Johor highlights"
                                    class="h-40 rounded-lg object-cover border border-gray-200"
                                >
                            </div>
                        @endif

                        <img 
                            id="johor_preview" 
                            src="" 
                            alt="Preview" 
                            class="hidden mt-4 h-40 rounded-lg object-cover border border-gray-200"
                        >
                    </div>

                    <button 
                        type="submit" 
                        class="w-full px-4 py-3 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition flex items-center justify-center gap-2"
                    >
                        <i class="fas fa-save"></i>
                        Save Johor Highlights
                    </button>
                </form>
            </div>
        </div>

        <!-- Footer Links -->
        <div class="mt-8 text-center">
            <a href="{{ route('staff.dashboard') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                ← Back to Dashboard
            </a>
        </div>
    </div>
</div>

<script>
function previewNewDeal() {
    const file = document.getElementById('deal_image').files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            document.getElementById('deal_preview').src = e.target.result;
            document.getElementById('deal_preview').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
}

function previewJohorImage() {
    const file = document.getElementById('johor_highlights_image').files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            document.getElementById('johor_preview').src = e.target.result;
            document.getElementById('johor_preview').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
}
</script>
@endsection
