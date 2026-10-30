<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Search Error - {{ $error_title ?? 'Search Error' }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ url('public/assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body class="view-standalone view-flight-error">
<div class="error-container">
    <div class="error-icon">
        <i class="fas fa-exclamation-triangle"></i>
    </div>

    <div class="error-code">Error Code: {{ $error_code ?? 'FLIGHT_SEARCH_ERROR' }}</div>

    <h1 class="error-title">{{ $error_title ?? 'Flight Search Error' }}</h1>

    <p class="error-message">
        {{ $error_message ?? 'We encountered an issue while processing your flight search. Please check your search parameters and try again.' }}
    </p>

    <!-- Display validation errors if any -->
    @if(isset($errors) && $errors->any())
        <div class="error-details">
            <h4><i class="fas fa-list-ul"></i> Search Parameter Issues:</h4>
            <ul class="error-list">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Display specific error details -->
    @if(isset($error_details))
        <div class="error-details">
            <h4><i class="fas fa-info-circle"></i> Error Details:</h4>
            <p>{{ $error_details }}</p>
        </div>
    @endif

    <!-- Search Tips -->
    <div class="search-tips">
        <h4><i class="fas fa-lightbulb"></i> Search Tips:</h4>
        <ul>
            <li>Make sure your departure date is today or in the future</li>
            <li>For return trips, return date must be after departure date</li>
            <li>Maximum 9 passengers allowed per booking</li>
            <li>Number of infants cannot exceed number of adults</li>
            <li>Use valid airport codes (e.g., NYC, LAX, LHR)</li>
        </ul>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">


        <a href="{{url('/')}}" class="btn btn-home">
            <i class="fas fa-home"></i>
            Home
        </a>
    </div>

    <!-- Loading state -->
    <div class="loading" id="loading">
        <div class="spinner"></div>
        <span>Redirecting...</span>
    </div>

    <!-- Footer -->
    <div class="footer-info">
        <p>Need help? Contact our support team at <strong>support@flightbooking.com</strong></p>
        <p>Error occurred at: {{ date('Y-m-d H:i:s') }}</p>
    </div>
</div>
</body>
</html>
