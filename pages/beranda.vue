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

    <!-- FILTER BAR -->
    <div class="filter-bar">
      <div class="filter-type-group">
        <button
          class="filter-btn"
          :class="{ active: selectedFilterType === 'tanggal' }"
          @click="selectedFilterType = 'tanggal'"
        >Tanggal</button>
        <button
          class="filter-btn"
          :class="{ active: selectedFilterType === 'bulan' }"
          @click="selectedFilterType = 'bulan'"
        >Bulan</button>
        <button
          class="filter-btn"
          :class="{ active: selectedFilterType === 'tahun' }"
          @click="selectedFilterType = 'tahun'"
        >Tahun</button>
      </div>

      <input
        v-if="selectedFilterType === 'tanggal'"
        type="date"
        v-model="filterDateInput"
        class="filter-input"
      />
      <input
        v-else-if="selectedFilterType === 'bulan'"
        type="month"
        v-model="filterMonthInput"
        class="filter-input"
      />
      <input
        v-else-if="selectedFilterType === 'tahun'"
        type="number"
        v-model.number="filterYearInput"
        class="filter-input filter-input-year"
        min="2020"
        :max="currentYear"
        placeholder="Tahun"
      />

      <button
        class="apply-btn"
        :disabled="!selectedFilterType"
        @click="applyFilter"
      >Terapkan</button>

      <button
        v-if="appliedFilter.type"
        class="reset-btn"
        @click="clearFilter"
      >Reset</button>
    </div>

    <!-- Active filter badge -->
    <div v-if="appliedFilter.type" class="filter-badge">
      <span class="filter-badge-dot"></span>
      Filter aktif:
      <strong v-if="appliedFilter.type === 'tanggal'">Tanggal {{ appliedFilter.date }}</strong>
      <strong v-else-if="appliedFilter.type === 'bulan'">Bulan {{ appliedFilter.month }}/{{ appliedFilter.year }}</strong>
      <strong v-else>Tahun {{ appliedFilter.year }}</strong>
    </div>
    
    <!-- ROW 1 -->
    <div class="chart-grid">
      <div class="chart-card">
        <div class="chart-header-custom">
          <h3 class="chart-title">Diagram pie Platform per hari</h3>
        </div>
        <div class="chart-main-content">
          <template v-if="isAvailable('platformPieDay')">
            <PiePlatform v-if="platformPieDay" :dataPlatform="platformPieDay" />
            <div v-else class="loading-placeholder">Menunggu data...</div>
          </template>
          <div v-else class="no-data-placeholder">Data tidak tersedia</div>
        </div>
      </div>

      <div class="chart-card">
        <div class="chart-title">Jenis Transaksi Hari Ini</div>
        <template v-if="isAvailable('transaksiDay')">
          <PieTransaksi v-if="transaksiDay" :dataTransaksi="transaksiDay" />
          <div v-else class="loading-placeholder">Menunggu data...</div>
        </template>
        <div v-else class="no-data-placeholder">Data tidak tersedia</div>
      </div>
    </div>

    <!-- ROW 2 -->
    <div class="chart-grid">
      <div class="chart-card">
        <div class="chart-title">Jenis Transaksi Bulan Ini</div>
        <template v-if="isAvailable('transaksiMonth')">
          <PieTransaksi v-if="transaksiMonth" :dataTransaksi="transaksiMonth" />
          <div v-else class="loading-placeholder">Menunggu data...</div>
        </template>
        <div v-else class="no-data-placeholder">Data tidak tersedia</div>
      </div>

      <div class="chart-card">
        <div class="chart-title">Platform Bulan Ini</div>
        <template v-if="isAvailable('platformMonth')">
          <PiePlatform v-if="platformMonth" :dataPlatform="platformMonth" />
          <div v-else class="loading-placeholder">Menunggu data...</div>
        </template>
        <div v-else class="no-data-placeholder">Data tidak tersedia</div>
      </div>
    </div>

    <!-- ROW 3 -->
    <div class="chart-grid">
      <div class="chart-card">
        <div class="chart-title">Grafik Omset Per Bulan</div>
        <template v-if="isAvailable('barangMonth')">
          <BarBarang v-if="barangMonth" :dataBarang="barangMonth" />
          <div v-else class="loading-placeholder">Menunggu data...</div>
        </template>
        <div v-else class="no-data-placeholder">Data tidak tersedia</div>
      </div>

      <div class="chart-card">
        <div class="chart-title">Diagram pie perbandingan customer baru dan customer lama per bulan</div>
        <template v-if="isAvailable('customerPie')">
          <PiePlatform v-if="customerPie" :dataPlatform="customerPie" />
          <div v-else class="loading-placeholder">Menunggu data...</div>
        </template>
        <div v-else class="no-data-placeholder">Data tidak tersedia</div>
      </div>
    </div>

    <!-- ROW 4 -->
    <div class="chart-grid">
      <div class="chart-card">
        <div class="chart-title">Jumlah Barang Masuk per bulan</div>
        <template v-if="isAvailable('jenisBarangPie')">
          <PiePlatform v-if="jenisBarangPie" :dataPlatform="jenisBarangPie" />
          <div v-else class="loading-placeholder">Menunggu data...</div>
        </template>
        <div v-else class="no-data-placeholder">Data tidak tersedia</div>
      </div>

      <div class="chart-card">
        <div class="chart-title">Jumlah Barang Terjual per bulan</div>
        <template v-if="isAvailable('barangTerjualPie')">
          <PiePlatform v-if="barangTerjualPie" :dataPlatform="barangTerjualPie" />
          <div v-else class="loading-placeholder">Menunggu data...</div>
        </template>
        <div v-else class="no-data-placeholder">Data tidak tersedia</div>
      </div>
    </div>

    <!-- ROW 5 line -->
    <div class="chart-grid-line">
      <div class="chart-card-line">
        <div class="chart-title">Grafik Omset Per Hari</div>
        <template v-if="isAvailable('barangLine')">
          <LineBarang v-if="barangLine" :dataBarang="barangLine" />
          <div v-else class="loading-placeholder">Menunggu data...</div>
        </template>
        <div v-else class="no-data-placeholder">Data tidak tersedia</div>
      </div>
    </div>

    <!-- ROW 6 line -->
    <div class="chart-grid-line">
      <div class="chart-card-line">
        <div class="chart-title">Pertumbuhan customer baru per tahun</div>
        <template v-if="isAvailable('customerLine')">
          <LineBarangCustomer v-if="customerLine" :dataBarang="customerLine" />
          <div v-else class="loading-placeholder">Menunggu data...</div>
        </template>
        <div v-else class="no-data-placeholder">Data tidak tersedia</div>
      </div>
    </div>

    <!-- ROW 7 — Nilai Pembelian & Jumlah Transaksi per Customer -->
    <div class="chart-grid-line" style="height: auto;">
      <div class="chart-card-line" style="height: auto; padding-bottom: 28px;">
        <div class="chart-title">Nilai Pembelian & Jumlah Transaksi per Customer (1 Bulan Terakhir)</div>
        <template v-if="isAvailable('customerChart')">
          <LineCustomerChart v-if="customerChart" :dataCustomer="customerChart" />
          <div v-else class="loading-placeholder">Menunggu data...</div>
        </template>
        <div v-else class="no-data-placeholder">Data tidak tersedia</div>
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
import LineCustomerChart from "~/components/LineCustomerChart.vue"
import dayjs from "dayjs"

