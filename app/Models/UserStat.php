<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserStat extends Model
{
    use HasFactory;
    protected $table = 'users_stats';
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'user_id');

        
    }

    protected $fillable = [
        'id',
        'user_id',
        'highest_streak',
        'last_taken_date',
        'current_streak'
    ];
}
