<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table            = 'invoice';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'nomor_invoice',
        'tanggal',
        'jatuh_tempo',
        'periode_dari',
        'periode_sampai',
        'total_porsi',
        'harga_per_porsi',
        'total_nilai',
        'status',
        'catatan',
        'file_pdf',
        'dibuat_oleh',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'tanggal'        => 'required|valid_date',
        'jatuh_tempo'    => 'required|valid_date',
        'periode_dari'   => 'required|valid_date',
        'periode_sampai' => 'required|valid_date',
        'total_porsi'    => 'required|integer|greater_than_equal_to[0]',
        'harga_per_porsi' => 'required|decimal',
        'dibuat_oleh'    => 'required|integer',
    ];

    protected $skipValidation = false;

    /**
     * Get all invoices with user info
     */
    public function getWithRelations(): array
    {
        return $this->db->table('invoice inv')
            ->select('inv.*, u.name AS dibuat_oleh_name')
            ->join('users u', 'u.id = inv.dibuat_oleh', 'left')
            ->orderBy('inv.tanggal', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Generate invoice number in format INV-YYYY-NNN
     */
    public function generateNomorInvoice(): string
    {
        $year  = date('Y');
        $count = $this->where("YEAR(created_at) = {$year}")->countAllResults();

        return 'INV-' . $year . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Update overdue invoices status
     */
    public function updateJatuhTempo(): bool
    {
        return $this->db->table('invoice')
            ->where('jatuh_tempo <', date('Y-m-d'))
            ->whereIn('status', ['dikirim'])
            ->update(['status' => 'jatuh_tempo']);
    }
}
