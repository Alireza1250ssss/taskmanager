<?php

namespace App\Models;

use App\Http\Traits\FilterRecords;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Condition extends Model
{
    use HasFactory,FilterRecords;

    protected $fillable = ['key' , 'params','title'];

    public array $filters = ['title'];

}
