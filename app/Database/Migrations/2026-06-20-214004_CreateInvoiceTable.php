<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInvoiceTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'auto_increment' => true],
            'nomor_invoice'   => ['type' => 'VARCHAR', 'constraint' => 30],
            'tanggal'         => ['type' => 'DATE', 'null' => false],
            'jatuh_tempo'     => ['type' => 'DATE', 'null' => false],
            'periode_dari'    => ['type' => 'DATE', 'null' => false],
            'periode_sampai'  => ['type' => 'DATE', 'null' => false],
            'total_porsi'     => ['type' => 'INT', 'default' => 0],
            'harga_per_porsi' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'total_nilai'     => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'status'          => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'dikirim', 'dibayar', 'jatuh_tempo'],
                'default'    => 'draft',
            ],
            'catatan'      => ['type' => 'TEXT', 'null' => true],
            'file_pdf'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'dibuat_oleh'  => ['type' => 'INT', 'null' => false],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nomor_invoice');
        $this->forge->addForeignKey('dibuat_oleh', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('invoice');
    }

    public function down()
    {
        $this->forge->dropTable('invoice');
    }
}
