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
        <div class="chart-header-custom">
          <h3 class="chart-title">Diagram pie Platform per hari</h3>
        </div>

        <div class="chart-main-content">
          <PiePlatform v-if="platformPieDay" :dataPlatform="platformPieDay" />
          <div v-else class="loading-placeholder">
            Menunggu data...
          </div>
        </div>
      </div>

      <div class="chart-card">
        <div class="chart-title">
          Jenis Transaksi Hari Ini
        </div>
        <PieTransaksi v-if="transaksiDay" :dataTransaksi="transaksiDay" />
        <div v-else class="loading-placeholder">
          Menunggu data...
        </div>
      </div>
    </div>

    <div class="chart-grid">
      <div class="chart-card">
        <div class="chart-title">
          Jenis Transaksi Bulan Ini
        </div>
        <PieTransaksi v-if="transaksiMonth" :dataTransaksi="transaksiMonth" />
        <div v-else class="loading-placeholder">
          Menunggu data...
        </div>
      </div>

      <div class="chart-card">
        <div class="chart-title">
          Platform Bulan Ini
        </div>
        <PiePlatform v-if="platformMonth" :dataPlatform="platformMonth" />
        <div v-else class="loading-placeholder">
          Menunggu data...
        </div>
      </div>
    </div>

    <div class="chart-grid">
      <div class="chart-card">
        <div class="chart-title">
          Komposisi Nilai Barang Bulan Ini
        </div>

        <PiePlatform v-if="barangMonth" :dataPlatform="barangMonth" />
        <div v-else class="loading-placeholder">
          Menunggu data...
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>

import { ref, onMounted } from "vue"
import { useRuntimeConfig, useNuxtApp } from "#imports"
import { CubeIcon } from "@heroicons/vue/24/solid"
import PieTransaksi from "~/components/PieTransaksi.vue"
import PiePlatform from "~/components/PiePlatform.vue"
import dayjs from "dayjs"

const { $api } = useNuxtApp()
const config = useRuntimeConfig()
const url = config.public.apiBase
const totalBarang = ref(0)
const platformPieDay = ref(null)
const transaksiDay = ref(null)
const transaksiMonth = ref(null)
const platformMonth = ref(null)
const barangMonth = ref(null)

onMounted(async () => {
  try {
    const today = dayjs().format("YYYY-MM-DD")
    const currentMonth = dayjs().month() + 1
    const currentYear = dayjs().year()

    const [
      responseBarang,
      resDashboard,
      resDay,
      resMonth,
      resBarangMonth
    ] = await Promise.all([

      $api.get(`${url}/api/codebarang`),

      $api.get(`${url}/api/dashboard/transaksi-platform`, {
        params: { type: "day" }
      }),

      $api.get(`${url}/api/transaksi/transaksi-summary`, {
        params: { type: "day", date: today }
      }),

      $api.get(`${url}/api/transaksi/transaksi-summary`, {
        params: {
          type: "month",
          month: currentMonth,
          year: currentYear
        }
      }),

      $api.get(`${url}/api/dashboard/barang`, {
        params: { type: "month" }
      })
    ])

    totalBarang.value = responseBarang.data.length;
    const pieDataRaw = resDashboard.data.pie_chart
    const mappedPlatformDay = {}
    if (pieDataRaw) {
      pieDataRaw.forEach(item => {
        const name = item.transaksi_platform || "Lainnya"
        mappedPlatformDay[name] = item.total
      })
      platformPieDay.value = mappedPlatformDay
    }

    const trxDayObj = {}
    resDay.data.data.per_transaksi.forEach(item => {
      trxDayObj[item.tipe] = item.total
    })

    transaksiDay.value = trxDayObj;
    const trxMonthObj = {}

    resMonth.data.data.per_transaksi.forEach(item => {
      trxMonthObj[item.tipe] = item.total
    })

    transaksiMonth.value = trxMonthObj;
    const platMonthObj = {}

    resMonth.data.data.per_platform.forEach(item => {
      platMonthObj[item.tipe] = item.total
    })

    platformMonth.value = platMonthObj
    const barangMonthObj = {}
    resBarangMonth.data.series.forEach(series => {
      const total = series.data.reduce((sum, val) => {
        return sum + Number(val)
      }, 0)
      barangMonthObj[series.name] = total
    })
    barangMonth.value = barangMonthObj
  }
  catch (error) {
    console.error("Gagal mengambil data dashboard:", error)
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

.stat-icon {
  width: 42px;
  height: 42px;
  color: #4f46e5;
}

.stat-label {
  font-size: 0.9rem;
  color: #64748b;
  margin-bottom: 4px;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
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
}

.chart-title {
  font-weight: 600;
  margin-bottom: 20px;
  text-align: center;
}

.chart-header-custom {
  text-align: left;
  margin-bottom: 25px;
}

.chart-main-content {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.loading-placeholder {
  height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-style: italic;
  color: #94a3b8;
}
</style>