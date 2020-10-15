<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TokenizationStatus extends Model
{
    protected $table = 'tokenization_statuses';

    public $timestamps = false;

    protected $fillable = ['id', 'name'];

    const STATUS_PENDING = 1;
    const STATUS_PARTIAL_ACCEPTANCE = 2;
    const STATUS_APPROVED = 3;
    const STATUS_REJECTED = 4;
}
