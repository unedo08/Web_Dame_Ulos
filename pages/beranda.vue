<template>
  <div class="beranda">
    <h1 class="page-title">Beranda</h1>

    <div class="stat-wrapper">
      <div class="stat-card">
        <CubeIcon class="stat-icon" />
        <div>
          <div class="stat-label">Total Barang Masuk</div>
          <div class="stat-value">{{ totalBarang }}</div>
        </div>
      </div>
    </div>

    <div class="chart-grid">
      <div class="chart-card">
        <div class="chart-title">Jumlah Barang per Bulan</div>
        <PieChart v-if="dataPerMonth" :dataPerMonth="dataPerMonth" />
      </div>

      <div class="chart-card">
        <div class="chart-title">Jenis Transaksi per Bulan</div>
        <PieTransaksi v-if="dataTransaksi" :dataTransaksi="dataTransaksi" />
      </div>
    </div>
    <div class="chart-grid">
      <div class="chart-card">
        <div class="chart-title">Platform Transaksi per Bulan</div>
        <PiePlatform v-if="dataPlatform" :dataPlatform="dataPlatform" />
      </div>

      <div class="chart-card">
        <!-- <div class="chart-title">Jenis Transaksi per Bulan</div>
        <PieTransaksi v-if="dataTransaksi" :dataTransaksi="dataTransaksi" /> -->
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRuntimeConfig } from '#imports'
import { CubeIcon } from '@heroicons/vue/24/solid'
import PieChart from "~/components/PieChart.vue";
import PieTransaksi from "~/components/PieTransaksi.vue";
import PiePlatform from '../components/PiePlatform.vue';
import dayjs from "dayjs";

const totalBarang = ref(0)
const dataPerMonth = ref(null);
const dataTransaksi = ref(null);
const dataPlatform = ref(null);
const url = ref('')
const { $api } = useNuxtApp();

onMounted(async () => {
  const config = useRuntimeConfig()
  url.value = config.public.apiBase

  try {
    const responseBarang = await $api.get(`${url.value}/api/codebarang`)
    const dataBarang = responseBarang.data;

    totalBarang.value = dataBarang.length;

    const monthlyCounts = {};
    dataBarang.forEach((item) => {
      const month = dayjs(item.created_at).format("MMMM YYYY");
      monthlyCounts[month] = (monthlyCounts[month] || 0) + 1;
    });

    dataPerMonth.value = monthlyCounts;

    const responseTransaksi = await $api.get(`${url.value}/api/transaksi/grouped`)
    const transaksiData = responseTransaksi.data.data;

    const currentMonth = dayjs().format("MMMM YYYY");

    const transaksiPerTipe = {};

    transaksiData.forEach((trx) => {
      const month = dayjs(trx.created_at).format("MMMM YYYY");

      if (month === currentMonth) {
        const tipe = trx.transaksi_tipe || "Unknown";
        transaksiPerTipe[tipe] = (transaksiPerTipe[tipe] || 0) + 1;
      }
    });

    dataTransaksi.value = transaksiPerTipe;

    const platformPerMonth = {};

    transaksiData.forEach((trx) => {
      const month = dayjs(trx.created_at).format("MMMM YYYY");

      if (month === currentMonth) {
        const platform = trx.transaksi_platform || "Offline";
        platformPerMonth[platform] =
          (platformPerMonth[platform] || 0) + 1;
      }
    });

    dataPlatform.value = platformPerMonth;

  } catch (error) {
    console.error('Gagal mengambil data:', error)
  }
})
</script>

<style scoped>
.beranda {
  padding: 32px 40px;
  background: #f1f5f9;
  min-height: 100vh;
}

.page-title {
  font-size: 1.3rem;
  font-weight: 700;
  margin-bottom: 32px;
}

.stat-wrapper {
  margin-bottom: 40px;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 20px;
  background: #ffffff;
  padding: 22px 32px;
  border-radius: 14px;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
  width: 280px;
}

.chart-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 32px;
  margin-bottom: 32px;
}

.chart-card {
  background: #ffffff;
  padding: 28px;
  border-radius: 16px;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
  text-align: center;
}

.chart-title {
  font-weight: 600;
  margin-bottom: 20px;
}

.stat-label {
  font-size: 0.9rem;
  color: #64748b;
  margin-bottom: 4px;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  line-height: 1.2;
}

.stat-icon {
  width: 42px;
  height: 42px;
  color: #4f46e5;
}
</style>