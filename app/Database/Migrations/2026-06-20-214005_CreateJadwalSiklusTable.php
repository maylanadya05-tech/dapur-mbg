<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJadwalSiklusTable extends Migration
{
    public function up()
    {
        // Main siklus table
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'auto_increment' => true],
            'nama_siklus'    => ['type' => 'VARCHAR', 'constraint' => 100],
            'durasi_hari'    => ['type' => 'INT', 'default' => 5],
            'tanggal_mulai'  => ['type' => 'DATE', 'null' => false],
            'tanggal_selesai' => ['type' => 'DATE', 'null' => false],
            'is_active'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_by'     => ['type' => 'INT', 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('jadwal_siklus');

        // Detail siklus table
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'auto_increment' => true],
            'siklus_id'   => ['type' => 'INT', 'null' => false],
            'hari_ke'     => ['type' => 'INT', 'null' => false],
            'resep_id'    => ['type' => 'INT', 'null' => false],
            'keterangan'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('siklus_id', 'jadwal_siklus', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('resep_id', 'resep', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('jadwal_siklus_detail');
    }

    public function down()
    {
        $this->forge->dropTable('jadwal_siklus_detail');
        $this->forge->dropTable('jadwal_siklus');
    }
}
