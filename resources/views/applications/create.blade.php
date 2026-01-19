<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Application') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-3xl font-bold text-gray-800 mb-6">Create New Laravel Application</h1>

                    <div class="bg-white rounded-lg shadow-md p-6 max-w-2xl mx-auto">
                        <form action="{{ route('applications.store') }}" method="POST">
                            @csrf

                            <div class="mb-4">
                                <label for="name" class="block text-gray-700 font-medium mb-2">Application Name</label>
                                <input type="text" id="name" name="name"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="repo_url" class="block text-gray-700 font-medium mb-2">Repository URL</label>
                                <input type="url" id="repo_url" name="repo_url"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="https://github.com/username/repository.git">
                                @error('repo_url')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="access_token" class="block text-gray-700 font-medium mb-2">Personal Access Token (optional)</label>
                                <input type="password" id="access_token" name="access_token"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="Enter personal access token for private repos">
                                @error('access_token')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-6">
                                <label for="version" class="block text-gray-700 font-medium mb-2">PHP Version</label>
                                <select id="version" name="version"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                    <option value="">Select PHP Version</option>
                                    <option value="8.0">PHP 8.0</option>
                                    <option value="8.1">PHP 8.1</option>
                                    <option value="8.2">PHP 8.2</option>
                                    <option value="8.3">PHP 8.3</option>
                                    <option value="8.4">PHP 8.4</option>
                                    <option value="9.0">PHP 9.0</option>
                                    <option value="10.0">PHP 10.0</option>
                                </select>
                                @error('version')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-between">
                                <a href="{{ route('applications.index') }}"
                                   class="text-blue-600 hover:text-blue-800 font-medium">
                                    Cancel
                                </a>
                                <button type="submit"
                                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Create Application
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>