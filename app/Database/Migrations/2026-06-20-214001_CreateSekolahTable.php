<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSekolahTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'auto_increment' => true],
            'kode'           => ['type' => 'VARCHAR', 'constraint' => 20],
            'nama'           => ['type' => 'VARCHAR', 'constraint' => 200],
            'jenjang'        => [
                'type'       => 'ENUM',
                'constraint' => ['SD', 'SMP', 'SMA', 'SMK'],
                'default'    => 'SD',
            ],
            'alamat'         => ['type' => 'TEXT'],
            'kelurahan'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'kecamatan'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'kota'           => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'kepala_sekolah' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'phone'          => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'jumlah_siswa'   => ['type' => 'INT', 'default' => 0],
            'is_active'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('kode');
        $this->forge->createTable('sekolah');
    }

    public function down()
    {
        $this->forge->dropTable('sekolah');
    }
}
