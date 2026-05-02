@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('staff.settings.edit') }}" class="text-blue-600 hover:text-blue-700 font-semibold mb-4 flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                Back to Settings
            </a>
            <h1 class="text-4xl font-bold text-gray-900">Edit Deal</h1>
            <p class="mt-2 text-gray-600">Update deal information and image</p>
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

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form action="{{ route('staff.deals.update', $deal->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Current Image -->
                @if ($deal->image_path)
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            Current Image
                        </label>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <img src="{{ Storage::disk('public')->url($deal->image_path) }}" 
                                 alt="Current Deal"
                                 class="h-48 rounded-lg object-cover w-full">
                        </div>
                    </div>
                @endif

                <!-- New Image Upload -->
                <div>
                    <label for="deal_image" class="block text-sm font-semibold text-gray-700 mb-3">
                        Update Deal Image (Optional)
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
                            class="hidden"
                            onchange="previewImage()"
                        >
                        <button 
                            type="button" 
                            onclick="document.getElementById('deal_image').click()"
                            class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                        >
                            Select File
                        </button>
                    </div>
                    <img id="preview" src="" alt="Preview" class="hidden mt-4 h-32 rounded-lg object-cover border border-gray-200">
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
                        value="{{ old('deal_title', $deal->title) }}"
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
                        rows="4"
                        placeholder="Describe this deal..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                        maxlength="500"
                    >{{ old('deal_description', $deal->description) }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Maximum 500 characters</p>
                </div>

                <!-- Form Actions -->
                <div class="flex gap-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('staff.settings.edit') }}" 
                       class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition">
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition flex items-center gap-2"
                    >
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Delete Option -->
        <div class="mt-6 bg-red-50 border border-red-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-red-900 mb-2">Danger Zone</h3>
            <p class="text-red-700 mb-4">Delete this deal permanently. This cannot be undone.</p>
            <form action="{{ route('staff.deals.delete', $deal->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button 
                    type="submit" 
                    class="px-6 py-3 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition"
                    onclick="return confirm('Are you sure you want to delete this deal? This cannot be undone.')"
                >
                    <i class="fas fa-trash"></i>
                    Delete Deal
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function previewImage() {
    const file = document.getElementById('deal_image').files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            document.getElementById('preview').src = e.target.result;
            document.getElementById('preview').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
}
</script>
@endsection
