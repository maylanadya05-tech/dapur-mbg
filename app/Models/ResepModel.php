<?php

namespace App\Models;

use CodeIgniter\Model;

class ResepModel extends Model
{
    protected $table            = 'resep';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'kode',
        'nama_menu',
        'deskripsi',
        'kategori',
        'total_kalori',
        'total_protein',
        'total_karbohidrat',
        'porsi_standar',
        'foto',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'kode'      => 'required|max_length[20]',
        'nama_menu' => 'required|max_length[200]',
        'kategori'  => 'required|in_list[Makanan Pokok,Lauk Pauk,Sayuran,Buah,Minuman]',
    ];

    protected $skipValidation = false;

    /**
     * Generate unique resep code
     */
    public function generateKode(): string
    {
        $year  = date('Y');
        $count = $this->withDeleted()->where('YEAR(created_at)', $year)->countAllResults();

        return 'RSP-' . $year . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get active resep list for dropdowns
     */
    public function getActiveList(): array
    {
        return $this->where('is_active', 1)
                    ->orderBy('nama_menu', 'ASC')
                    ->findAll();
    }
}
