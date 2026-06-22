<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->seedUsers();
        $this->seedSupplier();
        $this->seedBahanBaku();
        $this->seedSekolah();
        $this->seedResep();
        $this->seedJadwalSiklus();
    }

    // ─────────────────────────────────────────────────────────────────
    // USERS
    // ─────────────────────────────────────────────────────────────────
    private function seedUsers(): void
    {
        $now   = date('Y-m-d H:i:s');
        $users = [
            [
                'name'       => 'Administrator',
                'email'      => 'admin@dapurmbg.id',
                'password'   => password_hash('admin123', PASSWORD_DEFAULT),
                'role'       => 'admin',
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Staff Pembelian',
                'email'      => 'pembelian@dapurmbg.id',
                'password'   => password_hash('pembelian123', PASSWORD_DEFAULT),
                'role'       => 'pembelian',
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Staff Gudang',
                'email'      => 'gudang@dapurmbg.id',
                'password'   => password_hash('gudang123', PASSWORD_DEFAULT),
                'role'       => 'gudang',
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Staff Produksi',
                'email'      => 'produksi@dapurmbg.id',
                'password'   => password_hash('produksi123', PASSWORD_DEFAULT),
                'role'       => 'produksi',
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Avoid duplicates
        foreach ($users as $user) {
            $exists = $this->db->table('users')
                ->where('email', $user['email'])
                ->countAllResults();

            if (! $exists) {
                $this->db->table('users')->insert($user);
            }
        }

        echo "✓ Users seeded.\n";
    }

    // ─────────────────────────────────────────────────────────────────
    // SUPPLIER
    // ─────────────────────────────────────────────────────────────────
    private function seedSupplier(): void
    {
        $now       = date('Y-m-d H:i:s');
        $suppliers = [
            [
                'name'           => 'UD Makmur Jaya',
                'contact_person' => 'Budi Santoso',
                'phone'          => '081234567890',
                'email'          => 'makmurjaya@email.com',
                'address'        => 'Jl. Pasar Baru No. 12, Kota',
                'kategori'       => 'Karbohidrat',
                'rating'         => 4.50,
                'is_active'      => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'name'           => 'PT Sumber Protein',
                'contact_person' => 'Dewi Rahayu',
                'phone'          => '082345678901',
                'email'          => 'sumberprotein@email.com',
                'address'        => 'Jl. Industri Raya No. 45, Kota',
                'kategori'       => 'Protein',
                'rating'         => 4.20,
                'is_active'      => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'name'           => 'CV Sayur Segar',
                'contact_person' => 'Ahmad Fauzi',
                'phone'          => '083456789012',
                'email'          => 'sayursegar@email.com',
                'address'        => 'Jl. Kebun Sayur No. 8, Kota',
                'kategori'       => 'Sayuran',
                'rating'         => 4.70,
                'is_active'      => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
        ];

        foreach ($suppliers as $supplier) {
            $exists = $this->db->table('supplier')
                ->where('name', $supplier['name'])
                ->countAllResults();

            if (! $exists) {
                $this->db->table('supplier')->insert($supplier);
            }
        }

        echo "✓ Supplier seeded.\n";
    }

    // ─────────────────────────────────────────────────────────────────
    // BAHAN BAKU
    // ─────────────────────────────────────────────────────────────────
    private function seedBahanBaku(): void
    {
        $now = date('Y-m-d H:i:s');

        // Get supplier IDs
        $supKarbo   = $this->db->table('supplier')->where('kategori', 'Karbohidrat')->get()->getRow();
        $supProtein = $this->db->table('supplier')->where('kategori', 'Protein')->get()->getRow();
        $supSayur   = $this->db->table('supplier')->where('kategori', 'Sayuran')->get()->getRow();

        $supKarboId   = $supKarbo->id   ?? null;
        $supProteinId = $supProtein->id ?? null;
        $supSayurId   = $supSayur->id   ?? null;

        $bahanBaku = [
            [
                'kode'             => 'BB-001',
                'nama'             => 'Beras Putih',
                'kategori'         => 'Karbohidrat',
                'satuan'           => 'kg',
                'harga_per_satuan' => 12000,
                'stok_minimum'     => 50,
                'supplier_id'      => $supKarboId,
                'is_active'        => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'kode'             => 'BB-002',
                'nama'             => 'Daging Ayam',
                'kategori'         => 'Protein',
                'satuan'           => 'kg',
                'harga_per_satuan' => 38000,
                'stok_minimum'     => 20,
                'supplier_id'      => $supProteinId,
                'is_active'        => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'kode'             => 'BB-003',
                'nama'             => 'Wortel',
                'kategori'         => 'Sayuran',
                'satuan'           => 'kg',
                'harga_per_satuan' => 8000,
                'stok_minimum'     => 15,
                'supplier_id'      => $supSayurId,
                'is_active'        => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'kode'             => 'BB-004',
                'nama'             => 'Kentang',
                'kategori'         => 'Sayuran',
                'satuan'           => 'kg',
                'harga_per_satuan' => 9000,
                'stok_minimum'     => 15,
                'supplier_id'      => $supSayurId,
                'is_active'        => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'kode'             => 'BB-005',
                'nama'             => 'Minyak Goreng',
                'kategori'         => 'Minyak',
                'satuan'           => 'liter',
                'harga_per_satuan' => 18000,
                'stok_minimum'     => 10,
                'supplier_id'      => null,
                'is_active'        => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'kode'             => 'BB-006',
                'nama'             => 'Gula Pasir',
                'kategori'         => 'Bumbu',
                'satuan'           => 'kg',
                'harga_per_satuan' => 14000,
                'stok_minimum'     => 10,
                'supplier_id'      => null,
                'is_active'        => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'kode'             => 'BB-007',
                'nama'             => 'Garam',
                'kategori'         => 'Bumbu',
                'satuan'           => 'kg',
                'harga_per_satuan' => 5000,
                'stok_minimum'     => 5,
                'supplier_id'      => null,
                'is_active'        => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'kode'             => 'BB-008',
                'nama'             => 'Telur Ayam',
                'kategori'         => 'Protein',
                'satuan'           => 'kg',
                'harga_per_satuan' => 28000,
                'stok_minimum'     => 20,
                'supplier_id'      => $supProteinId,
                'is_active'        => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ];

        foreach ($bahanBaku as $item) {
            $exists = $this->db->table('bahan_baku')
                ->where('kode', $item['kode'])
                ->countAllResults();

            if (! $exists) {
                $this->db->table('bahan_baku')->insert($item);
            }
        }

        echo "✓ Bahan Baku seeded.\n";
    }

    // ─────────────────────────────────────────────────────────────────
    // SEKOLAH
    // ─────────────────────────────────────────────────────────────────
    private function seedSekolah(): void
    {
        $now     = date('Y-m-d H:i:s');
        $sekolah = [
            [
                'kode'         => 'SKL-001',
                'nama'         => 'SD Negeri 01 Sukamaju',
                'jenjang'      => 'SD',
                'alamat'       => 'Jl. Sukamaju No. 1, Kelurahan Sukamaju',
                'kelurahan'    => 'Sukamaju',
                'kecamatan'    => 'Kecamatan Barat',
                'kota'         => 'Kota Contoh',
                'jumlah_siswa' => 280,
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'kode'         => 'SKL-002',
                'nama'         => 'SD Negeri 02 Harapan',
                'jenjang'      => 'SD',
                'alamat'       => 'Jl. Harapan No. 5, Kelurahan Harapan',
                'kelurahan'    => 'Harapan',
                'kecamatan'    => 'Kecamatan Timur',
                'kota'         => 'Kota Contoh',
                'jumlah_siswa' => 320,
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'kode'         => 'SKL-003',
                'nama'         => 'SMP Negeri 01 Kota',
                'jenjang'      => 'SMP',
                'alamat'       => 'Jl. Pendidikan No. 10, Kelurahan Pusat',
                'kelurahan'    => 'Pusat',
                'kecamatan'    => 'Kecamatan Pusat',
                'kota'         => 'Kota Contoh',
                'jumlah_siswa' => 450,
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'kode'         => 'SKL-004',
                'nama'         => 'SD Negeri 03 Merdeka',
                'jenjang'      => 'SD',
                'alamat'       => 'Jl. Merdeka No. 17, Kelurahan Merdeka',
                'kelurahan'    => 'Merdeka',
                'kecamatan'    => 'Kecamatan Selatan',
                'kota'         => 'Kota Contoh',
                'jumlah_siswa' => 265,
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'kode'         => 'SKL-005',
                'nama'         => 'SMP Negeri 02 Bangsa',
                'jenjang'      => 'SMP',
                'alamat'       => 'Jl. Bangsa No. 23, Kelurahan Bangsa',
                'kelurahan'    => 'Bangsa',
                'kecamatan'    => 'Kecamatan Utara',
                'kota'         => 'Kota Contoh',
                'jumlah_siswa' => 380,
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ];

        foreach ($sekolah as $item) {
            $exists = $this->db->table('sekolah')
                ->where('kode', $item['kode'])
                ->countAllResults();

            if (! $exists) {
                $this->db->table('sekolah')->insert($item);
            }
        }

        echo "✓ Sekolah seeded.\n";
    }

    // ─────────────────────────────────────────────────────────────────
    // RESEP (with resep_detail / BOM)
    // ─────────────────────────────────────────────────────────────────
    private function seedResep(): void
    {
        $now = date('Y-m-d H:i:s');

        // Get bahan baku IDs
        $bb = [];
        $rows = $this->db->table('bahan_baku')->get()->getResultArray();
        foreach ($rows as $row) {
            $bb[$row['kode']] = $row['id'];
        }

        $resepData = [
            [
                'resep' => [
                    'kode'              => 'RSP-001',
                    'nama_menu'         => 'Nasi Ayam Teriyaki',
                    'deskripsi'         => 'Nasi putih dengan ayam teriyaki saus kecap manis, disajikan dengan sayuran tumis.',
                    'kategori'          => 'Makanan Pokok',
                    'total_kalori'      => 520.00,
                    'total_protein'     => 28.50,
                    'total_karbohidrat' => 72.00,
                    'porsi_standar'     => 1,
                    'is_active'         => 1,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ],
                'detail' => [
                    ['kode_bb' => 'BB-001', 'qty' => 0.1500, 'satuan' => 'kg', 'keterangan' => 'Beras untuk nasi'],
                    ['kode_bb' => 'BB-002', 'qty' => 0.1000, 'satuan' => 'kg', 'keterangan' => 'Daging ayam fillet'],
                    ['kode_bb' => 'BB-005', 'qty' => 0.0100, 'satuan' => 'liter', 'keterangan' => 'Untuk menumis'],
                    ['kode_bb' => 'BB-006', 'qty' => 0.0050, 'satuan' => 'kg', 'keterangan' => 'Gula untuk saus'],
                    ['kode_bb' => 'BB-007', 'qty' => 0.0020, 'satuan' => 'kg', 'keterangan' => 'Penyedap rasa'],
                ],
            ],
            [
                'resep' => [
                    'kode'              => 'RSP-002',
                    'nama_menu'         => 'Sayur Sop Tempe',
                    'deskripsi'         => 'Sop sayuran segar dengan tempe, wortel, dan kentang dalam kaldu bening.',
                    'kategori'          => 'Sayuran',
                    'total_kalori'      => 280.00,
                    'total_protein'     => 12.00,
                    'total_karbohidrat' => 38.00,
                    'porsi_standar'     => 1,
                    'is_active'         => 1,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ],
                'detail' => [
                    ['kode_bb' => 'BB-003', 'qty' => 0.0800, 'satuan' => 'kg', 'keterangan' => 'Wortel iris'],
                    ['kode_bb' => 'BB-004', 'qty' => 0.0800, 'satuan' => 'kg', 'keterangan' => 'Kentang potong dadu'],
                    ['kode_bb' => 'BB-005', 'qty' => 0.0050, 'satuan' => 'liter', 'keterangan' => 'Untuk menumis bumbu'],
                    ['kode_bb' => 'BB-007', 'qty' => 0.0015, 'satuan' => 'kg', 'keterangan' => 'Garam secukupnya'],
                ],
            ],
        ];

        foreach ($resepData as $item) {
            $exists = $this->db->table('resep')
                ->where('kode', $item['resep']['kode'])
                ->countAllResults();

            if (! $exists) {
                $this->db->table('resep')->insert($item['resep']);
                $resepId = $this->db->insertID();

                foreach ($item['detail'] as $detail) {
                    $bahanId = $bb[$detail['kode_bb']] ?? null;

                    if ($bahanId) {
                        $this->db->table('resep_detail')->insert([
                            'resep_id'      => $resepId,
                            'bahan_baku_id' => $bahanId,
                            'qty_per_porsi' => $detail['qty'],
                            'satuan'        => $detail['satuan'],
                            'keterangan'    => $detail['keterangan'],
                        ]);
                    }
                }
            }
        }

        echo "✓ Resep seeded.\n";
    }

    private function seedJadwalSiklus(): void
    {
        $now = date('Y-m-d H:i:s');
        $exists = $this->db->table('jadwal_siklus')->countAllResults();
        if ($exists) {
            return;
        }

        $r1 = $this->db->table('resep')->where('kode', 'RSP-001')->get()->getRow();
        $r2 = $this->db->table('resep')->where('kode', 'RSP-002')->get()->getRow();

        $r1Id = $r1->id ?? 1;
        $r2Id = $r2->id ?? 2;

        $cycles = [
            [
                'nama_siklus' => 'Siklus Gizi Utama - Juni A',
                'tanggal_mulai' => '2026-06-01',
                'tanggal_selesai' => '2026-06-05',
                'durasi_hari' => 5,
                'is_active' => 0,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama_siklus' => 'Siklus Gizi Utama - Juni B',
                'tanggal_mulai' => '2026-06-15',
                'tanggal_selesai' => '2026-06-19',
                'durasi_hari' => 5,
                'is_active' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($cycles as $cycle) {
            $this->db->table('jadwal_siklus')->insert($cycle);
            $cycleId = $this->db->insertID();

            for ($d = 1; $d <= 5; $d++) {
                $resepId = ($d % 2 == 1) ? $r1Id : $r2Id;
                $this->db->table('jadwal_siklus_detail')->insert([
                    'siklus_id' => $cycleId,
                    'hari_ke' => $d,
                    'resep_id' => $resepId,
                    'keterangan' => 'Menu hari ke ' . $d,
                ]);
            }
        }

        echo "✓ Jadwal Siklus seeded.\n";
    }
}
