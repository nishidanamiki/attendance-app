<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequestBreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'stamp_correction_request_id',
        'break_time_id',
        'break_in_at',
        'break_out_at',
    ];

    public function stampCorrectionRequest()
    {
        return $this->belongsTo(StampCorrectionRequest::class);
    }
}
