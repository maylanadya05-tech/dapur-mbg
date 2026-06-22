<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterBahanBakuAddHargaSatuan extends Migration
{
    public function up(): void
    {
        $db = \Config\Database::connect();
        $existingFields = $db->getFieldNames('bahan_baku');

        if (!in_array('harga_satuan', $existingFields)) {
            $this->forge->addColumn('bahan_baku', [
                'harga_satuan' => [
                    'type'    => 'DECIMAL',
                    'constraint' => '15,2',
                    'default' => 0,
                    'null'    => false,
                    'after'   => 'satuan',
                ],
            ]);
        }
    }

    public function down(): void
    {
        $db = \Config\Database::connect();
        $existingFields = $db->getFieldNames('bahan_baku');
        if (in_array('harga_satuan', $existingFields)) {
            $this->forge->dropColumn('bahan_baku', 'harga_satuan');
        }
    }
}