const { $api } = useNuxtApp()
const config = useRuntimeConfig()
const url = config.public.apiBase
const currentYear = dayjs().year()

// ── Chart data refs ──────────────────────────────────────────────────────────
const totalBarang      = ref(0)
const platformPieDay   = ref(null)
const transaksiDay     = ref(null)
const transaksiMonth   = ref(null)
const platformMonth    = ref(null)
const barangMonth      = ref(null)
const barangLine       = ref(null)
const customerPie      = ref(null)
const customerLine     = ref(null)
const jenisBarangPie   = ref(null)
const barangTerjualPie = ref(null)
const customerChart    = ref(null)

// ── Filter state ─────────────────────────────────────────────────────────────
const selectedFilterType = ref(null)                      // UI selection
const filterDateInput    = ref(dayjs().format("YYYY-MM-DD"))
const filterMonthInput   = ref(dayjs().format("YYYY-MM"))
const filterYearInput    = ref(dayjs().year())
const appliedFilter      = ref({ type: null, date: null, month: null, year: null })

// ── Chart ↔ filter compatibility ─────────────────────────────────────────────
const CHART_COMPAT = {
  platformPieDay:   ["tanggal"],
  transaksiDay:     ["tanggal"],
  transaksiMonth:   ["bulan"],
  platformMonth:    ["bulan"],
  barangMonth:      ["tahun"],
  customerPie:      ["bulan"],
  jenisBarangPie:   ["bulan"],
  barangTerjualPie: ["bulan"],
  barangLine:       ["bulan"],
  customerLine:     ["tahun"],
  customerChart:    ["bulan"],
}

