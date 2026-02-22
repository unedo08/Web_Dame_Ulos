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