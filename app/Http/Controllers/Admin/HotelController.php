<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelImage;
use App\Models\Location;
use App\Models\AllAmenity;
use App\Models\HotelPolicy;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Resend\Laravel\Facades\Resend;

class HotelController extends Controller
{
    private function isAgent(): bool
    {
        return auth()->check() && auth()->user()->user_type === 'agent';
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $type   = $request->input('type');

        $query = Hotel::with(['images', 'location']);

        // Agents see only their own records
        if ($this->isAgent()) {
            $query->where('agent_id', auth()->id());
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhereHas('location', function ($query) use ($search) {
                        $query->where('city', 'like', "%{$search}%")
                              ->orWhere('country', 'like', "%{$search}%");
                    });
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $hotels = $query->latest()->paginate(20);

        if ($this->isAgent()) {
            $stats = [
                'total'    => Hotel::where('agent_id', auth()->id())->count(),
                'pending'  => Hotel::where('agent_id', auth()->id())->where('approval_status', 'pending')->count(),
                'approved' => Hotel::where('agent_id', auth()->id())->where('approval_status', 'approved')->count(),
                'rejected' => Hotel::where('agent_id', auth()->id())->where('approval_status', 'rejected')->count(),
            ];
            return view('agent.hotels.index', compact('hotels', 'stats', 'search', 'status', 'type'));
        }

        $stats = [
            'total'    => Hotel::count(),
            'active'   => Hotel::where('status', 1)->count(),
            'inactive' => Hotel::where('status', 0)->count(),
            'hotel'    => Hotel::where('type', 'hotel')->count(),
            'resort'   => Hotel::where('type', 'resort')->count(),
            'pending'  => Hotel::where('approval_status', 'pending')->count(),
        ];

        return view('admin.hotels.index', compact('hotels', 'stats', 'search', 'status', 'type'));
    }

    public function create()
    {
        $locations = Location::where('status', '1')->get();
        $amenities = AllAmenity::all();

        if ($this->isAgent()) {
            return view('agent.hotels.create', compact('locations', 'amenities'));
        }

        return view('admin.hotels.create', compact('locations', 'amenities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'location_id'    => 'required|integer|exists:locations,id',
            'type'           => 'required|string|in:hotel,guest house,resort',
            'description'    => 'nullable|string',
            'address'        => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'whatsapp'       => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:100',
            'check_in_time'  => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'total_rooms'    => 'nullable|integer|min:0',
            'stars'          => 'nullable|integer|min:1|max:5',
            'total_rating'   => 'nullable|integer|min:0',
            'status'         => 'required|in:0,1',
            'amenities'      => 'nullable|array',
            'amenities.*'    => 'integer|exists:all_amenities,id',
            'images.*'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_types.*'  => 'nullable|string',
        ]);

        if ($this->isAgent()) {
            $validated['agent_id']       = auth()->id();
            $validated['added_by']       = 'agent';
            $validated['approval_status'] = 'pending';
            $validated['status']         = '0'; // hidden until approved
        } else {
            $validated['added_by']       = 'admin';
            $validated['approval_status'] = 'approved';
        }

        $hotel = Hotel::create($validated);

        if ($request->has('amenities')) {
            $hotel->amenities()->attach($request->amenities);
        }

        if ($request->hasFile('images')) {
            $uploadPath = public_path('assets/images/hotel');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            foreach ($request->file('images') as $index => $image) {
                $fileName  = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move($uploadPath, $fileName);
                $imageType = $request->image_types[$index] ?? 'general';
                HotelImage::create([
                    'hotel_id'   => $hotel->id,
                    'image_path' => 'hotel/' . $fileName,
                    'image_type' => $imageType,
                    'sort_order' => $index,
                ]);
            }
        }

        if ($request->has('policies')) {
            foreach ($request->policies as $policy) {
                if (!empty($policy['description'])) {
                    HotelPolicy::create([
                        'hotel_id'    => $hotel->id,
                        'policy_type' => $policy['type'] ?? 'general',
                        'description' => $policy['description'],
                    ]);
                }
            }
        }

        if ($this->isAgent()) {
            return redirect()->route('agent.hotels.index')
                ->with('success', 'Hotel submitted for approval successfully!');
        }

        return redirect()->route('admin.hotels.index')
            ->with('success', 'Hotel created successfully!');
    }

    public function edit(Hotel $hotel)
    {
        if ($this->isAgent() && $hotel->agent_id !== auth()->id()) {
            abort(403);
        }

        $locations      = Location::where('status', '1')->get();
        $amenities      = AllAmenity::all();
        $hotel->load(['images', 'amenities', 'policies']);
        $hotelRoomTypes = $hotel->roomTypes()->with('images')->get();
        $hotelRooms     = $hotel->rooms()->with('roomType')->get();

        if ($this->isAgent()) {
            return view('agent.hotels.edit', compact('hotel', 'locations', 'amenities', 'hotelRoomTypes', 'hotelRooms'));
        }

        return view('admin.hotels.edit', compact('hotel', 'locations', 'amenities', 'hotelRoomTypes', 'hotelRooms'));
    }

    public function update(Request $request, Hotel $hotel)
    {
        if ($this->isAgent() && $hotel->agent_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'location_id'    => 'required|integer|exists:locations,id',
            'type'           => 'required|string|in:hotel,guest house,resort',
            'description'    => 'nullable|string',
            'address'        => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'whatsapp'       => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:100',
            'check_in_time'  => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'total_rooms'    => 'nullable|integer|min:0',
            'stars'          => 'nullable|integer|min:1|max:5',
            'total_rating'   => 'nullable|integer|min:0',
            'status'         => 'required|in:0,1',
            'amenities'      => 'nullable|array',
            'amenities.*'    => 'integer|exists:all_amenities,id',
            'images.*'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_types.*'  => 'nullable|string',
        ]);

        // Re-submit for approval when agent edits
        if ($this->isAgent()) {
            $validated['approval_status'] = 'pending';
            $validated['status']          = '0';
        }

        $hotel->update($validated);

        if ($request->has('amenities')) {
            $hotel->amenities()->sync($request->amenities);
        } else {
            $hotel->amenities()->detach();
        }

        if ($request->hasFile('images')) {
            $uploadPath      = public_path('assets/images/hotel');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            $currentMaxOrder = $hotel->images()->max('sort_order') ?? 0;
            foreach ($request->file('images') as $index => $image) {
                $fileName  = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move($uploadPath, $fileName);
                $imageType = $request->image_types[$index] ?? 'general';
                HotelImage::create([
                    'hotel_id'   => $hotel->id,
                    'image_path' => 'hotel/' . $fileName,
                    'image_type' => $imageType,
                    'sort_order' => $currentMaxOrder + $index + 1,
                ]);
            }
        }

        if ($this->isAgent()) {
            return redirect()->route('agent.hotels.index')
                ->with('success', 'Hotel updated and resubmitted for approval!');
        }

        return redirect()->route('admin.hotels.index')
            ->with('success', 'Hotel updated successfully!');
    }

