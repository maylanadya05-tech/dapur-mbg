<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBahanBakuTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'auto_increment' => true],
            'kode'            => ['type' => 'VARCHAR', 'constraint' => 20],
            'nama'            => ['type' => 'VARCHAR', 'constraint' => 150],
            'kategori'        => [
                'type'       => 'ENUM',
                'constraint' => [
                    'Karbohidrat',
                    'Protein',
                    'Sayuran',
                    'Buah',
                    'Bumbu',
                    'Minyak',
                    'Susu',
                    'Lainnya',
                ],
                'default' => 'Lainnya',
            ],
            'satuan'          => ['type' => 'VARCHAR', 'constraint' => 20],
            'harga_per_satuan' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'stok_minimum'    => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'supplier_id'     => ['type' => 'INT', 'null' => true],
            'is_active'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('kode');
        $this->forge->addForeignKey('supplier_id', 'supplier', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('bahan_baku');
    }

    public function down()
    {
        $this->forge->dropTable('bahan_baku');
    }
}
