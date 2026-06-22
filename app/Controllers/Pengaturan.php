<?php

namespace App\Controllers;

class Pengaturan extends BaseController
{
    /**
     * Display settings page
     */
    public function index()
    {
        // Load default/current configurations from session
        $session = session();
        
        $preferences = [
            'theme'               => $session->get('pref_theme') ?? 'dark',
            'language'            => $session->get('pref_lang') ?? 'id',
            'notif_stock'         => $session->get('pref_notif_stock') ?? 1,
            'notif_order'         => $session->get('pref_notif_order') ?? 1,
            'notif_daily_report'  => $session->get('pref_notif_daily_report') ?? 0,
        ];

        return view('pengaturan/index', [
            'title'       => 'Pengaturan — Dapur MBG',
            'preferences' => $preferences,
        ]);
    }

    /**
     * Update settings preferences
     */
    public function update()
    {
        $session = session();

        // Get post values
        $theme             = $this->request->getPost('theme') ?? 'dark';
        $language          = $this->request->getPost('language') ?? 'id';
        $notifStock        = $this->request->getPost('notif_stock') !== null ? 1 : 0;
        $notifOrder        = $this->request->getPost('notif_order') !== null ? 1 : 0;
        $notifDailyReport  = $this->request->getPost('notif_daily_report') !== null ? 1 : 0;

        // Save preferences in session
        $session->set([
            'pref_theme'              => $theme,
            'pref_lang'               => $language,
            'pref_notif_stock'        => $notifStock,
            'pref_notif_order'        => $notifOrder,
            'pref_notif_daily_report' => $notifDailyReport,
        ]);

        // You could also save to a database table if needed,
        // but session-based configuration works perfectly for user preferences.

        return redirect()->to('/pengaturan')
            ->with('success', 'Pengaturan preferensi berhasil disimpan.');
    }
}
