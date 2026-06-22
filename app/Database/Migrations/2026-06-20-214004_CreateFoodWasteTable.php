<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFoodWasteTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'auto_increment' => true],
            'batch_id'       => ['type' => 'INT', 'null' => true],
            'tanggal'        => ['type' => 'DATE', 'null' => false],
            'kategori'       => [
                'type'       => 'ENUM',
                'constraint' => [
                    'sisa_makanan',
                    'bahan_kadaluarsa',
                    'kesalahan_porsi',
                    'lainnya',
                ],
                'default' => 'lainnya',
            ],
            'bahan_baku_id'  => ['type' => 'INT', 'null' => true],
            'qty'            => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'satuan'         => ['type' => 'VARCHAR', 'constraint' => 20],
            'estimasi_nilai' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'keterangan'     => ['type' => 'TEXT', 'null' => true],
            'dicatat_oleh'   => ['type' => 'INT', 'null' => false],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('batch_id', 'batch_produksi', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('dicatat_oleh', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('food_waste');
    }

    public function down()
    {
        $this->forge->dropTable('food_waste');
    }
}
