<?php

namespace App\Models;

use App\Http\Requests\StoreColumnRequest;
use App\Http\Traits\FilterRecords;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Column extends Model
{
    use HasFactory,SoftDeletes,FilterRecords;

    protected $primaryKey = 'column_id';
    protected $fillable = ['title','name','personal_ref_id','type','default','enum_values','nullable','length','params','level_type','level_id'];
    public $filters = ['title','name','personal_ref_id','type','nullable'];
    protected $casts =[
      'nullable' => 'boolean',
      'enum_values' => 'array' , 'params' => 'array'
    ];

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class,'personal_ref_id');
    }

    protected function type(): Attribute
    {
        return Attribute::set(fn($value) => StoreColumnRequest::$types[$value]);
    }
}
