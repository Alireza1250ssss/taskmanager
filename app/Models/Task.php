<?php

namespace App\Models;

use App\Http\Traits\FilterRecords;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory,FilterRecords,SoftDeletes;

    protected $primaryKey = 'task_id';
    protected $fillable = ['title' , 'description' , 'user_ref_id','parent_id'];
    public array $filters = ['title','description','user_ref_id','parent_id'];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class ,'user_ref_id');
    }

    /**
     * @return BelongsToMany
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class,
            'task_team',
            'task_ref_id',
            'team_ref_id',
            'task_id'
        )  ;
    }
}
