<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    //
    protected $fillable = ['name', 'tokenable_type', 'tokenable_id', 'token'];
}
