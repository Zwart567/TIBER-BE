<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personalization extends Model
{
    use HasFactory;
    protected $table = 'personalizations';
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'user_id');
    }

    protected $fillable = [
        'id',
        'user_id',
        'start_date',
        'duration_month',
        'control_freq_value',
        'control_freq_unit',
        'last_checkup_date',
        'next_checkup_date',
        'reminder_time',
        'time_category',
        'created_at',
        'updated_at',
    ];
}
