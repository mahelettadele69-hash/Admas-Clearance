<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLog extends Model
{
    protected $fillable = ['user_id', 'action', 'details', 'ip_address'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, ?string $details = null): void
    {
        self::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'details' => $details,
            'ip_address' => request()->ip(),
        ]);
    }
}