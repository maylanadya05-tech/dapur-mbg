<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFeedbackTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'auto_increment' => true],
            'distribusi_id' => ['type' => 'INT', 'null' => true],
            'sekolah_id'    => ['type' => 'INT', 'null' => false],
            'batch_id'      => ['type' => 'INT', 'null' => true],
            'rating'        => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false],
            'komentar'      => ['type' => 'TEXT', 'null' => true],
            'aspek'         => [
                'type'       => 'ENUM',
                'constraint' => [
                    'rasa',
                    'porsi',
                    'kebersihan',
                    'ketepatan_waktu',
                    'keseluruhan',
                ],
                'default' => 'keseluruhan',
            ],
            'nama_pemberi' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'tanggal'      => ['type' => 'DATE', 'null' => false],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('distribusi_id', 'distribusi', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('sekolah_id', 'sekolah', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('feedback');
    }

    public function down()
    {
        $this->forge->dropTable('feedback');
    }
}
