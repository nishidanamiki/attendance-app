<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequestBreakTime;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'attendance_id', 'work_date',
        'clock_in_at', 'clock_out_at',
        'remarks', 'status',
        'approve_by', 'approved_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function breakRequests()
    {
        return $this->hasMany(StampCorrectionRequestBreakTime::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
