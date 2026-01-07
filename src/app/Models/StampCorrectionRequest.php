<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'attendance_id',
        'clock_in_at', 'clock_out_at',
        'break1_start_at', 'break1_end_at',
        'break2_start_at', 'break2_end_at',
        'remarks', 'status',
    ];

    public function breakRequests()
    {
        return $this->hasmany(StampCorrectionRequest::class, 'stamp_correction_request_id');
    }
}
