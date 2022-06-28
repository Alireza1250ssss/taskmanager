<?php

namespace App\Models;

use App\Http\Traits\FilterRecords;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes, FilterRecords;

    protected $primaryKey = 'project_id';
    protected $fillable = ['name','company_ref_id'];
    public array $filters = ['name','company_ref_id'];

    /**
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_ref_id');
    }

    /**
     * @return HasMany
     */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class, 'project_ref_id');
    }
}
