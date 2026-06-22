<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBatchProduksiTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'auto_increment' => true],
            'nomor_batch'      => ['type' => 'VARCHAR', 'constraint' => 30],
            'tanggal_produksi' => ['type' => 'DATE', 'null' => false],
            'resep_id'         => ['type' => 'INT', 'null' => false],
            'target_porsi'     => ['type' => 'INT', 'null' => false],
            'porsi_selesai'    => ['type' => 'INT', 'default' => 0],
            'status'           => [
                'type'       => 'ENUM',
                'constraint' => ['persiapan', 'sedang_masak', 'selesai', 'dibatalkan'],
                'default'    => 'persiapan',
            ],
            'tim_produksi'  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'mulai_masak'   => ['type' => 'DATETIME', 'null' => true],
            'selesai_masak' => ['type' => 'DATETIME', 'null' => true],
            'catatan'       => ['type' => 'TEXT', 'null' => true],
            'dibuat_oleh'   => ['type' => 'INT', 'null' => false],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nomor_batch');
        $this->forge->addForeignKey('resep_id', 'resep', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('dibuat_oleh', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('batch_produksi');
    }

    public function down()
    {
        $this->forge->dropTable('batch_produksi');
    }
}