function isAvailable(chartKey) {
  if (!appliedFilter.value.type) return true
  return CHART_COMPAT[chartKey]?.includes(appliedFilter.value.type) ?? false
}

// ── Apply / Reset ─────────────────────────────────────────────────────────────
function applyFilter() {
  if (!selectedFilterType.value) return

  const f = { type: selectedFilterType.value, date: null, month: null, year: null }

  if (selectedFilterType.value === "tanggal") {
    f.date = filterDateInput.value
  } else if (selectedFilterType.value === "bulan") {
    const [y, m] = filterMonthInput.value.split("-")
    f.year  = parseInt(y)
    f.month = parseInt(m)
  } else {
    f.year = parseInt(filterYearInput.value)
  }

  appliedFilter.value = f
  fetchData()
}

function clearFilter() {
  selectedFilterType.value = null
  appliedFilter.value = { type: null, date: null, month: null, year: null }
  fetchData()
}

// ── Reset all chart refs to null ──────────────────────────────────────────────
function resetCharts() {
  platformPieDay.value   = null
  transaksiDay.value     = null
  transaksiMonth.value   = null
  platformMonth.value    = null
  barangMonth.value      = null
  barangLine.value       = null
  customerPie.value      = null
  customerLine.value     = null
  jenisBarangPie.value   = null
  barangTerjualPie.value = null
  customerChart.value    = null
}

// ── Main fetch dispatcher ─────────────────────────────────────────────────────
async function fetchData() {
  resetCharts()
  try {
    const res = await $api.get(`${url}/api/codebarang`)
    totalBarang.value = res.data.length

    const f = appliedFilter.value
    if (!f.type)              await fetchDefault()
    else if (f.type === "tanggal") await fetchByTanggal(f.date)
    else if (f.type === "bulan")   await fetchByBulan(f.month, f.year)
    else                           await fetchByTahun(f.year)
  } catch (err) {
    console.error("Gagal mengambil data dashboard:", err)
  }
}

// ── Default load (current date / month / year) ────────────────────────────────
async function fetchDefault() {
  const today        = dayjs().format("YYYY-MM-DD")
  const currentMonth = dayjs().month() + 1
  const year         = dayjs().year()

  const [resPlatformDay, resDay, resMonth, resBarangYear, resCustomer,
         resJenisEntry, resJenisJual, resBarangMonth, resCustomerChart] = await Promise.all([
    $api.get(`${url}/api/dashboard/transaksi-platform`, { params: { type: "day" } }),
    $api.get(`${url}/api/transaksi/transaksi-summary`,  { params: { type: "day", date: today } }),
    $api.get(`${url}/api/transaksi/transaksi-summary`,  { params: { type: "month", month: currentMonth, year } }),
    $api.get(`${url}/api/dashboard/barang`,             { params: { type: "year" } }),
    $api.get(`${url}/api/dashboard/jumlah-customer`,    { params: { month: currentMonth, year } }),
    $api.get(`${url}/api/dashboard/jenis-barang-entry`, { params: { type: "month" } }),
    $api.get(`${url}/api/dashboard/jenis-barang-jual`,  { params: { type: "month" } }),
    $api.get(`${url}/api/dashboard/barang`,             { params: { type: "month" } }),
    $api.get(`${url}/api/dashboard/customer-chart`),
  ])

  mapPlatformDay(resPlatformDay)
  mapTransaksiDay(resDay)
  mapTransaksiMonth(resMonth)
  mapBarangYear(resBarangYear)
  mapCustomerPie(resCustomer)
  mapCustomerLine(resCustomer)
  mapJenisBarangEntry(resJenisEntry, currentMonth)
  mapJenisBarangJual(resJenisJual, currentMonth)
  mapOmsetPerHari(resBarangMonth)
  mapCustomerChart(resCustomerChart)
}

