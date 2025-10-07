@extends('advanced-logger::layouts.app')

@section('title', 'Logs')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">📋 All Logs</h2>
        <p class="mt-1 text-sm text-gray-600">Browse and search through all your application logs</p>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <form method="GET" action="{{ route('advanced-logger.logs') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Level Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Level</label>
                    <select name="level" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Levels</option>
                        @foreach($levels as $level)
                            <option value="{{ $level }}" {{ ($filters['level'] ?? '') === $level ? 'selected' : '' }}>
                                {{ ucfirst($level) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ ($filters['category'] ?? '') === $category ? 'selected' : '' }}>
                                {{ ucfirst($category) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="is_resolved" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Status</option>
                        <option value="0" {{ (isset($filters['is_resolved']) && $filters['is_resolved'] === '0') ? 'selected' : '' }}>Unresolved</option>
                        <option value="1" {{ (isset($filters['is_resolved']) && $filters['is_resolved'] === '1') ? 'selected' : '' }}>Resolved</option>
                    </select>
                </div>

                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search logs..." class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Action Button -->
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
                        🔍 Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Export Actions -->
    <div class="mb-6 flex justify-end space-x-3">
        <a href="{{ route('advanced-logger.export.json', request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
            📄 Export JSON
        </a>
        <a href="{{ route('advanced-logger.export.csv', request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
            📊 Export CSV
        </a>
        <a href="{{ route('advanced-logger.export.xml', request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">
            📋 Export XML
        </a>
    </div>

    <!-- Logs Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($log->level === 'emergency' || $log->level === 'alert' || $log->level === 'critical') bg-red-100 text-red-800
                                    @elseif($log->level === 'error') bg-orange-100 text-orange-800
                                    @elseif($log->level === 'warning') bg-yellow-100 text-yellow-800
                                    @elseif($log->level === 'notice') bg-blue-100 text-blue-800
                                    @elseif($log->level === 'info') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $log->level_icon }} {{ strtoupper($log->level) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $log->category ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="max-w-md">
                                    <p class="truncate" title="{{ $log->message }}">{{ $log->message }}</p>
                                    @if($log->context)
                                        <details class="mt-1">
                                            <summary class="text-xs text-blue-600 cursor-pointer">View context</summary>
                                            <pre class="mt-2 text-xs bg-gray-100 p-2 rounded overflow-auto max-h-40">{{ json_encode($log->context, JSON_PRETTY_PRINT) }}</pre>
                                        </details>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->user_id ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->ip_address ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div title="{{ $log->created_at }}">
                                    {{ $log->created_at->diffForHumans() }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($log->is_resolved)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✅ Resolved
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        ⏳ Unresolved
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button onclick="toggleResolve({{ $log->id }}, {{ $log->is_resolved ? 'false' : 'true' }})" class="text-blue-600 hover:text-blue-900 mr-3">
                                    {{ $log->is_resolved ? 'Unresolve' : 'Resolve' }}
                                </button>
                                <button onclick="deleteLog({{ $log->id }})" class="text-red-600 hover:text-red-900">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <div class="text-4xl mb-2">📭</div>
                                <p class="text-lg">No logs found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $logs->withQueryString()->links() }}
        </div>
    </div>

    <script>
        function toggleResolve(logId, resolve) {
            const route = resolve 
                ? `/advanced-logger/logs/${logId}/resolve` 
                : `/advanced-logger/logs/${logId}/unresolve`;
            
            fetch(route, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }

        function deleteLog(logId) {
            if (!confirm('Are you sure you want to delete this log?')) return;
            
            fetch(`/advanced-logger/logs/${logId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
    </script>
@endsection


