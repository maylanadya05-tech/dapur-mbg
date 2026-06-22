<?php

namespace App\Models;

use CodeIgniter\Model;

class FeedbackModel extends Model
{
    protected $table            = 'feedback';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'distribusi_id',
        'sekolah_id',
        'batch_id',
        'rating',
        'komentar',
        'aspek',
        'nama_pemberi',
        'tanggal',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'sekolah_id' => 'required|integer',
        'rating'     => 'required|integer|greater_than[0]|less_than[6]',
        'tanggal'    => 'required|valid_date',
    ];

    protected $skipValidation = false;

    /**
     * Get all feedback with relations
     */
    public function getWithRelations(): array
    {
        return $this->db->table('feedback f')
            ->select('f.*, s.nama AS nama_sekolah, s.jenjang, bp.nomor_batch, r.nama_menu')
            ->join('sekolah s', 's.id = f.sekolah_id', 'left')
            ->join('batch_produksi bp', 'bp.id = f.batch_id', 'left')
            ->join('resep r', 'r.id = bp.resep_id', 'left')
            ->orderBy('f.tanggal', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get average rating per school
     */
    public function getAverageRatingBySekolah(): array
    {
        return $this->db->table('feedback f')
            ->select('s.nama AS nama_sekolah, AVG(f.rating) AS avg_rating, COUNT(f.id) AS total_feedback')
            ->join('sekolah s', 's.id = f.sekolah_id', 'left')
            ->groupBy('f.sekolah_id')
            ->orderBy('avg_rating', 'DESC')
            ->get()
            ->getResultArray();
    }
}
