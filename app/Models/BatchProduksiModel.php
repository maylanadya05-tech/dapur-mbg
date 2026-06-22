<?php

namespace App\Models;

use CodeIgniter\Model;

class BatchProduksiModel extends Model
{
    protected $table            = 'batch_produksi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'nomor_batch',
        'tanggal_produksi',
        'resep_id',
        'target_porsi',
        'porsi_selesai',
        'status',
        'tim_produksi',
        'mulai_masak',
        'selesai_masak',
        'catatan',
        'dibuat_oleh',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'tanggal_produksi' => 'required|valid_date',
        'resep_id'         => 'required|integer',
        'target_porsi'     => 'required|integer|greater_than[0]',
        'dibuat_oleh'      => 'required|integer',
    ];

    protected $skipValidation = false;

    /**
     * Get today's batches with resep info
     */
    public function getHariIni(): array
    {
        $today = date('Y-m-d');

        return $this->db->table('batch_produksi bp')
            ->select('bp.*, r.nama_menu, r.kategori AS kategori_menu, u.name AS dibuat_oleh_name')
            ->join('resep r', 'r.id = bp.resep_id', 'left')
            ->join('users u', 'u.id = bp.dibuat_oleh', 'left')
            ->where('bp.tanggal_produksi', $today)
            ->orderBy('bp.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get all batches with resep and user info
     */
    public function getWithRelations(): array
    {
        return $this->db->table('batch_produksi bp')
            ->select('bp.*, r.nama_menu, r.kategori AS kategori_menu, u.name AS dibuat_oleh_name')
            ->join('resep r', 'r.id = bp.resep_id', 'left')
            ->join('users u', 'u.id = bp.dibuat_oleh', 'left')
            ->orderBy('bp.tanggal_produksi', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get tren produksi last 7 days
     */
    public function getTrenProduksi(int $days = 7): array
    {
        $from = date('Y-m-d', strtotime("-{$days} days"));

        return $this->db->table('batch_produksi')
            ->select('tanggal_produksi, SUM(porsi_selesai) AS total_porsi, COUNT(id) AS total_batch')
            ->where('tanggal_produksi >=', $from)
            ->groupBy('tanggal_produksi')
            ->orderBy('tanggal_produksi', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Generate batch number in format BATCH-YYYYMMDD-NNN
     */
    public function generateNomorBatch(): string
    {
        $date  = date('Ymd');
        $today = date('Y-m-d');
        $count = $this->where('tanggal_produksi', $today)->countAllResults();

        return 'BATCH-' . $date . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Count today's active batches (not cancelled)
     */
    public function countHariIniAktif(): int
    {
        return $this->where('tanggal_produksi', date('Y-m-d'))
                    ->whereNotIn('status', ['dibatalkan'])
                    ->countAllResults();
    }
}
