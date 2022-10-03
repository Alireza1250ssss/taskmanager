<?php

namespace App\Models;

use App\Http\Requests\StoreColumnRequest;
use App\Http\Traits\FilterRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Column extends Model
{
    use HasFactory,SoftDeletes,FilterRecords;

    protected $primaryKey = 'column_id';
    protected $fillable = ['title','name','card_type_ref_id','type','default','enum_values','nullable','length','params','level_type','level_id'];
    public $filters = ['title','name','card_type_ref_id','type','nullable'];
    protected $casts =[
      'nullable' => 'boolean',
      'enum_values' => 'array' , 'params' => 'array'
    ];

    public function cardType(): BelongsTo
    {
        return $this->belongsTo(CardType::class,'card_type_ref_id');
    }

    protected function type(): Attribute
    {
        return Attribute::set(fn($value) => StoreColumnRequest::$types[$value]);
    }


    public static function getCardTypeColumns(int $cardTypeId, int $teamId)
    {
        $team = Team::query()->find($teamId);
        $columns = CardType::query()->find($cardTypeId)->columns()
            ->where(function ($query)use($team){
                $query->whereNull('level_type')
                ->orWhere(function (Builder $builder) use($team){
                    $builder->where([
                        'level_type' => 'project',
                        'level_id' => $team->project->project_id
                    ]);
                })->orWhere(function (Builder $builder) use($team){
                    $builder->where([
                        'level_type' => 'company',
                        'level_id' => $team->project->company->company_id
                    ]);
                })->orWhere(function (Builder $builder) use($team){
                    $builder->where([
                        'level_type' => 'team',
                        'level_id' => $team->team_id
                    ]);
                });
            })
            ->get();
        return $columns;
    }
}
