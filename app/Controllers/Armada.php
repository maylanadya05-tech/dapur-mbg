<?php

namespace App\Controllers;

use App\Models\ArmadaModel;
use App\Libraries\AuditLogger;

class Armada extends BaseController
{
    protected ArmadaModel $armadaModel;

    public function __construct()
    {
        $this->armadaModel = new ArmadaModel();
    }

    /**
     * List all vehicles
     */
    public function index(): string
    {
        $armada = $this->armadaModel->getWithUsageToday();

        return view('armada/index', [
            'title'  => 'Manajemen Armada Kendaraan – Dapur MBG',
            'armada' => $armada,
        ]);
    }

    /**
     * Form create vehicle
     */
    public function create(): string
    {
        return view('armada/create', [
            'title' => 'Tambah Kendaraan Baru – Dapur MBG',
        ]);
    }

    /**
     * Store new vehicle
     */
    public function store()
    {
        $rules = [
            'no_polisi'       => 'required|max_length[20]',
            'jenis'           => 'required|in_list[Motor,Mobil Pick-Up,Mobil Box,Van,Truk]',
            'kapasitas_porsi' => 'required|integer|greater_than_equal_to[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'no_polisi'       => strtoupper($this->request->getPost('no_polisi')),
            'jenis'           => $this->request->getPost('jenis'),
            'kapasitas_porsi' => (int) $this->request->getPost('kapasitas_porsi'),
            'pengemudi'       => $this->request->getPost('pengemudi'),
            'phone_pengemudi' => $this->request->getPost('phone_pengemudi'),
            'status'          => $this->request->getPost('status') ?: 'tersedia',
            'keterangan'      => $this->request->getPost('keterangan'),
        ];

        if (!$this->armadaModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->armadaModel->errors());
        }

        AuditLogger::log('CREATE', 'Armada', $this->armadaModel->getInsertID(),
            "Kendaraan {$data['no_polisi']} ditambahkan.", [], $data);

        return redirect()->to('/armada')->with('success', "Kendaraan {$data['no_polisi']} berhasil ditambahkan.");
    }

    /**
     * Edit form
     */
    public function edit(int $id)
    {
        $kendaraan = $this->armadaModel->find($id);
        if (!$kendaraan) {
            return redirect()->to('/armada')->with('error', 'Kendaraan tidak ditemukan.');
        }

        return view('armada/edit', [
            'title'     => 'Edit Kendaraan – Dapur MBG',
            'kendaraan' => $kendaraan,
        ]);
    }

    /**
     * Update vehicle
     */
    public function update(int $id)
    {
        $kendaraan = $this->armadaModel->find($id);
        if (!$kendaraan) {
            return redirect()->to('/armada')->with('error', 'Kendaraan tidak ditemukan.');
        }

        $rules = [
            'no_polisi'       => 'required|max_length[20]',
            'jenis'           => 'required|in_list[Motor,Mobil Pick-Up,Mobil Box,Van,Truk]',
            'kapasitas_porsi' => 'required|integer|greater_than_equal_to[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'no_polisi'       => strtoupper($this->request->getPost('no_polisi')),
            'jenis'           => $this->request->getPost('jenis'),
            'kapasitas_porsi' => (int) $this->request->getPost('kapasitas_porsi'),
            'pengemudi'       => $this->request->getPost('pengemudi'),
            'phone_pengemudi' => $this->request->getPost('phone_pengemudi'),
            'status'          => $this->request->getPost('status') ?: 'tersedia',
            'keterangan'      => $this->request->getPost('keterangan'),
        ];

        $this->armadaModel->update($id, $data);

        AuditLogger::log('UPDATE', 'Armada', $id, "Kendaraan {$data['no_polisi']} diperbarui.", $kendaraan, $data);

        return redirect()->to('/armada')->with('success', "Kendaraan {$data['no_polisi']} berhasil diperbarui.");
    }

    /**
     * Delete vehicle
     */
    public function delete(int $id)
    {
        $kendaraan = $this->armadaModel->find($id);
        if (!$kendaraan) {
            return redirect()->to('/armada')->with('error', 'Kendaraan tidak ditemukan.');
        }

        $this->armadaModel->delete($id);

        AuditLogger::log('DELETE', 'Armada', $id, "Kendaraan {$kendaraan['no_polisi']} dihapus.", $kendaraan);

        return redirect()->to('/armada')->with('success', "Kendaraan {$kendaraan['no_polisi']} berhasil dihapus.");
    }
}
