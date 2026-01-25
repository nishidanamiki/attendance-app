<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StampCorrectionRequest;
use App\Models\BreakTime;

class StampCorrectionRequestBreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'stamp_correction_request_id',
        'break_time_id',
        'break_in_at',
        'break_out_at',
    ];

    public function request()
    {
        return $this->belongsTo(StampCorrectionRequest::class, 'stamp_correction_request_id');
    }

    public function originalBreak()
    {
        return $this->belongsTo(BreakTime::class, 'break_time_id');
    }
}
