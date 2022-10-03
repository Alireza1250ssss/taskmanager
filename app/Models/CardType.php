<?php

namespace App\Models;

use App\Http\Controllers\ResolvePermissionController;
use App\Http\Traits\FilterRecords;
use App\Http\Traits\MainPropertyGetter;
use App\Http\Traits\MainPropertySetter;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CardType extends Model
{
    use HasFactory,SoftDeletes,FilterRecords,MainPropertySetter,MainPropertyGetter;

    protected $primaryKey = 'card_type_id';
    protected $fillable = ['name','company_ref_id','description','level_id','level_type'];
    public $filters = ['name','company_ref_id'];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class,'card_type_ref_id');
    }

    public function columns(): HasMany
    {
        return $this->hasMany(Column::class , 'card_type_ref_id');
    }
}
