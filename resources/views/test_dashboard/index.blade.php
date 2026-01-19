<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Test Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-3xl font-bold text-gray-800 mb-8">Laravel Application Manager - Master App Test Dashboard</h1>
                    <p class="text-lg text-gray-600 mb-6">Testing core functionality of the master application that manages Laravel deployments</p>

                    <!-- Master App Core Functionality Tests -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Master App Core Functionality Tests</h2>
                        <p class="text-gray-600 mb-4">Test the master application's core capabilities for managing Laravel deployments</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                                <h2 class="text-xl font-semibold text-blue-800 mb-4">Clone with Custom Name Test</h2>
                                <p class="text-gray-600 mb-4">Tests the master app's ability to clone repositories with custom application names</p>
                                <button
                                    onclick="runTest('repository_cloning')"
                                    class="run-test-btn bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded"
                                    data-test="repository_cloning">
                                    Run Test
                                </button>
                            </div>

                            <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                                <h2 class="text-xl font-semibold text-green-800 mb-4">Composer Install Test</h2>
                                <p class="text-gray-600 mb-4">Tests the master app's composer install functionality for Laravel applications</p>
                                <button
                                    onclick="runTest('composer_install')"
                                    class="run-test-btn bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded"
                                    data-test="composer_install">
                                    Run Test
                                </button>
                            </div>

                            <div class="bg-yellow-50 p-6 rounded-lg border border-yellow-200">
                                <h2 class="text-xl font-semibold text-yellow-800 mb-4">App Key Generation Test</h2>
                                <p class="text-gray-600 mb-4">Tests the master app's application key generation functionality</p>
                                <button
                                    onclick="runTest('app_key_generation')"
                                    class="run-test-btn bg-yellow-600 hover:bg-yellow-700 text-white py-2 px-4 rounded"
                                    data-test="app_key_generation">
                                    Run Test
                                </button>
                            </div>

                            <div class="bg-purple-50 p-6 rounded-lg border border-purple-200">
                                <h2 class="text-xl font-semibold text-purple-800 mb-4">NPM Install & Run Dev Test</h2>
                                <p class="text-gray-600 mb-4">Tests the master app's ability to run npm install and npm run dev commands</p>
                                <button
                                    onclick="runTest('deploy_official_laravel')"
                                    class="run-test-btn bg-purple-600 hover:bg-purple-700 text-white py-2 px-4 rounded"
                                    data-test="deploy_official_laravel">
                                    Run NPM Test
                                </button>
                            </div>

                            <div class="bg-red-50 p-6 rounded-lg border border-red-200">
                                <h2 class="text-xl font-semibold text-red-800 mb-4">Directory Creation Test</h2>
                                <p class="text-gray-600 mb-4">Tests the master app's creation of proper Laravel application directory structures</p>
                                <button
                                    onclick="runTest('directory_creation')"
                                    class="run-test-btn bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded"
                                    data-test="directory_creation">
                                    Run Test
                                </button>
                            </div>

                            <div class="bg-indigo-50 p-6 rounded-lg border border-indigo-200">
                                <h2 class="text-xl font-semibold text-indigo-800 mb-4">Permission Management Test</h2>
                                <p class="text-gray-600 mb-4">Verifies the master app's ability to set proper file permissions for web server access</p>
                                <button
                                    onclick="runTest('permission_management')"
                                    class="run-test-btn bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded"
                                    data-test="permission_management">
                                    Run Test
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- All Tests Section -->
                    <div class="bg-gradient-to-r from-gray-800 to-gray-900 p-6 rounded-lg border border-gray-700 mb-8 text-white">
                        <h2 class="text-xl font-semibold mb-4">Master App Complete System Validation</h2>
                        <p class="mb-4">Execute all tests in sequence to verify complete functionality of the Laravel Application Manager master application</p>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button
                                onclick="runTest('all_tests')"
                                class="run-test-btn bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white py-3 px-6 rounded font-semibold"
                                data-test="all_tests">
                                Run All Master App Tests
                            </button>
                            <button
                                onclick="runTest('deploy_official_laravel')"
                                class="run-test-btn bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white py-3 px-6 rounded font-semibold"
                                data-test="deploy_official_laravel">
                                Deploy Test Only
                            </button>
                        </div>
                    </div>
                    
                    <div id="results-container" class="mt-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">Test Results</h2>
                        <div id="results" class="bg-gray-100 p-4 rounded-lg min-h-32">
                            <p class="text-gray-600">Click a "Run Test" button to begin testing...</p>
                        </div>
                    </div>


                    <!-- Artisan Commands Section -->
                    <div class="mt-12 bg-gray-50 p-6 rounded-lg border border-gray-200">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Artisan Commands</h2>
                        <p class="text-gray-600 mb-4">Execute Laravel Artisan commands directly from the master application</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <input type="text" id="artisan-command" placeholder="Enter artisan command (e.g., list, migrate, cache:clear)"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button onclick="runArtisanCommand()"
                                    class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md">
                                Execute Command
                            </button>
                        </div>

                        <div id="artisan-results" class="bg-gray-100 p-4 rounded-lg min-h-24 hidden">
                            <h3 class="font-semibold text-gray-700 mb-2">Command Output:</h3>
                            <pre id="artisan-output" class="whitespace-pre-wrap text-sm"></pre>
                        </div>
                    </div>

                    <!-- NPM Commands Section -->
                    <div class="mt-12 bg-gray-50 p-6 rounded-lg border border-gray-200">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">NPM Commands</h2>
                        <p class="text-gray-600 mb-4">Execute NPM commands for frontend asset management</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <input type="text" id="npm-command" placeholder="Enter npm command (e.g., install, run dev, run build)"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button onclick="runNpmCommand()"
                                    class="bg-orange-600 hover:bg-orange-700 text-white py-2 px-4 rounded-md">
                                Execute NPM Command
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <button onclick="runNpmCommandDirect('install')"
                                    class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md">
                                npm install
                            </button>
                            <button onclick="runNpmCommandDirect('run dev')"
                                    class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md">
                                npm run dev
                            </button>
                            <button onclick="runNpmCommandDirect('run build')"
                                    class="bg-purple-600 hover:bg-purple-700 text-white py-2 px-4 rounded-md">
                                npm run build
                            </button>
                        </div>

                        <div id="npm-results" class="bg-gray-100 p-4 rounded-lg min-h-24 hidden">
                            <h3 class="font-semibold text-gray-700 mb-2">NPM Command Output:</h3>
                            <pre id="npm-output" class="whitespace-pre-wrap text-sm"></pre>
                        </div>
                    </div>

                    <!-- Quick Test Execution Section -->
                    <div class="mt-12 bg-gray-50 p-6 rounded-lg border border-gray-200">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Quick Test Execution</h2>
                        <p class="text-gray-600 mb-4">Run any test by selecting from the dropdown</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <select id="test-selector" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select a test to run...</option>
                                <option value="repository_cloning">Repository Cloning Test</option>
                                <option value="directory_creation">Directory Creation Test</option>
                                <option value="permission_management">Permission Management Test</option>
                                <option value="composer_install">Composer Install Test</option>
                                <option value="app_key_generation">App Key Generation Test</option>
                                <option value="database_migration">Database Migration Test</option>
                                <option value="cache_clearing">Cache Clearing Test</option>
                                <option value="deploy_official_laravel">Deploy Official Laravel Test</option>
                                <option value="all_tests">Run All Tests</option>
                            </select>
                            <button onclick="runSelectedTest()"
                                    class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md">
                                Run Selected Test
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function runTest(testName) {
            // Disable all buttons during test
            document.querySelectorAll('.run-test-btn').forEach(btn => {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
            });

            // Show running status
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = `<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                <p><strong>Running test: ${testName}</strong></p>
                <p class="text-sm">Please wait...</p>
            </div>`;

            try {
                const response = await fetch(`/test-dashboard/run/${testName}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({})
                });

                const result = await response.json();

                // Display result
                const statusClass = result.success ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
                const statusIcon = result.success ? '✅' : '❌';

                resultsDiv.innerHTML = `
                    <div class="border ${statusClass} px-4 py-3 rounded">
                        <p><strong>${statusIcon} Test: ${result.test_name}</strong></p>
                        <p><strong>Status:</strong> ${result.status}</p>
                        <p><strong>Success:</strong> ${result.success ? 'Yes' : 'No'}</p>
                        <p><strong>Timestamp:</strong> ${result.timestamp}</p>
                        <div class="mt-2">
                            <strong>Output:</strong>
                            <pre class="bg-white p-2 mt-1 rounded text-sm overflow-x-auto">${result.output}</pre>
                        </div>
                    </div>
                `;
            } catch (error) {
                resultsDiv.innerHTML = `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <p><strong>❌ Error running test: ${testName}</strong></p>
                        <p>Error: ${error.message}</p>
                    </div>
                `;
            } finally {
                // Re-enable all buttons
                document.querySelectorAll('.run-test-btn').forEach(btn => {
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                });
            }
        }

        async function runArtisanCommand() {
            const commandInput = document.getElementById('artisan-command');
            const command = commandInput.value.trim();

            if (!command) {
                alert('Please enter an artisan command to execute.');
                return;
            }

            const resultsDiv = document.getElementById('artisan-results');
            const outputDiv = document.getElementById('artisan-output');

            // Show running status
            resultsDiv.classList.remove('hidden');
            outputDiv.textContent = `Executing: php artisan ${command}\nPlease wait...`;

            try {
                // Send the artisan command to the server
                const response = await fetch('/test-dashboard/artisan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ command: command })
                });

                const result = await response.json();

                if (result.success) {
                    outputDiv.textContent = result.output || 'Command executed successfully with no output.';
                } else {
                    outputDiv.textContent = `Error: ${result.error || 'Unknown error occurred'}`;
                }
            } catch (error) {
                outputDiv.textContent = `Error executing command: ${error.message}`;
            }
        }

        async function runSelectedTest() {
            const selector = document.getElementById('test-selector');
            const testName = selector.value;

            if (!testName) {
                alert('Please select a test to run.');
                return;
            }

            // Run the selected test using the existing runTest function
            runTest(testName);
        }

        async function runNpmCommand() {
            const commandInput = document.getElementById('npm-command');
            const command = commandInput.value.trim();

            if (!command) {
                alert('Please enter an npm command to execute.');
                return;
            }

            // Validate command to prevent dangerous operations
            const forbiddenCommands = ['rm', 'mv', 'cp', 'chmod', 'chown', 'useradd', 'userdel', 'passwd', 'su', 'sudo', 'dd', 'mkfs', 'mount', 'umount'];
            for (const forbidden of forbiddenCommands) {
                if (command.toLowerCase().includes(forbidden)) {
                    alert(`Forbidden command detected: ${forbidden}`);
                    return;
                }
            }

            const resultsDiv = document.getElementById('npm-results');
            const outputDiv = document.getElementById('npm-output');

            // Show running status
            resultsDiv.classList.remove('hidden');
            outputDiv.textContent = `Executing: npm ${command}\nPlease wait...`;

            try {
                // Send the npm command to the server
                const response = await fetch('/test-dashboard/npm', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ command: command })
                });

                const result = await response.json();

                if (result.success) {
                    outputDiv.textContent = result.output || 'Command executed successfully with no output.';
                } else {
                    outputDiv.textContent = `Error: ${result.error || 'Unknown error occurred'}`;
                }
            } catch (error) {
                outputDiv.textContent = `Error executing command: ${error.message}`;
            }
        }

        async function runNpmCommandDirect(command) {
            const resultsDiv = document.getElementById('npm-results');
            const outputDiv = document.getElementById('npm-output');

            // Show running status
            resultsDiv.classList.remove('hidden');
            outputDiv.textContent = `Executing: npm ${command}\nPlease wait...`;

            try {
                // Send the npm command to the server
                const response = await fetch('/test-dashboard/npm', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ command: command })
                });

                const result = await response.json();

                if (result.success) {
                    outputDiv.textContent = result.output || 'Command executed successfully with no output.';
                } else {
                    outputDiv.textContent = `Error: ${result.error || 'Unknown error occurred'}`;
                }
            } catch (error) {
                outputDiv.textContent = `Error executing command: ${error.message}`;
            }
        }
    </script>
</x-app-layout>