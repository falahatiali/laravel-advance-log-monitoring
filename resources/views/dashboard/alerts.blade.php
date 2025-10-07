@extends('advanced-logger::layouts.app')

@section('title', 'Alerts')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">🚨 Alert Configuration</h2>
        <p class="mt-1 text-sm text-gray-600">Monitor alert thresholds and channel status</p>
    </div>

    <!-- Alert Channels Status -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Alert Channels</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($channels as $name => $config)
                <div class="border rounded-lg p-4 {{ $config['enabled'] ? 'border-green-300 bg-green-50' : 'border-gray-300 bg-gray-50' }}">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-md font-semibold text-gray-900">
                            @if($name === 'email') 📧 Email
                            @elseif($name === 'slack') 💬 Slack
                            @elseif($name === 'telegram') ✈️ Telegram
                            @else {{ ucfirst($name) }}
                            @endif
                        </h4>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $config['enabled'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $config['enabled'] ? '✅ Enabled' : '❌ Disabled' }}
                        </span>
                    </div>
                    @if($config['enabled'])
                        <div class="text-sm text-gray-600 mt-2">
                            @if($name === 'email')
                                <p><strong>To:</strong> {{ $config['to'] ?? 'Not configured' }}</p>
                            @elseif($name === 'slack')
                                <p><strong>Channel:</strong> {{ $config['channel'] ?? '#alerts' }}</p>
                            @elseif($name === 'telegram')
                                <p><strong>Chat ID:</strong> {{ $config['chat_id'] ?? 'Not configured' }}</p>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-500 mt-2">Configure in .env to enable</p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            <button onclick="testAlerts()" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition">
                🧪 Test All Channels
            </button>
            <div id="testResults" class="mt-4"></div>
        </div>
    </div>

    <!-- Alert Thresholds & Statistics -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Alert Thresholds & Current Status</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Threshold</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time Window</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recent Count</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($alertStats as $level => $stat)
                        <tr class="{{ $stat['threshold_exceeded'] ? 'bg-red-50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($level === 'critical') bg-red-100 text-red-800
                                    @elseif($level === 'error') bg-orange-100 text-orange-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ strtoupper($level) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $stat['threshold'] }} logs
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $stat['time_window'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="{{ $stat['threshold_exceeded'] ? 'text-red-600 font-bold' : '' }}">
                                    {{ $stat['recent_count'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($stat['threshold_exceeded'])
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        🚨 Threshold Exceeded
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✅ Normal
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No alert thresholds configured</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Configuration Guide -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-3">📚 Configuration Guide</h3>
        <div class="space-y-4 text-sm text-blue-800">
            <div>
                <h4 class="font-semibold mb-1">Email Alerts:</h4>
                <pre class="bg-white p-3 rounded border border-blue-200 overflow-x-auto"><code>LOG_ALERT_EMAIL_ENABLED=true
LOG_ALERT_EMAIL_TO=admin@example.com</code></pre>
            </div>
            <div>
                <h4 class="font-semibold mb-1">Slack Alerts:</h4>
                <pre class="bg-white p-3 rounded border border-blue-200 overflow-x-auto"><code>LOG_ALERT_SLACK_ENABLED=true
LOG_ALERT_SLACK_WEBHOOK=https://hooks.slack.com/services/YOUR/WEBHOOK
LOG_ALERT_SLACK_CHANNEL=#alerts</code></pre>
            </div>
            <div>
                <h4 class="font-semibold mb-1">Telegram Alerts:</h4>
                <pre class="bg-white p-3 rounded border border-blue-200 overflow-x-auto"><code>LOG_ALERT_TELEGRAM_ENABLED=true
LOG_ALERT_TELEGRAM_BOT_TOKEN=123456789:ABCdefGHIjklMNOpqrsTUVwxyz
LOG_ALERT_TELEGRAM_CHAT_ID=-123456789</code></pre>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function testAlerts() {
        const resultsDiv = document.getElementById('testResults');
        resultsDiv.innerHTML = '<div class="text-blue-600">Testing alert channels...</div>';

        fetch('{{ route("advanced-logger.test-alerts") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = '<div class="bg-green-50 border border-green-200 rounded-lg p-4">';
                html += '<h4 class="font-semibold text-green-900 mb-2">Test Results:</h4>';
                html += '<ul class="space-y-1">';
                for (const [channel, result] of Object.entries(data.results)) {
                    const icon = result === 'success' ? '✅' : '❌';
                    const color = result === 'success' ? 'text-green-700' : 'text-red-700';
                    html += `<li class="${color}">${icon} ${channel}: ${result}</li>`;
                }
                html += '</ul></div>';
                resultsDiv.innerHTML = html;
            }
        })
        .catch(error => {
            resultsDiv.innerHTML = '<div class="text-red-600">Error testing channels: ' + error.message + '</div>';
        });
    }
</script>
@endpush


