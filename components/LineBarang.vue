<template>
  <div v-if="chartData.datasets.length">
    <Line :data="chartData" :options="chartOptions" />
  </div>

  <div v-else class="loading-placeholder">
    Data kosong...
  </div>
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

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale
)

const props = defineProps({
  dataBarang: Object
})

const chartData = computed(() => {
  const labels = props.dataBarang?.categories || []

  const datasets = (props.dataBarang?.series || []).map((item, index) => {
    const colors = ["#22c55e", "#3b82f6", "#f59e0b"]

    return {
      label: item.name,
      data: item.data.map(v => Number(v)),
      borderColor: colors[index % colors.length],
      backgroundColor: colors[index % colors.length],
      tension: 0.4,
      fill: false,
      pointRadius: 4
    }
  })

  return {
    labels,
    datasets
  }
})

const chartOptions = {
  responsive: true,

  plugins: {
    legend: {
      position: "top"
    },
    tooltip: {
      callbacks: {
        label: ctx =>
          `${ctx.dataset.label}: Rp ${ctx.raw.toLocaleString("id-ID")}`
      }
    }
  },

  scales: {
    x: {
      ticks: {
        display: false
      }
    },
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