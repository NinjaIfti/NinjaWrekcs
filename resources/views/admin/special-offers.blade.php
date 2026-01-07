<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Special Offers') }}
            </h2>
            <a href="{{ route('admin.special-offers.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                + Add New Offer
            </a>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            @if($offers->isEmpty())
                <div class="text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">No special offers yet.</p>
                    <a href="{{ route('admin.special-offers.create') }}" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Create Your First Offer
                    </a>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($offers as $offer)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden {{ $offer->is_active ? '' : 'opacity-60' }}">
                            <div class="flex flex-col md:flex-row">
                                <!-- Image -->
                                <div class="md:w-1/3 bg-gray-100 dark:bg-gray-700 p-4 flex items-center justify-center">
                                    @if($offer->image_path)
                                        <img src="{{ asset('storage/' . $offer->image_path) }}" alt="{{ $offer->main_title }}" class="max-w-full h-48 object-cover rounded-lg">
                                    @else
                                        <div class="w-full h-48 bg-gray-300 dark:bg-gray-600 rounded-lg flex items-center justify-center">
                                            <span class="text-gray-500 dark:text-gray-400">No Image</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Content -->
                                <div class="md:w-2/3 p-6">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <span class="inline-block px-3 py-1 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 text-xs rounded-full mb-2">
                                                {{ $offer->badge_text }}
                                            </span>
                                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $offer->main_title }}</h3>
                                            @if($offer->subtitle)
                                                <p class="text-violet-600 dark:text-violet-400 text-sm mt-1">{{ $offer->subtitle }}</p>
                                            @endif
                                        </div>
                                        <span class="px-3 py-1 text-xs rounded-full {{ $offer->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                            {{ $offer->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>

                                    <p class="text-gray-600 dark:text-gray-400 mb-4">{{ Str::limit($offer->description, 150) }}</p>

                                    @if($offer->features)
                                        <div class="mb-4">
                                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Features:</p>
                                            <ul class="space-y-1">
                                                @foreach($offer->features as $feature)
                                                    <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                                        <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                        </svg>
                                                        {{ $feature }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.special-offers.edit', $offer) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                                            Edit
                                        </a>
                                        <form action="{{ route('admin.special-offers.destroy', $offer) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this special offer?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>













