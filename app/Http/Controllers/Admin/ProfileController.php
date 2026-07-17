<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display the admin profile page
     */
    public function index()
    {
        $user = Auth::user();
        return view('admin.profile.index', compact('user'));
    }

    /**
     * Update admin profile information
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other', 'prefer-not-to-say'])],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'employee_id' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:100'],
        ]);

        $user->update($validated);

        return redirect()->route('admin.profile.index')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Update profile picture
     */
public function updateProfilePicture(Request $request)
{
    $user = Auth::user();

    $request->validate([
        'profile_image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
    ]);

    // Public folder path
    $uploadPath = public_path('profile_images');

    // Create folder if not exists
    if (!file_exists($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }

    // Delete old image
    if ($user->profile_image && file_exists(public_path($user->profile_image))) {
        unlink(public_path($user->profile_image));
    }

    // New file name
    $file = $request->file('profile_image');
    $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

    // Move to public folder
    $file->move($uploadPath, $fileName);

    // Save path in DB (relative path)
    $user->update([
        'profile_image' => 'profile_images/' . $fileName
    ]);

    return redirect()->route('admin.profile.index')
        ->with('success', 'Profile picture updated successfully!');
}


    /**
     * Remove profile picture
     */
public function removeProfilePicture()
{
    $user = Auth::user();

    if ($user->profile_image && file_exists(public_path($user->profile_image))) {
        unlink(public_path($user->profile_image));
    }

    $user->update(['profile_image' => null]);

    return redirect()->route('admin.profile.index')
        ->with('success', 'Profile picture removed successfully!');
}


    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Check if current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.profile.index')
            ->with('success', 'Password updated successfully!');
    }

    /**
     * Update notification preferences
     */
    public function updateNotifications(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'email_notifications' => ['boolean'],
            'sms_notifications' => ['boolean'],
            'marketing_emails' => ['boolean'],
        ]);

        $user->update([
            'email_notifications' => $request->has('email_notifications'),
            'sms_notifications' => $request->has('sms_notifications'),
            'marketing_emails' => $request->has('marketing_emails'),
        ]);

        return redirect()->route('admin.profile.index')
            ->with('success', 'Notification preferences updated successfully!');
    }
}
