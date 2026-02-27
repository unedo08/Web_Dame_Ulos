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
        <div class="chart-title">Jenis Transaksi Hari Ini</div>
        <PieTransaksi v-if="transaksiDay" :dataTransaksi="transaksiDay" />
      </div>

      <div class="chart-card">
        <div class="chart-title">Platform Hari Ini</div>
        <PiePlatform v-if="platformDay" :dataPlatform="platformDay" />
      </div>
    </div>

    <div class="chart-grid">
      <div class="chart-card">
        <div class="chart-title">Jenis Transaksi Bulan Ini</div>
        <PieTransaksi v-if="transaksiMonth" :dataTransaksi="transaksiMonth" />
      </div>

      <div class="chart-card">
        <div class="chart-title">Platform Bulan Ini</div>
        <PiePlatform v-if="platformMonth" :dataPlatform="platformMonth" />
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

const transaksiDay = ref(null);
const platformDay = ref(null);

const transaksiMonth = ref(null);
const platformMonth = ref(null);

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

    const today = dayjs().format("YYYY-MM-DD");

    const responseDay = await $api.get(`${url.value}/api/transaksi/transaksi-summary`, {
      params: {
        type: "day",
        date: today
      }
    });

    const dayData = responseDay.data.data;

    const trxDayObj = {};
    dayData.per_transaksi.forEach(item => {
      trxDayObj[item.tipe] = item.total;
    });

    transaksiDay.value = trxDayObj;

    const platformDayObj = {};
    dayData.per_platform.forEach(item => {
      platformDayObj[item.tipe] = item.total;
    });

    platformDay.value = platformDayObj;

    const currentMonth = dayjs().month() + 1;
    const currentYear = dayjs().year();

    const responseMonth = await $api.get(`${url.value}/api/transaksi/transaksi-summary`, {
      params: {
        type: "month",
        month: currentMonth,
        year: currentYear
      }
    });

    const monthData = responseMonth.data.data;

    const trxMonthObj = {};
    monthData.per_transaksi.forEach(item => {
      trxMonthObj[item.tipe] = item.total;
    });

    transaksiMonth.value = trxMonthObj;

    const platformMonthObj = {};
    monthData.per_platform.forEach(item => {
      platformMonthObj[item.tipe] = item.total;
    });

    platformMonth.value = platformMonthObj;

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