{{-- Standalone 404 page (Laravel renders this automatically for 404s). --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page not found · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="error-shell">
    <div class="error-card">
        <div class="error-code">404</div>
        <h1>Page not found</h1>
        <p>The page you are looking for does not exist or has been moved.</p>
        <a class="btn btn-primary" href="{{ url('/dashboard') }}">Go to dashboard</a>
    </div>
</body>
</html>
