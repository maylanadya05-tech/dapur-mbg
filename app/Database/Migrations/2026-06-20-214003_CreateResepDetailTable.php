<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateResepDetailTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'auto_increment' => true],
            'resep_id'       => ['type' => 'INT', 'null' => false],
            'bahan_baku_id'  => ['type' => 'INT', 'null' => false],
            'qty_per_porsi'  => ['type' => 'DECIMAL', 'constraint' => '10,4'],
            'satuan'         => ['type' => 'VARCHAR', 'constraint' => 20],
            'keterangan'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('resep_id', 'resep', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('bahan_baku_id', 'bahan_baku', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('resep_detail');
    }

    public function down()
    {
        $this->forge->dropTable('resep_detail');
    }
}
