<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $agent = auth()->user();
        $countries = Country::orderBy('name')->get();
        return view('agent.profile.index', compact('agent', 'countries'));
    }

    public function update(Request $request)
    {
        $agent = auth()->user();

        $request->validate([
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'phone'           => 'nullable|string|max:30',
            'country'         => 'nullable|string|max:100',
            'company_name'    => 'nullable|string|max:255',
            'company_phone'   => 'nullable|string|max:30',
            'company_address' => 'nullable|string',
        ]);

        $agent->update($request->only([
            'first_name', 'last_name', 'phone', 'country',
            'company_name', 'company_phone', 'company_address',
        ]));

        return back()->with('success', 'Profile updated.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        $agent = auth()->user();

        if (!Hash::check($request->current_password, $agent->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $agent->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password changed successfully.');
    }

    public function uploadLogo(Request $request)
    {
        $request->validate(['logo' => 'required|image|max:2048']);

        $agent     = auth()->user();
        $file      = $request->file('logo');
        $filename  = time() . '_' . uniqid() . '_company_logo.' . $file->getClientOriginalExtension();
        $dest      = public_path('assets/images/settings/branding');
        $file->move($dest, $filename);

        $agent->update(['company_logo' => $filename]);

        return back()->with('success', 'Company logo updated.');
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate(['photo' => 'required|image|max:2048']);

        $agent    = auth()->user();
        $file     = $request->file('photo');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $dest     = public_path('assets/images/agents');

        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        if ($agent->profile_image && file_exists(public_path('assets/images/agents/' . $agent->profile_image))) {
            unlink(public_path('assets/images/agents/' . $agent->profile_image));
        }

        $file->move($dest, $filename);
        $agent->update(['profile_image' => $filename]);

        return back()->with('success', 'Profile photo updated.');
    }
}
