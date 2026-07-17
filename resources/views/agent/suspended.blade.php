<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Suspended</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="min-vh-100 d-flex align-items-center justify-content-center p-3">
    <div class="card shadow border-0 text-center" style="max-width:500px;">
        <div class="card-body p-5">
            <i class="bi bi-slash-circle text-danger" style="font-size:4rem;"></i>
            <h4 class="mt-3">Account Suspended</h4>
            <p class="text-muted">
                Your agent account has been suspended. Please contact admin for more details.
            </p>
            <form method="POST" action="{{ route('agent.logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
