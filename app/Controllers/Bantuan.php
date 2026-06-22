<?php

namespace App\Controllers;

class Bantuan extends BaseController
{
    /**
     * Display the system help and user guide page
     */
    public function index()
    {
        $lang = session()->get('pref_lang') ?? 'id';

        return view('bantuan/index', [
            'title' => $lang === 'en' ? 'User Guide' : 'Panduan Penggunaan',
        ]);
    }
}
