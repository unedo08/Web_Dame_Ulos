<template>
  <div class="grafik-benang">
    <title>Grafik Benang</title>
    <section class="filter-section">
      <h1 class="page-title">Grafik Benang</h1>

      <!-- FILTER BAR -->
      <div class="filter-bar">
        <h2 class="filter-heading">Filter</h2>
        <div class="filter-group filter-category">
          <label class="filter-label">Kategori</label>
          <select v-model="tipe" class="filter-input filter-select" @change="fetchAll">
            <option v-if="!pewarnaList.length" value="PEWARNA_ALAM">Pewarna Alam</option>
            <option v-for="item in pewarnaList" :key="item.pewarna_id" :value="item.pewarna_kode">
              {{ item.pewarna_nama }}
            </option>
          </select>
        </div>
        <div class="filter-period">
          <div class="filter-group">
            <label class="filter-label">Tahun</label>
            <input type="number" v-model.number="tahun" class="filter-input filter-input-year" min="2020"
              :max="currentYear" @change="fetchPertahun" />
          </div>
          <div class="filter-group">
            <label class="filter-label">Bulan</label>
            <input type="month" v-model="bulanInput" class="filter-input" @change="fetchPerbulan" />
          </div>
        </div>
      </div>
    </section>

    <!-- GRAFIK PERTAHUN -->
    <div class="chart-grid-line">
      <div class="chart-card">
        <div class="chart-header">
          <div>
            <div class="chart-title">
              <div class="chart-title-row">
                <span>Tren Produksi Benang &amp; Benang Keluar</span>
                <span class="chart-info-wrap">
                  <button class="chart-info-button" type="button" aria-label="Keterangan grafik"
                    aria-describedby="line-chart-description">i</button>
                  <span id="line-chart-description" class="chart-info-tooltip" role="tooltip">
                    {{ lineDescription }}
                  </span>
                </span>
              </div>
              <span class="chart-year">{{ pertahun?.tahun || tahun }}</span>
            </div>
          </div>
          <span class="tipe-badge">{{ tipeLabel }}</span>
        </div>
        <div v-if="isLoadingTahun" class="loading-placeholder">Menunggu data...</div>
        <div v-else-if="!pertahun" class="no-data-placeholder">Data tidak tersedia</div>
        <div v-else class="chart-horizontal-scroll">
          <div class="chart-horizontal-content chart-content-line">
            <div class="chart-inner chart-inner-line">
              <Line :data="lineData" :options="lineOptions" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- GRAFIK PERBULAN -->
    <div class="chart-grid">
      <div v-for="chart in barCharts" :key="chart.key" class="chart-card">
        <div class="chart-header">
          <div>
            <div class="chart-title">
              <div class="chart-title-row">
                <span>{{ chart.title }}</span>
                <span class="chart-info-wrap">
                  <button class="chart-info-button" type="button" aria-label="Keterangan grafik"
                    :aria-describedby="`description-${chart.key}`">i</button>
                  <span :id="`description-${chart.key}`" class="chart-info-tooltip" role="tooltip">
                    {{ chart.subtitle }}
                  </span>
                </span>
              </div>
              <span class="chart-year">{{ bulanTitle }}</span>
            </div>
          </div>
          <span class="tipe-badge">{{ tipeLabel }}</span>
        </div>
        <div v-if="isLoadingBulan" class="loading-placeholder">Menunggu data...</div>
        <div v-else-if="!chart.rows.length" class="no-data-placeholder">Data tidak tersedia</div>
        <div v-else class="chart-horizontal-scroll">
          <div class="chart-horizontal-content chart-content-bar">
            <div class="chart-inner">
              <Bar :data="barData(chart.rows)" :options="chart.options" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { Line, Bar } from "vue-chartjs";
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  BarElement,
  CategoryScale,
  LinearScale
} from "chart.js";
import { useGrafikBenang } from "~/composables/useGrafikBenang";

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  BarElement,
  CategoryScale,
  LinearScale
);

const {
  currentYear,
  tipe,
  tahun,
  bulanInput,
  pewarnaList,
  pertahun,
  perbulan,
  isLoadingTahun,
  isLoadingBulan,
  fetchPertahun,
  fetchPerbulan,
  fetchAll,
} = useGrafikBenang();

const tipeLabel = computed(() => {
  const found = pewarnaList.value.find((p) => p.pewarna_kode === tipe.value);
  return found ? found.pewarna_nama : "Pewarna Alam";
});

