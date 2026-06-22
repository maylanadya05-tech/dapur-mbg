<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStokGudangTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'auto_increment' => true],
            'bahan_baku_id' => ['type' => 'INT', 'null' => false],
            'stok_saat_ini' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'stok_masuk'    => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'stok_keluar'   => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'tanggal'       => ['type' => 'DATE', 'null' => false],
            'keterangan'    => ['type' => 'TEXT', 'null' => true],
            'created_by'    => ['type' => 'INT', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('bahan_baku_id', 'bahan_baku', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('stok_gudang');
    }

    public function down()
    {
        $this->forge->dropTable('stok_gudang');
    }
}
