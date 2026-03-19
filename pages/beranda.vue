<template>
  <div class="beranda">
    <h1 class="page-title">Beranda</h1>
    <title>Beranda</title>

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
          <div v-else class="loading-placeholder">Menunggu data...</div>
        </div>
      </div>

      <div class="chart-card">
        <div class="chart-title">Jenis Transaksi Hari Ini</div>
        <PieTransaksi v-if="transaksiDay" :dataTransaksi="transaksiDay" />
        <div v-else class="loading-placeholder">Menunggu data...</div>
      </div>
    </div>

    <div class="chart-grid">
      <div class="chart-card">
        <div class="chart-title">Jenis Transaksi Bulan Ini</div>
        <PieTransaksi v-if="transaksiMonth" :dataTransaksi="transaksiMonth" />
        <div v-else class="loading-placeholder">Menunggu data...</div>
      </div>

      <div class="chart-card">
        <div class="chart-title">Platform Bulan Ini</div>
        <PiePlatform v-if="platformMonth" :dataPlatform="platformMonth" />
        <div v-else class="loading-placeholder">Menunggu data...</div>
      </div>
    </div>

    <div class="chart-grid">
      <div class="chart-card">
        <div class="chart-title">Grafik Omset Per Bulan</div>

        <BarBarang v-if="barangMonth" :dataBarang="barangMonth" />
        <div v-else class="loading-placeholder">Menunggu data...</div>
      </div>

      <div class="chart-card">
        <div class="chart-title">Diagram pie perbandingan customer baru dan custumer
          lama per bulan</div>

        <PiePlatform v-if="customerPie" :dataPlatform="customerPie" />
        <div v-else class="loading-placeholder">Menunggu data...</div>
      </div>
    </div>
    <div class="chart-grid">
      <div class="chart-card">
        <div class="chart-title">Jumlah Barang Masuk per bulan</div>

        <PiePlatform v-if="jenisBarangPie" :dataPlatform="jenisBarangPie" />
        <div v-else class="loading-placeholder">Menunggu data...</div>
      </div>
      <div class="chart-card">
        <div class="chart-title">Jumlah Barang Terjual per bulan</div>

        <PiePlatform v-if="barangTerjualPie" :dataPlatform="barangTerjualPie" />
        <div v-else class="loading-placeholder">Menunggu data...</div>
      </div>
    </div>
    <div class="chart-grid-line">
      <div class="chart-card-line">
        <div class="chart-title">Grafik Omset Per Hari</div>

        <LineBarang v-if="barangLine" :dataBarang="barangLine" />
        <div v-else class="loading-placeholder">Menunggu data...</div>
      </div>
    </div>
    <div class="chart-grid-line">
      <div class="chart-card-line">
        <div class="chart-title">Pertumbuhan costumer baru per tahun</div>

        <LineBarangCustomer v-if="customerLine" :dataBarang="customerLine" />
        <div v-else class="loading-placeholder">Menunggu data...</div>
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
import BarBarang from "~/components/BarBarang.vue"
import LineBarang from "~/components/LineBarang.vue"
import LineBarangCustomer from "~/components/LineBarangCustomer.vue"
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
const barangLine = ref(null)
const customerPie = ref(null)
const customerLine = ref(null)
const jenisBarangPie = ref(null)
const barangTerjualPie = ref(null)

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
      resBarangMonth,
      resCustomer,
      resJenisBarang,
      resJenisBarangTerjual
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
      }),

      $api.get(`${url}/api/dashboard/jumlah-customer`),
      $api.get(`${url}/api/dashboard/jenis-barang-entry`, {
        params: { type: "month" }
      }),
       $api.get(`${url}/api/dashboard/jenis-barang-jual`, {
        params: { type: "month" }
      })
    ])

    totalBarang.value = responseBarang.data.length

    const mappedPlatformDay = {}
    resDashboard.data.pie_chart?.forEach(item => {
      const name = item.transaksi_platform || "Lainnya"
      mappedPlatformDay[name] = item.total
    })
    platformPieDay.value = mappedPlatformDay

    const trxDayObj = {}
    resDay.data.data.per_transaksi.forEach(item => {
      trxDayObj[item.tipe] = item.total
    })
    transaksiDay.value = trxDayObj

    const trxMonthObj = {}
    resMonth.data.data.per_transaksi.forEach(item => {
      trxMonthObj[item.tipe] = item.total
    })
    transaksiMonth.value = trxMonthObj

    const platMonthObj = {}
    resMonth.data.data.per_platform.forEach(item => {
      platMonthObj[item.tipe] = item.total
    })
    platformMonth.value = platMonthObj

    const barangObj = {}

    resBarangMonth.data.series.forEach(series => {
      const total = series.data.reduce((sum, val) => {
        return sum + Number(val)
      }, 0)

      barangObj[series.name] = total
    })

    barangMonth.value = barangObj

    const lineSeries = resBarangMonth.data.series.map(series => ({
      name: series.name,
      data: series.data.map(val => Number(val))
    }))

    const categories = resBarangMonth.data.labels || []

    barangLine.value = {
      series: lineSeries,
      categories
    }

    barangLine.value = {
      series: lineSeries,
      categories
    }

    customerPie.value = {
      "Customer Baru": Number(resCustomer.data.pie_chart.customer_baru),
      "Customer Lama": Number(resCustomer.data.pie_chart.customer_lama)
    }

    const customerSeries = [
      {
        name: "Total Customer",
        data: resCustomer.data.line_chart.map(item => Number(item.total))
      }
    ]

    const customerCategories = resCustomer.data.line_chart.map(item => item.bulan)

    customerLine.value = {
      series: customerSeries,
      categories: customerCategories
    }

    const jenisBarangObj = {}

    resJenisBarang.data.forEach(item => {
      if (item.Bulan === currentMonth) {
        const name = "Kode " + item.kategori
        jenisBarangObj[name] = (jenisBarangObj[name] || 0) + Number(item.total)
      }
    })

    jenisBarangPie.value = jenisBarangObj

    const barangTerjualObj = {}

    resJenisBarangTerjual.data.forEach(item => {
      if (item.Bulan === currentMonth) {
        const name = "Kode " + item.kategori
        barangTerjualObj[name] = (barangTerjualObj[name] || 0) + Number(item.total)
      }
    })

    barangTerjualPie.value = barangTerjualObj

  } catch (error) {
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

.chart-grid-line {
  display: grid;
  grid-template-columns: 1fr;
  width: 100%;
  margin-bottom: 32px;
  height: 400px;
}

.chart-card {
  background: #ffffff;
  padding: 28px;
  border-radius: 16px;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
}

.chart-card-line {
  background: #ffffff;
  padding: 28px;
  border-radius: 16px;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
  height: 400px;
}

.chart-card-line canvas {
  height: 100% !important;
  width: 100% !important;
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