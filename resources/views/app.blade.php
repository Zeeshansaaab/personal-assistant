<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Personal Life Manager</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        // Initialize theme before page loads to prevent flash
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            const isDark = theme === 'dark';
            document.documentElement.classList.toggle('dark', isDark);
            document.body.classList.toggle('dark', isDark);
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
</head>
<body>
    <div id="app"></div>
</body>
</html>

