<?php

namespace App\Models;

use App\Http\ColumnTypes\CalcTime;
use App\Http\ColumnTypes\CustomField;
use App\Http\ColumnTypes\DropDown;
use App\Http\ColumnTypes\Number;
use App\Http\ColumnTypes\Text;
use App\Http\Contracts\ClearRelations;
use App\Http\Traits\FilterRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Column extends Model implements ClearRelations
{
    use HasFactory, SoftDeletes, FilterRecords;

    protected $primaryKey = 'column_id';
    protected $fillable = ['title', 'name', 'card_type_ref_id', 'type', 'default', 'enum_values',
        'nullable', 'length', 'params', 'level_type', 'level_id', 'show'
    ];
    public $filters = ['title', 'name', 'card_type_ref_id', 'type', 'nullable'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts = [
        'nullable' => 'boolean', 'show' => 'boolean',
        'enum_values' => 'array', 'params' => 'array'
    ];

    public static array $columnTypes = [
        'text' => Text::class,
        'dropdown' => DropDown::class,
        'calc-time' => CalcTime::class,
        'number' => Number::class
    ];

    public function cardType(): BelongsTo
    {
        return $this->belongsTo(CardType::class, 'card_type_ref_id')->select(['card_type_id', 'name', 'description']);
    }

    public static function getCardTypeColumns(int $cardTypeId, int $teamId)
    {
        $team = Team::query()->find($teamId);
        $columns = CardType::query()->find($cardTypeId)->columns()
            ->where(function ($query) use ($team) {
                $query->whereNull('level_type')
                    ->orWhere(function (Builder $builder) use ($team) {
                        $builder->where([
                            'level_type' => 'project',
                            'level_id' => $team->project->project_id
                        ]);
                    })->orWhere(function (Builder $builder) use ($team) {
                        $builder->where([
                            'level_type' => 'company',
                            'level_id' => $team->project->company->company_id
                        ]);
                    })->orWhere(function (Builder $builder) use ($team) {
                        $builder->where([
                            'level_type' => 'team',
                            'level_id' => $team->team_id
                        ]);
                    });
            })
            ->get();
        return $columns;
    }

    public static function prepareColumns(iterable &$items)
    {
        foreach ($items as &$item) {
            $cardTypes = $item->cardTypes;
            foreach ($item->projects as &$project) {
                $projectCards = $cardTypes->map(fn($card, $key) => static::filterColumns($card->replicate(), $project));
                $project->cardTypes = $projectCards;
                foreach ($project->teams as $team) {
                    $teamCards = $cardTypes->map(fn($card, $key) => static::filterColumns($card->replicate(), $team));
                    $team->cardTypes = $teamCards;
                    $team->unsetRelation('project');
                }
                $project->unsetRelation('company');
            }
        }
    }

    public static function filterColumns(CardType $cardType, Model $model): CardType
    {
        $columns = $cardType->columns;
        $filteredCols = new Collection();
        foreach ($columns as $column) {
            $teams = Team::getHierarchyItems($model)->pluck('team_id')->toArray();
            $projects = Project::getHierarchyItems($model)->pluck('project_id')->toArray();
            $companies = Company::getHierarchyItems($model)->pluck('company_id')->toArray();
            if ($column->level_type === 'team' && !in_array($column->level_id, $teams)) continue;
            if ($column->level_type === 'project' && !in_array($column->level_id, $projects)) continue;
            if ($column->level_type === 'company' && !in_array($column->level_id, $companies)) continue;
            $filteredCols->push($column);
        }
        $cardType->setRelation('columns', $filteredCols);
        return $cardType;
    }

    public static function getFieldType(int $columnId): ?CustomField
    {
        $column = Column::find($columnId);
        if (empty($column)) return null;
        return new self::$columnTypes[$column->type]($column);
    }

    public function deleteRelations()
    {
        TaskMeta::query()->where('column_ref_id',$this->column_id)->delete();
    }
}
