<?php

namespace App\Models;

use App\Http\Traits\FilterRecords;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Leave extends Model
{
    use HasFactory,FilterRecords,SoftDeletes;

    protected $primaryKey = 'leave_id';
    protected $fillable = ['user_ref_id','time_from','time_to','type','status','params','description'];
    protected $casts = [
      'params' => 'array'
    ];
    public array $filters = ['user_ref_id','time_from','time_to','type','status'];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class , 'user_ref_id');
    }

    /**
     * @return HasMany
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class , 'leave_ref_id');
    }

}
