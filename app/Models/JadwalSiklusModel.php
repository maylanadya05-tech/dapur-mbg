<?php

namespace App\Models;

use CodeIgniter\Model;

class JadwalSiklusModel extends Model
{
    protected $table            = 'jadwal_siklus';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'nama_siklus',
        'durasi_hari',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
        'created_by',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'nama_siklus'     => 'required|max_length[100]',
        'tanggal_mulai'   => 'required|valid_date',
        'tanggal_selesai' => 'required|valid_date',
        'created_by'      => 'required|integer',
    ];

    protected $skipValidation = false;

    /**
     * Get current active siklus
     */
    public function getAktif(): ?array
    {
        return $this->where('is_active', 1)
                    ->where('tanggal_mulai <=', date('Y-m-d'))
                    ->where('tanggal_selesai >=', date('Y-m-d'))
                    ->orderBy('tanggal_mulai', 'DESC')
                    ->first();
    }

    /**
     * Get siklus with detail count
     */
    public function getWithDetailCount(): array
    {
        return $this->db->table('jadwal_siklus js')
            ->select('js.*, u.name AS created_by_name, COUNT(jsd.id) AS jumlah_menu')
            ->join('users u', 'u.id = js.created_by', 'left')
            ->join('jadwal_siklus_detail jsd', 'jsd.siklus_id = js.id', 'left')
            ->groupBy('js.id')
            ->orderBy('js.tanggal_mulai', 'DESC')
            ->get()
            ->getResultArray();
    }
}
