@extends('advanced-logger::layouts.app')

@section('title', 'Statistics')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">📈 Statistics & Analytics</h2>
        <p class="mt-1 text-sm text-gray-600">Comprehensive insights into your application logs</p>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <form method="GET" action="{{ route('advanced-logger.stats') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Level</label>
                    <select name="level" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Levels</option>
                        <option value="emergency" {{ ($filters['level'] ?? '') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                        <option value="alert" {{ ($filters['level'] ?? '') === 'alert' ? 'selected' : '' }}>Alert</option>
                        <option value="critical" {{ ($filters['level'] ?? '') === 'critical' ? 'selected' : '' }}>Critical</option>
                        <option value="error" {{ ($filters['level'] ?? '') === 'error' ? 'selected' : '' }}>Error</option>
                        <option value="warning" {{ ($filters['level'] ?? '') === 'warning' ? 'selected' : '' }}>Warning</option>
                        <option value="notice" {{ ($filters['level'] ?? '') === 'notice' ? 'selected' : '' }}>Notice</option>
                        <option value="info" {{ ($filters['level'] ?? '') === 'info' ? 'selected' : '' }}>Info</option>
                        <option value="debug" {{ ($filters['level'] ?? '') === 'debug' ? 'selected' : '' }}>Debug</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
                        📊 Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg shadow-lg p-6">
            <div class="text-sm opacity-90 mb-1">Total Logs</div>
            <div class="text-3xl font-bold">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white rounded-lg shadow-lg p-6">
            <div class="text-sm opacity-90 mb-1">This Week</div>
            <div class="text-3xl font-bold">{{ number_format($stats['this_week']) }}</div>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg shadow-lg p-6">
            <div class="text-sm opacity-90 mb-1">This Month</div>
            <div class="text-3xl font-bold">{{ number_format($stats['this_month']) }}</div>
        </div>
        <div class="bg-gradient-to-br from-red-500 to-red-600 text-white rounded-lg shadow-lg p-6">
            <div class="text-sm opacity-90 mb-1">Unresolved</div>
            <div class="text-3xl font-bold">{{ number_format($stats['unresolved']) }}</div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- By Level -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribution by Level</h3>
            <div style="height: 300px;">
                <canvas id="levelPieChart"></canvas>
            </div>
        </div>

        <!-- By Category -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribution by Category</h3>
            <div style="height: 300px;">
                <canvas id="categoryBarChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Trends Over Time -->
    @if($dailyStats->isNotEmpty())
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Logs Trend (Last 30 Days)</h3>
            <div style="height: 400px;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    @endif

    <!-- Detailed Breakdown Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- By Level Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Breakdown by Level</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Count</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Percentage</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($stats['by_level'] as $level => $count)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ ucfirst($level) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                {{ number_format($count) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                {{ $stats['total'] > 0 ? round(($count / $stats['total']) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- By Category Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Breakdown by Category</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Count</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Percentage</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($stats['by_category'] as $category => $count)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $category ?: 'Uncategorized' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                {{ number_format($count) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                {{ $stats['total'] > 0 ? round(($count / $stats['total']) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">No categories found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Level Pie Chart
    new Chart(document.getElementById('levelPieChart'), {
        type: 'pie',
        data: {
            labels: {!! json_encode(array_map('ucfirst', array_keys($stats['by_level']))) !!},
            datasets: [{
                data: {!! json_encode(array_values($stats['by_level'])) !!},
                backgroundColor: ['#EF4444', '#F97316', '#EAB308', '#3B82F6', '#10B981', '#6B7280']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // Category Bar Chart
    new Chart(document.getElementById('categoryBarChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_map('ucfirst', array_keys($stats['by_category']))) !!},
            datasets: [{
                label: 'Logs',
                data: {!! json_encode(array_values($stats['by_category'])) !!},
                backgroundColor: '#3B82F6'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    @if($dailyStats->isNotEmpty())
    // Trend Chart
    const trendData = {!! json_encode($dailyStats) !!};
    const dates = Object.keys(trendData);
    const levels = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'];
    const colors = {
        emergency: '#DC2626', alert: '#DC2626', critical: '#DC2626',
        error: '#F97316', warning: '#EAB308',
        notice: '#3B82F6', info: '#10B981', debug: '#6B7280'
    };

    const datasets = levels.map(level => ({
        label: level.charAt(0).toUpperCase() + level.slice(1),
        data: dates.map(date => {
            const dayData = trendData[date].find(d => d.level === level);
            return dayData ? dayData.count : 0;
        }),
        borderColor: colors[level],
        backgroundColor: colors[level],
        tension: 0.3
    }));

    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: { labels: dates, datasets: datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true, stacked: true } }
        }
    });
    @endif
</script>
@endpush

