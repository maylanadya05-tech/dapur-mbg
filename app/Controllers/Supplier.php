<?php

namespace App\Controllers;

use App\Models\SupplierModel;
use CodeIgniter\HTTP\ResponseInterface;

class Supplier extends BaseController
{
    /**
     * List suppliers, categories, and ratings.
     * Computes the supplier rating scorecard based on recent POs and waste events.
     */
    public function index(): string
    {
        $supplierModel = new SupplierModel();
        
        $suppliers = $supplierModel->orderBy('name', 'ASC')->findAll();
        
        // Compute scorecard for each supplier
        $scorecards = [];
        foreach ($suppliers as &$supplier) {
            $scorecard = $this->calculateScorecard((int)$supplier['id']);
            $supplier['calculated_stats'] = $scorecard;
            // update local array reference with calculated rating
            $supplier['rating'] = $scorecard['rating'];
        }

        return view('supplier/index', [
            'title'     => 'Manajemen Supplier – Dapur MBG',
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Form to add supplier.
     */
    public function create(): string
    {
        return view('supplier/create', [
            'title' => 'Tambah Supplier Baru – Dapur MBG',
        ]);
    }

    /**
     * Store new supplier.
     */
    public function store()
    {
        $supplierModel = new SupplierModel();

        $data = [
            'name'           => $this->request->getPost('name'),
            'contact_person' => $this->request->getPost('contact_person'),
            'phone'          => $this->request->getPost('phone'),
            'email'          => $this->request->getPost('email'),
            'address'        => $this->request->getPost('address'),
            'kategori'       => $this->request->getPost('kategori'),
            'rating'         => 5.0, // default rating
            'is_active'      => $this->request->getPost('is_active') !== null ? (int)$this->request->getPost('is_active') : 1,
        ];

        if (!$this->validate($supplierModel->validationRules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        if (!$supplierModel->insert($data)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $supplierModel->errors());
        }

        return redirect()->to('/supplier')
            ->with('success', 'Supplier ' . $data['name'] . ' berhasil ditambahkan.');
    }

    /**
     * Edit supplier form.
     */
    public function edit(int $id)
    {
        $supplierModel = new SupplierModel();
        $supplier = $supplierModel->find($id);

        if (!$supplier) {
            return redirect()->to('/supplier')
                ->with('error', 'Supplier tidak ditemukan.');
        }

        $scorecard = $this->calculateScorecard($id);

        return view('supplier/edit', [
            'title'     => 'Edit Supplier – Dapur MBG',
            'supplier'  => $supplier,
            'scorecard' => $scorecard,
        ]);
    }

    /**
     * Update supplier.
     */
    public function update(int $id)
    {
        $supplierModel = new SupplierModel();
        $supplier = $supplierModel->find($id);

        if (!$supplier) {
            return redirect()->to('/supplier')
                ->with('error', 'Supplier tidak ditemukan.');
        }

        $data = [
            'name'           => $this->request->getPost('name'),
            'contact_person' => $this->request->getPost('contact_person'),
            'phone'          => $this->request->getPost('phone'),
            'email'          => $this->request->getPost('email'),
            'address'        => $this->request->getPost('address'),
            'kategori'       => $this->request->getPost('kategori'),
            'is_active'      => $this->request->getPost('is_active') !== null ? (int)$this->request->getPost('is_active') : 0,
        ];

        if (!$this->validate($supplierModel->validationRules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        if (!$supplierModel->update($id, $data)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $supplierModel->errors());
        }

        // Recalculate scorecard
        $this->calculateScorecard($id);

        return redirect()->to('/supplier')
            ->with('success', 'Supplier ' . $data['name'] . ' berhasil diperbarui.');
    }

    /**
     * Soft delete supplier.
     */
    public function delete(int $id)
    {
        $supplierModel = new SupplierModel();
        $supplier = $supplierModel->find($id);

        if (!$supplier) {
            return redirect()->to('/supplier')
                ->with('error', 'Supplier tidak ditemukan.');
        }

        $supplierModel->delete($id);

        return redirect()->to('/supplier')
            ->with('success', 'Supplier ' . $supplier['name'] . ' berhasil dihapus.');
    }

    /**
     * Compute the supplier rating scorecard based on recent POs and waste events.
     */
    private function calculateScorecard(int $supplierId): array
    {
        $db = \Config\Database::connect();

        // 1. PO Statistics (Created vs Completed)
        $poStats = $db->table('purchase_orders')
            ->select('COUNT(id) as total_po, SUM(CASE WHEN status = "selesai" THEN 1 ELSE 0 END) as selesai_po')
            ->where('supplier_id', $supplierId)
            ->get()
            ->getRowArray();

        $totalPo = (int)($poStats['total_po'] ?? 0);
        $selesaiPo = (int)($poStats['selesai_po'] ?? 0);

        // 2. Fulfillment rate from completed PO items (qty received vs qty ordered)
        $fulfillment = $db->table('po_detail pd')
            ->select('pd.qty, pd.qty_diterima')
            ->join('purchase_orders po', 'po.id = pd.po_id')
            ->where('po.supplier_id', $supplierId)
            ->where('po.status', 'selesai')
            ->get()
            ->getResultArray();

        $totalItems = count($fulfillment);
        $totalRatio = 0.0;
        if ($totalItems > 0) {
            foreach ($fulfillment as $item) {
                $ordered = (float)$item['qty'];
                $received = (float)$item['qty_diterima'];
                if ($ordered > 0) {
                    $totalRatio += min(1.0, $received / $ordered);
                }
            }
            $fulfillmentRate = ($totalRatio / $totalItems) * 100;
        } else {
            $fulfillmentRate = 100.0; // Default perfect rate if no completed orders
        }

        // 3. Food waste events related to this supplier's raw materials
        $wasteStats = $db->table('food_waste fw')
            ->select('COUNT(fw.id) as total_events, SUM(fw.qty) as total_qty, SUM(fw.estimasi_nilai) as total_nilai')
            ->join('bahan_baku bb', 'bb.id = fw.bahan_baku_id')
            ->where('bb.supplier_id', $supplierId)
            ->get()
            ->getRowArray();

        $wasteEvents = (int)($wasteStats['total_events'] ?? 0);
        $wasteQty = (float)($wasteStats['total_qty'] ?? 0);
        $wasteNilai = (float)($wasteStats['total_nilai'] ?? 0);

        // 4. Calculate Scorecard Rating (Base 5.0)
        // Deduct points proportional to fulfillment rate (e.g. 90% fulfillment = -0.5 points)
        $shortfallDeduction = (1.0 - ($fulfillmentRate / 100.0)) * 5.0;

        // Deduct 0.25 points per ingredient waste incident, capped at 2.5 points deduction
        $wasteDeduction = min(2.5, $wasteEvents * 0.25);

        $calculatedRating = 5.0 - $shortfallDeduction - $wasteDeduction;
        $calculatedRating = max(1.0, min(5.0, round($calculatedRating, 2)));

        // Cache the calculated score back to the database
        $db->table('supplier')
            ->where('id', $supplierId)
            ->update(['rating' => $calculatedRating]);

        return [
            'total_po'         => $totalPo,
            'selesai_po'       => $selesaiPo,
            'fulfillment_rate' => round($fulfillmentRate, 2),
            'waste_events'     => $wasteEvents,
            'waste_qty'        => $wasteQty,
            'waste_nilai'      => $wasteNilai,
            'rating'           => $calculatedRating,
        ];
    }
}
