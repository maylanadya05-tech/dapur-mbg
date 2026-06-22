<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
$feedbackList = $feedbackList ?? [];
if (empty($feedbackList)) {
  $feedbackList = [
    [
      'id' => 1,
      'sekolah_nama' => 'SDN Merdeka 01',
      'batch_no' => 'BCH-2606-A',
      'rating' => 5,
      'komentar' => 'Makanannya sangat disukai siswa. Porsi nasi pas, ayam goreng sangat gurih dan matang merata. Kebersihan wadah luar biasa bersih.',
      'tanggal' => '2026-06-21',
      'nama_perwakilan' => 'H. Mulyadi, S.Pd.',
      'rasa' => 5, 'porsi' => 5, 'kebersihan' => 5, 'ketepatan_waktu' => 5, 'keseluruhan' => 5
    ],
    [
      'id' => 2,
      'sekolah_nama' => 'SMP Negeri 1 Bogor',
      'batch_no' => 'BCH-2606-A',
      'rating' => 4,
      'komentar' => 'Rasa ayam sangat lezat, namun porsi sayur dirasa kurang sedikit untuk anak SMP. Waktu pengiriman sangat tepat.',
      'tanggal' => '2026-06-21',
      'nama_perwakilan' => 'Dra. Sri Wahyuni',
      'rasa' => 5, 'porsi' => 3, 'kebersihan' => 5, 'ketepatan_waktu' => 5, 'keseluruhan' => 4
    ],
    [
      'id' => 3,
      'sekolah_nama' => 'SMA Negeri 2 Bogor',
      'batch_no' => 'BCH-2606-B',
      'rating' => 3,
      'komentar' => 'Lauk utama daging semur rasanya agak terlalu manis bagi siswa. Waktu pengantaran terlambat 15 menit dari jadwal.',
      'tanggal' => '2026-06-21',
      'nama_perwakilan' => 'Ir. Hermawan',
      'rasa' => 3, 'porsi' => 4, 'kebersihan' => 4, 'ketepatan_waktu' => 2, 'keseluruhan' => 3
    ]
  ];
}

// Calculate averages
$totalRatings = count($feedbackList);
$avgRating = $totalRatings > 0 ? array_sum(array_column($feedbackList, 'rating')) / $totalRatings : 0;
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Feedback & Ulasan Sekolah</h1>
    <p class="page-subtitle">Penilaian kualitas porsi Makan Bergizi Gratis langsung dari perwakilan sekolah</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= base_url('/feedback/create') ?>" class="btn btn-primary btn-sm">
      <i data-lucide="plus"></i>
      Isi Feedback Baru
    </a>
  </div>
</div>

<!-- ══ RATING SCOREBOARD CARD ══ -->
<div class="stats-grid" style="display:grid;grid-template-columns:1.2fr 2fr;gap:1.5rem;margin-bottom:2rem;">
  
  <!-- Average Big Score -->
  <div class="card" style="display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;">
    <div style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.06em;color:var(--text-muted);margin-bottom:0.5rem;font-weight:700;">Rating Keseluruhan</div>
    <div style="font-size:3.5rem;font-weight:800;color:var(--emerald);line-height:1;margin-bottom:0.5rem;"><?= number_format($avgRating, 1) ?></div>
    
    <!-- Render Star icons -->
    <div style="display:flex;gap:0.25rem;color:var(--status-warning);margin-bottom:0.5rem;">
      <?php for($i=1; $i<=5; $i++): ?>
        <i data-lucide="star" style="width:20px;height:20px;<?= $i <= round($avgRating) ? 'fill:var(--status-warning);' : 'opacity:0.3;' ?>"></i>
      <?php endfor; ?>
    </div>
    <span style="font-size:0.8rem;color:var(--text-secondary);">Berdasarkan <?= $totalRatings ?> ulasan terverifikasi</span>
  </div>

  <!-- Aspect Breakdowns -->
  <div class="card" style="display:flex;flex-direction:column;justify-content:center;gap:0.75rem;">
    <h4 style="font-size:0.875rem;color:var(--text-primary);font-weight:700;margin-bottom:0.25rem;">Rata-Rata Berdasarkan Aspek Penilaian</h4>
    
    <?php
    $aspects = ['Rasa' => 'rasa', 'Porsi' => 'porsi', 'Kebersihan' => 'kebersihan', 'Ketepatan Waktu' => 'ketepatan_waktu'];
    foreach($aspects as $label => $key):
      $avgAspect = $totalRatings > 0 ? array_sum(array_column($feedbackList, $key)) / $totalRatings : 0;
      $pct = ($avgAspect / 5) * 100;
    ?>
    <div>
      <div style="display:flex;justify-content:space-between;font-size:0.8rem;margin-bottom:0.25rem;">
        <span style="color:var(--text-secondary);font-weight:600;"><?= $label ?></span>
        <span style="color:var(--text-primary);font-weight:700;"><?= number_format($avgAspect, 1) ?>/5.0</span>
      </div>
      <div class="progress-bar" style="height:6px;width:100%;background:var(--border-subtle);border-radius:4px;overflow:hidden;">
        <div class="progress-fill" style="height:100%;width:<?= $pct ?>%;background:var(--emerald);border-radius:4px;"></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

