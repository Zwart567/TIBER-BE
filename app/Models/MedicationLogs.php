<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicationLogs extends Model
{
    use HasFactory;
    protected $table = 'medication_log';
    // public $timestamps = false;
    
    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'user_id');
    }

    protected $fillable = [
        'id',
        'user_id',
        'log_date',
        'logged_time',
    ];
}
