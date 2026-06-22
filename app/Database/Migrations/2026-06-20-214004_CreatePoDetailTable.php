<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePoDetailTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'auto_increment' => true],
            'po_id'         => ['type' => 'INT', 'null' => false],
            'bahan_baku_id' => ['type' => 'INT', 'null' => false],
            'qty'           => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'satuan'        => ['type' => 'VARCHAR', 'constraint' => 20],
            'harga_satuan'  => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'subtotal'      => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'qty_diterima'  => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'catatan'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('po_id', 'purchase_orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('bahan_baku_id', 'bahan_baku', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('po_detail');
    }

    public function down()
    {
        $this->forge->dropTable('po_detail');
    }
}
