<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $user->name }}'s Ratings
                </h2>
            </div>
            <a href="{{ route('admin.users.activity', $user) }}" class="text-sm text-gray-600 hover:text-gray-900">
                ← Back to User Activity
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Admin Notice -->
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Viewing ratings from <strong>{{ $user->name }}</strong> ({{ $user->email }})
                        </p>
                    </div>
                </div>
            </div>

            @if($ratings->count() > 0)
                <div class="bg-white shadow-sm rounded-lg divide-y divide-gray-200">
                    @foreach($ratings as $rating)
                        <div class="p-6 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <a href="{{ route('library.show', $rating->book->slug) }}" class="text-lg font-medium text-blue-600 hover:text-blue-800" target="_blank">
                                            {{ $rating->book->title }}
                                        </a>
                                        <span class="text-sm text-gray-500">
                                            {{ $rating->created_at->diffForHumans() }}
                                        </span>
                                    </div>

                                    <div class="flex items-center mt-3">
                                        <div class="flex">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="h-5 w-5 {{ $i <= $rating->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                        </div>
                                        <span class="ml-2 text-sm font-medium text-gray-700">
                                            {{ $rating->rating }} out of 5
                                        </span>
                                    </div>

                                    <p class="text-xs text-gray-400 mt-2">
                                        Rated on {{ $rating->created_at->format('F j, Y') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $ratings->links() }}
                </div>
            @else
                <div class="bg-white shadow-sm rounded-lg p-12 text-center">
                    <h3 class="text-sm font-medium text-gray-900">No ratings</h3>
                    <p class="mt-1 text-sm text-gray-500">This user hasn't rated any books yet.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
