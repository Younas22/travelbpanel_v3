<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisaRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'visa_type',
        'visa_plan',
        'first_name',
        'middle_name',
        'surname',
        'father_name',
        'mother_name',
        'place_birth',
        'occupation',
        'marital_status',
        'religion',
        'nationality',
        'passport_no',
        'passport_issue_date',
        'passport_expiry_date',
        'gender',
        'passport_front',
        'passport_back',
        'passport_photo',
        'other_document',
        'agreed_terms',
        'agent_id',
        'user_id',
        'booked_via',
    ];

    protected $casts = [
        'passport_issue_date' => 'date',
        'passport_expiry_date' => 'date',
        'agreed_terms' => 'boolean',
    ];
}