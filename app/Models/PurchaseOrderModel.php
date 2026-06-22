<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchaseOrderModel extends Model
{
    protected $table            = 'purchase_orders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'nomor_po',
        'supplier_id',
        'tanggal_po',
        'tanggal_dibutuhkan',
        'status',
        'total_nilai',
        'catatan',
        'alasan_tolak',
        'dibuat_oleh',
        'disetujui_oleh',
        'tanggal_disetujui',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'supplier_id' => 'required|integer',
        'tanggal_po'  => 'required|valid_date',
        'dibuat_oleh' => 'required|integer',
    ];

    protected $skipValidation = false;

    /**
     * Get all POs with supplier and user info
     */
    public function getWithRelations(): array
    {
        return $this->db->table('purchase_orders po')
            ->select('po.*, s.name AS supplier_name, s.contact_person, u.name AS dibuat_oleh_name, ua.name AS disetujui_oleh_name')
            ->join('supplier s', 's.id = po.supplier_id', 'left')
            ->join('users u', 'u.id = po.dibuat_oleh', 'left')
            ->join('users ua', 'ua.id = po.disetujui_oleh', 'left')
            ->orderBy('po.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get single PO with relations
     */
    public function getWithRelationsById(int $id): ?array
    {
        $result = $this->db->table('purchase_orders po')
            ->select('po.*, s.name AS supplier_name, s.contact_person, s.phone AS supplier_phone, s.address AS supplier_address, u.name AS dibuat_oleh_name, ua.name AS disetujui_oleh_name')
            ->join('supplier s', 's.id = po.supplier_id', 'left')
            ->join('users u', 'u.id = po.dibuat_oleh', 'left')
            ->join('users ua', 'ua.id = po.disetujui_oleh', 'left')
            ->where('po.id', $id)
            ->get()
            ->getResultArray();

        return $result[0] ?? null;
    }

    /**
     * Generate PO number in format PO-YYYY-NNN
     */
    public function generateNomorPO(): string
    {
        $year  = date('Y');
        $count = $this->where("YEAR(created_at) = {$year}")->countAllResults();

        return 'PO-' . $year . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Count pending POs (status = diajukan)
     */
    public function countPending(): int
    {
        return $this->where('status', 'diajukan')->countAllResults();
    }
}
