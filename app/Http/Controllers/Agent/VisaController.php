<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\VisaRequest;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisaController extends Controller
{
    public function index(Request $request)
    {
        $agent = auth()->user();

        $query = VisaRequest::where('agent_id', $agent->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                  ->orWhere('surname', 'like', "%$search%")
                  ->orWhere('passport_no', 'like', "%$search%");
            });
        }

        $visaRequests = $query->latest()->paginate(15);

        return view('agent.visa.index', compact('agent', 'visaRequests'));
    }

    public function form()
    {
        $countries = Location::select('country', 'country_code')
            ->distinct()
            ->orderBy('country')
            ->get();

        return view('agent.visa.apply', compact('countries'));
    }

    public function submit(Request $request)
    {
        $validatedData = $request->validate([
            'visa_type'            => 'required|string|max:255',
            'visa_plan'            => 'required|string|max:255',
            'first_name'           => 'required|string|max:255',
            'middle_name'          => 'nullable|string|max:255',
            'surname'              => 'required|string|max:255',
            'father_name'          => 'required|string|max:255',
            'mother_name'          => 'required|string|max:255',
            'place_birth'          => 'required|string|max:255',
            'occupation'           => 'required|string|max:255',
            'marital_status'       => 'required|string',
            'religion'             => 'required|string|max:255',
            'nationality'          => 'required|string',
            'passport_no'          => 'required|string|max:255',
            'passport_issue_date'  => 'required|date',
            'passport_expiry_date' => 'required|date|after:passport_issue_date',
            'gender'               => 'required|in:male,female',
            'passport_front'       => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'passport_back'        => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'passport_photo'       => 'required|file|mimes:jpg,jpeg,png|max:5120',
            'other_document'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'agreed_terms'         => 'required',
        ]);

        if (isset($validatedData['agreed_terms'])) {
            $validatedData['agreed_terms'] = $validatedData['agreed_terms'] === 'on' ? 1 : 0;
        }

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

        $agent = auth()->user();
        $visaRequest = VisaRequest::create($validatedData);

        DB::table('visa_requests')
            ->where('id', $visaRequest->id)
            ->update(['agent_id' => $agent->id, 'booked_via' => 'agent']);

        return redirect()->route('agent.visa.status', $visaRequest->id)
            ->with('success', 'Visa application submitted successfully!');
    }

    public function status(int $id)
    {
        $agent = auth()->user();

        $visaRequest = VisaRequest::where('id', $id)
            ->where('agent_id', $agent->id)
            ->firstOrFail();

        return view('agent.visa.status', compact('visaRequest'));
    }
}