// Textile is bought (pembelian), Pewarna Alam is dyed in-house (produksi).
const isTextile = computed(() => tipe.value === "TEXTILE");
const masukLabel = computed(() => (isTextile.value ? "Pembelian" : "Produksi"));
const lineDescription = computed(() =>
  `Perbandingan ${masukLabel.value.toLowerCase()} benang dan benang keluar setiap bulan`
);

const bulanTitle = computed(() =>
  perbulan.value ? `${perbulan.value.bulan_label} ${perbulan.value.tahun}` : ""
);

// ---------- Line: produksi vs keluar per bulan ----------
const lineData = computed(() => ({
  labels: pertahun.value?.labels || [],
  datasets: [
    {
      label: `${masukLabel.value} Benang`,
      data: pertahun.value?.produksi || [],
      borderColor: "#3b82f6",
      backgroundColor: "#3b82f6",
      tension: 0.4,
      fill: false,
      pointRadius: 4
    },
    {
      label: "Benang Keluar",
      data: pertahun.value?.keluar || [],
      borderColor: "#a78bfa",
      backgroundColor: "#a78bfa",
      tension: 0.4,
      fill: false,
      pointRadius: 4
    }
  ]
}));

const numberTick = (value) => Number(value).toLocaleString("id-ID");

const axisTitle = (text) => ({
  display: true,
  text,
  color: "#475569",
  font: { size: 12, weight: "500" }
});

const lineOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: "top" }
  },
  scales: {
    x: {
      title: axisTitle("Bulan")
    },
    y: {
      beginAtZero: true,
      title: axisTitle("Jumlah Benang"),
      ticks: { precision: 0, callback: numberTick }
    }
  }
};

// ---------- Bar: per warna / per orang dalam sebulan ----------
const barCharts = computed(() => {
  const masuk = masukLabel.value.toLowerCase();
  return [
    {
      key: "produksi_warna",
      title: "Distribusi Benang per Warna",
      subtitle: `Jumlah ${masuk} benang per warna dalam sebulan`,
      rows: perbulan.value?.produksi_warna || [],
      options: barOptions("Warna Benang")
    },
    {
      key: "produksi_orang",
      title: "Produksi Benang Berdasarkan Personel",
      subtitle: `Jumlah ${masuk} benang per orang dalam sebulan`,
      rows: perbulan.value?.produksi_orang || [],
      options: barOptions("Nama Author")
    },
    {
      key: "keluar_warna",
      title: "Benang Keluar Berdasarkan Warna",
      subtitle: "Jumlah benang keluar dalam sebulan per warna benang",
      rows: perbulan.value?.keluar_warna || [],
      options: barOptions("Warna Benang")
    }
  ];
});

const barData = (rows) => ({
  labels: rows.map((r) => r.label),
  datasets: [
    {
      label: "Jumlah",
      data: rows.map((r) => r.total),
      backgroundColor: "#3b82f6",
      borderRadius: 6
    }
  ]
});

function barOptions(xTitle) {
  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false }
    },
    scales: {
      x: {
        title: axisTitle(xTitle)
      },
      y: {
        beginAtZero: true,
        title: axisTitle("Jumlah Benang"),
        ticks: { precision: 0, callback: numberTick }
      }
    }
  };
}
</script>

<style scoped>
.grafik-benang {
  width: 100%;
  max-width: 100%;
  padding: 0 32px 32px;
  background: #f1f5f9;
  min-height: 100vh;
  box-sizing: border-box;
  overflow-x: hidden;
}

.filter-section {
  width: calc(100% + 64px);
  margin: 0 -32px 24px;
  padding: 32px 40px 28px;
  background: #ffffff;
  box-sizing: border-box;
}

.page-title {
  font-size: 1.3rem;
  font-weight: 700;
  color: #111827;
  margin: 0 0 24px;
}

.filter-bar {
  display: grid;
  grid-template-columns: 100px 250px 120px 220px;
  align-items: center;
  gap: 24px;
  width: 100%;
  box-sizing: border-box;
}

.filter-heading {
  margin: 0;
  color: #1e293b;
  font-size: 16px;
  font-weight: 700;
}

.filter-group,
.filter-period {
  display: grid;
  gap: 8px;
  min-width: 0;
}

.filter-period {
  display: contents;
}

.filter-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #475569;
}

.filter-input {
  width: 100%;
  min-width: 0;
  min-height: 42px;
  padding: 10px 16px;
  border-radius: 8px;
  border: 1.5px solid #E4EAF1;
  font-size: 13px;
  color: #1e293b;
  outline: none;
  transition: border-color 0.15s;
  background: #FDFDFF;
  box-sizing: border-box;
}

