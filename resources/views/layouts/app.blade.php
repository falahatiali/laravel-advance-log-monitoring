<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Simorgh Logger</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 flex items-center">
                            <span class="text-2xl mr-2">🦅</span>
                            <h1 class="text-xl font-bold text-gray-900">Simorgh Logger</h1>
                        </div>
                    </div>
                    <div class="flex items-center space-x-1">
                        <a href="{{ route('advanced-logger.dashboard') }}" class="text-gray-700 hover:bg-gray-100 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('advanced-logger.dashboard') ? 'bg-gray-100 text-gray-900' : '' }}">
                            📊 Dashboard
                        </a>
                        <a href="{{ route('advanced-logger.logs') }}" class="text-gray-700 hover:bg-gray-100 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('advanced-logger.logs') ? 'bg-gray-100 text-gray-900' : '' }}">
                            📋 Logs
                        </a>
                        <a href="{{ route('advanced-logger.stats') }}" class="text-gray-700 hover:bg-gray-100 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('advanced-logger.stats') ? 'bg-gray-100 text-gray-900' : '' }}">
                            📈 Statistics
                        </a>
                        <a href="{{ route('advanced-logger.alerts') }}" class="text-gray-700 hover:bg-gray-100 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('advanced-logger.alerts') ? 'bg-gray-100 text-gray-900' : '' }}">
                            🚨 Alerts
                        </a>
                        <a href="{{ route('advanced-logger.settings') }}" class="text-gray-700 hover:bg-gray-100 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('advanced-logger.settings') ? 'bg-gray-100 text-gray-900' : '' }}">
                            ⚙️ Settings
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>
</html>