    public function destroy(Hotel $hotel)
    {
        if ($this->isAgent() && $hotel->agent_id !== auth()->id()) {
            abort(403);
        }

        foreach ($hotel->images as $image) {
            $filePath = public_path('assets/images/' . $image->image_path);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $image->delete();
        }

        $hotel->amenities()->detach();
        foreach ($hotel->roomTypes as $roomType) {
            $roomType->amenities()->detach();
            $roomType->rooms()->delete();
            $roomType->delete();
        }
        $hotel->rooms()->delete();
        $hotel->policies()->delete();
        $hotel->delete();

        if ($this->isAgent()) {
            return redirect()->route('agent.hotels.index')
                ->with('success', 'Hotel deleted successfully!');
        }

        return redirect()->route('admin.hotels.index')
            ->with('success', 'Hotel deleted successfully!');
    }

    public function approve(Hotel $hotel)
    {
        $hotel->update([
            'approval_status' => 'approved',
            'status'          => 1,
        ]);

        $this->sendHotelApprovalEmail($hotel);

        return redirect()->back()->with('success', 'Hotel approved and made active!');
    }

    public function reject(Request $request, Hotel $hotel)
    {
        $hotel->update([
            'approval_status' => 'rejected',
            'status'          => 0,
        ]);

        $this->sendHotelRejectionEmail($hotel, $request->input('reason', ''));

        return redirect()->back()->with('success', 'Hotel submission rejected.');
    }

    private function sendHotelApprovalEmail(Hotel $hotel): void
    {
        if (!$hotel->agent_id) return;

        try {
            $agent        = User::find($hotel->agent_id);
            if (!$agent) return;

            $senderEmail  = getSetting('sender_email', 'email', 'contact@travelbookingpanel.com');
            $senderName   = getSetting('sender_name',  'email', 'Travel Booking Panel');
            $businessName = getSetting('business_name', 'main', 'Travel Booking Panel');
            $loginUrl     = url('/login');

            $html = "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;background:#fff;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;'>
                <div style='background:#0077BE;padding:24px 32px;text-align:center;'>
                    <h1 style='color:#fff;margin:0;font-size:22px;'>Hotel Approved</h1>
                </div>
                <div style='padding:32px;'>
                    <p style='font-size:15px;color:#333;'>Dear <strong>{$agent->full_name}</strong>,</p>
                    <p style='font-size:15px;color:#333;'>We are pleased to inform you that your hotel submission <strong style='color:#0077BE;'>{$hotel->name}</strong> has been <strong style='color:#28a745;'>approved</strong> and is now live on <strong>{$businessName}</strong>.</p>
                    <div style='text-align:center;margin:32px 0;'>
                        <a href='{$loginUrl}' style='background:#0077BE;color:#fff;padding:12px 32px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:15px;'>Login to Agent Portal</a>
                    </div>
                    <p style='font-size:13px;color:#777;'>If you have any questions, please contact our support team.</p>
                </div>
                <div style='background:#f5f5f5;padding:16px 32px;text-align:center;'>
                    <p style='font-size:12px;color:#999;margin:0;'>© " . date('Y') . " {$businessName}. All rights reserved.</p>
                </div>
            </div>";

            $apiKey = Setting::getValue('resend_api_key', 'email');
            if ($apiKey) {
                config(['services.resend.key' => $apiKey]);
            }

            Resend::emails()->send([
                'from'    => "{$senderName} <{$senderEmail}>",
                'to'      => [$agent->email],
                'subject' => "Your Hotel Has Been Approved – {$businessName}",
                'html'    => $html,
            ]);
        } catch (\Exception $e) {
            Log::error('Hotel approval email failed: ' . $e->getMessage());
        }
    }

