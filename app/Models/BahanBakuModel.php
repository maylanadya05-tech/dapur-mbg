<?php

namespace App\Models;

use CodeIgniter\Model;

class BahanBakuModel extends Model
{
    protected $table            = 'bahan_baku';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'kode',
        'nama',
        'kategori',
        'satuan',
        'harga_per_satuan',
        'harga_satuan',
        'stok_minimum',
        'supplier_id',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'kode'  => 'required|max_length[20]',
        'nama'  => 'required|max_length[150]',
        'satuan' => 'required|max_length[20]',
    ];

    protected $skipValidation = false;

    /**
     * Get all bahan baku with current stock joined from stok_gudang
     */
    public function getWithStok(): array
    {
        return $this->db->table('bahan_baku bb')
            ->select('bb.*, s.name AS supplier_name, COALESCE(SUM(sg.stok_masuk) - SUM(sg.stok_keluar), 0) AS stok_saat_ini')
            ->join('supplier s', 's.id = bb.supplier_id', 'left')
            ->join('stok_gudang sg', 'sg.bahan_baku_id = bb.id', 'left')
            ->where('bb.deleted_at IS NULL')
            ->groupBy('bb.id')
            ->orderBy('bb.nama', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get bahan baku where current stock is below minimum threshold
     */
    public function getStokKritis(): array
    {
        return $this->db->table('bahan_baku bb')
            ->select('bb.*, COALESCE(SUM(sg.stok_masuk) - SUM(sg.stok_keluar), 0) AS stok_saat_ini')
            ->join('stok_gudang sg', 'sg.bahan_baku_id = bb.id', 'left')
            ->where('bb.deleted_at IS NULL')
            ->where('bb.is_active', 1)
            ->groupBy('bb.id')
            ->having('stok_saat_ini <', $this->db->table('bahan_baku')->select('stok_minimum')->where('id = bb.id')->getCompiledSelect())
            ->get()
            ->getResultArray();
    }

    /**
     * Get bahan baku where stock < minimum (simpler approach)
     */
    public function getStokKritisSimple(): array
    {
        $sql = "SELECT bb.*, s.name AS supplier_name,
                    COALESCE((SELECT SUM(sg.stok_masuk) - SUM(sg.stok_keluar) FROM stok_gudang sg WHERE sg.bahan_baku_id = bb.id), 0) AS stok_saat_ini
                FROM bahan_baku bb
                LEFT JOIN supplier s ON s.id = bb.supplier_id
                WHERE bb.deleted_at IS NULL
                AND bb.is_active = 1
                HAVING stok_saat_ini < bb.stok_minimum
                ORDER BY bb.nama ASC";

        return $this->db->query($sql)->getResultArray();
    }
}
