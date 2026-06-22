<?php

namespace App\Models;

use CodeIgniter\Model;

class PoDetailModel extends Model
{
    protected $table            = 'po_detail';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'po_id',
        'bahan_baku_id',
        'qty',
        'satuan',
        'harga_satuan',
        'subtotal',
        'qty_diterima',
        'catatan',
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'po_id'         => 'required|integer',
        'bahan_baku_id' => 'required|integer',
        'qty'           => 'required|decimal',
        'harga_satuan'  => 'required|decimal',
    ];

    protected $skipValidation = false;

    /**
     * Get detail items for a PO with bahan baku info
     */
    public function getByPO(int $poId): array
    {
        return $this->db->table('po_detail pd')
            ->select('pd.*, bb.nama AS nama_bahan, bb.kode AS kode_bahan, bb.kategori')
            ->join('bahan_baku bb', 'bb.id = pd.bahan_baku_id', 'left')
            ->where('pd.po_id', $poId)
            ->orderBy('bb.nama', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Delete all items for a PO
     */
    public function deleteByPO(int $poId): bool
    {
        return $this->where('po_id', $poId)->delete();
    }
}
