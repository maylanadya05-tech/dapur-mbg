<?php

namespace App\Models;

use CodeIgniter\Model;

class FoodWasteModel extends Model
{
    protected $table            = 'food_waste';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'batch_id',
        'tanggal',
        'kategori',
        'bahan_baku_id',
        'qty',
        'satuan',
        'estimasi_nilai',
        'keterangan',
        'dicatat_oleh',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'tanggal'      => 'required|valid_date',
        'kategori'     => 'required|in_list[sisa_makanan,bahan_kadaluarsa,kesalahan_porsi,lainnya]',
        'qty'          => 'required|decimal|greater_than[0]',
        'satuan'       => 'required|max_length[20]',
        'dicatat_oleh' => 'required|integer',
    ];

    protected $skipValidation = false;

    /**
     * Get all waste with relations
     */
    public function getWithRelations(): array
    {
        return $this->db->table('food_waste fw')
            ->select('fw.*, bp.nomor_batch, bb.nama AS nama_bahan, u.name AS dicatat_oleh_name')
            ->join('batch_produksi bp', 'bp.id = fw.batch_id', 'left')
            ->join('bahan_baku bb', 'bb.id = fw.bahan_baku_id', 'left')
            ->join('users u', 'u.id = fw.dicatat_oleh', 'left')
            ->orderBy('fw.tanggal', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get total waste value for a date range
     */
    public function getTotalNilai(string $dari, string $sampai): float
    {
        $result = $this->selectSum('estimasi_nilai')
                       ->where('tanggal >=', $dari)
                       ->where('tanggal <=', $sampai)
                       ->first();

        return (float) ($result['estimasi_nilai'] ?? 0);
    }
}
