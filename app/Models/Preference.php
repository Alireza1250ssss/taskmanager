<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Preference extends Model
{
    use HasFactory;
    protected $primaryKey = 'preference_id';
    protected $fillable = ['user_ref_id', 'column_preference'];
    protected $casts = [
      'column_preference' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_ref_id');
    }
}
