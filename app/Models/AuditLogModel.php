<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table      = 'audit_log';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = [
        'user_id', 'user_name', 'user_role', 'action',
        'module', 'record_id', 'description',
        'old_values', 'new_values', 'ip_address', 'user_agent',
    ];
    protected $useTimestamps  = false;
    protected $createdField   = 'created_at';
    protected $dateFormat     = 'datetime';

    /**
     * Log an action
     */
    public function logAction(
        string $action,
        string $module,
        ?int   $recordId    = null,
        string $description = '',
        array  $oldValues   = [],
        array  $newValues   = []
    ): bool {
        $session = session();
        return (bool) $this->insert([
            'user_id'     => $session->get('user_id'),
            'user_name'   => $session->get('user_name') ?? 'System',
            'user_role'   => $session->get('user_role') ?? 'system',
            'action'      => $action,
            'module'      => $module,
            'record_id'   => $recordId,
            'description' => $description,
            'old_values'  => !empty($oldValues) ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
            'new_values'  => !empty($newValues) ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
            'ip_address'  => service('request')->getIPAddress(),
            'user_agent'  => substr(service('request')->getUserAgent()->getAgentString(), 0, 500),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get paginated logs with optional filters
     */
    public function getFiltered(
        ?string $module    = null,
        ?string $action    = null,
        ?int    $userId    = null,
        ?string $startDate = null,
        ?string $endDate   = null,
        int     $perPage   = 25
    ) {
        $builder = $this->orderBy('created_at', 'DESC');

        if ($module)    $builder->where('module', $module);
        if ($action)    $builder->where('action', $action);
        if ($userId)    $builder->where('user_id', $userId);
        if ($startDate) $builder->where('DATE(created_at) >=', $startDate);
        if ($endDate)   $builder->where('DATE(created_at) <=', $endDate);

        return $builder->paginate($perPage);
    }
}
