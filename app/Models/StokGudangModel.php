<?php

namespace App\Models;

use CodeIgniter\Model;

class StokGudangModel extends Model
{
    protected $table            = 'stok_gudang';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'bahan_baku_id',
        'stok_saat_ini',
        'stok_masuk',
        'stok_keluar',
        'tanggal',
        'keterangan',
        'created_by',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'bahan_baku_id' => 'required|integer',
        'tanggal'       => 'required|valid_date',
    ];

    protected $skipValidation = false;

    /**
     * Get current total stock for a specific bahan baku
     */
    public function getStokSaatIni(int $bahanBakuId): float
    {
        $result = $this->selectSum('stok_masuk')
                       ->selectSum('stok_keluar')
                       ->where('bahan_baku_id', $bahanBakuId)
                       ->first();

        if (! $result) {
            return 0.0;
        }

        return (float) (($result['stok_masuk'] ?? 0) - ($result['stok_keluar'] ?? 0));
    }

    /**
     * Get movement history (kartu stok) for a specific bahan baku
     */
    public function getKartuStok(int $bahanBakuId): array
    {
        return $this->db->table('stok_gudang sg')
            ->select('sg.*, bb.nama AS nama_bahan, bb.satuan, u.name AS nama_user')
            ->join('bahan_baku bb', 'bb.id = sg.bahan_baku_id', 'left')
            ->join('users u', 'u.id = sg.created_by', 'left')
            ->where('sg.bahan_baku_id', $bahanBakuId)
            ->orderBy('sg.tanggal', 'ASC')
            ->orderBy('sg.created_at', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get all stock movements with bahan baku and user info
     */
    public function getAllWithRelations(): array
    {
        return $this->db->table('stok_gudang sg')
            ->select('sg.*, bb.nama AS nama_bahan, bb.satuan, bb.kode, u.name AS nama_user')
            ->join('bahan_baku bb', 'bb.id = sg.bahan_baku_id', 'left')
            ->join('users u', 'u.id = sg.created_by', 'left')
            ->orderBy('sg.tanggal', 'DESC')
            ->orderBy('sg.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }
}
