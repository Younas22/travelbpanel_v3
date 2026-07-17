<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Access Denied</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="min-vh-100 d-flex align-items-center justify-content-center p-3">
    <div class="card shadow border-0 text-center" style="max-width:500px;">
        <div class="card-body p-5">
            <i class="bi bi-lock text-secondary" style="font-size:4rem;"></i>
            <h4 class="mt-3">Access Denied</h4>
            <p class="text-muted">
                You do not have permission to access this module.
                Please contact your admin to enable this feature for your account.
            </p>
            <a href="{{ route('agent.dashboard') }}" class="btn btn-primary">
                <i class="bi bi-house"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>
</body>
</html>
