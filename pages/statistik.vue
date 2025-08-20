<template>
  <title>Menu Statistik</title>
  <div class="judul text-xl font-semibold mb-4">Menu Statistik</div>
  <div style="width: 300px; height: 300px">
    <h1>Pie Chart Jumlah Barang per Bulan</h1>
    <PieChart v-if="dataPerMonth" :dataPerMonth="dataPerMonth" />
  </div>
</template>
<script setup>
import PieChart from "~/components/PieChart.vue";
import axios from "axios";
import dayjs from "dayjs";
import { ref, onMounted } from "vue";
import { useRuntimeConfig } from "#imports";

const dataPerMonth = ref(null);
const url = ref('')
onMounted(async () => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;
  try {
    const response = await axios.get(`${url.value}/api/codebarang`);
    const data = response.data;

    const monthlyCounts = {};

    data.forEach((item) => {
      const month = dayjs(item.created_at).format("MMMM YYYY");
      monthlyCounts[month] = (monthlyCounts[month] || 0) + 1;
    });

    dataPerMonth.value = monthlyCounts;
  } catch (error) {
    console.error("Gagal ambil data:", error);
  }
});
</script>
