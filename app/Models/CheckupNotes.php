<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckupNotes extends Model
{
    use HasFactory;
    protected $table = 'checkup_note';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    protected $fillable = [
        'id',
        'user_id',
        'checkup_control',
        'status',
        'color_status',
        'notes',

    ];
}