    private function sendHotelRejectionEmail(Hotel $hotel, string $reason): void
    {
        if (!$hotel->agent_id) return;

        try {
            $agent        = User::find($hotel->agent_id);
            if (!$agent) return;

            $senderEmail  = getSetting('sender_email', 'email', 'contact@travelbookingpanel.com');
            $senderName   = getSetting('sender_name',  'email', 'Travel Booking Panel');
            $businessName = getSetting('business_name', 'main', 'Travel Booking Panel');
            $contactEmail = getSetting('contact_email', 'contact', 'support@travelbookingpanel.com');

            $reasonBlock = $reason
                ? "<div style='background:#fff3f3;border-left:4px solid #dc3545;padding:16px 20px;border-radius:4px;margin:20px 0;'>
                       <p style='font-size:14px;color:#333;margin:0;'><strong>Reason:</strong></p>
                       <p style='font-size:14px;color:#555;margin:8px 0 0;'>{$reason}</p>
                   </div>"
                : '';

            $html = "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;background:#fff;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;'>
                <div style='background:#dc3545;padding:24px 32px;text-align:center;'>
                    <h1 style='color:#fff;margin:0;font-size:22px;'>Hotel Submission Rejected</h1>
                </div>
                <div style='padding:32px;'>
                    <p style='font-size:15px;color:#333;'>Dear <strong>{$agent->full_name}</strong>,</p>
                    <p style='font-size:15px;color:#333;'>Unfortunately, your hotel submission <strong>{$hotel->name}</strong> on <strong>{$businessName}</strong> has been <strong style='color:#dc3545;'>rejected</strong>.</p>
                    {$reasonBlock}
                    <p style='font-size:15px;color:#333;'>If you have any questions, please contact us at <a href='mailto:{$contactEmail}' style='color:#0077BE;'>{$contactEmail}</a>.</p>
                </div>
                <div style='background:#f5f5f5;padding:16px 32px;text-align:center;'>
                    <p style='font-size:12px;color:#999;margin:0;'>© " . date('Y') . " {$businessName}. All rights reserved.</p>
                </div>
            </div>";

            $apiKey = Setting::getValue('resend_api_key', 'email');
            if ($apiKey) {
                config(['services.resend.key' => $apiKey]);
            }

            Resend::emails()->send([
                'from'    => "{$senderName} <{$senderEmail}>",
                'to'      => [$agent->email],
                'subject' => "Your Hotel Submission Update – {$businessName}",
                'html'    => $html,
            ]);
        } catch (\Exception $e) {
            Log::error('Hotel rejection email failed: ' . $e->getMessage());
        }
    }

    public function toggleFeatured(Hotel $hotel)
    {
        $hotel->update(['featured' => $hotel->featured == '1' ? '0' : '1']);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Featured status updated successfully!']);
        }

        return redirect()->route('admin.hotels.index')
            ->with('success', 'Featured status updated successfully!');
    }

    public function toggleStatus(Hotel $hotel)
    {
        $hotel->status = !$hotel->status;
        $hotel->save();

        return response()->json([
            'success' => true,
            'status'  => $hotel->status,
            'message' => 'Status updated successfully!',
        ]);
    }

    public function deleteImage($imageId)
    {
        $image    = HotelImage::findOrFail($imageId);
        $filePath = public_path('assets/images/' . $image->image_path);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully!',
        ]);
    }

    public function reorderImages(Request $request)
    {
        $request->validate([
            'images'              => 'required|array',
            'images.*.id'         => 'required|integer|exists:hotel_images,id',
            'images.*.sort_order' => 'required|integer',
        ]);

        foreach ($request->images as $item) {
            HotelImage::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Image order updated successfully!',
        ]);
    }
}
