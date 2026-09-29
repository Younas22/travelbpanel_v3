<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\PaymentGateways;
use Illuminate\Http\Request;
use Resend\Laravel\Facades\Resend;
use Illuminate\Support\Facades\Http;

class SettingController extends Controller
{

    public function website()
{
    // Get all settings grouped by their group
    $allSettings = Setting::where('is_active', true)->get()->groupBy('group');

    // Convert to array format for easier access in view
    $settings = [];
    foreach ($allSettings as $group => $groupSettings) {
        $settings[$group] = $groupSettings->pluck('value', 'key')->toArray();
    }

    return view('admin.settings.website', compact('settings'));
}


public function updateWebsite(Request $request)
{
    $group = $request->input('group', 'main');

    // Define validation rules for different groups
    $validationRules = $this->getValidationRules($group);

    $validated = $request->validate($validationRules);

    // Remove group from validated data
    unset($validated['group']);

    try {
        foreach ($validated as $key => $value) {
            // Handle file uploads to public folder
            if ($request->hasFile($key)) {
                // Get the uploaded file
                $file = $request->file($key);

                // Generate unique filename
                $filename = time() . '_' . uniqid() . '_' . $key . '.' . $file->getClientOriginalExtension();

                // Set destination path
                $destinationPath = public_path("assets/images/settings/{$group}");

                // Create directory if it doesn't exist
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                // Get old file path for deletion (if exists)
                $oldSetting = Setting::where('group', $group)->where('key', $key)->first();
                if ($oldSetting && $oldSetting->value) {
                    $oldFilePath = public_path('assets/images/' . $oldSetting->value);
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }

                // Move file to public directory
                $file->move($destinationPath, $filename);

                // Store relative path for database
                $value = "settings/{$group}/{$filename}";
            }

            // Handle checkboxes (convert to boolean)
            if (in_array($key, ['website_offline', 'guest_booking', 'user_registration', 'supplier_registration', 'agent_registration'])) {
                $value = $request->has($key) ? '1' : '0';
            }

            Setting::updateOrCreate(
                ['group' => $group, 'key' => $key],
                ['value' => $value, 'is_active' => true]
            );
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => ucfirst($group) . ' settings updated successfully!'
            ]);
        }

        return back()->with('success', ucfirst($group) . ' settings updated successfully!');

    } catch (\Exception $e) {
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating settings: ' . $e->getMessage()
            ], 500);
        }

        return back()->withErrors(['error' => 'Error updating settings: ' . $e->getMessage()]);
    }
}


