<template>
  <div v-if="chartData.datasets.length" class="bar-chart-wrapper">
    <div class="bar-chart-inner">
      <Bar :data="chartData" :options="chartOptions" />
    </div>
  </div>

  <div v-else class="loading-placeholder">
    Data kosong...
  </div>
</template>

<script setup>
import { computed } from "vue"
import { Bar } from "vue-chartjs"
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale
} from "chart.js"

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale
)

const props = defineProps({
  dataBarang: Object
})

const chartData = computed(() => {
  if (!props.dataBarang) return { labels: [], datasets: [] }

  const colorMap = {
    "Harga Net": "#22c55e",
    "Harga Jual": "#3b82f6",
    "Harga Modal": "#ef4444"
  }

  return {
    labels: props.dataBarang.labels || [],
    datasets: props.dataBarang.datasets.map(ds => ({
      ...ds,
      backgroundColor: colorMap[ds.label] || "#94a3b8",
      borderRadius: 6
    }))
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: "bottom"
    }
  },
  scales: {
    x: {
      ticks: {
        autoSkip: false,
        maxRotation: 45,
        minRotation: 45
      }
    },
    y: {
      beginAtZero: true,
      ticks: {
        callback: value => Number(value).toLocaleString("id-ID")
      }
    }
  }
}
</script>

<style scoped>
.loading-placeholder {
  height: 350px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
}

.bar-chart-wrapper {
  width: 100%;
  max-width: 100%;
  min-width: 0;
}

.bar-chart-inner {
  position: relative;
  height: 260px;
  width: 100%;
}
</style>