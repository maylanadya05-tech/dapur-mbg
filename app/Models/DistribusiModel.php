<?php

namespace App\Models;

use CodeIgniter\Model;

class DistribusiModel extends Model
{
    protected $table            = 'distribusi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'batch_id',
        'sekolah_id',
        'tanggal_distribusi',
        'jumlah_porsi',
        'status',
        'waktu_kirim',
        'waktu_terima',
        'pengirim',
        'penerima',
        'catatan',
        'armada_id',
        'qr_token',
        'foto_bukti',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'batch_id'           => 'required|integer',
        'sekolah_id'         => 'required|integer',
        'tanggal_distribusi' => 'required|valid_date',
        'jumlah_porsi'       => 'required|integer|greater_than[0]',
    ];

    protected $skipValidation = false;

    /**
     * Get all distribusi with batch and sekolah info
     */
    public function getWithRelations(): array
    {
        return $this->db->table('distribusi d')
            ->select('d.*, bp.nomor_batch, r.nama_menu, s.nama AS nama_sekolah, s.jenjang, s.kecamatan')
            ->join('batch_produksi bp', 'bp.id = d.batch_id', 'left')
            ->join('resep r', 'r.id = bp.resep_id', 'left')
            ->join('sekolah s', 's.id = d.sekolah_id', 'left')
            ->orderBy('d.tanggal_distribusi', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get today's distribution list
     */
    public function getDistribusiHariIni(): array
    {
        $today = date('Y-m-d');

        return $this->db->table('distribusi d')
            ->select('d.*, bp.nomor_batch, r.nama_menu, s.nama AS nama_sekolah, s.jenjang')
            ->join('batch_produksi bp', 'bp.id = d.batch_id', 'left')
            ->join('resep r', 'r.id = bp.resep_id', 'left')
            ->join('sekolah s', 's.id = d.sekolah_id', 'left')
            ->where('d.tanggal_distribusi', $today)
            ->orderBy('s.nama', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Count distinct schools served today
     */
    public function countSekolahHariIni(): int
    {
        return (int) $this->db->table('distribusi')
            ->where('tanggal_distribusi', date('Y-m-d'))
            ->countAllResults();
    }
}
