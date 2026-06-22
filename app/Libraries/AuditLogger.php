<?php

namespace App\Libraries;

use App\Models\AuditLogModel;

/**
 * AuditLogger — Central audit logging helper
 *
 * Usage:
 *   AuditLogger::log('CREATE', 'Pembelian', $poId, 'PO baru dibuat', [], $newData);
 *   AuditLogger::log('DELETE', 'Stok', $id, 'Stok dihapus', $oldData);
 */
class AuditLogger
{
    /**
     * Log an action to the audit_log table.
     *
     * @param string $action      CREATE|UPDATE|DELETE|LOGIN|LOGOUT|APPROVE|REJECT|EXPORT
     * @param string $module      Module/controller name (e.g., 'Pembelian', 'Stok')
     * @param int|null $recordId  Primary key of affected record
     * @param string $description Human-readable description
     * @param array $oldValues    Previous values (for UPDATE/DELETE)
     * @param array $newValues    New values (for CREATE/UPDATE)
     */
    public static function log(
        string $action,
        string $module,
        ?int   $recordId    = null,
        string $description = '',
        array  $oldValues   = [],
        array  $newValues   = []
    ): void {
        try {
            $model = new AuditLogModel();
            $model->logAction($action, $module, $recordId, $description, $oldValues, $newValues);
        } catch (\Throwable $e) {
            // Never let audit logging break the main application
            log_message('error', 'AuditLogger failed: ' . $e->getMessage());
        }
    }

    /**
     * Log login event
     */
    public static function login(string $userName, int $userId): void
    {
        self::log('LOGIN', 'Auth', $userId, "User '{$userName}' login.");
    }

    /**
     * Log logout event
     */
    public static function logout(string $userName, int $userId): void
    {
        self::log('LOGOUT', 'Auth', $userId, "User '{$userName}' logout.");
    }
}
