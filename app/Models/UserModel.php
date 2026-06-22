<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'phone',
        'is_active',
        'last_login',
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'name'  => 'required|min_length[2]|max_length[150]',
        'email' => 'required|valid_email|max_length[150]',
        'role'  => 'required|in_list[admin,pembelian,gudang,produksi]',
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;

    // Callbacks
    protected $beforeInsert = ['hashPasswordOnInsert'];
    protected $beforeUpdate = ['hashPasswordOnUpdate'];

    /**
     * Hash password before insert if set
     */
    protected function hashPasswordOnInsert(array $data): array
    {
        if (isset($data['data']['password']) && ! empty($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }

        return $data;
    }

    /**
     * Hash password before update only if password field is present
     */
    protected function hashPasswordOnUpdate(array $data): array
    {
        if (isset($data['data']['password']) && ! empty($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        } elseif (isset($data['data']['password'])) {
            // Remove empty password so existing hash is preserved
            unset($data['data']['password']);
        }

        return $data;
    }

    /**
     * Find user by email (including soft-deleted is excluded by default)
     */
    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)
                    ->where('is_active', 1)
                    ->first();
    }

    /**
     * Get all users by role
     */
    public function getByRole(string $role): array
    {
        return $this->where('role', $role)
                    ->where('is_active', 1)
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }
}
