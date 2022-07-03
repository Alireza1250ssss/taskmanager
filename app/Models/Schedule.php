<?php

namespace App\Models;

use App\Http\Traits\FilterRecords;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use HasFactory,FilterRecords,SoftDeletes;

    protected $fillable = ['time_to','time_from','day','type','user_ref_id'];
    protected $primaryKey = 'schedule_id';
    public array $filters = ['time_to','time_from','day','type','user_ref_id'];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class ,'user_ref_id');
    }

    /**
     * @return BelongsTo
     */
    public function leave(): BelongsTo
    {
        return $this->belongsTo(Leave::class ,'leave_ref_id');
    }
}
