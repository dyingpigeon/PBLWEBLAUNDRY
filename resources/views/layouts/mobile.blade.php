<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - LaundryKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .swipeable {
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
        }
        .swipeable > div {
            scroll-snap-align: start;
            flex: 0 0 85%;
        }
        .stat-card {
            transition: transform 0.2s ease;
        }
        .stat-card:active {
            transform: scale(0.98);
        }
        .refresh-indicator {
            transition: transform 0.3s ease;
        }
        .refreshing {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen">
    @include('partials.header')
    
    <main class="pb-20">
        @yield('content')
    </main>
    
    @include('partials.bottom-navigation')
    
    @stack('scripts')
</body>
</html>