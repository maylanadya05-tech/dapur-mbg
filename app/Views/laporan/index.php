<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Analisis & Laporan</h1>
    <p class="page-subtitle">Pusat penarikan data, grafik analitik, dan pelaporan operasional dapur</p>
  </div>
</div>

<div x-data="laporanDashboard()">
  
  <!-- ══ CONTROLS CARD ══ -->
  <div class="card" style="margin-bottom: 2rem;">
    <div style="display:grid;grid-template-columns:1.2fr 1.5fr 1fr;gap:1.5rem;align-items:end;flex-wrap:wrap;">
      
      <!-- Report Type Selector -->
      <div class="form-group" style="margin:0;">
        <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Jenis Laporan</label>
        <select
          class="form-select"
          x-model="reportType"
          @change="onReportTypeChange"
          style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
        >
          <option value="produksi">📊 Laporan Volume Produksi</option>
          <option value="distribusi">🚚 Laporan Pengiriman & Distribusi</option>
          <option value="stok">📦 Laporan Mutasi Stok Gudang</option>
          <option value="waste">🗑 Laporan Kerugian Food Waste</option>
        </select>
      </div>

      <!-- Date Range Picker -->
      <div class="form-group" style="margin:0;">
        <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Periode Tanggal</label>
        <div style="display:flex;align-items:center;gap:0.75rem;">
          <input
            type="date"
            class="form-control"
            x-model="startDate"
            @change="reloadChartData"
            style="width:100%;padding:0.5rem 0.75rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
          <span style="color:var(--text-muted);">s/d</span>
          <input
            type="date"
            class="form-control"
            x-model="endDate"
            @change="reloadChartData"
            style="width:100%;padding:0.5rem 0.75rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
        </div>
      </div>

      <!-- Export Buttons -->
      <div style="display:flex;gap:0.5rem;">
        <button @click="exportData('pdf')" class="btn btn-secondary" style="flex:1;display:inline-flex;align-items:center;justify-content:center;gap:0.375rem;">
          <i data-lucide="file-text" style="width:16px;height:16px;"></i> Export PDF
        </button>
        <button @click="exportData('excel')" class="btn btn-primary" style="flex:1;display:inline-flex;align-items:center;justify-content:center;gap:0.375rem;">
          <i data-lucide="sheet" style="width:16px;height:16px;"></i> Export Excel
        </button>
      </div>

    </div>
  </div>

  <!-- ══ ANALYTICS PREVIEW PANEL ══ -->
  <div style="display:grid;grid-template-columns:2fr 1fr;gap:2rem;align-items:start;">
    
    <!-- Chart Preview Card -->
    <div class="card" style="min-height: 400px; display:flex; flex-direction:column; justify-content:space-between; position:relative;">
      
      <!-- Loading Overlay -->
      <div x-show="loading" style="position:absolute;top:0;left:0;right:0;bottom:0;background:var(--bg-overlay);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;border-radius:var(--border-radius-lg);z-index:10;" x-transition>
        <div style="text-align:center;">
          <div style="width:36px;height:36px;border:3px solid var(--border-subtle);border-top-color:var(--emerald);border-radius:50%;animation:spin 1s linear infinite;margin:0 auto 0.5rem;"></div>
          <span style="font-size:0.8rem;color:var(--text-secondary);">Memproses data analitik...</span>
        </div>
      </div>

      <div class="card-header" style="margin-bottom:1.5rem;border-bottom:1px solid var(--border-subtle);padding-bottom:0.75rem;">
        <div>
          <h3 class="card-title" x-text="chartTitle">Laporan Volume Produksi</h3>
          <p class="card-subtitle">Visualisasi data operasional harian terverifikasi.</p>
        </div>
        <span class="badge badge-success" style="font-size:0.7rem;text-transform:uppercase;">PREVIEW GRAFIK</span>
      </div>

      <!-- Chart JS Canvas Wrapper -->
      <div style="flex:1;position:relative;height:280px;display:flex;align-items:center;justify-content:center;">
        <canvas id="analyticsChart" style="max-height: 100%; width: 100%;"></canvas>
      </div>

    </div>

    <!-- Quick Insights Summary Card -->
    <div style="display:flex;flex-direction:column;gap:1.5rem;">
      
      <!-- Metrics Card -->
      <div class="card">
        <h3 class="card-title" style="margin-bottom:1rem;color:var(--text-primary);display:flex;align-items:center;gap:0.5rem;">
          <i data-lucide="zap" style="color:var(--emerald);"></i> Ringkasan Parameter
        </h3>
        
        <div style="display:flex;flex-direction:column;gap:1rem;font-size:0.875rem;">
          <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--border-subtle);padding-bottom:0.5rem;">
            <span style="color:var(--text-secondary);">Parameter Aktif:</span>
            <strong style="color:var(--text-primary);" x-text="formatReportLabel()">Produksi</strong>
          </div>
          <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--border-subtle);padding-bottom:0.5rem;">
            <span style="color:var(--text-secondary);">Rentang Waktu:</span>
            <strong style="color:var(--text-primary);" x-text="formatDateRange()">15 Hari</strong>
          </div>
          <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--border-subtle);padding-bottom:0.5rem;">
            <span style="color:var(--text-secondary);">Akumulasi Nilai:</span>
            <strong style="color:var(--emerald-light);" x-text="accumulatedValue">Rp 0</strong>
          </div>
          <div style="display:flex;justify-content:space-between;padding-bottom:0.25rem;">
            <span style="color:var(--text-secondary);">Status Data:</span>
            <span class="badge badge-success" style="font-size:0.7rem;">TERKUNCI / AMAN</span>
          </div>
        </div>
      </div>

      <!-- Export Instructions Card -->
      <div class="card" style="background:var(--bg-card-hover);">
        <h4 style="font-size:0.875rem;font-weight:700;color:var(--text-primary);margin-bottom:0.5rem;display:flex;align-items:center;gap:0.5rem;">
          <i data-lucide="info" style="color:var(--emerald);"></i> Panduan Ekspor
        </h4>
        <p style="font-size:0.78rem;color:var(--text-secondary);line-height:1.5;">
          Format <strong>Excel</strong> mengekspor seluruh entitas baris data mentah beserta rumus perhitungan dasar, sedangkan <strong>PDF</strong> mengekspor format cetak dokumen resmi berlogo kop surat.
        </p>
      </div>

    </div>

  </div>

