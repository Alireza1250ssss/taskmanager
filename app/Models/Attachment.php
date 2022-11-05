<?php

namespace App\Models;

use App\Http\Contracts\ClearRelations;
use App\Http\Traits\FilterRecords;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Attachment extends Model implements ClearRelations
{
    use HasFactory, FilterRecords;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'path', 'size', 'extension', 'user_ref_id'
    ];
    public array $filters = ['extension', 'max_size', 'min_size'];
    protected $hidden = ['path'];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_ref_id')
            ->select(['user_id','username','first_name','last_name']);
    }

    /**
     * @return MorphTo
     */
    public function attachable(): MorphTo
    {
        return $this->morphTo('attachable');
    }

    public function deleteRelations()
    {
        File::delete(storage_path("app/" . $this->path));
    }

    protected static function boot()
    {
        parent::boot();

        $creationCallback = function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        };

        static::creating($creationCallback);
    }
}
