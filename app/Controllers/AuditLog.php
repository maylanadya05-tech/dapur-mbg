<?php

namespace App\Controllers;

use App\Models\AuditLogModel;

class AuditLog extends BaseController
{
    protected AuditLogModel $auditModel;

    public function __construct()
    {
        $this->auditModel = new AuditLogModel();
    }

    /**
     * Display audit log with filters
     */
    public function index(): string
    {
        $module    = $this->request->getGet('module');
        $action    = $this->request->getGet('action');
        $userId    = $this->request->getGet('user_id') ? (int) $this->request->getGet('user_id') : null;
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-d', strtotime('-7 days'));
        $endDate   = $this->request->getGet('end_date')   ?: date('Y-m-d');

        $db = \Config\Database::connect();

        // Apply filters manually for pagination
        $builder = $db->table('audit_log')->orderBy('created_at', 'DESC');
        if ($module)    $builder->where('module', $module);
        if ($action)    $builder->where('action', $action);
        if ($userId)    $builder->where('user_id', $userId);
        if ($startDate) $builder->where('DATE(created_at) >=', $startDate);
        if ($endDate)   $builder->where('DATE(created_at) <=', $endDate);

        $totalRows = $builder->countAllResults(false);
        $perPage   = 30;
        $page      = (int)($this->request->getGet('page') ?: 1);
        $offset    = ($page - 1) * $perPage;

        $logs = $builder->limit($perPage, $offset)->get()->getResultArray();

        // Distinct modules and users for filter dropdowns
        $modules  = $db->table('audit_log')->select('module')->distinct()->orderBy('module')->get()->getResultArray();
        $actions  = ['CREATE', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT', 'APPROVE', 'REJECT', 'EXPORT'];
        $users    = $db->table('audit_log')->select('user_id, user_name')->distinct()->orderBy('user_name')->get()->getResultArray();

        return view('audit/index', [
            'title'      => 'Audit Log Aktivitas – Dapur MBG',
            'logs'       => $logs,
            'modules'    => array_column($modules, 'module'),
            'actions'    => $actions,
            'users'      => $users,
            'totalRows'  => $totalRows,
            'perPage'    => $perPage,
            'page'       => $page,
            'totalPages' => ceil($totalRows / $perPage),
            'filters'    => compact('module', 'action', 'userId', 'startDate', 'endDate'),
        ]);
    }
}
