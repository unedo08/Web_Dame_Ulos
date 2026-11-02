<template>
  <div class="grafik-benang">
    <title>Grafik Benang</title>
    <h1 class="page-title">Grafik Benang</h1>

    <!-- FILTER BAR -->
    <div class="filter-bar">
      <label class="filter-label">Tipe</label>
      <select v-model="tipe" class="filter-input" @change="fetchAll">
        <option v-if="!pewarnaList.length" value="PEWARNA_ALAM">Pewarna Alam</option>
        <option v-for="item in pewarnaList" :key="item.pewarna_id" :value="item.pewarna_kode">
          {{ item.pewarna_nama }}
        </option>
      </select>

      <label class="filter-label">Tahun</label>
      <input type="number" v-model.number="tahun" class="filter-input filter-input-year" min="2020"
        :max="currentYear" @change="fetchPertahun" />

      <label class="filter-label">Bulan</label>
      <input type="month" v-model="bulanInput" class="filter-input" @change="fetchPerbulan" />
    </div>

    <!-- GRAFIK PERTAHUN -->
    <div class="chart-grid-line">
      <div class="chart-card">
        <div class="chart-header">
          <div>
            <div class="chart-title">Grafik Pertahun {{ pertahun?.tahun || tahun }}</div>
            <div class="chart-subtitle">Perbandingan {{ masukLabel.toLowerCase() }} benang dan benang keluar setiap bulan</div>
          </div>
          <span class="tipe-badge" :class="tipeBadgeClass">{{ tipeLabel }}</span>
        </div>
        <div v-if="isLoadingTahun" class="loading-placeholder">Menunggu data...</div>
        <div v-else-if="!pertahun" class="no-data-placeholder">Data tidak tersedia</div>
        <div v-else class="chart-inner chart-inner-line">
          <Line :data="lineData" :options="lineOptions" />
        </div>
      </div>
    </div>

    <!-- GRAFIK PERBULAN -->
    <div class="chart-grid">
      <div v-for="chart in barCharts" :key="chart.key" class="chart-card">
        <div class="chart-header">
          <div>
            <div class="chart-title">Grafik Perbulan {{ bulanTitle }}</div>
            <div class="chart-subtitle">{{ chart.subtitle }}</div>
          </div>
          <span class="tipe-badge" :class="tipeBadgeClass">{{ tipeLabel }}</span>
        </div>
        <div v-if="isLoadingBulan" class="loading-placeholder">Menunggu data...</div>
        <div v-else-if="!chart.rows.length" class="no-data-placeholder">Data tidak tersedia</div>
        <div v-else class="chart-inner">
          <Bar :data="barData(chart.rows)" :options="chart.options" />
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
const tipeBadgeClass = computed(() => (isTextile.value ? "tipe-badge--textile" : ""));

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
      subtitle: `Jumlah ${masuk} benang per warna dalam sebulan`,
      rows: perbulan.value?.produksi_warna || [],
      options: barOptions("Warna Benang")
    },
    {
      key: "produksi_orang",
      subtitle: `Jumlah ${masuk} benang per orang dalam sebulan`,
      rows: perbulan.value?.produksi_orang || [],
      options: barOptions("Nama Author")
    },
    {
      key: "keluar_warna",
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
  padding: 32px 40px;
  background: #f1f5f9;
  min-height: 100vh;
  box-sizing: border-box;
  overflow-x: hidden;
}

.page-title {
  font-size: 1.3rem;
  font-weight: 700;
  color: #111827;
  margin: 0 0 24px;
}

.filter-bar {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
  width: 100%;
  background: #ffffff;
  padding: 16px 24px;
  border-radius: 14px;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
  margin-bottom: 24px;
  box-sizing: border-box;
}

.filter-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #475569;
}

.filter-input {
  min-width: 150px;
  padding: 7px 12px;
  min-height: 34px;
  border-radius: 8px;
  border: 1.5px solid #cbd5e1;
  font-size: 0.875rem;
  color: #1e293b;
  outline: none;
  transition: border-color 0.15s;
  background: #fff;
  box-sizing: border-box;
}

.filter-input:focus {
  border-color: #4f46e5;
}

.filter-input-year {
  width: 100px;
  min-width: 100px;
}

.chart-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 32px;
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
  padding: 28px;
  border-radius: 16px;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
  box-sizing: border-box;
  overflow: hidden;
}

.chart-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.chart-title {
  font-weight: 600;
  color: #1e293b;
  line-height: 1.4;
}

.chart-subtitle {
  font-size: 0.875rem;
  color: #64748b;
  margin: 4px 0 20px;
}

.tipe-badge {
  background: #06b6d4;
  color: #ffffff;
  font-weight: 600;
  font-size: 0.875rem;
  padding: 6px 16px;
  border-radius: 999px;
  white-space: nowrap;
}

.tipe-badge--textile {
  background: #c96ce6;
}

.chart-inner {
  position: relative;
  height: 280px;
  width: 100%;
}

.chart-inner-line {
  height: 320px;
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

@media (max-width: 900px) {
  .chart-grid {
    grid-template-columns: minmax(0, 1fr);
    gap: 20px;
  }
}

@media (max-width: 767px) {
  .grafik-benang {
    padding: 16px 12px;
  }

  .chart-card {
    padding: 18px;
  }
}
</style>
