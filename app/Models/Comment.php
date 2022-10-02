<?php

namespace App\Models;

use App\Http\Traits\FilterRecords;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory,FilterRecords,SoftDeletes;

    protected $primaryKey = 'comment_id' ;
    protected $fillable = ['content','parent_id','user_ref_id'];
    protected $filters = ['user_ref_id','parent_id'];

    /**
     * @return HasMany
     */
    public function replyComments(): HasMany
    {
        return $this->hasMany(Comment::class , 'parent_id');
    }

    /**
     * @return BelongsTo
     */
    public function parentComment(): BelongsTo
    {
        return $this->belongsTo(Comment::class , 'parent_id');
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class , 'user_ref_id')->select(['user_id','first_name','last_name','email']);
    }

    /**
     * Get the parent commentable model (task or request or ...).
     * @return MorphTo
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }
}
