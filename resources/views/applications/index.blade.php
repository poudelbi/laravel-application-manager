<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Applications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                        <h1 class="text-3xl font-bold text-gray-800">Laravel Applications</h1>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('applications.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create New Application
                            </a>
                            <form action="{{ route('applications.deploy.official') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit"
                                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                                        onclick="return confirm('This will deploy the official Laravel application with test migration and seeding. Continue?')">
                                    Deploy Official Laravel
                                </button>
                            </form>
                        </div>
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

                    @if(count($applications) > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($applications as $app)
                                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                                    <h2 class="text-xl font-semibold text-gray-800 mb-2">{{ $app['name'] }}</h2>

                                    <div class="mb-4">
                                        <p class="text-gray-600 text-sm">Path: {{ $app['path'] }}</p>
                                        <p class="text-gray-600 text-sm">Created: {{ date('M j, Y g:i A', $app['created_at']) }}</p>
                                    </div>

                                    <!-- Composer.json Info -->
                                    <div class="mb-4 p-3 bg-gray-50 rounded">
                                        <h3 class="font-medium text-gray-700 text-sm mb-1">Composer Info:</h3>
                                        <p class="text-xs text-gray-600 truncate" title="{{ $app['composer_info']['description'] }}">Description: {{ $app['composer_info']['description'] }}</p>
                                        <p class="text-xs text-gray-600">Name: {{ $app['composer_info']['name'] }}</p>
                                        <p class="text-xs text-gray-600">Version: {{ $app['composer_info']['version'] }}</p>
                                        <p class="text-xs text-gray-600">PHP: {{ $app['composer_info']['php_version'] }}</p>
                                        <p class="text-xs text-gray-600">Laravel: {{ $app['composer_info']['laravel_version'] }}</p>
                                        @if(!empty($app['composer_info']['keywords']))
                                            <p class="text-xs text-gray-600">Keywords: {{ implode(', ', $app['composer_info']['keywords']) }}</p>
                                        @endif
                                        <p class="text-xs text-gray-600">License: {{ $app['composer_info']['license'] }}</p>

                                        <!-- Display all PHP dependencies directly on card -->
                                        @if(!empty($app['composer_info']['require']))
                                            <div class="mt-1">
                                                <p class="text-xs text-gray-600 font-medium">PHP Dependencies:</p>
                                                @foreach($app['composer_info']['require'] as $pkg => $version)
                                                    <p class="text-xs text-gray-600 ml-2">{{ $pkg }}: {{ $version }}</p>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if(!empty($app['composer_info']['require_dev']))
                                            <details class="mt-1">
                                                <summary class="text-xs text-gray-600 cursor-pointer">Show Dev Dependencies</summary>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    @foreach($app['composer_info']['require_dev'] as $pkg => $version)
                                                        <div>{{ $pkg }}: {{ $version }}</div>
                                                    @endforeach
                                                </div>
                                            </details>
                                        @endif
                                    </div>

                                    <!-- Package.json Info -->
                                    <div class="mb-4 p-3 bg-gray-50 rounded">
                                        <h3 class="font-medium text-gray-700 text-sm mb-1">Package Info:</h3>
                                        <p class="text-xs text-gray-600">Name: {{ $app['package_info']['name'] }}</p>
                                        <p class="text-xs text-gray-600">Version: {{ $app['package_info']['version'] }}</p>
                                        <p class="text-xs text-gray-600">Author: {{ $app['package_info']['author'] }}</p>
                                        <p class="text-xs text-gray-600">License: {{ $app['package_info']['license'] }}</p>
                                        @if(!empty($app['package_info']['scripts']))
                                            <details class="mt-1">
                                                <summary class="text-xs text-gray-600 cursor-pointer">Show Scripts</summary>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    @foreach($app['package_info']['scripts'] as $script => $cmd)
                                                        <div>{{ $script }}: {{ $cmd }}</div>
                                                    @endforeach
                                                </div>
                                            </details>
                                        @endif
                                        <!-- Display all NPM dependencies directly on card -->
                                        @if(!empty($app['package_info']['dependencies']))
                                            <div class="mt-1">
                                                <p class="text-xs text-gray-600 font-medium">NPM Dependencies:</p>
                                                @foreach($app['package_info']['dependencies'] as $pkg => $version)
                                                    <p class="text-xs text-gray-600 ml-2">{{ $pkg }}: {{ $version }}</p>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if(!empty($app['package_info']['dev_dependencies']))
                                            <details class="mt-1">
                                                <summary class="text-xs text-gray-600 cursor-pointer">Show Dev Dependencies</summary>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    @foreach($app['package_info']['dev_dependencies'] as $pkg => $version)
                                                        <div>{{ $pkg }}: {{ $version }}</div>
                                                    @endforeach
                                                </div>
                                            </details>
                                        @endif
                                    </div>

                                    <div class="flex space-x-2">
                                        <a href="{{ route('applications.deploy', $app['name']) }}"
                                           class="bg-green-600 hover:bg-green-700 text-white py-1 px-3 rounded text-sm">
                                            Deploy
                                        </a>
                                        <a href="{{ route('applications.nginx-config', $app['name']) }}"
                                           class="bg-blue-600 hover:bg-blue-700 text-white py-1 px-3 rounded text-sm"
                                           target="_blank">
                                            Nginx Config
                                        </a>
                                        <form action="{{ route('applications.destroy', $app['name']) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="bg-red-600 hover:bg-red-700 text-white py-1 px-3 rounded text-sm"
                                                    onclick="return confirm('Are you sure you want to delete this application?')">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-white rounded-lg shadow-md p-8 text-center">
                            <h3 class="text-xl font-medium text-gray-700 mb-2">No Applications Found</h3>
                            <p class="text-gray-600 mb-4">Create your first Laravel application to get started.</p>
                            <a href="{{ route('applications.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create Application
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>