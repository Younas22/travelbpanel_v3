<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Search Error - {{ $error_title ?? 'Search Error' }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .error-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 600px;
            width: 100%;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .error-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4);
            border-radius: 20px 20px 0 0;
        }

        .error-icon {
            font-size: 4rem;
            color: #ff6b6b;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .error-code {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .error-title {
            font-size: 2rem;
            color: #333;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .error-message {
            font-size: 1.1rem;
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .error-details {
            background: #f8f9fa;
            border-left: 4px solid #ff6b6b;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
            text-align: left;
        }

        .error-details h4 {
            color: #333;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .error-list {
            list-style: none;
            padding: 0;
        }

        .error-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            color: #666;
            position: relative;
            padding-left: 25px;
        }

        .error-list li:before {
            content: '⚠️';
            position: absolute;
            left: 0;
            top: 8px;
        }

        .error-list li:last-child {
            border-bottom: none;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 140px;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(45deg, #4ecdc4, #45b7d1);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(69, 183, 209, 0.3);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #666;
            border: 2px solid #dee2e6;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }

        .btn-home {
            background: linear-gradient(45deg, #96ceb4, #8bc34a);
            color: white;
        }

        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(150, 206, 180, 0.3);
        }

        .search-tips {
            background: #e8f5e8;
            border: 1px solid #c3e6c3;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }

        .search-tips h4 {
            color: #2e7d32;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .search-tips ul {
            color: #4a5568;
            margin-left: 20px;
        }

        .search-tips li {
            margin: 5px 0;
        }

        .footer-info {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #999;
            font-size: 0.9rem;
        }

        .loading {
            display: none;
            align-items: center;
            gap: 10px;
            color: #666;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #4ecdc4;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .error-container {
                padding: 30px 20px;
                margin: 10px;
            }

            .error-title {
                font-size: 1.5rem;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 280px;
            }
        }
    </style>
</head>
<body>
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