// ── Filter: Tanggal ───────────────────────────────────────────────────────────
async function fetchByTanggal(date) {
  const [resPlatform, resTransaksi] = await Promise.all([
    $api.get(`${url}/api/dashboard/transaksi-platform`, { params: { type: "day", date } }),
    $api.get(`${url}/api/transaksi/transaksi-summary`,  { params: { type: "day", date } }),
  ])
  mapPlatformDay(resPlatform)
  mapTransaksiDay(resTransaksi)
}

// ── Filter: Bulan ─────────────────────────────────────────────────────────────
async function fetchByBulan(month, year) {
  const [resMonth, resBarangMonth, resCustomer, resJenisEntry, resJenisJual, resCustomerChart] = await Promise.all([
    $api.get(`${url}/api/transaksi/transaksi-summary`,  { params: { type: "month", month, year } }),
    $api.get(`${url}/api/dashboard/barang`,             { params: { type: "month", month, year } }),
    $api.get(`${url}/api/dashboard/jumlah-customer`,    { params: { month, year } }),
    $api.get(`${url}/api/dashboard/jenis-barang-entry`, { params: { type: "month", month, year } }),
    $api.get(`${url}/api/dashboard/jenis-barang-jual`,  { params: { type: "month", month, year } }),
    $api.get(`${url}/api/dashboard/customer-chart`,     { params: { month, year } }),
  ])
  mapTransaksiMonth(resMonth)
  mapOmsetPerHari(resBarangMonth)
  mapCustomerPie(resCustomer)
  mapJenisBarangEntry(resJenisEntry, month)
  mapJenisBarangJual(resJenisJual, month)
  mapCustomerChart(resCustomerChart)
}

// ── Filter: Tahun ─────────────────────────────────────────────────────────────
async function fetchByTahun(year) {
  const [resBarangYear, resCustomer] = await Promise.all([
    $api.get(`${url}/api/dashboard/barang`,          { params: { type: "year", year } }),
    $api.get(`${url}/api/dashboard/jumlah-customer`, { params: { year } }),
  ])
  mapBarangYear(resBarangYear)
  mapCustomerLine(resCustomer)
}

// ── Data mappers ──────────────────────────────────────────────────────────────
function mapPlatformDay(res) {
  const obj = {}
  res.data.pie_chart?.forEach(item => {
    const name = item.transaksidetail_platform || "Lainnya"
    obj[name] = item.total
  })
  platformPieDay.value = Object.keys(obj).length ? obj : null
}

function mapTransaksiDay(res) {
  const obj = {}
  res.data.data?.per_transaksi?.forEach(item => { obj[item.tipe] = item.total })
  transaksiDay.value = Object.keys(obj).length ? obj : null
}

function mapTransaksiMonth(res) {
  const trx = {}
  res.data.data?.per_transaksi?.forEach(item => { trx[item.tipe] = item.total })
  transaksiMonth.value = Object.keys(trx).length ? trx : null

  const plat = {}
  res.data.data?.per_platform?.forEach(item => { plat[item.tipe] = item.total })
  platformMonth.value = Object.keys(plat).length ? plat : null
}

function mapBarangYear(res) {
  const labelsAPI = res.data.labels || []
  const seriesAPI = res.data.series || []
  if (!labelsAPI.length) { barangMonth.value = null; return }

  const grouped = {}
  labelsAPI.forEach((date, i) => {
    const bulan = dayjs(date).format("MMM YYYY")
    if (!grouped[bulan]) grouped[bulan] = {}
    seriesAPI.forEach(s => {
      grouped[bulan][s.name] = (grouped[bulan][s.name] || 0) + Number(s.data[i] || 0)
    })
  })

  const labels   = Object.keys(grouped).sort((a, b) => dayjs(a, "MMM YYYY") - dayjs(b, "MMM YYYY"))
  const datasets = seriesAPI.map(s => ({
    label: s.name,
    data:  labels.map(b => grouped[b][s.name] || 0)
  }))
  barangMonth.value = { labels, datasets }
}

