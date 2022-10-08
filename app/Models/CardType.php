<?php

namespace App\Models;

use App\Http\Traits\FilterRecords;
use App\Http\Traits\MainPropertyGetter;
use App\Http\Traits\MainPropertySetter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CardType extends Model
{
    use HasFactory, SoftDeletes, FilterRecords, MainPropertySetter, MainPropertyGetter;

    protected $primaryKey = 'card_type_id';
    protected $fillable = ['name', 'company_ref_id', 'description', 'level_id', 'level_type'];
    public $filters = ['name', 'company_ref_id'];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'card_type_ref_id');
    }

    public function columns(): HasMany
    {
        return $this->hasMany(Column::class, 'card_type_ref_id');
    }

    public static function createDefaultCardTypeFor(Company $company): CardType
    {
        /** @var CardType $cardType */
        $cardType = static::query()->create([
            'name' => 'کارد پیشفرض',
            'description' => 'این کارد تایپ پیشفرض سیستم می باشد',
            'company_ref_id' => $company->company_id
        ]);
        return $cardType;
    }

    public function makeDefaultColumns()
    {
        $this->columns()->createMany([
            [
                'name' => 'name', 'title' => 'عنوان', 'type' => 'text', 'nullable' => false, 'show' => true
                , 'level_type' => 'company', 'level_id' => $this->company_ref_id
            ],
            [
                'name' => 'stage', 'title' => 'مرحله', 'type' => 'dropdown', 'nullable' => false, 'show' => true
                , 'level_type' => 'company', 'level_id' => $this->company_ref_id ,'default' => 'backlog' ,
                'enum_values' => ['backlog','todo','doing','done','review']
            ]
        ]);
    }
}
