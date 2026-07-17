<?php
namespace App\Http\Controllers;

use App\Models\VisaRequest;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Resend\Laravel\Facades\Resend;

class VisaController extends Controller
{
    public function create()
    {
        $countries = Location::select('country', 'country_code')
            ->distinct()
            ->orderBy('country', 'asc')
            ->get();
        return view('visa.visa', compact('countries'));
    }


 
    public function store(Request $request)
    {
        try {
            // Validation rules
            $rules = [
                'visa_type' => 'required|string|max:255',
                'visa_plan' => 'required|string|max:255',
                'first_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'surname' => 'required|string|max:255',
                'father_name' => 'required|string|max:255',
                'mother_name' => 'required|string|max:255',
                'place_birth' => 'required|string|max:255',
                'occupation' => 'required|string|max:255',
                'marital_status' => 'required|string',
                'religion' => 'required|string|max:255',
                'nationality' => 'required|string',
                'passport_no' => 'required|string|max:255',
                'passport_issue_date' => 'required|date',
                'passport_expiry_date' => 'required|date|after:passport_issue_date',
                'gender' => 'required|in:male,female',
                'passport_front' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'passport_back' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'passport_photo' => 'required|file|mimes:jpg,jpeg,png|max:5120',
                'other_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'agreed_terms' => 'required',
            ];

            // Validate data
            $validatedData = $request->validate($rules);

            // Convert agreed_terms to boolean
            if (isset($validatedData['agreed_terms'])) {
                $validatedData['agreed_terms'] = $validatedData['agreed_terms'] === 'on' ? 1 : 0;
            }

            // Handle file uploads
            if ($request->hasFile('passport_front')) {
                $validatedData['passport_front'] = $request->file('passport_front')->store('visa-documents', 'public');
            }

            if ($request->hasFile('passport_back')) {
                $validatedData['passport_back'] = $request->file('passport_back')->store('visa-documents', 'public');
            }

            if ($request->hasFile('passport_photo')) {
                $validatedData['passport_photo'] = $request->file('passport_photo')->store('visa-documents', 'public');
            }

            if ($request->hasFile('other_document')) {
                $validatedData['other_document'] = $request->file('other_document')->store('visa-documents', 'public');
            }

            // Attach user/agent identity
            $validatedData['booked_via'] = 'guest';
            if (auth()->check()) {
                $authUser = auth()->user();
                if ($authUser->isAgent()) {
                    $validatedData['agent_id']   = $authUser->id;
                    $validatedData['booked_via'] = 'agent';
                } elseif ($authUser->isCustomer()) {
                    $validatedData['user_id']    = $authUser->id;
                    $validatedData['booked_via'] = 'user';
                }
            }

            // Create visa request
            $visaRequest = VisaRequest::create($validatedData);

            // Send email notification
            $this->sendVisaEmail($visaRequest);

            // Return success response
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'redirect_url' => route('visa.success')
                ]);
            }

            return redirect()->route('visa.success')->with('success', 'Visa application submitted successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation Error:', $e->errors());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }

            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            \Log::error('Visa submission error:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while submitting your application'
                ], 500);
            }

            return back()->withErrors(['error' => 'An error occurred. Please try again.'])->withInput();
        }
    }


    private function sendVisaEmail($visaRequest)
    {
        $visaTypeText = strtoupper($visaRequest->visa_type ?? 'VISA');
        $visaPlan = strtoupper($visaRequest->visa_plan ?? '');

        // Get dynamic settings
        $sender_name = getSetting('sender_name', 'email');
        $sender_email = getSetting('sender_email', 'email');
        $businessName = getSetting('business_name', 'main', 'Travel Booking Panel');
        $businessLogo = getSettingImage('business_logo_white', 'branding');
        $businessAddress = getSetting('business_address', 'contact', '');
        $contactPhone = getSetting('contact_phone', 'contact', '+92 21 1234 5678');
        $contactEmail = getSetting('contact_email', 'contact', 'support@travelbookingpanel.com');

        $html = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>New ' . $visaTypeText . ' Application</title>
        </head>
        <body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif; background: #f8f9fa; line-height: 1.6;">

            <!-- Main Container -->
            <div style="max-width: 800px; margin: 0 auto; background: white; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);">

                <!-- Header Section -->
                <div style="background: linear-gradient(135deg, #0052FE 0%, #0041CC 50%, #003399 100%); color: white; padding: 40px 30px; text-align: center; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -50px; right: -50px; width: 100px; height: 100px; background: rgba(255, 255, 255, 0.1); border-radius: 50%;"></div>
                    <div style="position: absolute; bottom: -30px; left: -30px; width: 60px; height: 60px; background: rgba(255, 255, 255, 0.1); border-radius: 50%;"></div>

                    <div style="position: relative; z-index: 2;">' .
                        ($businessLogo ? '<img src="' . $businessLogo . '" alt="' . $businessName . '" style="max-height: 60px; margin-bottom: 20px;" />' :
                        '<div style="background: rgba(255, 255, 255, 0.15); display: inline-block; padding: 15px; border-radius: 50%; margin-bottom: 20px;">
                            <div style="font-size: 40px;">✈️</div>
                        </div>') . '
                        <h1 style="margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -0.5px;">New Visa Application</h1>
                        <p style="margin: 15px 0 0 0; font-size: 16px; opacity: 0.9;">Submitted on ' . now()->format('F j, Y \a\t g:i A') . '</p>

                        <!-- Status Badge -->
                        <div style="background: #28a745; color: white; display: inline-block; padding: 8px 20px; border-radius: 20px; font-size: 14px; font-weight: 600; margin-top: 15px;">
                            📋 NEW APPLICATION
                        </div>
                    </div>
                </div>

                <!-- Content Section -->
                <div style="padding: 40px 30px;">

                    <!-- Visa Details Card -->
                    <div style="background: #f0fff4; border: 2px solid #d4edda; border-radius: 15px; padding: 25px; margin-bottom: 30px; position: relative;">
                        <div style="position: absolute; top: -10px; left: 25px; background: #28a745; color: white; padding: 5px 15px; border-radius: 15px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                            📋 Visa Information
                        </div>

                        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                            <tr>
                                <td style="padding: 12px 0; font-weight: 600; width: 180px; color: #555; font-size: 14px; vertical-align: top;">
                                    <span style="background: #d4edda; padding: 4px 8px; border-radius: 8px; display: inline-block;">🌍 Visa Type</span>
                                </td>
                                <td style="padding: 12px 0; font-size: 16px; font-weight: 600; color: #333;">' . $visaTypeText . '</td>
                            </tr>
                            <tr style="border-top: 1px solid #f0f0f0;">
                                <td style="padding: 12px 0; font-weight: 600; color: #555; font-size: 14px; vertical-align: top;">
                                    <span style="background: #d4edda; padding: 4px 8px; border-radius: 8px; display: inline-block;">📦 Plan</span>
                                </td>
                                <td style="padding: 12px 0; font-size: 15px; color: #666;">' . $visaPlan . '</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Passenger Details Card -->
                    <div style="background: #f8f9ff; border: 2px solid #e3f2fd; border-radius: 15px; padding: 25px; margin-bottom: 30px; position: relative;">
                        <div style="position: absolute; top: -10px; left: 25px; background: #0052FE; color: white; padding: 5px 15px; border-radius: 15px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                            👤 Passenger Information
                        </div>

                        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                            <tr>
                                <td style="padding: 12px 0; font-weight: 600; width: 180px; color: #555; font-size: 14px; vertical-align: top;">
                                    <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 8px; display: inline-block;">👤 Full Name</span>
                                </td>
                                <td style="padding: 12px 0; font-size: 16px; font-weight: 600; color: #333;">
                                    ' . $visaRequest->first_name . ' ' . ($visaRequest->middle_name ?? '') . ' ' . $visaRequest->surname . '
                                </td>
                            </tr>
                            <tr style="border-top: 1px solid #f0f0f0;">
                                <td style="padding: 12px 0; font-weight: 600; color: #555; font-size: 14px; vertical-align: top;">
                                    <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 8px; display: inline-block;">👨 Father\'s Name</span>
                                </td>
                                <td style="padding: 12px 0; font-size: 15px; color: #666;">' . $visaRequest->father_name . '</td>
                            </tr>
                            <tr style="border-top: 1px solid #f0f0f0;">
                                <td style="padding: 12px 0; font-weight: 600; color: #555; font-size: 14px; vertical-align: top;">
                                    <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 8px; display: inline-block;">👩 Mother\'s Name</span>
                                </td>
                                <td style="padding: 12px 0; font-size: 15px; color: #666;">' . $visaRequest->mother_name . '</td>
                            </tr>
                            <tr style="border-top: 1px solid #f0f0f0;">
                                <td style="padding: 12px 0; font-weight: 600; color: #555; font-size: 14px; vertical-align: top;">
                                    <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 8px; display: inline-block;">📍 Place of Birth</span>
                                </td>
                                <td style="padding: 12px 0; font-size: 15px; color: #666;">' . $visaRequest->place_birth . '</td>
                            </tr>
                            <tr style="border-top: 1px solid #f0f0f0;">
                                <td style="padding: 12px 0; font-weight: 600; color: #555; font-size: 14px; vertical-align: top;">
                                    <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 8px; display: inline-block;">🌍 Nationality</span>
                                </td>
                                <td style="padding: 12px 0; font-size: 15px; color: #666;">' . strtoupper($visaRequest->nationality) . '</td>
                            </tr>
                            <tr style="border-top: 1px solid #f0f0f0;">
                                <td style="padding: 12px 0; font-weight: 600; color: #555; font-size: 14px; vertical-align: top;">
                                    <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 8px; display: inline-block;">📘 Passport No</span>
                                </td>
                                <td style="padding: 12px 0; font-size: 15px; color: #666; font-family: monospace; font-weight: 600;">' . $visaRequest->passport_no . '</td>
                            </tr>
                            <tr style="border-top: 1px solid #f0f0f0;">
                                <td style="padding: 12px 0; font-weight: 600; color: #555; font-size: 14px; vertical-align: top;">
                                    <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 8px; display: inline-block;">📅 Passport Issue</span>
                                </td>
                                <td style="padding: 12px 0; font-size: 15px; color: #666;">' . $visaRequest->passport_issue_date->format('d M Y') . '</td>
                            </tr>
                            <tr style="border-top: 1px solid #f0f0f0;">
                                <td style="padding: 12px 0; font-weight: 600; color: #555; font-size: 14px; vertical-align: top;">
                                    <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 8px; display: inline-block;">📅 Passport Expiry</span>
                                </td>
                                <td style="padding: 12px 0; font-size: 15px; color: #666;">' . $visaRequest->passport_expiry_date->format('d M Y') . '</td>
                            </tr>
                            <tr style="border-top: 1px solid #f0f0f0;">
                                <td style="padding: 12px 0; font-weight: 600; color: #555; font-size: 14px; vertical-align: top;">
                                    <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 8px; display: inline-block;">⚧ Gender</span>
                                </td>
                                <td style="padding: 12px 0; font-size: 15px; color: #666;">' . ucfirst($visaRequest->gender) . '</td>
                            </tr>
                            <tr style="border-top: 1px solid #f0f0f0;">
                                <td style="padding: 12px 0; font-weight: 600; color: #555; font-size: 14px; vertical-align: top;">
                                    <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 8px; display: inline-block;">💼 Occupation</span>
                                </td>
                                <td style="padding: 12px 0; font-size: 15px; color: #666;">' . $visaRequest->occupation . '</td>
                            </tr>
                            <tr style="border-top: 1px solid #f0f0f0;">
                                <td style="padding: 12px 0; font-weight: 600; color: #555; font-size: 14px; vertical-align: top;">
                                    <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 8px; display: inline-block;">💍 Marital Status</span>
                                </td>
                                <td style="padding: 12px 0; font-size: 15px; color: #666;">' . ucfirst($visaRequest->marital_status) . '</td>
                            </tr>
                            <tr style="border-top: 1px solid #f0f0f0;">
                                <td style="padding: 12px 0; font-weight: 600; color: #555; font-size: 14px; vertical-align: top;">
                                    <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 8px; display: inline-block;">🕌 Religion</span>
                                </td>
                                <td style="padding: 12px 0; font-size: 15px; color: #666;">' . $visaRequest->religion . '</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Action Required Section -->
                    <div style="background: linear-gradient(135deg, #fff3cd, #ffeaa7); border: 2px solid #ffc107; border-radius: 15px; padding: 25px; margin-bottom: 30px; position: relative;">
                        <div style="position: absolute; top: -10px; left: 25px; background: #ffc107; color: #333; padding: 5px 15px; border-radius: 15px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                            ⚠️ Action Required
                        </div>

                        <div style="margin-top: 10px;">
                            <h3 style="color: #856404; margin: 0 0 15px 0; font-size: 18px;">Next Steps:</h3>
                            <ul style="color: #856404; margin: 0; padding-left: 20px; font-size: 15px;">
                                <li style="margin-bottom: 8px;">📋 Review the application details carefully</li>
                                <li style="margin-bottom: 8px;">📞 Contact the applicant within 24 hours</li>
                                <li style="margin-bottom: 8px;">📄 Verify all submitted documents</li>
                                <li style="margin-bottom: 8px;">✅ Update application status in the system</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Application Stats -->
                    <div style="display: flex; gap: 15px; margin-bottom: 30px;">
                        <div style="flex: 1; background: #e3f2fd; padding: 20px; border-radius: 12px; text-align: center;">
                            <div style="font-size: 24px; font-weight: 700; color: #0052FE; margin-bottom: 5px;">ID</div>
                            <div style="font-size: 14px; color: #666; font-family: monospace;">#' . str_pad($visaRequest->id ?? 'N/A', 6, '0', STR_PAD_LEFT) . '</div>
                        </div>
                        <div style="flex: 1; background: #f0fff4; padding: 20px; border-radius: 12px; text-align: center;">
                            <div style="font-size: 24px; font-weight: 700; color: #28a745; margin-bottom: 5px;">Type</div>
                            <div style="font-size: 14px; color: #666; text-transform: uppercase; font-weight: 600;">' . $visaTypeText . '</div>
                        </div>
                        <div style="flex: 1; background: #fff3cd; padding: 20px; border-radius: 12px; text-align: center;">
                            <div style="font-size: 24px; font-weight: 700; color: #856404; margin-bottom: 5px;">Status</div>
                            <div style="font-size: 14px; color: #666; font-weight: 600;">Pending Review</div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div style="background: #f8f9fa; border-left: 4px solid #6c757d; padding: 20px; border-radius: 0 8px 8px 0; margin-bottom: 20px;">
                        <h4 style="color: #495057; margin: 0 0 10px 0; font-size: 16px;">📞 Need Help?</h4>
                        <p style="margin: 0; color: #6c757d; font-size: 14px; line-height: 1.5;">
                            If you need assistance with this application, contact our support team:<br>
                            <strong>Email:</strong> ' . $contactEmail . '<br>
                            <strong>Phone:</strong> ' . $contactPhone . '<br>' .
                            ($businessAddress ? '<strong>Address:</strong> ' . $businessAddress . '<br>' : '') . '
                            <strong>Hours:</strong> Mon-Fri 9AM-8PM, Sat-Sun 10AM-6PM
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div style="background: #343a40; color: white; padding: 25px 30px; text-align: center;">
                    <div style="font-size: 20px; font-weight: 700; margin-bottom: 10px; color: #17a2b8;">' . $businessName . '</div>
                    <p style="margin: 0; font-size: 14px; opacity: 0.8; line-height: 1.5;">
                        Your Trusted Travel Partner<br>
                        Making travel dreams come true since 2018
                    </p>
                    <div style="margin-top: 15px; font-size: 12px; opacity: 0.6;">
                        © ' . date('Y') . ' ' . $businessName . '. All rights reserved.
                    </div>
                </div>
            </div>

        </body>
        </html>';

        try {
            Resend::emails()->send([
                'from' => "{$sender_name} <{$sender_email}>",
                'to' => ['amdglobal62@gmail.com'],
                'subject' => 'New ' . $visaTypeText . ' Application - ' . $visaRequest->first_name . ' ' . $visaRequest->surname,
                'html' => $html,
            ]);
        } catch (\Exception $e) {
            \Log::error('Email sending failed: ' . $e->getMessage());
        }
    }

    public function success()
    {
        return view('visa.success');
    }



    /**
     * Display a listing of the visa requests.
     */
    public function visaindex(Request $request)
    {
        $query = VisaRequest::query();

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('surname', 'like', "%{$searchTerm}%")
                  ->orWhere('passport_no', 'like', "%{$searchTerm}%")
                  ->orWhere('nationality', 'like', "%{$searchTerm}%")
                  ->orWhere('guarantor_name', 'like', "%{$searchTerm}%")
                  ->orWhere('guarantor_email', 'like', "%{$searchTerm}%");
            });
        }

        // Filter by visa category
        if ($request->filled('visa_category')) {
            $query->where('visa_category', $request->visa_category);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Get paginated results
        $visaRequests = $query->latest()->paginate(15);

        // Calculate stats
        $stats = [
            'total' => VisaRequest::count(),
            'uae' => VisaRequest::where('visa_category', 'uae')->count(),
            'other' => VisaRequest::where('visa_category', 'other')->count(),
        ];

        return view('admin.visa-requests.index', compact('visaRequests', 'stats'));
    }

    /**
     * Display the specified visa request.
     */
    public function show(VisaRequest $visaRequest)
    {
        return view('admin.visa-requests.show', compact('visaRequest'));
    }

    /**
     * Download document
     */
    public function downloadDocument(VisaRequest $visaRequest, $documentType)
    {
        $allowedTypes = ['passport_front', 'passport_back', 'passport_photo', 'other_document'];
        
        if (!in_array($documentType, $allowedTypes)) {
            abort(404);
        }

        $filePath = $visaRequest->{$documentType};
        
        if (!$filePath || !Storage::exists($filePath)) {
            abort(404);
        }

        $fileName = basename($filePath);
        
        return Storage::download($filePath, $fileName);
    }

    /**
     * Export visa requests to CSV
     */
    public function export(Request $request)
    {
        $query = VisaRequest::query();

        // Apply same filters as index
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('surname', 'like', "%{$searchTerm}%")
                  ->orWhere('passport_no', 'like', "%{$searchTerm}%")
                  ->orWhere('nationality', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('visa_category')) {
            $query->where('visa_category', $request->visa_category);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $visaRequests = $query->latest()->get();

        $filename = 'visa_requests_' . now()->format('Y_m_d_H_i_s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($visaRequests) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'ID',
                'Full Name',
                'Visa Category',
                'Visa Type',
                'Passport Number',
                'Nationality',
                'Guarantor Name',
                'Receipt Amount',
                'Applied Date'
            ]);

            // CSV Data
            foreach ($visaRequests as $request) {
                fputcsv($file, [
                    'VR' . str_pad($request->id, 6, '0', STR_PAD_LEFT),
                    trim($request->first_name . ' ' . ($request->middle_name ?? '') . ' ' . $request->surname),
                    ucfirst($request->visa_category),
                    ucfirst($request->visa_type ?? ''),
                    $request->passport_no,
                    ucfirst($request->nationality),
                    $request->guarantor_name ?? '',
                    $request->receipt_amount ?? '',
                    $request->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}