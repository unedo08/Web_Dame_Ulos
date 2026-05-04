<template>
  <div v-if="hasData" style="height: 320px; width: 100%;">
    <Line :data="chartData" :options="chartOptions" style="height: 100%; width: 100%;" />
  </div>
  <div v-else class="empty-state">Data kosong...</div>
</template>

<script setup>
import { computed } from "vue"
import { Line } from "vue-chartjs"
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale
} from "chart.js"

ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale)

const props = defineProps({
  dataCustomer: {
    type: Array,
    default: () => []
  }
})

const hasData = computed(() => props.dataCustomer.length > 0)

const chartData = computed(() => ({
  labels: props.dataCustomer.map(c => c.customer_akun),
  datasets: [
    {
      label: "Nilai Pembelian",
      data: props.dataCustomer.map(c => Number(c.nilai_pembelian || 0)),
      borderColor: "#3b82f6",
      backgroundColor: "#3b82f6",
      yAxisID: "yNilai",
      tension: 0.4,
      fill: false,
      pointRadius: 5,
      pointHoverRadius: 7
    },
    {
      label: "Jumlah Transaksi",
      data: props.dataCustomer.map(c => Number(c.jumlah_transaksi || 0)),
      borderColor: "#F5A623",
      backgroundColor: "#E67E22",
      yAxisID: "yJumlah",
      tension: 0.4,
      fill: false,
      pointRadius: 5,
      pointHoverRadius: 7
    }
  ]
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    mode: "index",
    intersect: false
  },
  plugins: {
    legend: {
      position: "top",
      labels: { usePointStyle: true, pointStyle: "circle", padding: 20 }
    },
    tooltip: {
      callbacks: {
        label(ctx) {
          if (ctx.dataset.yAxisID === "yNilai") {
            return ` Nilai Pembelian: Rp ${Number(ctx.raw).toLocaleString("id-ID")}`
          }
          return ` Jumlah Transaksi: ${ctx.raw}`
        }
      }
    }
  },
  scales: {
    x: {
      ticks: {
        maxRotation: 45,
        minRotation: 30
      },
      grid: { display: false }
    },
    yNilai: {
      type: "linear",
      position: "left",
      title: { display: true, text: "Nilai Pembelian (Rp)", color: "#3b82f6" },
      ticks: {
        color: "#000000",
        callback: val => "Rp " + val.toLocaleString("id-ID")
      },
      grid: { color: "#f1f5f9" }
    },
    yJumlah: {
      type: "linear",
      position: "right",
      title: { display: true, text: "Jumlah Transaksi", color: "#a78bfa" },
      ticks: {
        color: "#a78bfa",
        stepSize: 1,
        precision: 0
      },
      grid: { drawOnChartArea: false }
    }
  }
}
</script>

<style scoped>
.empty-state {
  height: 320px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
  font-style: italic;
}
</style>