.filter-input:focus,
.filter-input:focus-visible {
  border-color: #E4EAF1 !important;
  box-shadow: none !important;
  outline: none !important;
}

.filter-select {
  appearance: none;
  -webkit-appearance: none;
  padding-right: 48px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364758b' stroke-width='2.3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m5 9 7 7 7-7'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 16px center;
  background-size: 20px 20px;
}

.filter-input-year {
  width: 100%;
}

.chart-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 24px;
  width: 100%;
  margin-bottom: 32px;
  min-width: 0;
}

.chart-grid-line {
  display: grid;
  grid-template-columns: minmax(0, 1fr);
  width: 100%;
  margin-bottom: 32px;
  min-width: 0;
}

.chart-card {
  width: 100%;
  min-width: 0;
  background: #ffffff;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
  box-sizing: border-box;
  overflow: visible;
}

.chart-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.chart-title {
  display: block;
  font-size: 16px;
  font-weight: 600;
  color: #1e293b;
  line-height: 1.4;
}

.chart-title-row {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
}

.chart-info-wrap {
  position: relative;
  display: inline-flex;
  align-items: center;
}

.chart-info-button {
  display: inline-flex;
  width: 14px;
  height: 14px;
  align-items: center;
  justify-content: center;
  padding: 0;
  border: 1.5px solid #64748b;
  border-radius: 50%;
  background: #ffffff;
  color: #64748b;
  font-size: 9px;
  font-weight: 700;
  line-height: 1;
  cursor: help;
}

.chart-info-button:focus-visible {
  outline: 2px solid #64748b;
  outline-offset: 2px;
}

.chart-info-tooltip {
  position: absolute;
  right: 0;
  bottom: calc(100% + 6px);
  z-index: 20;
  display: none;
  width: max-content;
  max-width: min(280px, calc(100vw - 48px));
  padding: 9px 12px;
  border-radius: 8px;
  border: 1px solid #d9dee8;
  background: #ffffff;
  color: #1e293b;
  box-shadow: 0 6px 18px rgba(15, 23, 42, 0.12);
  font-size: 10.5px;
  font-weight: 400;
  line-height: 1.5;
  text-align: left;
}

.chart-info-tooltip::after {
  position: absolute;
  right: 7px;
  bottom: -5px;
  width: 8px;
  height: 8px;
  border-right: 1px solid #d9dee8;
  border-bottom: 1px solid #d9dee8;
  background: #ffffff;
  content: "";
  transform: rotate(45deg);
}

.chart-info-wrap:hover .chart-info-tooltip,
.chart-info-wrap:focus-within .chart-info-tooltip {
  display: block;
}

.chart-year {
  display: block;
  margin:6px 0px 14px;
  font-size: 13px;
  font-weight: 500;
  color: #64748b;
}

.tipe-badge {
  background: #e9f3ff;
  color: #285b91;
  font-weight: 600;
  font-size: 12px;
  padding: 6px 16px;
  border-radius: 8px;
  white-space: nowrap;
}

.chart-inner {
  position: relative;
  height: 280px;
  width: 100%;
}

.chart-inner-line {
  height: 320px;
}

.chart-horizontal-scroll {
  width: 100%;
  max-width: 100%;
  min-width: 0;
  overflow-x: hidden;
  overflow-y: hidden;
}

.chart-horizontal-content {
  width: 100%;
  min-width: 0;
}

.chart-content-line {
  height: 320px;
}

.chart-content-bar {
  height: 280px;
}

.loading-placeholder {
  height: 200px;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-style: italic;
  color: #94a3b8;
}

.no-data-placeholder {
  min-height: 200px;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  font-weight: 500;
  color: #94a3b8;
  background: #f8fafc;
  border-radius: 10px;
  border: 1.5px dashed #cbd5e1;
  box-sizing: border-box;
}

:global(.page-content:has(.grafik-benang)) {
  background: #f1f5f9;
  padding-top: 60px !important;
  padding-left: 0 !important;
  padding-right: 0 !important;
}

@media (max-width: 1199px) {
  .grafik-benang {
    padding: 0 24px 28px;
  }

  .chart-header {
    flex-direction: column;
    gap: 14px;
  }

  .chart-header > .tipe-badge {
    order: -1;
    align-self: flex-start;
  }

  .filter-section {
    width: calc(100% + 48px);
    margin: 0 -24px 24px;
    padding: 28px 24px;
  }

  .filter-bar {
    grid-template-columns: minmax(160px, 250px) minmax(100px, 120px) minmax(170px, 220px);
    gap: 24px;
  }

  .filter-heading {
    grid-column: 1 / -1;
  }

  .chart-grid {
    gap: 20px;
    margin-bottom: 20px;
  }

  .chart-grid-line {
    margin-bottom: 20px;
  }

  .chart-card {
    padding: 22px;
  }
}

