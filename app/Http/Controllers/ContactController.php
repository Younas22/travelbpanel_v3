<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function index()
    {
        return view('contact');
    }

    public function thankYou()
    {
        return view('pages.contact-thank-you');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string',
            'bookingRef' => 'nullable|string|max:50',
            'message' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Save to database
            $contactMessage = ContactMessage::create([
                'first_name' => $request->firstName,
                'last_name' => $request->lastName,
                'email' => $request->email,
                'phone' => $request->phone,
                'subject' => $request->subject,
                'booking_ref' => $request->bookingRef,
                'message' => $request->message,
            ]);

            // Send email to admin
            $this->sendAdminNotification($contactMessage);

            // Send confirmation email to customer
            $this->sendCustomerConfirmation($contactMessage);

            return response()->json([
                'success' => true,
                'message' => 'Thank you for contacting us! We will get back to you soon.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Contact form error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }

    private function sendAdminNotification($contactMessage)
    {
        try {
            // Get dynamic settings
            $sender_email = getSetting('sender_email', 'email', 'noreply@travelbookingpanel.com');
            $sender_name = getSetting('sender_name', 'email', 'Travel Booking Panel');
            $contact_email = getSetting('contact_email', 'contact', 'support@travelbookingpanel.com');

            Mail::send([], [], function ($message) use ($contactMessage, $sender_email, $sender_name, $contact_email) {
                $message->to($contact_email)
                        ->from($sender_email, $sender_name)
                        ->subject('New Contact Form Submission - ' . $contactMessage->subject)
                        ->html($this->getAdminEmailHtml($contactMessage));
            });

            \Log::info('Admin notification email sent successfully');
        } catch (\Exception $e) {
            \Log::error('Admin email sending failed: ' . $e->getMessage());
        }
    }

    private function sendCustomerConfirmation($contactMessage)
    {
        try {
            // Get dynamic settings
            $sender_email = getSetting('sender_email', 'email', 'noreply@travelbookingpanel.com');
            $sender_name = getSetting('sender_name', 'email', 'Travel Booking Panel');
            $businessName = getSetting('business_name', 'main', 'Travel Booking Panel');

            Mail::send([], [], function ($message) use ($contactMessage, $sender_email, $sender_name, $businessName) {
                $message->to($contactMessage->email)
                        ->from($sender_email, $sender_name)
                        ->subject('Thank You for Contacting ' . $businessName)
                        ->html($this->getCustomerEmailHtml($contactMessage));
            });

            \Log::info('Customer confirmation email sent successfully');
        } catch (\Exception $e) {
            \Log::error('Customer email sending failed: ' . $e->getMessage());
        }
    }

    private function getAdminEmailHtml($contactMessage)
    {
        // Get dynamic settings
        $businessName = getSetting('business_name', 'main', 'Travel Booking Panel');
        $businessLogo = getSettingImage('business_logo_white', 'branding');
        $businessAddress = getSetting('business_address', 'contact', '');
        $contactPhone = getSetting('contact_phone', 'contact', '+92 21 1234 5678');
        $contactEmail = getSetting('contact_email', 'contact', 'support@travelbookingpanel.com');

        $subjectLabels = [
            'booking' => 'Booking Assistance',
            'cancellation' => 'Cancellation/Refund',
            'modification' => 'Flight Modification',
            'complaint' => 'Complaint',
            'feedback' => 'Feedback',
            'partnership' => 'Business Partnership',
            'other' => 'Other'
        ];

        $subjectText = $subjectLabels[$contactMessage->subject] ?? ucfirst($contactMessage->subject);

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>New Contact Form Submission</title>
        </head>
        <body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background: #f8f9fa; line-height: 1.6;">
            
            <!-- Main Container -->
            <div style="max-width: 700px; margin: 0 auto; background: white; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);">
                
                <!-- Header Section -->
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 30px; text-align: center; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -50px; right: -50px; width: 100px; height: 100px; background: rgba(255, 255, 255, 0.1); border-radius: 50%;"></div>
                    <div style="position: absolute; bottom: -30px; left: -30px; width: 60px; height: 60px; background: rgba(255, 255, 255, 0.1); border-radius: 50%;"></div>
                    
                    <div style="position: relative; z-index: 2;">
                        <div style="background: rgba(255, 255, 255, 0.15); display: inline-block; padding: 15px; border-radius: 50%; margin-bottom: 20px;">
                            <div style="font-size: 40px;">📧</div>
                        </div>
                        <h1 style="margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -0.5px;">New Contact Form Submission</h1>
                        <p style="margin: 15px 0 0 0; font-size: 16px; opacity: 0.9;">Received on ' . now()->format('F j, Y \a\t g:i A') . '</p>
                        
                        <!-- Status Badge -->
                        <div style="background: #28a745; color: white; display: inline-block; padding: 8px 20px; border-radius: 20px; font-size: 14px; font-weight: 600; margin-top: 15px;">
                            ✉️ NEW MESSAGE
                        </div>
                    </div>
                </div>
                
                <!-- Content Section -->
                <div style="padding: 40px 30px;">
                    
                    <!-- Contact Details Card -->
                    <div style="background: #f8f9ff; border: 2px solid #e3f2fd; border-radius: 15px; padding: 25px; margin-bottom: 30px; position: relative;">
                        <div style="position: absolute; top: -10px; left: 25px; background: #667eea; color: white; padding: 5px 15px; border-radius: 15px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                            👤 Contact Information
                        </div>
                        
                        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                            <tr>
                                <td style="padding: 12px 0; font-weight: 600; width: 150px; color: #555; font-size: 14px; vertical-align: top;">
                                    <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 8px; display: inline-block;">👤 Name</span>
                                </td>
                                <td style="padding: 12px 0; font-size: 16px; font-weight: 600; color: #333;">
                                    ' . $contactMessage->full_name . '
                                </td>
                            </tr>
                            <tr style="border-top: 1px solid #f0f0f0;">
                                <td style="padding: 12px 0; font-weight: 600; color: #555; font-size: 14px; vertical-align: top;">
                                    <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 8px; display: inline-block;">📧 Email</span>
                                </td>
                                <td style="padding: 12px 0; font-size: 15px;">
                                    <a href="mailto:' . $contactMessage->email . '" style="color: #007bff; text-decoration: none; font-weight: 600;">' . $contactMessage->email . '</a>
                                </td>
                            </tr>
                            ' . ($contactMessage->phone ? '
                            <tr style="border-top: 1px solid #f0f0f0;">
                                <td style="padding: 12px 0; font-weight: 600; color: #555; font-size: 14px; vertical-align: top;">
                                    <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 8px; display: inline-block;">📱 Phone</span>
                                </td>
                                <td style="padding: 12px 0; font-size: 15px; color: #28a745; font-weight: 600;">
                                    <a href="tel:' . $contactMessage->phone . '" style="color: #28a745; text-decoration: none;">' . $contactMessage->phone . '</a>
                                </td>
                            </tr>' : '') . '
                            <tr style="border-top: 1px solid #f0f0f0;">
                                <td style="padding: 12px 0; font-weight: 600; color: #555; font-size: 14px; vertical-align: top;">
                                    <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 8px; display: inline-block;">📋 Subject</span>
                                </td>
                                <td style="padding: 12px 0; font-size: 15px; color: #666; font-weight: 600;">' . $subjectText . '</td>
                            </tr>
                            ' . ($contactMessage->booking_ref ? '
                            <tr style="border-top: 1px solid #f0f0f0;">
                                <td style="padding: 12px 0; font-weight: 600; color: #555; font-size: 14px; vertical-align: top;">
                                    <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 8px; display: inline-block;">🔖 Booking Ref</span>
                                </td>
                                <td style="padding: 12px 0; font-size: 15px; color: #666; font-family: monospace; font-weight: 600;">' . $contactMessage->booking_ref . '</td>
                            </tr>' : '') . '
                        </table>
                    </div>
                    
                    <!-- Message Card -->
                    <div style="background: white; border: 2px solid #dee2e6; border-left: 4px solid #667eea; border-radius: 10px; padding: 25px; margin-bottom: 30px;">
                        <h3 style="margin: 0 0 15px 0; color: #333; font-size: 18px;">💬 Message:</h3>
                        <p style="margin: 0; color: #555; font-size: 15px; line-height: 1.7; white-space: pre-wrap;">' . nl2br(htmlspecialchars($contactMessage->message)) . '</p>
                    </div>
                    
                    <!-- Action Required Section -->
                    <div style="background: linear-gradient(135deg, #fff3cd, #ffeaa7); border: 2px solid #ffc107; border-radius: 15px; padding: 25px; margin-bottom: 30px; position: relative;">
                        <div style="position: absolute; top: -10px; left: 25px; background: #ffc107; color: #333; padding: 5px 15px; border-radius: 15px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                            ⚠️ Action Required
                        </div>
                        
                        <div style="margin-top: 10px;">
                            <h3 style="color: #856404; margin: 0 0 15px 0; font-size: 18px;">Next Steps:</h3>
                            <ul style="color: #856404; margin: 0; padding-left: 20px; font-size: 15px;">
                                <li style="margin-bottom: 8px;">📧 Reply to the customer within 24 hours</li>
                                <li style="margin-bottom: 8px;">📋 Review the inquiry details carefully</li>
                                <li style="margin-bottom: 8px;">✅ Mark as resolved in the system</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Stats -->
                    <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                        <div style="flex: 1; background: #e3f2fd; padding: 20px; border-radius: 12px; text-align: center;">
                            <div style="font-size: 24px; font-weight: 700; color: #667eea; margin-bottom: 5px;">ID</div>
                            <div style="font-size: 14px; color: #666; font-family: monospace;">#' . str_pad($contactMessage->id, 6, '0', STR_PAD_LEFT) . '</div>
                        </div>
                        <div style="flex: 1; background: #f0fff4; padding: 20px; border-radius: 12px; text-align: center;">
                            <div style="font-size: 24px; font-weight: 700; color: #28a745; margin-bottom: 5px;">Status</div>
                            <div style="font-size: 14px; color: #666; font-weight: 600;">New</div>
                        </div>
                        <div style="flex: 1; background: #fff3cd; padding: 20px; border-radius: 12px; text-align: center;">
                            <div style="font-size: 24px; font-weight: 700; color: #856404; margin-bottom: 5px;">Priority</div>
                            <div style="font-size: 14px; color: #666; font-weight: 600;">Normal</div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div style="background: #343a40; color: white; padding: 25px 30px; text-align: center;">
                    ' . ($businessLogo ? '<div style="margin-bottom: 15px;"><img src="' . $businessLogo . '" alt="' . htmlspecialchars($businessName) . '" style="max-height: 40px;"></div>' : '') . '
                    <div style="font-size: 20px; font-weight: 700; margin-bottom: 10px; color: #17a2b8;">' . htmlspecialchars($businessName) . '</div>
                    <p style="margin: 0; font-size: 14px; opacity: 0.8; line-height: 1.5;">
                        Your Trusted Travel Partner
                    </p>
                    ' . ($businessAddress ? '<p style="margin: 10px 0 0 0; font-size: 13px; opacity: 0.7;">' . nl2br(htmlspecialchars($businessAddress)) . '</p>' : '') . '
                    ' . ($contactPhone || $contactEmail ? '<p style="margin: 10px 0 0 0; font-size: 13px; opacity: 0.7;">' . ($contactPhone ? 'Phone: ' . htmlspecialchars($contactPhone) : '') . ($contactPhone && $contactEmail ? ' | ' : '') . ($contactEmail ? 'Email: ' . htmlspecialchars($contactEmail) : '') . '</p>' : '') . '
                    <div style="margin-top: 15px; font-size: 12px; opacity: 0.6;">
                        © ' . date('Y') . ' ' . htmlspecialchars($businessName) . '. All rights reserved.
                    </div>
                </div>
            </div>

        </body>
        </html>';
    }

    private function getCustomerEmailHtml($contactMessage)
    {
        // Get dynamic settings
        $businessName = getSetting('business_name', 'main', 'Travel Booking Panel');
        $businessLogo = getSettingImage('business_logo_white', 'branding');
        $businessAddress = getSetting('business_address', 'contact', '');
        $contactPhone = getSetting('contact_phone', 'contact', '+92 21 1234 5678');
        $contactEmail = getSetting('contact_email', 'contact', 'support@travelbookingpanel.com');

        $subjectLabels = [
            'booking' => 'Booking Assistance',
            'cancellation' => 'Cancellation/Refund',
            'modification' => 'Flight Modification',
            'complaint' => 'Complaint',
            'feedback' => 'Feedback',
            'partnership' => 'Business Partnership',
            'other' => 'Other'
        ];

        $subjectText = $subjectLabels[$contactMessage->subject] ?? ucfirst($contactMessage->subject);

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Thank You for Contacting Us</title>
        </head>
        <body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background: #f8f9fa; line-height: 1.6;">
            
            <!-- Main Container -->
            <div style="max-width: 700px; margin: 0 auto; background: white; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);">
                
                <!-- Header Section -->
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 30px; text-align: center; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -50px; right: -50px; width: 100px; height: 100px; background: rgba(255, 255, 255, 0.1); border-radius: 50%;"></div>
                    <div style="position: absolute; bottom: -30px; left: -30px; width: 60px; height: 60px; background: rgba(255, 255, 255, 0.1); border-radius: 50%;"></div>
                    
                    <div style="position: relative; z-index: 2;">
                        <div style="background: rgba(255, 255, 255, 0.15); display: inline-block; padding: 15px; border-radius: 50%; margin-bottom: 20px;">
                            <div style="font-size: 40px;">✅</div>
                        </div>
                        <h1 style="margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -0.5px;">Thank You for Contacting Us!</h1>
                        <p style="margin: 15px 0 0 0; font-size: 16px; opacity: 0.9;">We\'ve received your message</p>
                    </div>
                </div>
                
                <!-- Content Section -->
                <div style="padding: 40px 30px;">
                    
                    <p style="font-size: 16px; color: #333; margin: 0 0 20px 0;">Dear ' . $contactMessage->first_name . ',</p>
                    
                    <p style="font-size: 16px; color: #555; line-height: 1.7; margin: 0 0 25px 0;">
                        Thank you for reaching out to ' . htmlspecialchars($businessName) . '. We have received your message and our team will review it shortly. We appreciate you taking the time to contact us!
                    </p>
                    
                    <!-- Message Summary Card -->
                    <div style="background: #f8f9ff; border: 2px solid #e3f2fd; border-radius: 15px; padding: 25px; margin-bottom: 30px; position: relative;">
                        <div style="position: absolute; top: -10px; left: 25px; background: #667eea; color: white; padding: 5px 15px; border-radius: 15px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                            📋 Your Message Details
                        </div>
                        
                        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                            <tr>
                                <td style="padding: 12px 0; font-weight: 600; width: 150px; color: #555; font-size: 14px;">
                                    <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 8px; display: inline-block;">📋 Subject</span>
                                </td>
                                <td style="padding: 12px 0; font-size: 15px; color: #666; font-weight: 600;">' . $subjectText . '</td>
                            </tr>
                            ' . ($contactMessage->booking_ref ? '
                            <tr style="border-top: 1px solid #f0f0f0;">
                                <td style="padding: 12px 0; font-weight: 600; color: #555; font-size: 14px;">
                                    <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 8px; display: inline-block;">🔖 Booking Ref</span>
                                </td>
                                <td style="padding: 12px 0; font-size: 15px; color: #666; font-family: monospace; font-weight: 600;">' . $contactMessage->booking_ref . '</td>
                            </tr>' : '') . '
                            <tr style="border-top: 1px solid #f0f0f0;">
                                <td style="padding: 12px 0; font-weight: 600; color: #555; font-size: 14px;">
                                    <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 8px; display: inline-block;">📅 Submitted</span>
                                </td>
                                <td style="padding: 12px 0; font-size: 15px; color: #666;">' . $contactMessage->created_at->format('M d, Y h:i A') . '</td>
                            </tr>
                            <tr style="border-top: 1px solid #f0f0f0;">
                                <td style="padding: 12px 0; font-weight: 600; color: #555; font-size: 14px;">
                                    <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 8px; display: inline-block;">🆔 Reference ID</span>
                                </td>
                                <td style="padding: 12px 0; font-size: 15px; color: #666; font-family: monospace; font-weight: 600;">#' . str_pad($contactMessage->id, 6, '0', STR_PAD_LEFT) . '</td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- What Happens Next -->
                    <div style="background: #f0fff4; border: 2px solid #d4edda; border-radius: 15px; padding: 25px; margin-bottom: 30px; position: relative;">
                        <div style="position: absolute; top: -10px; left: 25px; background: #28a745; color: white; padding: 5px 15px; border-radius: 15px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                            ⏱️ What Happens Next?
                        </div>
                        
                        <div style="margin-top: 10px;">
                            <ul style="color: #155724; margin: 0; padding-left: 20px; font-size: 15px; line-height: 1.8;">
                                <li style="margin-bottom: 10px;">📧 Our team will review your inquiry</li>
                                <li style="margin-bottom: 10px;">⏰ We typically respond within 24-48 hours</li>
                                <li style="margin-bottom: 10px;">📞 For urgent matters, please call our support line</li>
                                <li style="margin-bottom: 10px;">✅ You\'ll receive a response via email</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Contact Information -->
                    <div style="background: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; border-radius: 0 8px 8px 0; margin-bottom: 20px;">
                        <h4 style="color: #495057; margin: 0 0 10px 0; font-size: 16px;">📞 Need Immediate Assistance?</h4>
                        <p style="margin: 0; color: #6c757d; font-size: 14px; line-height: 1.7;">
                            If you have any urgent concerns, please don\'t hesitate to contact us directly:<br><br>
                            <strong>Email:</strong> <a href="mailto:' . htmlspecialchars($contactEmail) . '" style="color: #667eea; text-decoration: none;">' . htmlspecialchars($contactEmail) . '</a><br>
                            <strong>Phone:</strong> ' . htmlspecialchars($contactPhone) . '<br>
                            <strong>Hours:</strong> Mon-Fri 9AM-6PM
                        </p>
                    </div>
                    
                    <!-- CTA Button -->
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="' . url('/') . '" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 40px; border-radius: 30px; text-decoration: none; font-weight: 600; font-size: 16px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);">
                            🌐 Visit Our Website
                        </a>
                    </div>
                </div>
                
                <!-- Footer -->
                <div style="background: #343a40; color: white; padding: 25px 30px; text-align: center;">
                    ' . ($businessLogo ? '<div style="margin-bottom: 15px;"><img src="' . $businessLogo . '" alt="' . htmlspecialchars($businessName) . '" style="max-height: 40px;"></div>' : '') . '
                    <div style="font-size: 20px; font-weight: 700; margin-bottom: 10px; color: #17a2b8;">' . htmlspecialchars($businessName) . '</div>
                    <p style="margin: 0; font-size: 14px; opacity: 0.8; line-height: 1.5;">
                        Your Trusted Travel Partner<br>
                        Making travel dreams come true
                    </p>
                    ' . ($businessAddress ? '<p style="margin: 10px 0 0 0; font-size: 13px; opacity: 0.7;">' . nl2br(htmlspecialchars($businessAddress)) . '</p>' : '') . '
                    ' . ($contactPhone || $contactEmail ? '<p style="margin: 10px 0 0 0; font-size: 13px; opacity: 0.7;">' . ($contactPhone ? 'Phone: ' . htmlspecialchars($contactPhone) : '') . ($contactPhone && $contactEmail ? ' | ' : '') . ($contactEmail ? 'Email: ' . htmlspecialchars($contactEmail) : '') . '</p>' : '') . '

                    <div style="font-size: 12px; opacity: 0.6; margin-top: 15px;">
                        © ' . date('Y') . ' ' . htmlspecialchars($businessName) . '. All rights reserved.
                    </div>
                </div>
            </div>

        </body>
        </html>';
    }




        // Admin Methods
    public function adminIndex(Request $request)
    {
        $query = ContactMessage::query()->orderBy('created_at', 'desc');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('booking_ref', 'like', "%{$search}%");
            });
        }

        // Subject Filter
        if ($request->filled('subject')) {
            $query->where('subject', $request->subject);
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date Range Filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $contactMessages = $query->paginate(20)->withQueryString();

        // Statistics
        $stats = [
            'total' => ContactMessage::count(),
            'new' => ContactMessage::where('status', 'new')->count(),
            'read' => ContactMessage::where('status', 'read')->count(),
            'replied' => ContactMessage::where('status', 'replied')->count(),
        ];

        return view('admin.contact-messages.index', compact('contactMessages', 'stats'));
    }

    public function adminShow($id)
    {
        $contactMessage = ContactMessage::findOrFail($id);
        
        // Mark as read if it's new
        if ($contactMessage->status === 'new') {
            $contactMessage->update(['status' => 'read']);
        }

        return view('admin.contact-messages.show', compact('contactMessage'));
    }

    public function updateStatus(Request $request, $id)
    {
        $contactMessage = ContactMessage::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:new,read,replied'
        ]);

        $contactMessage->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Status updated successfully!');
    }

    public function destroy($id)
    {
        $contactMessage = ContactMessage::findOrFail($id);
        $contactMessage->delete();

        return redirect()->route('admin.contact-messages.index')
                        ->with('success', 'Contact message deleted successfully!');
    }
    
}