<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $countries = Country::orderBy('name')->get();
        return view('user.profile.index', compact('user', 'countries'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'title'      => 'nullable|string|max:10',
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'phone'      => 'nullable|string|max:30',
            'address'    => 'nullable|string|max:255',
            'city'       => 'nullable|string|max:100',
            'country'    => 'nullable|string|max:100',
            'state'      => 'nullable|string|max:100',
            'zip_code'   => 'nullable|string|max:20',
        ]);

        $user->update($request->only(['title', 'first_name', 'last_name', 'phone', 'address', 'city', 'country', 'state', 'zip_code']));

        return back()->with('success', 'Profile updated successfully.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password changed successfully.');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = auth()->user();

        $file     = $request->file('avatar');
        $filename = time() . '_' . uniqid() . '_avatar.' . $file->getClientOriginalExtension();
        $dest     = public_path('assets/images/avatars');

        if (!file_exists($dest)) {
            mkdir($dest, 0775, true);
        }

        if ($user->profile_image && file_exists(public_path('assets/images/avatars/' . $user->profile_image))) {
            unlink(public_path('assets/images/avatars/' . $user->profile_image));
        }

        $file->move($dest, $filename);
        $user->update(['profile_image' => $filename]);

        return back()->with('success', 'Profile photo updated successfully.');
    }
}
