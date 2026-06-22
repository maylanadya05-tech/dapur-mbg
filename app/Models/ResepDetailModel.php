<?php

namespace App\Models;

use CodeIgniter\Model;

class ResepDetailModel extends Model
{
    protected $table            = 'resep_detail';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'resep_id',
        'bahan_baku_id',
        'qty_per_porsi',
        'satuan',
        'keterangan',
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'resep_id'      => 'required|integer',
        'bahan_baku_id' => 'required|integer',
        'qty_per_porsi' => 'required|decimal',
        'satuan'        => 'required|max_length[20]',
    ];

    protected $skipValidation = false;

    /**
     * Get resep detail with bahan baku info for a given resep
     */
    public function getByResep(int $resepId): array
    {
        return $this->db->table('resep_detail rd')
            ->select('rd.*, bb.nama AS nama_bahan, bb.kode AS kode_bahan, bb.harga_per_satuan, bb.kategori AS kategori_bahan')
            ->join('bahan_baku bb', 'bb.id = rd.bahan_baku_id', 'left')
            ->where('rd.resep_id', $resepId)
            ->orderBy('bb.nama', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Delete all detail for a resep (used before re-inserting)
     */
    public function deleteByResep(int $resepId): bool
    {
        return $this->where('resep_id', $resepId)->delete();
    }
}
