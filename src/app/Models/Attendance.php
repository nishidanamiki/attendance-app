<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'work_date', 'clock_in_at', 'clock_out_at'
    ];

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }
}