function mapOmsetPerHari(res) {
  const labels = res.data.labels || []
  const series = res.data.series || []
  if (!labels.length) { barangLine.value = null; return }

  barangLine.value = {
    categories: labels,
    series: series.map(s => ({ name: s.name, data: s.data.map(v => Number(v || 0)) }))
  }
}

function mapCustomerPie(res) {
  customerPie.value = {
    "Customer Baru": Number(res.data.pie_chart.customer_baru),
    "Customer Lama": Number(res.data.pie_chart.customer_lama),
  }
}

function mapCustomerLine(res) {
  customerLine.value = {
    series:     [{ name: "Total Customer", data: res.data.line_chart.map(i => Number(i.total)) }],
    categories: res.data.line_chart.map(i => i.bulan),
  }
}

function mapJenisBarangEntry(res, filterMonth) {
  const obj = {}
  res.data.forEach(item => {
    if (item.Bulan === filterMonth) {
      const name = "Kode " + item.kategori
      obj[name] = (obj[name] || 0) + Number(item.total)
    }
  })
  jenisBarangPie.value = Object.keys(obj).length ? obj : null
}

function mapJenisBarangJual(res, filterMonth) {
  const obj = {}
  res.data.forEach(item => {
    if (item.Bulan === filterMonth) {
      const name = "Kode " + item.kategori
      obj[name] = (obj[name] || 0) + Number(item.total)
    }
  })
  barangTerjualPie.value = Object.keys(obj).length ? obj : null
}

function mapCustomerChart(res) {
  customerChart.value = Array.isArray(res.data) && res.data.length ? res.data : null
}

onMounted(() => fetchData())
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
  margin-bottom: 24px;
}

/* ── Filter bar ─────────────────────────────────────────────────────────────── */
.filter-bar {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
  background: #ffffff;
  padding: 16px 24px;
  border-radius: 14px;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
  margin-bottom: 16px;
}

.filter-type-group {
  display: flex;
  gap: 6px;
}

.filter-btn {
  padding: 7px 18px;
  border-radius: 8px;
  border: 1.5px solid #cbd5e1;
  background: #f8fafc;
  color: #475569;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
}

.filter-btn:hover {
  border-color: #4f46e5;
  color: #4f46e5;
}

.filter-btn.active {
  background: #4f46e5;
  border-color: #4f46e5;
  color: #ffffff;
}

.filter-input {
  padding: 7px 12px;
  border-radius: 8px;
  border: 1.5px solid #cbd5e1;
  font-size: 0.875rem;
  color: #1e293b;
  outline: none;
  transition: border-color 0.15s;
}

.filter-input:focus {
  border-color: #4f46e5;
}

.filter-input-year {
  width: 100px;
}

.apply-btn {
  padding: 7px 20px;
  border-radius: 8px;
  background: #4f46e5;
  color: #ffffff;
  border: none;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
}

.apply-btn:hover:not(:disabled) {
  background: #4338ca;
}

.apply-btn:disabled {
  background: #a5b4fc;
  cursor: not-allowed;
}

.reset-btn {
  padding: 7px 16px;
  border-radius: 8px;
  background: transparent;
  color: #ef4444;
  border: 1.5px solid #ef4444;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
}

.reset-btn:hover {
  background: #fef2f2;
}

/* ── Filter badge ────────────────────────────────────────────────────────────── */
.filter-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #eef2ff;
  color: #3730a3;
  border: 1px solid #c7d2fe;
  border-radius: 20px;
  padding: 6px 14px;
  font-size: 0.82rem;
  margin-bottom: 24px;
}

.filter-badge-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #4f46e5;
}

/* ── Stat card ───────────────────────────────────────────────────────────────── */
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

/* ── Chart grid ──────────────────────────────────────────────────────────────── */
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

/* ── Placeholders ────────────────────────────────────────────────────────────── */
.loading-placeholder {
  height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-style: italic;
  color: #94a3b8;
}

.no-data-placeholder {
  height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  font-weight: 500;
  color: #94a3b8;
  background: #f8fafc;
  border-radius: 10px;
  border: 1.5px dashed #cbd5e1;
  width: 100%;
}
</style>
