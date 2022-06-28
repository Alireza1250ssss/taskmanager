<?php

namespace App\Models;

use App\Http\Traits\FilterRecords;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use HasFactory,SoftDeletes,FilterRecords;

    protected $primaryKey = 'team_id';
    protected $fillable = ['name','project_ref_id'];
    public array $filters = ['name','project_ref_id'];

    /**
     * @return BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class,'project_ref_id');
    }
}
