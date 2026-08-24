<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelPartnerImport extends Model
{
    protected $fillable = [
        'travel_partner_id',
        'import_type',
        'file_format',
        'original_filename',
        'stored_path',
        'status',
        'records_count',
        'preview_data',
        'error_message',
        'created_by',
    ];

    protected $casts = [
        'preview_data' => 'array',
        'records_count' => 'integer',
    ];

    public function partner()
    {
        return $this->belongsTo(TravelPartner::class, 'travel_partner_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
