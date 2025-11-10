<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $user->name }}'s Reviews
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
                            Viewing reviews from <strong>{{ $user->name }}</strong> ({{ $user->email }})
                        </p>
                    </div>
                </div>
            </div>

            @if($reviews->count() > 0)
                <div class="space-y-6">
                    @foreach($reviews as $review)
                        <div class="bg-white shadow-sm rounded-lg p-6">
                            <div class="flex items-start justify-between mb-3">
                                <a href="{{ route('library.show', $review->book->slug) }}" class="text-lg font-medium text-blue-600 hover:text-blue-800" target="_blank">
                                    {{ $review->book->title }}
                                </a>
                                <div class="flex items-center space-x-2">
                                    @if($review->is_approved)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Approved
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    @endif
                                    <span class="text-sm text-gray-500">
                                        {{ $review->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>

                            <div class="prose prose-sm max-w-none text-gray-700 bg-gray-50 p-4 rounded border border-gray-200">
                                {{ $review->review_text }}
                            </div>

                            <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
                                <span>Submitted on {{ $review->created_at->format('F j, Y') }}</span>
                                @if($review->is_approved && $review->approved_at)
                                    <span>Approved on {{ $review->approved_at->format('F j, Y') }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $reviews->links() }}
                </div>
            @else
                <div class="bg-white shadow-sm rounded-lg p-12 text-center">
                    <h3 class="text-sm font-medium text-gray-900">No reviews</h3>
                    <p class="mt-1 text-sm text-gray-500">This user hasn't submitted any reviews yet.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
