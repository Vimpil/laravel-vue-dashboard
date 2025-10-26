<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FraudLog extends Model
{
    protected $fillable = ['ip', 'user_id', 'reason', 'payload'];
}
