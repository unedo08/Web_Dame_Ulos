<template>
  <div class="cd-page">
    <title>Detail Customer</title>

    <!-- Breadcrumb -->
    <div class="cd-breadcrumb">
      <div class="cd-breadcrumb-text">
        <NuxtLink to="/customer" class="cd-breadcrumb-link">All Customer</NuxtLink>
        <span class="cd-breadcrumb-sep">&gt;</span>
        <span>{{ customerData?.customer_nama || '...' }}</span>
      </div>
      <NuxtLink to="/customer" class="cd-back-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali
      </NuxtLink>
    </div>

    <!-- Customer Info Card -->
    <div class="cd-card cd-info-card">
      <div class="cd-info-top">
        <div class="cd-avatar">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
        </div>

        <div>
          <p class="cd-info-name">{{ customerData?.customer_nama || '-' }}</p>
          <p class="cd-info-since">Pelanggan sejak {{ formatDateLong(customerData?.created_at) }}</p>
          <div class="cd-info-contact">
            <span class="cd-info-phone">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21L8.5 10.5a11.04 11.04 0 005 5l1.113-1.724a1 1 0 011.21-.502l4.493 1.498A1 1 0 0121 15.72V19a2 2 0 01-2 2h-1C9.163 21 3 14.837 3 7V5z" />
              </svg>
              {{ customerData?.customer_notelepon || '-' }}
            </span>
            <span class="cd-info-address">{{ customerData?.customer_alamat || '-' }}</span>
          </div>
        </div>
      </div>

      <div class="cd-stats">
        <div>
          <p class="cd-stat-label">Total  Pembelian</p>
          <p class="cd-stat-value">{{ formatCurrency(totalPembelian) }}</p>
        </div>
        <div>
          <p class="cd-stat-label">Total  Transaksi</p>
          <p class="cd-stat-value">{{ totalTransaksi }}</p>
        </div>
      </div>
    </div>

    <!-- Search -->
    <div class="cd-card cd-search-card">
      <svg class="cd-search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z" />
      </svg>
      <input
        v-model="searchQuery"
        type="text"
        class="cd-search-input"
        placeholder="Cari transaksi..."
        @input="currentPage = 1"
      />
    </div>

    <!-- Transaction Table Card -->
    <div class="cd-card cd-table-card">
      <div class="cd-table-header">Riwayat Transaksi ({{ filteredTransaksi.length }})</div>

      <table class="cd-table">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Jenis Transaksi</th>
            <th>Platform</th>
            <th>Harga</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="paginatedTransaksi.length === 0">
            <td colspan="7" class="cd-table-empty">Tidak ada data transaksi.</td>
          </tr>
          <tr v-for="(trx, i) in paginatedTransaksi" :key="i">
            <td>{{ formatDate(trx.created_at) }}</td>
            <td>{{ trx.code_nama || '-' }}</td>
            <td>{{ trx.barangentry_nama || '-' }}</td>
            <td>{{ trx.transaksi_tipe || '-' }}</td>
            <td>{{ trx.transaksi_platform || '-' }}</td>
            <td>{{ formatCurrency(trx.transaksidetail_harga_barang) }}</td>
            <td>
              <span
                class="cd-badge"
                :class="trx.transaksidetail_status_penjualan === 1 ? 'cd-badge-closed' : 'cd-badge-open'"
              >
                {{ trx.transaksidetail_status_penjualan === 1 ? 'Closed' : 'Open' }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="cd-pagination">
      <div class="cd-pagination-left">
        <span>Menampilkan {{ startItem }} sampai {{ endItem }} dari {{ filteredTransaksi.length }}</span>
        <span class="cd-pagination-sep">|</span>
        <span>Tampilkan</span>
        <select v-model="itemsPerPage" class="cd-perpage-select" @change="currentPage = 1">
          <option :value="5">5</option>
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="100">100</option>
        </select>
        <span>data</span>
      </div>

      <div class="cd-pagination-pages">
        <!-- First -->
        <button class="cd-page-nav" :disabled="currentPage === 1" @click="currentPage = 1">&#xAB;</button>
        <!-- Prev -->
        <button class="cd-page-nav" :disabled="currentPage === 1" @click="currentPage--">&#x3C;</button>

        <template v-for="(page, idx) in paginatedPages" :key="idx">
          <button
            v-if="page !== '...'"
            class="cd-page-btn"
            :class="{ 'cd-page-btn--active': currentPage === page }"
            @click="currentPage = page"
          >
            {{ page }}
          </button>
          <span v-else class="cd-page-btn cd-page-btn--dots">...</span>
        </template>

        <!-- Next -->
        <button class="cd-page-nav" :disabled="currentPage === totalPages" @click="currentPage++">&#x3E;</button>
        <!-- Last -->
        <button class="cd-page-nav" :disabled="currentPage === totalPages" @click="currentPage = totalPages">&#xBB;</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const route = useRoute()
const { $api } = useNuxtApp()
const config = useRuntimeConfig()
const url = config.public.apiBase

const customerData  = ref(null)
const transactions  = ref([])
const totalPembelian = ref(0)
const totalTransaksi = ref(0)
const searchQuery   = ref('')
const currentPage   = ref(1)
const itemsPerPage  = ref(5)

onMounted(fetchData)

async function fetchData() {
  try {
    const res = await $api.get(`${url}/api/customer/${route.params.id}/detail`)
    const d = res.data.data
    customerData.value   = d.customer
    transactions.value   = d.transactions
    totalPembelian.value = d.total_pembelian
    totalTransaksi.value = d.total_transaksi
  } catch (e) {
    console.error('Gagal memuat detail customer:', e)
  }
}

const filteredTransaksi = computed(() => {
  const q = searchQuery.value.toLowerCase().trim()
  if (!q) return transactions.value
  return transactions.value.filter(t =>
    (t.code_nama        || '').toLowerCase().includes(q) ||
    (t.barangentry_nama || '').toLowerCase().includes(q) ||
    (t.transaksi_tipe   || '').toLowerCase().includes(q) ||
    (t.transaksi_platform || '').toLowerCase().includes(q)
  )
})

const totalPages = computed(() =>
  Math.max(1, Math.ceil(filteredTransaksi.value.length / itemsPerPage.value))
)

const paginatedTransaksi = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return filteredTransaksi.value.slice(start, start + itemsPerPage.value)
})

const startItem = computed(() =>
  filteredTransaksi.value.length ? (currentPage.value - 1) * itemsPerPage.value + 1 : 0
)

const endItem = computed(() =>
  Math.min(currentPage.value * itemsPerPage.value, filteredTransaksi.value.length)
)

const paginatedPages = computed(() => {
  const total   = totalPages.value
  const current = currentPage.value
  if (total <= 5) return Array.from({ length: total }, (_, i) => i + 1)
  if (current <= 3) return [1, 2, 3, '...', total]
  if (current >= total - 2) return [1, '...', total - 2, total - 1, total]
  return [1, '...', current - 1, current, current + 1, '...', total]
})

const formatDate = (d) =>
  d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) : '-'

const formatDateLong = (d) =>
  d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-'

const formatCurrency = (v) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(Number(v) || 0)
</script>

<style>
@import '~/assets/css/customer-detail.css';
</style>