private function getValidationRules($group)
{
    $rules = [
        'main' => [
            'business_name' => 'required|string|max:255',
            'domain_name' => 'required|string|max:255',
            'license_key' => 'nullable|string|max:255',
            'website_offline' => 'nullable|boolean'
        ],
        'seo' => [
            'meta_title' => 'required|string|max:60',
            'meta_description' => 'required|string|max:160',
            'meta_keywords' => 'nullable|string|max:255'
        ],
        'accounts' => [
            'guest_booking' => 'nullable|boolean',
            'user_registration' => 'nullable|boolean',
            'supplier_registration' => 'nullable|boolean',
            'agent_registration' => 'nullable|boolean'
        ],
        'system' => [
            'business_model' => 'nullable|string|in:both,b2b,b2c',
            'agent_signup_url' => 'nullable|string|max:255',
            'google_analytics_id' => 'nullable|string|max:50',
            'google_tag_manager_id' => 'nullable|string|max:50',
            'facebook_pixel_id' => 'nullable|string|max:50',
            'custom_tracking_code' => 'nullable|string'
        ],
        'contact' => [
            'business_address' => 'nullable|string',
            'map_embed_code' => 'nullable|string',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'support_email' => 'nullable|email|max:255',
            'emergency_contact' => 'nullable|string|max:20'
        ],
        'branding' => [
            'business_logo' => 'nullable|file|extensions:jpeg,png,jpg,gif|max:2048',
            'business_logo_white' => 'nullable|file|extensions:jpeg,png,jpg,gif|max:2048',
            'favicon' => 'nullable|file|extensions:jpeg,png,jpg,ico|max:1024'
        ],
        'homepage' => [
            'cover_image' => 'nullable|image|max:5120',
            'cover_title' => 'nullable|string|max:255',
            'cover_subtitle' => 'nullable|string|max:500'
        ],
        'social' => [
            'facebook_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'linkedin_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'google_business_url' => 'nullable|url',
            'youtube_url' => 'nullable|url',
            'whatsapp_number' => 'nullable|string|max:20'
        ]
    ];

    return $rules[$group] ?? [];
}


    public function payment()
    {
        // Get all payment settings
        $payment_gateways = PaymentGateways::all();
        return view('admin.settings.payment', compact('payment_gateways'));
    }

    public function editPayment($gateway)
    {
        // Get payment gateway by name or ID
        $payment_gateway = PaymentGateways::where('name', $gateway)->orWhere('id', $gateway)->firstOrFail();
        return view('admin.settings.payment-edit', compact('payment_gateway'));
    }

    public function updatePayment(Request $request)
    {
        try {
            // Check if it's a simple status toggle (gateway_id and status only)
            if ($request->has('gateway_id') && $request->has('status')) {
                $gatewayId = $request->input('gateway_id');
                $status = $request->input('status'); // Keep as string '0' or '1'

                $gateway = PaymentGateways::findOrFail($gatewayId);
                $gateway->status = $status; // Mutator will handle conversion
                $gateway->save();

                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => ucfirst($gateway->name) . ' status updated successfully!'
                    ]);
                }

                return back()->with('success', 'Payment gateway status updated successfully!');
            }

            // Otherwise it's a full settings update
            $gateway = $request->input('gateway');
            $settings = $request->input('settings', []);

            // Find the gateway by name
            $paymentGateway = PaymentGateways::where('name', $gateway)->first();

            if (!$paymentGateway) {
                throw new \Exception('Payment gateway not found');
            }

            // Update the gateway fields
            $paymentGateway->credential_1 = $settings['credential_1'] ?? $paymentGateway->credential_1;
            $paymentGateway->credential_2 = $settings['credential_2'] ?? $paymentGateway->credential_2;
            $paymentGateway->credential_3 = $settings['credential_3'] ?? $paymentGateway->credential_3;
            $paymentGateway->credential_4 = $settings['credential_4'] ?? $paymentGateway->credential_4;
            $paymentGateway->credential_5 = $settings['credential_5'] ?? $paymentGateway->credential_5;
            // Mutators will handle conversion from string to enum
            $paymentGateway->mode = isset($settings['mode']) ? $settings['mode'] : $paymentGateway->getRawOriginal('mode');
            $paymentGateway->status = isset($settings['status']) ? $settings['status'] : $paymentGateway->getRawOriginal('status');

            $paymentGateway->save();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => ucfirst($gateway) . ' payment settings updated successfully!'
                ]);
            }

            return back()->with('success', 'Payment settings updated successfully!');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating settings: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Error updating settings: ' . $e->getMessage()]);
        }
    }

    private function validatePaymentSettings($gateway, $settings)
    {
        $rules = [];

        switch ($gateway) {
            case 'stripe':
                $rules = [
                    'stripe_public_key' => 'required|string',
                    'stripe_secret_key' => 'required|string',
                    'stripe_webhook_secret' => 'nullable|string',
                    'stripe_notes' => 'nullable|string'
                ];
                break;

            case 'paypal':
                $rules = [
                    'paypal_client_id' => 'required|string',
                    'paypal_client_secret' => 'required|string',
                    'paypal_webhook_id' => 'nullable|string',
                    'paypal_notes' => 'nullable|string'
                ];
                break;

            case 'razorpay':
                $rules = [
                    'razorpay_key_id' => 'required|string',
                    'razorpay_key_secret' => 'required|string',
                    'razorpay_webhook_secret' => 'nullable|string',
                    'razorpay_notes' => 'nullable|string'
                ];
                break;

            case 'square':
                $rules = [
                    'square_application_id' => 'required|string',
                    'square_access_token' => 'required|string',
                    'square_location_id' => 'nullable|string',
                    'square_notes' => 'nullable|string'
                ];
                break;

            case 'payone':
                $rules = [
                    'payone_merchant_id' => 'required|string',
                    'payone_portal_id' => 'required|string',
                    'payone_api_key' => 'required|string',
                    'payone_api_password' => 'required|string',
                    'payone_webhook_secret' => 'nullable|string',
                    'payone_notes' => 'nullable|string'
                ];
                break;

            case 'general':
                $rules = [
                    'payment_currency' => 'required|string|max:3',
                    'payment_mode' => 'required|in:sandbox,live',
                    'transaction_fee_percentage' => 'nullable|numeric|min:0|max:10',
                    'transaction_fee_fixed' => 'nullable|numeric|min:0'
                ];
                break;
        }

        if (!empty($rules)) {
            \Validator::make($settings, $rules)->validate();
        }
    }



