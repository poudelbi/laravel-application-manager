<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Sites Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-3xl font-bold text-gray-800">Available Applications</h1>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(count($sites) > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($sites as $site)
                                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                                    <h2 class="text-xl font-semibold text-gray-800 mb-2">{{ $site['name'] }}</h2>
                                    <p class="text-gray-600 text-sm mb-2">Path: {{ $site['path'] }}</p>
                                    <p class="text-gray-600 text-sm mb-2">Created: {{ date('M j, Y g:i A', $site['created_at']) }}</p>
                                    <p class="text-gray-600 text-sm mb-2">Laravel: {{ $site['laravel_version'] }}</p>
                                    <p class="text-gray-600 text-sm mb-2">PHP: {{ $site['php_version'] }}</p>
                                    <p class="text-gray-600 text-sm mb-2">Port: {{ $site['port'] }}</p>
                                    <p class="text-gray-600 text-sm mb-4">
                                        Status:
                                        @if($site['is_running'])
                                            <span class="text-green-600">Running</span>
                                        @else
                                            <span class="text-red-600">Stopped</span>
                                        @endif
                                    </p>

                                    <div class="flex flex-wrap gap-2">
                                        @if($site['is_running'])
                                            <a href="{{ $site['url'] }}" target="_blank"
                                               class="bg-blue-600 hover:bg-blue-700 text-white py-1 px-3 rounded text-sm">
                                                Visit
                                            </a>
                                            <form action="{{ route('sites.stop', $site['name']) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="bg-red-600 hover:bg-red-700 text-white py-1 px-3 rounded text-sm"
                                                        onclick="return confirm('Are you sure you want to stop this application?')">
                                                    Stop
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('sites.start', $site['name']) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="bg-green-600 hover:bg-green-700 text-white py-1 px-3 rounded text-sm">
                                                   Start
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('sites.update', $site['name']) }}"
                                           class="bg-yellow-600 hover:bg-yellow-700 text-white py-1 px-3 rounded text-sm"
                                           onclick="return confirm('Are you sure you want to update this application from Git?')">
                                            Update
                                        </a>

                                        <a href="{{ route('sites.refresh', $site['name']) }}"
                                           class="bg-purple-600 hover:bg-purple-700 text-white py-1 px-3 rounded text-sm">
                                            Refresh
                                        </a>

                                        <a href="{{ route('sites.nginx-config', $site['name']) }}"
                                           class="bg-indigo-600 hover:bg-indigo-700 text-white py-1 px-3 rounded text-sm"
                                           onclick="return confirm('Are you sure you want to create nginx configuration for this application?')">
                                            Nginx Config
                                        </a>

                                        <form action="{{ route('sites.destroy', $site['name']) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="bg-red-700 hover:bg-red-800 text-white py-1 px-3 rounded text-sm"
                                                    onclick="return confirm('Are you sure you want to remove nginx configuration for this application?')">
                                                Remove Config
                                            </button>
                                        </form>

                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-white rounded-lg shadow-md p-8 text-center">
                            <h3 class="text-xl font-medium text-gray-700 mb-2">No Applications Found</h3>
                            <p class="text-gray-600 mb-4">No applications created yet. Create an application first.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-app-layout>