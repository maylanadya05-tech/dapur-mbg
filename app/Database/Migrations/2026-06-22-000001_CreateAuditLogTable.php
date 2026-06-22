<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditLogTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'user_name'   => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'user_role'   => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'action'      => ['type' => 'ENUM', 'constraint' => ['CREATE', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT', 'APPROVE', 'REJECT', 'EXPORT'], 'null' => false],
            'module'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'record_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'description' => ['type' => 'TEXT', 'null' => true],
            'old_values'  => ['type' => 'JSON', 'null' => true],
            'new_values'  => ['type' => 'JSON', 'null' => true],
            'ip_address'  => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent'  => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id']);
        $this->forge->addKey(['action']);
        $this->forge->addKey(['module']);
        $this->forge->addKey(['created_at']);
        $this->forge->createTable('audit_log');
    }

    public function down(): void
    {
        $this->forge->dropTable('audit_log', true);
    }
}
