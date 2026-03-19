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
    },
    datalabels: {
      color: "#fff",
      font: {
        weight: "bold",
        size: 14,
      },
      formatter: (value) => {
        return value.toLocaleString("id-ID");
      },
    },
  },
};
</script>

<style scoped>
.chart-wrapper {
  width: 280px;
  height: 280px;
  margin: 0 auto;
}
</style>