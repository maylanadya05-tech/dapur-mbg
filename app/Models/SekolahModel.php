<?php

namespace App\Models;

use CodeIgniter\Model;

class SekolahModel extends Model
{
    protected $table            = 'sekolah';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'kode',
        'nama',
        'jenjang',
        'alamat',
        'kelurahan',
        'kecamatan',
        'kota',
        'kepala_sekolah',
        'phone',
        'jumlah_siswa',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'kode'    => 'required|max_length[20]',
        'nama'    => 'required|max_length[200]',
        'jenjang' => 'required|in_list[SD,SMP,SMA,SMK]',
        'alamat'  => 'required',
    ];

    protected $skipValidation = false;

    /**
     * Get active schools for dropdowns
     */
    public function getActiveList(): array
    {
        return $this->where('is_active', 1)
                    ->orderBy('nama', 'ASC')
                    ->findAll();
    }

    /**
     * Count total active students
     */
    public function countTotalSiswa(): int
    {
        $result = $this->selectSum('jumlah_siswa')->where('is_active', 1)->first();

        return (int) ($result['jumlah_siswa'] ?? 0);
    }
}
