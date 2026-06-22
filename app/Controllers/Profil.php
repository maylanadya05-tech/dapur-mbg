<?php

namespace App\Controllers;

use App\Models\UserModel;

class Profil extends BaseController
{
    /**
     * Display current user's profile
     */
    public function index()
    {
        $userModel = new UserModel();
        $userId    = session()->get('user_id');
        $user      = $userModel->find($userId);

        if (!$user) {
            return redirect()->to('/dashboard')->with('error', 'Pengguna tidak ditemukan.');
        }

        return view('profil/index', [
            'title' => 'Profil Saya — Dapur MBG',
            'user'  => $user,
        ]);
    }

    /**
     * Update current user's profile
     */
    public function update()
    {
        $userId    = session()->get('user_id');
        $userModel = new UserModel();
        $user      = $userModel->find($userId);

        if (!$user) {
            return redirect()->to('/dashboard')->with('error', 'Pengguna tidak ditemukan.');
        }

        $validationRules = [
            'name'  => 'required|min_length[2]|max_length[150]',
            'email' => 'required|valid_email|is_unique[users.email,id,' . $userId . ']',
            'phone' => 'permit_empty|max_length[20]',
        ];

        // If password is provided, validate it
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $validationRules['password'] = 'min_length[6]';
            $validationRules['password_confirm'] = 'matches[password]';
        }

        if (!$this->validate($validationRules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'  => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
        ];

        if (!empty($password)) {
            $data['password'] = $password; // UserModel hashes it in beforeUpdate callback
        }

        // Process avatar if uploaded
        $avatarFile = $this->request->getFile('avatar');
        if ($avatarFile && $avatarFile->isValid() && !$avatarFile->hasMoved()) {
            // Delete old avatar if it exists
            if (!empty($user['avatar'])) {
                $oldPath = ROOTPATH . 'public/' . $user['avatar'];
                if (file_exists($oldPath) && is_file($oldPath)) {
                    unlink($oldPath);
                }
            }

            $newName = $avatarFile->getRandomName();
            $avatarFile->move(ROOTPATH . 'public/uploads/avatars', $newName);
            $data['avatar'] = 'uploads/avatars/' . $newName;
        }

        if (!$userModel->update($userId, $data)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $userModel->errors());
        }

        // Sync session data with updated info
        session()->set([
            'user_name'   => $data['name'],
            'user_email'  => $data['email'],
            'user_avatar' => $data['avatar'] ?? $user['avatar'],
        ]);

        return redirect()->to('/profil')
            ->with('success', 'Profil Anda berhasil diperbarui.');
    }
}
