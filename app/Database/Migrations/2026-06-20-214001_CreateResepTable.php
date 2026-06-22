<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateResepTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'auto_increment' => true],
            'kode'             => ['type' => 'VARCHAR', 'constraint' => 20],
            'nama_menu'        => ['type' => 'VARCHAR', 'constraint' => 200],
            'deskripsi'        => ['type' => 'TEXT', 'null' => true],
            'kategori'         => [
                'type'       => 'ENUM',
                'constraint' => [
                    'Makanan Pokok',
                    'Lauk Pauk',
                    'Sayuran',
                    'Buah',
                    'Minuman',
                ],
                'default' => 'Makanan Pokok',
            ],
            'total_kalori'      => ['type' => 'DECIMAL', 'constraint' => '8,2', 'null' => true],
            'total_protein'     => ['type' => 'DECIMAL', 'constraint' => '8,2', 'null' => true],
            'total_karbohidrat' => ['type' => 'DECIMAL', 'constraint' => '8,2', 'null' => true],
            'porsi_standar'     => ['type' => 'INT', 'default' => 1],
            'foto'              => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_active'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('kode');
        $this->forge->createTable('resep');
    }

    public function down()
    {
        $this->forge->dropTable('resep');
    }
}