</div>

<!-- Spinner Animation -->
<style>
  @keyframes spin { 100% { transform: rotate(360deg); } }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function laporanDashboard() {
    return {
      reportType: 'produksi',
      startDate: '2026-06-01',
      endDate: '2026-06-15',
      loading: false,
      chartTitle: 'Laporan Volume Produksi',
      accumulatedValue: '124,500 Porsi',
      chartInstance: null,

      init() {
        this.renderChart();
      },

      onReportTypeChange() {
        this.loading = true;
        this.chartTitle = this.formatReportLabel() + ' — Grafik';
        
        // Update mock summary values
        if (this.reportType === 'produksi') {
          this.accumulatedValue = '124,500 Porsi';
        } else if (this.reportType === 'distribusi') {
          this.accumulatedValue = '123,800 Sukses (99.4%)';
        } else if (this.reportType === 'stok') {
          this.accumulatedValue = 'Rp 1.450.000.000 (Nilai Stok)';
        } else if (this.reportType === 'waste') {
          this.accumulatedValue = 'Rp 3.420.000 (Kerugian)';
        }

        setTimeout(() => {
          this.loading = false;
          this.renderChart();
        }, 600);
      },

      reloadChartData() {
        this.loading = true;
        setTimeout(() => {
          this.loading = false;
          this.renderChart();
        }, 400);
      },

      formatReportLabel() {
        return {
          'produksi': 'Volume Produksi',
          'distribusi': 'Volume Distribusi',
          'stok': 'Mutasi Stok Gudang',
          'waste': 'Kerugian Food Waste'
        }[this.reportType] || '';
      },

      formatDateRange() {
        if(!this.startDate || !this.endDate) return '-';
        const start = new Date(this.startDate);
        const end = new Date(this.endDate);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        return diffDays + ' Hari Kerja';
      },

      renderChart() {
        const ctx = document.getElementById('analyticsChart')?.getContext('2d');
        if (!ctx) return;

        // Destroy existing chart to avoid overlay bugs
        if (this.chartInstance) {
          this.chartInstance.destroy();
        }

        // Mock chart datasets based on type
        let labelData = ['01 Jun', '03 Jun', '05 Jun', '07 Jun', '09 Jun', '11 Jun', '13 Jun', '15 Jun'];
        let chartData = [];
        let type = 'line';
        let borderColor = 'hsl(150, 84%, 37%)';
        let backgroundColor = 'hsla(150, 84%, 37%, 0.15)';

        if (this.reportType === 'produksi') {
          chartData = [8000, 8200, 8100, 8500, 9000, 9200, 9400, 9500];
          type = 'line';
        } else if (this.reportType === 'distribusi') {
          chartData = [7900, 8150, 8050, 8450, 8900, 9100, 9350, 9450];
          borderColor = 'hsl(210, 100%, 56%)';
          backgroundColor = 'hsla(210, 100%, 56%, 0.15)';
          type = 'line';
        } else if (this.reportType === 'stok') {
          labelData = ['Beras (10kg)', 'Ayam Fillet (kg)', 'Minyak (Lt)', 'Bayam (kg)', 'Gula (kg)', 'Telur (kg)'];
          chartData = [250, 30, 80, 15, 45, 500];
          borderColor = 'hsl(43, 96%, 56%)';
          backgroundColor = 'hsla(43, 96%, 56%, 0.2)';
          type = 'bar';
        } else if (this.reportType === 'waste') {
          labelData = ['05 Jun', '07 Jun', '09 Jun', '11 Jun', '13 Jun', '15 Jun'];
          chartData = [120000, 450000, 90000, 320000, 180000, 240000];
          borderColor = 'hsl(0, 84%, 60%)';
          backgroundColor = 'hsla(0, 84%, 60%, 0.2)';
          type = 'bar';
        }

        this.chartInstance = new Chart(ctx, {
          type: type,
          data: {
            labels: labelData,
            datasets: [{
              label: this.formatReportLabel(),
              data: chartData,
              borderColor: borderColor,
              backgroundColor: backgroundColor,
              borderWidth: 2,
              fill: true,
              tension: 0.3,
              borderRadius: type === 'bar' ? 4 : 0
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                grid: {
                  color: 'hsla(215, 28%, 18%, 0.5)'
                },
                ticks: {
                  color: 'hsl(215, 16%, 60%)',
                  font: {
                    family: 'Plus Jakarta Sans',
                    size: 10
                  }
                }
              },
              x: {
                grid: {
                  display: false
                },
                ticks: {
                  color: 'hsl(215, 16%, 60%)',
                  font: {
                    family: 'Plus Jakarta Sans',
                    size: 10
                  }
                }
              }
            }
          }
        });
      },

      exportData(format) {
        alert('Mengunduh dokumen laporan ' + this.formatReportLabel() + ' dalam format ' + format.toUpperCase() + '...');
        // In actual implementation, redirect to export controller:
        // window.location.href = '<?= base_url('/laporan/export/') ?>' + this.reportType + '/' + format + '?start=' + this.startDate + '&end=' + this.endDate;
      }
    };
  }
</script>
<?= $this->endSection() ?>
