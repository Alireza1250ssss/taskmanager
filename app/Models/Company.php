<?php

namespace App\Models;

use App\Http\Traits\FilterRecords;
use App\Http\Traits\MainPropertyGetter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory,SoftDeletes,FilterRecords,MainPropertyGetter;

    protected $fillable = ['name'];
    protected $primaryKey = 'company_id';
    public array $filters = ['name'];

    /**
     * @return HasMany
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class,'company_ref_id');
    }
}
