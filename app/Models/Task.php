<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status' => 'pending',
        'assigned_to',
        'deadline',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
