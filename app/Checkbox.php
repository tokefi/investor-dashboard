<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Checkbox extends Model
{
    protected $table = 'checkboxes';

    protected $guarded = [];

    protected $fillable = ['checkbox_name'];

}
