<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $user->name }}'s Downloads
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
                            Viewing downloads from <strong>{{ $user->name }}</strong> ({{ $user->email }})
                        </p>
                    </div>
                </div>
            </div>

            @if($downloads->count() > 0)
                <div class="bg-white shadow-sm rounded-lg divide-y divide-gray-200">
                    @foreach($downloads as $download)
                        <div class="p-6 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <a href="{{ route('library.show', $download->book->slug) }}" class="text-lg font-medium text-blue-600 hover:text-blue-800" target="_blank">
                                            {{ $download->book->title }}
                                        </a>
                                        <span class="text-sm text-gray-500">
                                            {{ $download->created_at->diffForHumans() }}
                                        </span>
                                    </div>

                                    <div class="flex items-center mt-2 space-x-4">
                                        @if($download->book->publication_year)
                                            <span class="text-sm text-gray-500">
                                                Published: {{ $download->book->publication_year }}
                                            </span>
                                        @endif

                                        @php
                                            $badgeColors = [
                                                'full' => 'bg-green-100 text-green-800',
                                                'limited' => 'bg-yellow-100 text-yellow-800',
                                                'unavailable' => 'bg-red-100 text-red-800'
                                            ];
                                            $badgeColor = $badgeColors[$download->book->access_level] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeColor }}">
                                            {{ ucfirst($download->book->access_level) }} Access
                                        </span>
                                    </div>

                                    <p class="text-xs text-gray-400 mt-3">
                                        Downloaded on {{ $download->created_at->format('F j, Y \a\t g:i A') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $downloads->links() }}
                </div>
            @else
                <div class="bg-white shadow-sm rounded-lg p-12 text-center">
                    <h3 class="text-sm font-medium text-gray-900">No downloads</h3>
                    <p class="mt-1 text-sm text-gray-500">This user hasn't downloaded any books yet.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