</div>

<!-- ══ TREND CHART CARD ══ -->
<div class="card" style="margin-bottom: 2rem;">
  <div class="card-header" style="margin-bottom: 1rem;">
    <h3 class="card-title">Tren Rating Umpan Balik</h3>
    <p class="card-subtitle">Grafik rata-rata rating bulanan per sekolah sasaran</p>
  </div>
  <div style="height: 280px; position: relative;">
    <canvas id="feedbackTrendChart"></canvas>
  </div>
</div>

<!-- ══ GRID OF FEEDBACK CARDS ══ -->
<h3 style="font-size:1.1rem;font-weight:700;margin-bottom:1rem;color:var(--text-primary);">Daftar Ulasan Masuk</h3>

<div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(360px, 1fr));gap:1.5rem;">
  <?php foreach ($feedbackList as $item): ?>
  <div class="card" style="display:flex;flex-direction:column;justify-content:space-between;height:100%;">
    <div>
      <!-- Card Header -->
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.75rem;">
        <div>
          <h4 style="font-size:0.95rem;font-weight:700;color:var(--text-primary);"><?= esc($item['sekolah_nama']) ?></h4>
          <span style="font-size:0.75rem;color:var(--text-muted);">Batch: <?= esc($item['batch_no']) ?></span>
        </div>
        <!-- Stars -->
        <div style="display:flex;gap:0.125rem;color:var(--status-warning);">
          <?php for($i=1; $i<=5; $i++): ?>
            <i data-lucide="star" style="width:14px;height:14px;<?= $i <= $item['rating'] ? 'fill:var(--status-warning);' : 'opacity:0.3;' ?>"></i>
          <?php endfor; ?>
        </div>
      </div>

      <!-- Aspect Badges -->
      <div style="display:flex;flex-wrap:wrap;gap:0.375rem;margin-bottom:1rem;">
        <span class="badge" style="font-size:0.68rem;background:var(--bg-card-hover);color:var(--text-secondary);border:1px solid var(--border-subtle);">Rasa: <?= $item['rasa'] ?>★</span>
        <span class="badge" style="font-size:0.68rem;background:var(--bg-card-hover);color:var(--text-secondary);border:1px solid var(--border-subtle);">Porsi: <?= $item['porsi'] ?>★</span>
        <span class="badge" style="font-size:0.68rem;background:var(--bg-card-hover);color:var(--text-secondary);border:1px solid var(--border-subtle);">Bersih: <?= $item['kebersihan'] ?>★</span>
        <span class="badge" style="font-size:0.68rem;background:var(--bg-card-hover);color:var(--text-secondary);border:1px solid var(--border-subtle);">Waktu: <?= $item['ketepatan_waktu'] ?>★</span>
      </div>

      <!-- Comment -->
      <p style="font-size:0.875rem;color:var(--text-secondary);line-height:1.6;font-style:italic;margin-bottom:1.25rem;">
        "<?= esc($item['komentar']) ?>"
      </p>
    </div>

    <!-- Footer of Card -->
    <div style="display:flex;justify-content:space-between;align-items:center;border-top:1px solid var(--border-subtle);padding-top:0.75rem;margin-top:auto;">
      <div>
        <div style="font-size:0.78rem;font-weight:700;color:var(--text-primary);"><?= esc($item['nama_perwakilan']) ?></div>
        <div style="font-size:0.72rem;color:var(--text-muted);"><?= date('d F Y', strtotime($item['tanggal'])) ?></div>
      </div>
      <a href="<?= base_url('/feedback/show/' . $item['id']) ?>" class="btn btn-secondary btn-icon btn-sm" title="Detail Aspek & Koreksi">
        <i data-lucide="eye" style="width:16px;height:16px;"></i>
      </a>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  fetch('<?= base_url('/feedback/chartData') ?>')
    .then(res => res.json())
    .then(res => {
      const rawData = res.success && res.data ? res.data : [];
      
      // Get unique months and unique schools
      const months = [...new Set(rawData.map(d => d.bulan))].sort();
      const schools = [...new Set(rawData.map(d => d.nama_sekolah))];
      
      const datasets = schools.map((school, i) => {
        const colors = [
          'hsl(150, 84%, 37%)', // Emerald
          'hsl(210, 100%, 56%)', // Blue
          'hsl(35, 100%, 52%)',  // Orange
          'hsl(340, 82%, 52%)',  // Pink/Red
          'hsl(270, 76%, 53%)',  // Purple
        ];
        const color = colors[i % colors.length];
        
        const data = months.map(m => {
          const entry = rawData.find(d => d.nama_sekolah === school && d.bulan === m);
          return entry ? parseFloat(entry.avg_rating) : null;
        });
        
        return {
          label: school,
          data: data,
          borderColor: color,
          backgroundColor: color,
          tension: 0.3,
          fill: false,
          spanGaps: true
        };
      });

      // If data is empty, populate demo data
      const finalMonths = months.length > 0 ? months : ['2026-01', '2026-02', '2026-03', '2026-04', '2026-05', '2026-06'];
      const finalDatasets = datasets.length > 0 ? datasets : [
        {
          label: 'SDN Merdeka 01',
          data: [4.2, 4.5, 4.3, 4.8, 4.7, 5.0],
          borderColor: 'hsl(150, 84%, 37%)',
          backgroundColor: 'hsl(150, 84%, 37%)',
          tension: 0.3,
          fill: false
        },
        {
          label: 'SMP Negeri 1 Bogor',
          data: [4.0, 4.2, 4.1, 4.0, 4.3, 4.2],
          borderColor: 'hsl(210, 100%, 56%)',
          backgroundColor: 'hsl(210, 100%, 56%)',
          tension: 0.3,
          fill: false
        }
      ];
      
      const ctx = document.getElementById('feedbackTrendChart').getContext('2d');
      new Chart(ctx, {
        type: 'line',
        data: {
          labels: finalMonths.map(m => {
            const parts = m.split('-');
            const dateObj = new Date(parts[0], parts[1] - 1, 1);
            return dateObj.toLocaleString('id-ID', { month: 'short', year: '2-digit' });
          }),
          datasets: finalDatasets
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                color: 'hsl(215, 16%, 60%)',
                font: { family: "'Plus Jakarta Sans', sans-serif", weight: '600' }
              }
            },
            tooltip: {
              backgroundColor: 'hsl(218, 30%, 13%)',
              borderColor: 'hsl(215, 28%, 22%)',
              borderWidth: 1,
              titleColor: 'hsl(210, 40%, 98%)',
              bodyColor: 'hsl(215, 16%, 60%)',
              cornerRadius: 8,
              padding: 12
            }
          },
          scales: {
            x: {
              grid: { color: 'hsla(215, 28%, 18%, 0.5)', drawBorder: false },
              ticks: { color: 'hsl(215, 16%, 60%)', font: { weight: '600' } },
              border: { display: false }
            },
            y: {
              grid: { color: 'hsla(215, 28%, 18%, 0.5)', drawBorder: false },
              ticks: { color: 'hsl(215, 16%, 60%)' },
              border: { display: false },
              min: 1,
              max: 5
            }
          }
        }
      });
    });
});
</script>
<?= $this->endSection() ?>
