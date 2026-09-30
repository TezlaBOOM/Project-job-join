<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Log an action to the audit_logs table.
     */
    public static function log(
        string $action,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null
    ): AuditLog {
        $filteredOld = $oldValues ? self::sanitizeSensitive($oldValues) : null;
        $filteredNew = $newValues ? self::sanitizeSensitive($newValues) : null;

        return AuditLog::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $filteredOld,
            'new_values' => $filteredNew,
            'ip' => Request::ip() ?? '127.0.0.1',
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }

    protected static function sanitizeSensitive(array $data): array
    {
        $sensitiveKeys = ['password', 'password_confirmation', 'two_factor_code', 'code_hash', 'remember_token', 'cancel_token_hash'];

        foreach ($sensitiveKeys as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = '***REDACTED***';
            }
        }

        return $data;
    }
}
