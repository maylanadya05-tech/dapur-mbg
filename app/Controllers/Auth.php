<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    /**
     * Show login form
     */
    public function login()
    {
        // Already logged in → go to dashboard
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login', [
            'title' => 'Login – Dapur MBG SPPG',
        ]);
    }

    /**
     * Process login form submission
     */
    public function processLogin()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $model = new UserModel();
        $user  = $model->where('email', $this->request->getPost('email'))
                       ->where('is_active', 1)
                       ->first();

        if (! $user || ! password_verify($this->request->getPost('password'), $user['password'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Email atau password salah.');
        }

        // Set session data
        $sessionData = [
            'isLoggedIn'   => true,
            'user_id'      => $user['id'],
            'user_name'    => $user['name'],
            'user_email'   => $user['email'],
            'user_role'    => $user['role'],
            'user_avatar'  => $user['avatar'],
        ];

        session()->set($sessionData);

        // Update last login timestamp
        $model->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);

        return redirect()->to('/dashboard')
            ->with('success', 'Selamat datang, ' . $user['name'] . '!');
    }

    /**
     * Logout and destroy session
     */
    public function logout()
    {
        session()->destroy();

        return redirect()->to('/auth/login')
            ->with('success', 'Berhasil logout. Sampai jumpa!');
    }
}
