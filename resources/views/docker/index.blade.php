<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Docker Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('success'))
                        <div class="alert alert-success bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Docker Status -->
                    <div class="mb-8">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Docker Status</h3>
                                <p><strong>Installed:</strong> 
                                    @if($dockerStatus['installed'])
                                        <span class="text-green-600">Yes</span>
                                    @else
                                        <span class="text-red-600">No</span>
                                    @endif
                                </p>
                                <p><strong>Running:</strong> 
                                    @if($dockerStatus['running'])
                                        <span class="text-green-600">Yes</span>
                                    @else
                                        <span class="text-red-600">No</span>
                                    @endif
                                </p>
                                @if($dockerStatus['version'])
                                    <p><strong>Version:</strong> {{ $dockerStatus['version'] }}</p>
                                @endif
                                <p><strong>Status Message:</strong> {{ $dockerStatus['message'] }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Managed Applications with Docker Controls -->
                    <div class="mb-8">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Managed Applications</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Path</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Docker Status</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($applications as $app)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $app['name'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $app['path'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span id="status-{{ $app['name'] }}">Checking...</span>
                                                    <script>
                                                        fetch('/docker/applications/{{ $app['name'] }}/status')
                                                            .then(response => response.json())
                                                            .then(data => {
                                                                const statusElement = document.getElementById('status-' + data.appName);
                                                                if (data.hasContainers) {
                                                                    statusElement.innerHTML = `<span class="text-green-600">Running (${data.containers.length} container(s))</span>`;
                                                                } else {
                                                                    statusElement.innerHTML = '<span class="text-yellow-600">Not Running</span>';
                                                                }
                                                            })
                                                            .catch(error => {
                                                                const statusElement = document.getElementById('status-' + '{{ $app['name'] }}');
                                                                statusElement.innerHTML = '<span class="text-gray-500">Error checking status</span>';
                                                            });
                                                    </script>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    <form method="POST" action="{{ route('docker.build-app-image', $app['name']) }}" style="display:inline-block;" onsubmit="return confirm('Build Docker image for {{ $app['name'] }}?')">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                                                            Build Image
                                                        </button>
                                                    </form>
                                                    
                                                    <form method="POST" action="{{ route('docker.start-app-container', $app['name']) }}" style="display:inline-block;">
                                                        @csrf
                                                        <div class="flex items-center">
                                                            <input type="number" name="port" value="90{{ $loop->index + 1 }}" min="8000" max="9999" class="border-gray-300 rounded-md shadow-sm w-20 mr-2" placeholder="Port">
                                                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:border-green-700 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                                                Start Container
                                                            </button>
                                                        </div>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Running Containers -->
                    <div class="mb-8">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Running Containers</h3>
                                @if(count($containers) > 0)
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ports</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($containers as $container)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ substr($container['id'], 0, 12) }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $container['name'] }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $container['image'] }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $container['status'] }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $container['ports'] }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                        <form method="POST" action="{{ route('docker.stop-app-container', $container['id']) }}" style="display:inline-block;" onsubmit="return confirm('Stop and remove container {{ $container['name'] }}?')">
                                                            @csrf
                                                            @method('POST')
                                                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:border-red-700 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                                                                Stop & Remove
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p>No containers are currently running.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Docker Images -->
                    <div>
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Docker Images</h3>
                                @if(count($images) > 0)
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Repository</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tag</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($images as $image)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $image['repository'] }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $image['tag'] }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ substr($image['id'], 0, 12) }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $image['created'] }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $image['size'] }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p>No Docker images found.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>