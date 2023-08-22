<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'email', 'gender', 'ip_address', 'username', 'password'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
