<?php

namespace App\Models;

use CodeIgniter\Model;

class ArmadaModel extends Model
{
    protected $table      = 'armada';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = [
        'no_polisi', 'jenis', 'kapasitas_porsi',
        'pengemudi', 'phone_pengemudi', 'status', 'keterangan',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'no_polisi'       => 'required|max_length[20]',
        'jenis'           => 'required|in_list[Motor,Mobil Pick-Up,Mobil Box,Van,Truk]',
        'kapasitas_porsi' => 'required|integer|greater_than_equal_to[0]',
    ];

    /**
     * Get armada that are available (tersedia)
     */
    public function getAvailable(): array
    {
        return $this->where('status', 'tersedia')->orderBy('no_polisi')->findAll();
    }

    /**
     * Get all armada with usage count today
     */
    public function getWithUsageToday(): array
    {
        $db = \Config\Database::connect();
        return $db->table('armada a')
            ->select('a.*, COUNT(d.id) as pengiriman_hari_ini')
            ->join('distribusi d', 'd.armada_id = a.id AND DATE(d.tanggal_distribusi) = CURDATE()', 'left')
            ->groupBy('a.id')
            ->orderBy('a.no_polisi')
            ->get()->getResultArray();
    }
}
