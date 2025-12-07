<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Personal Life Manager</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo e(asset('favicon.svg')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('favicon.svg')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
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

<?php /**PATH /Users/zeeshan/Herd/Companies/TSH/personal-assistant/backend/resources/views/app.blade.php ENDPATH**/ ?>