public function email()
{
    $settings = Setting::where('group', 'email')->pluck('value', 'key');

    // Mock email statistics (you can replace with real data from your email service)
    $stats = [
        'total_sent' => 1250,
        'delivered' => 1198,
        'opened' => 856,
        'bounced' => 12
    ];

    return view('admin.settings.email', compact('settings', 'stats'));
}

// Update your updateEmail method to handle AJAX:

public function updateEmail(Request $request)
{
    $validated = $request->validate([
        'resend_api_key' => 'required|string|starts_with:re_',
        'sender_name' => 'required|string|max:255',
        'sender_email' => 'required|email',
    ]);

    // Test API key before saving
    $testResult = $this->testResendConnection($validated['resend_api_key']);

    if (!$testResult['success']) {
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $testResult['message']
            ], 422);
        }
        return back()->withErrors(['resend_api_key' => $testResult['message']]);
    }

    foreach ($validated as $key => $value) {
        Setting::updateOrCreate(
            ['group' => 'email', 'key' => $key],
            ['value' => $value]
        );
    }

    // Update API status
    Setting::updateOrCreate(
        ['group' => 'email', 'key' => 'api_status'],
        ['value' => 'connected']
    );

    if ($request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => 'Email settings updated successfully!'
        ]);
    }

    return back()->with('success', 'Email settings updated successfully!');
}



public function testEmailConnection(Request $request)
{
    $request->validate([
        'api_key' => 'required|string|starts_with:re_',
    ]);

    $result = $this->testResendConnection($request->api_key);

    return response()->json($result);
}

public function sendTestEmail(Request $request)
{

    $request->validate([
        'test_email' => 'required|email',
    ]);

    $apiKey = Setting::getValue('resend_api_key', 'email');
    $senderName = Setting::getValue('sender_name', 'email', 'TravelBookingPanel');
    $senderEmail = Setting::getValue('sender_email', 'email', 'noreply@Travelbookingpanel.com');

    if (!$apiKey) {
        return response()->json([
            'success' => false,
            'message' => 'API key not configured. Please save your API settings first.'
        ]);
    }

    try {
        // Configure Resend temporarily
        config(['services.resend.key' => $apiKey]);

        $result = Resend::emails()->send([
            'from' => "{$senderName} <{$senderEmail}>",
            'to' => [$request->test_email],
            'subject' => $senderName.' Email Test - Configuration Successful',
            'html' => $this->getTestEmailTemplate(),
        ]);

        // Save last test email
        Setting::updateOrCreate(
            ['group' => 'email', 'key' => 'last_test_email'],
            ['value' => $request->test_email]
        );

        return response()->json([
            'success' => true,
            'message' => 'Test email sent successfully! Please check your inbox.',
            'email_id' => $result['id'] ?? null
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to send test email: ' . $e->getMessage()
        ]);
    }
}

private function testResendConnection($apiKey)
{
    try {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->get('https://api.resend.com/domains');

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => 'API connection successful!'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Invalid API key or connection failed.'
            ];
        }
    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => 'Connection error: ' . $e->getMessage()
        ];
    }
}

private function getTestEmailTemplate()
{
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f8f9fa; }
            .success { background: #d1edff; border-left: 4px solid #0066cc; padding: 15px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🛫 Travelbookingpanel Email Test</h1>
            </div>
            <div class="content">
                <div class="success">
                    <strong>✅ Configuration Successful!</strong><br>
                    Your email settings are working correctly.
                </div>
                <p>This is a test email from your Travelbookingpanel travel platform.</p>
                <p><strong>Test Details:</strong></p>
                <ul>
                    <li>Service: Resend Email API</li>
                    <li>Time: ' . now()->format('Y-m-d H:i:s') . '</li>
                    <li>Status: Email delivery successful</li>
                </ul>
                <p>If you received this email, your email configuration is working perfectly!</p>
            </div>
        </div>
    </body>
    </html>';
}

}
