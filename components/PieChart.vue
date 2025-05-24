<template>
  <div>
    <Pie :data="chartData" :options="chartOptions" />
  </div>
</template>

<script setup>
import { Pie } from "vue-chartjs";
import { Chart as ChartJS, Title, Tooltip, Legend, ArcElement } from "chart.js";
import { computed } from "vue";

ChartJS.register(Title, Tooltip, Legend, ArcElement);

const props = defineProps({
  dataPerMonth: {
    type: Object,
    required: true,
  },
});

const chartData = computed(() => ({
  labels: Object.keys(props.dataPerMonth),
  datasets: [
    {
      label: "Jumlah Barang per Bulan",
      backgroundColor: ["#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0", "#9966FF"],
      data: Object.values(props.dataPerMonth),
    },
  ],
}));

const chartOptions = {
  responsive: true,
  plugins: {
    legend: {
      position: "bottom",
    },
  },
};
</script>
