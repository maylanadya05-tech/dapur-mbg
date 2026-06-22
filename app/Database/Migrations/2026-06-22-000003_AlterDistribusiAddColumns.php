<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterDistribusiAddColumns extends Migration
{
    public function up(): void
    {
        // Add foto_bukti, qr_token, armada_id, waktu_kirim if not exist
        $fields = [
            'foto_bukti' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true, 'after' => 'catatan'],
            'qr_token'   => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true, 'after' => 'foto_bukti'],
            'armada_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'qr_token'],
            'waktu_kirim'=> ['type' => 'DATETIME', 'null' => true, 'after' => 'armada_id'],
        ];

        // Only add columns that don't exist yet
        $db = \Config\Database::connect();
        $existingFields = $db->getFieldNames('distribusi');

        foreach ($fields as $colName => $colDef) {
            if (!in_array($colName, $existingFields)) {
                $this->forge->addColumn('distribusi', [$colName => $colDef]);
            }
        }
    }

    public function down(): void
    {
        $db = \Config\Database::connect();
        $existingFields = $db->getFieldNames('distribusi');

        $dropCols = ['foto_bukti', 'qr_token', 'armada_id', 'waktu_kirim'];
        foreach ($dropCols as $col) {
            if (in_array($col, $existingFields)) {
                $this->forge->dropColumn('distribusi', $col);
            }
        }
    }
}
