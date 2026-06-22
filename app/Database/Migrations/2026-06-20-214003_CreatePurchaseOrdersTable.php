<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePurchaseOrdersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'auto_increment' => true],
            'nomor_po'          => ['type' => 'VARCHAR', 'constraint' => 30],
            'supplier_id'       => ['type' => 'INT', 'null' => false],
            'tanggal_po'        => ['type' => 'DATE', 'null' => false],
            'tanggal_dibutuhkan' => ['type' => 'DATE', 'null' => true],
            'status'            => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'diajukan', 'disetujui', 'dikirim', 'selesai', 'ditolak'],
                'default'    => 'draft',
            ],
            'total_nilai'       => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'catatan'           => ['type' => 'TEXT', 'null' => true],
            'alasan_tolak'      => ['type' => 'TEXT', 'null' => true],
            'dibuat_oleh'       => ['type' => 'INT', 'null' => false],
            'disetujui_oleh'    => ['type' => 'INT', 'null' => true],
            'tanggal_disetujui' => ['type' => 'DATETIME', 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nomor_po');
        $this->forge->addForeignKey('supplier_id', 'supplier', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('dibuat_oleh', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('purchase_orders');
    }

    public function down()
    {
        $this->forge->dropTable('purchase_orders');
    }
}