@media (max-width: 900px) {
  .grafik-benang {
    padding: 0 20px 24px;
  }

  .filter-section {
    width: calc(100% + 40px);
    margin: 0 -20px 20px;
    padding: 24px 20px;
  }

  .filter-bar {
    grid-template-columns: minmax(0, 1.2fr) minmax(0, 0.65fr) minmax(0, 1fr);
    gap: 16px;
  }

  .filter-heading {
    grid-column: 1 / -1;
  }

  .chart-grid {
    gap: 16px;
  }

  .chart-card {
    padding: 18px;
  }
}

@media (max-width: 767px) {
  :global(.page-content:has(.grafik-benang)) {
    padding-top: 60px !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
  }

  .grafik-benang {
    padding: 0;
    width: 100vw;
    max-width: 100vw;
    margin-left: calc(50% - 50vw);
  }

  .page-title {
    display: none;
  }

  .filter-section {
    width: 100%;
    margin: 0 0 12px;
    padding: 16px 22px 28px;
  }

  .filter-bar {
    display: block;
    width: 100%;
    margin: 0;
  }

  .filter-heading {
    display: block;
    margin: 0 0 16px;
    color: #1e293b;
    font-size: 16px;
    font-weight: 700;
  }

  .filter-group,
  .filter-period {
    display: grid;
  }

  .filter-bar {
    grid-template-columns: minmax(0, 1fr);
    gap: 0;
  }

  .filter-category {
    grid-template-columns: minmax(0, 1fr);
    gap: 8px;
    margin-bottom: 16px;
  }

  .filter-period {
    grid-template-columns: minmax(0, 1fr) minmax(0, 2.1fr);
    gap: 20px;
  }

  .filter-period .filter-group {
    grid-template-columns: minmax(0, 1fr);
    gap: 8px;
  }

  .filter-label {
    color: #64748b;
    font-size: 14px;
  }

  .filter-input,
  .filter-input-year {
    width: 100%;
    min-width: 0;
    min-height: 44px;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 14px;
  }

  .chart-grid,
  .chart-grid-line {
    padding: 16px 22px 0;
    margin-bottom: 0;
  }

  .chart-grid {
    grid-template-columns: minmax(0, 1fr);
    gap: 14px;
    margin-bottom: 14px;
  }

  .chart-grid-line {
    margin-bottom: 0;
  }

  .chart-card {
    padding: 16px;
    border-radius: 12px;
  }

  .chart-header {
    flex-direction: column;
    gap: 14px;
  }

  .tipe-badge {
    order: -1;
  }

  .chart-title {
    display: block;
    font-size: 16px;
    line-height: 1.3;
  }

  .chart-year {
    display: block;
    margin: 8px 0px 22px;
    font-size: 13px;
    color: #64748b;
  }

  .tipe-badge {
    font-size: 12px;
    padding: 6px 12px;
  }
}

@media (max-width: 480px) {
  .filter-section {
    padding: 16px 14px 26px;
  }

  .filter-heading {
    font-size: 14px;
  }

  .filter-period {
    gap: 12px;
  }

  .filter-label {
    font-size: 12px;
  }

  .filter-input,
  .filter-input-year {
    min-height: 34px;
    padding: 12px;
    border-radius: 8px;
    font-size: 13px;
  }

  .chart-grid,
  .chart-grid-line {
    padding: 10px 14px 0;
  }

  .chart-grid {
    gap: 16px;
    margin-bottom: 10px;
  }

  .chart-grid-line {
    margin-bottom: 10px;
  }

}

@media (min-width: 768px) and (max-width: 1024px) {
  .chart-grid {
    grid-template-columns: minmax(0, 1fr);
  }

  .chart-horizontal-scroll {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: #94a3b8 #f1f5f9;
  }

  .chart-horizontal-content {
    min-width: 900px;
  }

  .chart-content-line,
  .chart-inner-line {
    height: 250px;
  }

  .chart-content-bar,
  .chart-inner {
    height: 260px;
  }

  .chart-title {
    font-size: 16px;
  }

}

@media (max-width: 767px) {
  .chart-horizontal-scroll {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: #94a3b8 #f1f5f9;
  }

  .chart-horizontal-content {
    min-width: 700px;
  }

  .chart-content-line,
  .chart-inner-line {
    height: 260px;
  }

  .chart-content-bar,
  .chart-inner {
    height: 280px;
  }
}
</style>
