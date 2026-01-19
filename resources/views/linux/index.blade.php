<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Linux System Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- System Info Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">System Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><strong>Hostname:</strong> {{ $systemInfo['hostname'] ?? 'N/A' }}</div>
                        <div><strong>OS:</strong> {{ $systemInfo['os'] ?? 'N/A' }}</div>
                        <div><strong>Release:</strong> {{ $systemInfo['release'] ?? 'N/A' }}</div>
                        <div><strong>Uptime:</strong> {{ $systemInfo['uptime'] ?? 'N/A' }}</div>
                        <div><strong>Current User:</strong> {{ $systemInfo['current_user'] ?? 'N/A' }}</div>
                        <div><strong>Load Average:</strong> {{ implode(', ', $systemInfo['load_average'] ?? []) }}</div>
                    </div>
                </div>
            </div>

            <!-- Disk Usage Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Disk Usage</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filesystem</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Used</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Use%</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mounted On</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($diskUsage as $disk)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $disk['filesystem'] ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $disk['size'] ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $disk['used'] ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $disk['available'] ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if(intval(rtrim($disk['use_percent'] ?? '0%', '%')) < 70) bg-green-100 text-green-800
                                                @elseif(intval(rtrim($disk['use_percent'] ?? '0%', '%')) < 90) bg-yellow-100 text-yellow-800
                                                @else bg-red-100 text-red-800
                                                @endif">
                                                {{ $disk['use_percent'] ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $disk['mounted_on'] ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Memory Info Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Memory Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if(isset($memoryInfo['mem']))
                            <div class="bg-gray-50 p-4 rounded">
                                <h4 class="font-medium text-gray-700">Memory (RAM)</h4>
                                <div>Total: {{ $memoryInfo['mem']['total'] ?? 'N/A' }}</div>
                                <div>Used: {{ $memoryInfo['mem']['used'] ?? 'N/A' }}</div>
                                <div>Free: {{ $memoryInfo['mem']['free'] ?? 'N/A' }}</div>
                                <div>Available: {{ $memoryInfo['mem']['available'] ?? 'N/A' }}</div>
                            </div>
                        @endif
                        
                        @if(isset($memoryInfo['swap']))
                            <div class="bg-gray-50 p-4 rounded">
                                <h4 class="font-medium text-gray-700">Swap</h4>
                                <div>Total: {{ $memoryInfo['swap']['total'] ?? 'N/A' }}</div>
                                <div>Used: {{ $memoryInfo['swap']['used'] ?? 'N/A' }}</div>
                                <div>Free: {{ $memoryInfo['swap']['free'] ?? 'N/A' }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- CPU Info Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">CPU Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><strong>Architecture:</strong> {{ $cpuInfo['architecture'] ?? 'N/A' }}</div>
                        <div><strong>CPU Op-Mode(s):</strong> {{ $cpuInfo['cpu_op_modes'] ?? 'N/A' }}</div>
                        <div><strong>Number of Cores:</strong> {{ $cpuInfo['cpu_count'] ?? 'N/A' }}</div>
                        <div><strong>Thread(s) per Core:</strong> {{ $cpuInfo['threads_per_core'] ?? 'N/A' }}</div>
                        <div><strong>Core(s) per Socket:</strong> {{ $cpuInfo['cores_per_socket'] ?? 'N/A' }}</div>
                        <div><strong>Socket(s):</strong> {{ $cpuInfo['sockets'] ?? 'N/A' }}</div>
                        <div><strong>Vendor ID:</strong> {{ $cpuInfo['vendor_id'] ?? 'N/A' }}</div>
                        <div><strong>CPU Model:</strong> {{ $cpuInfo['model_name'] ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <!-- Network Interfaces Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Network Interfaces</h3>
                    <div class="space-y-4">
                        @foreach($networkInfo as $interface)
                            <div class="border rounded p-4">
                                <h4 class="font-medium text-gray-700">{{ $interface['name'] }}</h4>
                                @if(!empty($interface['ipv4']))
                                    <div><strong>IPv4:</strong> {{ implode(', ', $interface['ipv4']) }}</div>
                                @endif
                                @if(!empty($interface['ipv6']))
                                    <div><strong>IPv6:</strong> {{ implode(', ', $interface['ipv6']) }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Top Processes Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Top Processes</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">USER</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">%CPU</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">%MEM</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">COMMAND</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach(array_slice($processes, 0, 10) as $process)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $process['user'] ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $process['pid'] ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $process['%cpu'] ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $process['%mem'] ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap max-w-xs truncate">{{ $process['command'] ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Command Runner Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Execute Linux Command</h3>
                    <form id="commandForm" class="space-y-4">
                        @csrf
                        <div>
                            <label for="command" class="block text-sm font-medium text-gray-700">Command</label>
                            <input type="text" name="command" id="command" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Enter a safe Linux command...">
                        </div>
                        <div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Execute Command
                            </button>
                        </div>
                    </form>
                    
                    <div id="commandResult" class="mt-4 hidden">
                        <h4 class="text-md font-medium text-gray-900 mb-2">Command Output:</h4>
                        <pre id="commandOutput" class="bg-gray-100 p-4 rounded text-sm overflow-x-auto"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('commandForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const command = document.getElementById('command').value;
            const resultDiv = document.getElementById('commandResult');
            const outputDiv = document.getElementById('commandOutput');
            
            if (!command.trim()) {
                alert('Please enter a command');
                return;
            }
            
            // Show loading state
            outputDiv.textContent = 'Executing command...';
            resultDiv.classList.remove('hidden');
            
            fetch('/linux/run-command', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ command: command })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    outputDiv.textContent = data.output;
                    outputDiv.className = 'bg-gray-100 p-4 rounded text-sm overflow-x-auto';
                } else {
                    outputDiv.textContent = 'Error: ' + (data.error || data.output);
                    outputDiv.className = 'bg-red-100 p-4 rounded text-sm overflow-x-auto text-red-700';
                }
            })
            .catch(error => {
                outputDiv.textContent = 'Error: ' + error.message;
                outputDiv.className = 'bg-red-100 p-4 rounded text-sm overflow-x-auto text-red-700';
            });
        });
    </script>
</x-app-layout>