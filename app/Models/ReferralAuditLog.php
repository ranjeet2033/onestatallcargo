<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'target_type',
        'target_id',
        'details',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an audit log entry.
     */
    public static function log(string $action, $target = null, ?array $details = null, ?int $userId = null): self
    {
        $targetType = null;
        $targetId = null;

        if ($target instanceof Model) {
            $targetType = class_basename($target);
            $targetId = $target->getKey();
        } elseif (is_string($target)) {
            $targetType = $target;
        }

        return self::create([
            'user_id'     => $userId ?: auth()->id(),
            'action'      => $action,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'details'     => $details,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);
    }
}
