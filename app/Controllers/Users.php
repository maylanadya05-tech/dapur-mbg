<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class Users extends BaseController
{
    /**
     * Helper to verify admin role. Throws 404 if not admin.
     */
    private function checkAdminAccess()
    {
        if (session()->get('user_role') !== 'admin') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Halaman tidak ditemukan.");
        }
    }

    /**
     * List all users, roles, and status (only Admin can access!).
     */
    public function index(): string
    {
        $this->checkAdminAccess();

        $userModel = new UserModel();
        // Fetch all users (including active and inactive)
        $users = $userModel->orderBy('name', 'ASC')->findAll();

        return view('users/index', [
            'title' => 'Manajemen Pengguna – Dapur MBG',
            'users' => $users,
        ]);
    }

    /**
     * Form to add user.
     */
    public function create(): string
    {
        $this->checkAdminAccess();

        return view('users/create', [
            'title' => 'Tambah Pengguna Baru – Dapur MBG',
        ]);
    }

    /**
     * Save new user with password hashing.
     */
    public function store()
    {
        $this->checkAdminAccess();

        $userModel = new UserModel();

        $validationRules = [
            'name'     => 'required|min_length[2]|max_length[150]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'role'     => 'required|in_list[admin,pembelian,gudang,produksi]',
            'phone'    => 'permit_empty|max_length[20]',
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'      => $this->request->getPost('name'),
            'email'     => $this->request->getPost('email'),
            'password'  => $this->request->getPost('password'), // UserModel hashes it before insert
            'role'      => $this->request->getPost('role'),
            'phone'     => $this->request->getPost('phone'),
            'is_active' => $this->request->getPost('is_active') !== null ? (int)$this->request->getPost('is_active') : 1,
        ];

        // Process avatar if uploaded
        $avatarFile = $this->request->getFile('avatar');
        if ($avatarFile && $avatarFile->isValid() && !$avatarFile->hasMoved()) {
            $newName = $avatarFile->getRandomName();
            $avatarFile->move(ROOTPATH . 'public/uploads/avatars', $newName);
            $data['avatar'] = 'uploads/avatars/' . $newName;
        }

        if (!$userModel->insert($data)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $userModel->errors());
        }

        return redirect()->to('/users')
            ->with('success', 'Pengguna baru ' . $data['name'] . ' berhasil dibuat.');
    }

    /**
     * Edit form.
     */
    public function edit(int $id)
    {
        $this->checkAdminAccess();

        $userModel = new UserModel();
        $user = $userModel->find($id);

        if (!$user) {
            return redirect()->to('/users')
                ->with('error', 'Pengguna tidak ditemukan.');
        }

        return view('users/edit', [
            'title' => 'Edit Pengguna – Dapur MBG',
            'user'  => $user,
        ]);
    }

    /**
     * Update user, hash password if updated.
     */
    public function update(int $id)
    {
        $this->checkAdminAccess();

        $userModel = new UserModel();
        $user = $userModel->find($id);

        if (!$user) {
            return redirect()->to('/users')
                ->with('error', 'Pengguna tidak ditemukan.');
        }

        $validationRules = [
            'name'  => 'required|min_length[2]|max_length[150]',
            'email' => 'required|valid_email|is_unique[users.email,id,' . $id . ']',
            'role'  => 'required|in_list[admin,pembelian,gudang,produksi]',
            'phone' => 'permit_empty|max_length[20]',
        ];

        // Require password length only if password is provided
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $validationRules['password'] = 'min_length[6]';
        }

        if (!$this->validate($validationRules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'      => $this->request->getPost('name'),
            'email'     => $this->request->getPost('email'),
            'role'      => $this->request->getPost('role'),
            'phone'     => $this->request->getPost('phone'),
            'is_active' => $this->request->getPost('is_active') !== null ? (int)$this->request->getPost('is_active') : 0,
        ];

        // Only include password if it is not empty
        if (!empty($password)) {
            $data['password'] = $password; // UserModel hashes it before update
        }

        // Process avatar if uploaded
        $avatarFile = $this->request->getFile('avatar');
        if ($avatarFile && $avatarFile->isValid() && !$avatarFile->hasMoved()) {
            $newName = $avatarFile->getRandomName();
            $avatarFile->move(ROOTPATH . 'public/uploads/avatars', $newName);
            $data['avatar'] = 'uploads/avatars/' . $newName;
        }

        if (!$userModel->update($id, $data)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $userModel->errors());
        }

        return redirect()->to('/users')
            ->with('success', 'Data pengguna ' . $data['name'] . ' berhasil diperbarui.');
    }

    /**
     * Delete user (soft delete).
     */
    public function delete(int $id)
    {
        $this->checkAdminAccess();

        $userModel = new UserModel();
        $user = $userModel->find($id);

        if (!$user) {
            return redirect()->to('/users')
                ->with('error', 'Pengguna tidak ditemukan.');
        }

        // Avoid self-deletion
        if ((int)session()->get('user_id') === $id) {
            return redirect()->to('/users')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $userModel->delete($id);

        return redirect()->to('/users')
            ->with('success', 'Pengguna ' . $user['name'] . ' berhasil dihapus.');
    }
}
