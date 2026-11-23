<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'message', 'is_read'])]
class ClearanceNotification extends Model
{
    protected $casts = ['is_read' => 'boolean'];
}