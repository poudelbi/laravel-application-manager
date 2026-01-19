<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Setup Application') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-3xl font-bold text-gray-800 mb-6">Setup Application: {{ $name }}</h1>

                    <div class="bg-white rounded-lg shadow-md p-6 max-w-4xl mx-auto">
                        <form action="{{ route('applications.store.setup', ['name' => $name]) }}" method="POST">
                            @csrf

                            <div class="mb-6">
                                <h2 class="text-xl font-semibold text-gray-700 mb-4">Step 1: Environment Configuration</h2>
                                <textarea id="environment" name="environment"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                          rows="10"
                                          placeholder="APP_NAME=Laravel&#10;APP_ENV=local&#10;APP_KEY=&#10;APP_DEBUG=true&#10;...">{{ file_exists("/var/www/html/applications/{$name}/.env") ? file_get_contents("/var/www/html/applications/{$name}/.env") : '' }}</textarea>
                                <p class="text-sm text-gray-500 mt-1">Configure your .env file here</p>
                            </div>

                            <div class="mb-6">
                                <h2 class="text-xl font-semibold text-gray-700 mb-4">Step 2: Generate Application Key</h2>
                                <div class="flex items-center">
                                    <input type="checkbox" id="generate_key" name="generate_key" value="1" checked class="mr-2">
                                    <label for="generate_key" class="text-gray-700">Generate application key (php artisan key:generate)</label>
                                </div>
                            </div>

                            <div class="mb-6">
                                <h2 class="text-xl font-semibold text-gray-700 mb-4">Step 3: Install Dependencies</h2>
                                <div class="space-y-2">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="run_composer" name="run_commands[]" value="composer" checked class="mr-2">
                                        <label for="run_composer" class="text-gray-700">Run composer install</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="run_npm_install" name="run_commands[]" value="npm_install" class="mr-2">
                                        <label for="run_npm_install" class="text-gray-700">Run npm install</label>
                                    </div>
                                    <div class="flex items-center ml-5">
                                        <input type="checkbox" id="run_npm_build" name="run_commands[]" value="npm_build" class="mr-2">
                                        <label for="run_npm_build" class="text-gray-700">Run npm run build (requires npm install first)</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-6">
                                <h2 class="text-xl font-semibold text-gray-700 mb-4">Step 4: Database Operations</h2>
                                <div class="space-y-2">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="run_migrate" name="run_commands[]" value="migrate" class="mr-2">
                                        <label for="run_migrate" class="text-gray-700">Run migrations (php artisan migrate)</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="run_seed" name="run_commands[]" value="seed" class="mr-2">
                                        <label for="run_seed" class="text-gray-700">Run seeders (php artisan db:seed)</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-6">
                                <h2 class="text-xl font-semibold text-gray-700 mb-4">Additional Options</h2>
                                <div class="space-y-2">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="run_storage_link" name="run_commands[]" value="storage_link" class="mr-2">
                                        <label for="run_storage_link" class="text-gray-700">Create storage symlink (php artisan storage:link)</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                                <h2 class="text-lg font-semibold text-yellow-800 mb-2">Important Server Configuration</h2>
                                <p class="text-yellow-700 mb-2">After setup completes, you may need to run the following commands manually on your server:</p>
                                <pre class="bg-gray-800 text-green-400 p-3 rounded text-sm overflow-x-auto">cd /var/www/html/applications/{{ $name }}
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache</pre>
                                <p class="text-yellow-700 mt-2">These commands ensure proper permissions for Laravel's storage and cache directories.</p>
                            </div>

                            <div class="mb-6">
                                <label for="php_version" class="block text-gray-700 font-medium mb-2">PHP Version</label>
                                <select id="php_version" name="php_version"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                    <option value="8.0" {{ (file_get_contents("/var/www/html/applications/{$name}/composer.json") ?? '') && strpos(file_get_contents("/var/www/html/applications/{$name}/composer.json"), '"php": "^8.0"') !== false ? 'selected' : '' }}>PHP 8.0</option>
                                    <option value="8.1" {{ (file_get_contents("/var/www/html/applications/{$name}/composer.json") ?? '') && strpos(file_get_contents("/var/www/html/applications/{$name}/composer.json"), '"php": "^8.1"') !== false ? 'selected' : '' }}>PHP 8.1</option>
                                    <option value="8.2" {{ (file_get_contents("/var/www/html/applications/{$name}/composer.json") ?? '') && strpos(file_get_contents("/var/www/html/applications/{$name}/composer.json"), '"php": "^8.2"') !== false ? 'selected' : '' }}>PHP 8.2</option>
                                    <option value="8.3" {{ (file_get_contents("/var/www/html/applications/{$name}/composer.json") ?? '') && strpos(file_get_contents("/var/www/html/applications/{$name}/composer.json"), '"php": "^8.3"') !== false ? 'selected' : '' }}>PHP 8.3</option>
                                    <option value="8.4" {{ (file_get_contents("/var/www/html/applications/{$name}/composer.json") ?? '') && strpos(file_get_contents("/var/www/html/applications/{$name}/composer.json"), '"php": "^8.4"') !== false ? 'selected' : '' }}>PHP 8.4</option>
                                </select>
                            </div>

                            <div class="flex items-center justify-between">
                                <a href="{{ route('applications.index') }}"
                                   class="text-blue-600 hover:text-blue-800 font-medium">
                                    Cancel
                                </a>
                                <button type="submit"
                                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Complete Setup
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>