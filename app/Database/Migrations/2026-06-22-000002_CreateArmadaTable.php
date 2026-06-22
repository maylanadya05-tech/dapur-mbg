<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateArmadaTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'no_polisi'         => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false],
            'jenis'             => ['type' => 'ENUM', 'constraint' => ['Motor', 'Mobil Pick-Up', 'Mobil Box', 'Van', 'Truk'], 'default' => 'Mobil Box'],
            'kapasitas_porsi'   => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'pengemudi'         => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'phone_pengemudi'   => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'status'            => ['type' => 'ENUM', 'constraint' => ['tersedia', 'digunakan', 'servis', 'tidak_aktif'], 'default' => 'tersedia'],
            'keterangan'        => ['type' => 'TEXT', 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('no_polisi');
        $this->forge->createTable('armada');
    }

    public function down(): void
    {
        $this->forge->dropTable('armada', true);
    }
}
