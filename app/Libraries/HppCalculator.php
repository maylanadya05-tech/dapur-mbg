<?php

namespace App\Libraries;

/**
 * HppCalculator — Kalkulasi Harga Pokok Produksi per Porsi
 *
 * Menghitung HPP berdasarkan BOM (Bill of Material) dari resep
 * dan harga satuan terakhir bahan baku.
 */
class HppCalculator
{
    /**
     * Hitung HPP untuk sebuah resep
     *
     * @param int $resepId  ID resep
     * @param int $porsi    Jumlah porsi (default: porsi_standar resep)
     * @return array        [total_hpp, hpp_per_porsi, items[], has_missing_price]
     */
    public static function calculate(int $resepId, int $porsi = 0): array
    {
        $db = \Config\Database::connect();

        // Get recipe with standard portions
        $resep = $db->table('resep')->where('id', $resepId)->get()->getRowArray();
        if (!$resep) {
            return ['total_hpp' => 0, 'hpp_per_porsi' => 0, 'items' => [], 'has_missing_price' => false];
        }

        $porsi = $porsi ?: (int) ($resep['porsi_standar'] ?? 1);
        if ($porsi <= 0) $porsi = 1;

        // Get BOM details with current price
        $items = $db->table('resep_detail rd')
            ->select('rd.bahan_baku_id, rd.qty_per_porsi, rd.satuan, bb.nama as nama_bahan, bb.harga_satuan, bb.satuan as satuan_bahan')
            ->join('bahan_baku bb', 'bb.id = rd.bahan_baku_id', 'left')
            ->where('rd.resep_id', $resepId)
            ->get()->getResultArray();

        $totalHpp = 0;
        $hasMissingPrice = false;
        $resultItems = [];

        foreach ($items as $item) {
            $harga        = (float) ($item['harga_satuan'] ?? 0);
            $qtyPerPorsi  = (float) ($item['qty_per_porsi'] ?? 0);
            $totalQty     = $qtyPerPorsi * $porsi;
            $subtotal     = $harga * $totalQty;

            if ($harga <= 0) {
                $hasMissingPrice = true;
            }

            $totalHpp += $subtotal;
            $resultItems[] = [
                'bahan_baku_id' => $item['bahan_baku_id'],
                'nama_bahan'    => $item['nama_bahan'],
                'qty_per_porsi' => $qtyPerPorsi,
                'satuan'        => $item['satuan'] ?: $item['satuan_bahan'],
                'total_qty'     => $totalQty,
                'harga_satuan'  => $harga,
                'subtotal'      => $subtotal,
            ];
        }

        $hppPerPorsi = $porsi > 0 ? $totalHpp / $porsi : 0;

        return [
            'resep_id'        => $resepId,
            'nama_menu'       => $resep['nama_menu'],
            'porsi'           => $porsi,
            'porsi_standar'   => (int) $resep['porsi_standar'],
            'total_hpp'       => $totalHpp,
            'hpp_per_porsi'   => $hppPerPorsi,
            'items'           => $resultItems,
            'has_missing_price' => $hasMissingPrice,
        ];
    }

    /**
     * Estimasi kebutuhan bahan baku untuk satu periode siklus
     * berdasarkan total porsi per hari (jumlah sekolah × porsi)
     *
     * @param int $siklusId   ID jadwal siklus
     * @param int $porsiPerHari Total porsi per hari
     * @return array           Kebutuhan bahan baku per hari
     */
    public static function estimatiBahanSiklus(int $siklusId, int $porsiPerHari): array
    {
        $db = \Config\Database::connect();

        // Get siklus details (day → resep mapping)
        $details = $db->table('jadwal_siklus_detail jsd')
            ->select('jsd.hari_ke, jsd.resep_id, r.nama_menu, r.porsi_standar')
            ->join('resep r', 'r.id = jsd.resep_id', 'left')
            ->where('jsd.siklus_id', $siklusId)
            ->orderBy('jsd.hari_ke')
            ->get()->getResultArray();

        $result = [];
        foreach ($details as $detail) {
            $hpp = self::calculate((int) $detail['resep_id'], $porsiPerHari);
            $result[] = [
                'hari_ke'    => $detail['hari_ke'],
                'resep_id'   => $detail['resep_id'],
                'nama_menu'  => $detail['nama_menu'],
                'porsi'      => $porsiPerHari,
                'hpp'        => $hpp,
            ];
        }

        return $result;
    }
}
