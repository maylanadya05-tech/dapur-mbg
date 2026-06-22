<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDistribusiTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'auto_increment' => true],
            'batch_id'           => ['type' => 'INT', 'null' => false],
            'sekolah_id'         => ['type' => 'INT', 'null' => false],
            'tanggal_distribusi' => ['type' => 'DATE', 'null' => false],
            'jumlah_porsi'       => ['type' => 'INT', 'null' => false],
            'status'             => [
                'type'       => 'ENUM',
                'constraint' => ['dijadwalkan', 'dikirim', 'diterima', 'bermasalah'],
                'default'    => 'dijadwalkan',
            ],
            'waktu_kirim'  => ['type' => 'DATETIME', 'null' => true],
            'waktu_terima' => ['type' => 'DATETIME', 'null' => true],
            'pengirim'     => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'penerima'     => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'catatan'      => ['type' => 'TEXT', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('batch_id', 'batch_produksi', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('sekolah_id', 'sekolah', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('distribusi');
    }

    public function down()
    {
        $this->forge->dropTable('distribusi');
    }
}
