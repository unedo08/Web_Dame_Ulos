<template>
  <div class="chart-wrapper">
    <Pie :data="chartData" :options="chartOptions" />
  </div>
</template>

<script setup>
import { Pie } from "vue-chartjs";
import {
  Chart as ChartJS,
  Tooltip,
  Legend,
  ArcElement,
} from "chart.js";
import { computed } from "vue";
import ChartDataLabels from "chartjs-plugin-datalabels";

ChartJS.register(Tooltip, Legend, ArcElement);

const props = defineProps({
  dataPlatform: {
    type: Object,
    required: true,
  },
});

const chartData = computed(() => ({
  labels: Object.keys(props.dataPlatform),
  datasets: [
    {
      data: Object.values(props.dataPlatform),
      backgroundColor: [
        "#22c55e",
        "#ef4444",
        "#3b82f6",
        "#a855f7",
        "#f59e0b",
        "#14b8a6",
      ],
    },
  ],
}));

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: "bottom",
      labels: { boxWidth: 14, padding: 12, font: { size: 11 } }
    },
    tooltip: {
      callbacks: {
        label(ctx) {
          const label = ctx.label || ""
          const value = Number(ctx.raw || 0)
          return ` ${label}: Rp ${value.toLocaleString("id-ID")}`
        }
      }
    }
  }
};
</script>

<style scoped>
.chart-wrapper {
  width: 100%;
  height: 320px;
  min-width: 0;
  margin: 0 auto;
}
</style>