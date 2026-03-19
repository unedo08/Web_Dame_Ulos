<template>
  <div v-if="chartData.datasets.length">
    <Bar :data="chartData" :options="chartOptions" />
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
  const labels = Object.keys(props.dataBarang || {})
  const data = Object.values(props.dataBarang || {}).map(v => Number(v))

  return {
    labels,
    datasets: [
      {
        label: "Total",
        data,
        backgroundColor: [
          "#22c55e",
          "#3b82f6",
          "#f59e0b",
          "#ef4444",
          "#a855f7"
        ],
        borderRadius: 8
      }
    ]
  }
})

const chartOptions = {
  responsive: true,
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      callbacks: {
        label: ctx => "Rp " + ctx.raw.toLocaleString("id-ID")
      }
    }
  },
  scales: {
    y: {
      title: {
        display: true,
        text: "Nilai (Rp)"
      },
      ticks: {
        callback: val => val.toLocaleString("id-ID")
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
</style>