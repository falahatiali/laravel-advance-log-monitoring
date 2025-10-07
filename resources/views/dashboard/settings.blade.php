@extends('advanced-logger::layouts.app')

@section('title', 'Settings')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">⚙️ Settings & Configuration</h2>
        <p class="mt-1 text-sm text-gray-600">View and understand your logger configuration</p>
    </div>

    <!-- General Settings -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">General Settings</h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between py-3 border-b">
                <div>
                    <h4 class="text-sm font-medium text-gray-900">Logger Enabled</h4>
                    <p class="text-sm text-gray-500">Master switch for the logging system</p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $config['enabled'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $config['enabled'] ? '✅ Enabled' : '❌ Disabled' }}
                </span>
            </div>

            <div class="flex items-center justify-between py-3 border-b">
                <div>
                    <h4 class="text-sm font-medium text-gray-900">Storage Driver</h4>
                    <p class="text-sm text-gray-500">Where logs are stored</p>
                </div>
                <span class="text-sm font-medium text-gray-900">{{ ucfirst($config['storage']['driver']) }}</span>
            </div>

            <div class="flex items-center justify-between py-3 border-b">
                <div>
                    <h4 class="text-sm font-medium text-gray-900">Database Table</h4>
                    <p class="text-sm text-gray-500">Table name for log storage</p>
                </div>
                <span class="text-sm font-medium text-gray-900">{{ $config['database']['table'] }}</span>
            </div>

            <div class="flex items-center justify-between py-3">
                <div>
                    <h4 class="text-sm font-medium text-gray-900">Dashboard Enabled</h4>
                    <p class="text-sm text-gray-500">Web dashboard accessibility</p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $config['dashboard']['enabled'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $config['dashboard']['enabled'] ? '✅ Enabled' : '❌ Disabled' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Auto-Logging Settings -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Auto-Logging Configuration</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="border rounded-lg p-4 {{ $config['auto_logging']['requests']['enabled'] ? 'border-green-300 bg-green-50' : 'border-gray-300 bg-gray-50' }}">
                <h4 class="font-semibold text-gray-900 mb-2">📨 HTTP Requests</h4>
                <p class="text-sm text-gray-600 mb-3">Automatic logging of HTTP requests</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $config['auto_logging']['requests']['enabled'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $config['auto_logging']['requests']['enabled'] ? 'Enabled' : 'Disabled' }}
                </span>
            </div>

            <div class="border rounded-lg p-4 {{ $config['auto_logging']['models']['enabled'] ? 'border-green-300 bg-green-50' : 'border-gray-300 bg-gray-50' }}">
                <h4 class="font-semibold text-gray-900 mb-2">📦 Model Events</h4>
                <p class="text-sm text-gray-600 mb-3">Automatic logging of model changes</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $config['auto_logging']['models']['enabled'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $config['auto_logging']['models']['enabled'] ? 'Enabled' : 'Disabled' }}
                </span>
            </div>

            <div class="border rounded-lg p-4 {{ $config['auto_logging']['queries']['enabled'] ? 'border-green-300 bg-green-50' : 'border-gray-300 bg-gray-50' }}">
                <h4 class="font-semibold text-gray-900 mb-2">🗄️ Database Queries</h4>
                <p class="text-sm text-gray-600 mb-3">Automatic logging of slow queries</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $config['auto_logging']['queries']['enabled'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $config['auto_logging']['queries']['enabled'] ? 'Enabled' : 'Disabled' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Retention Policy -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Retention Policy</h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between py-3 border-b">
                <div>
                    <h4 class="text-sm font-medium text-gray-900">Retention Enabled</h4>
                    <p class="text-sm text-gray-500">Automatic cleanup of old logs</p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $config['retention']['enabled'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $config['retention']['enabled'] ? '✅ Enabled' : '❌ Disabled' }}
                </span>
            </div>

            <div class="flex items-center justify-between py-3 border-b">
                <div>
                    <h4 class="text-sm font-medium text-gray-900">Retention Days</h4>
                    <p class="text-sm text-gray-500">How long logs are kept per environment</p>
                </div>
                <div class="text-right">
                    @foreach($config['retention']['days'] as $env => $days)
                        <div class="text-sm text-gray-900">{{ ucfirst($env) }}: <strong>{{ $days }} days</strong></div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-between py-3">
                <div>
                    <h4 class="text-sm font-medium text-gray-900">Compress Before Delete</h4>
                    <p class="text-sm text-gray-500">Archive logs before deletion</p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $config['retention']['compress_before_delete'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $config['retention']['compress_before_delete'] ? '✅ Yes' : '❌ No' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Performance Settings -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Settings</h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between py-3 border-b">
                <div>
                    <h4 class="text-sm font-medium text-gray-900">Use Queue</h4>
                    <p class="text-sm text-gray-500">Process logs asynchronously</p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $config['performance']['use_queue'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $config['performance']['use_queue'] ? '✅ Enabled' : '❌ Disabled' }}
                </span>
            </div>

            <div class="flex items-center justify-between py-3 border-b">
                <div>
                    <h4 class="text-sm font-medium text-gray-900">Queue Name</h4>
                    <p class="text-sm text-gray-500">Queue for log processing</p>
                </div>
                <span class="text-sm font-medium text-gray-900">{{ $config['performance']['queue_name'] }}</span>
            </div>

            <div class="flex items-center justify-between py-3">
                <div>
                    <h4 class="text-sm font-medium text-gray-900">Batch Size</h4>
                    <p class="text-sm text-gray-500">Number of logs processed per batch</p>
                </div>
                <span class="text-sm font-medium text-gray-900">{{ $config['performance']['batch_size'] }}</span>
            </div>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Security Settings</h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between py-3 border-b">
                <div>
                    <h4 class="text-sm font-medium text-gray-900">Sanitize Sensitive Data</h4>
                    <p class="text-sm text-gray-500">Automatically redact sensitive information</p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $config['security']['sanitize_sensitive_data'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $config['security']['sanitize_sensitive_data'] ? '✅ Enabled' : '❌ Disabled' }}
                </span>
            </div>

            <div class="py-3">
                <h4 class="text-sm font-medium text-gray-900 mb-2">Sensitive Patterns</h4>
                <p class="text-sm text-gray-500 mb-3">Fields that will be automatically redacted</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($config['security']['sensitive_patterns'] as $pattern)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            {{ trim($pattern, '/i') }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Categories -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Log Categories</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($config['categories'] as $key => $description)
                <div class="border border-gray-200 rounded-lg p-3">
                    <h4 class="text-sm font-semibold text-gray-900">{{ ucfirst($key) }}</h4>
                    <p class="text-xs text-gray-600 mt-1">{{ $description }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
        <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('advanced-logger.logs') }}" class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-4 transition text-center">
                <div class="text-2xl mb-2">📋</div>
                <div class="font-medium">View Logs</div>
            </a>
            <a href="{{ route('advanced-logger.stats') }}" class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-4 transition text-center">
                <div class="text-2xl mb-2">📈</div>
                <div class="font-medium">View Statistics</div>
            </a>
            <a href="{{ route('advanced-logger.alerts') }}" class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-4 transition text-center">
                <div class="text-2xl mb-2">🚨</div>
                <div class="font-medium">Configure Alerts</div>
            </a>
        </div>
    </div>

    <!-- Info Box -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h4 class="font-semibold text-blue-900 mb-2">💡 Configuration Tip</h4>
        <p class="text-sm text-blue-800">
            To modify these settings, edit the <code class="bg-blue-100 px-2 py-1 rounded">config/advanced-logger.php</code> file 
            or update your <code class="bg-blue-100 px-2 py-1 rounded">.env</code> environment variables.
        </p>
    </div>
@endsection

