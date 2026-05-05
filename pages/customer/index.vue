<template>
  <title>Menu Customer</title>
  <div class="cl-page">
    <h1 class="cl-title">Customer</h1>

    <!-- Search -->
    <div class="cl-search-wrap">
      <svg class="cl-search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z" />
      </svg>
      <input
        v-model="searchQuery"
        type="text"
        class="cl-search-input"
        placeholder="Cari customer..."
        @input="currentPage = 1"
      />
    </div>

    <!-- Table -->
    <div class="cl-table-wrap">
      <table class="cl-table">
        <colgroup>
          <col style="width: 50px">
          <col style="width: 120px">
          <col style="width: 150px">
          <col style="width: 200px">
          <col style="width: 250px">
          <col style="width: 150px">
          <col style="width: 150px">
          <col style="width: 180px">
        </colgroup>
        <thead>
          <tr>
            <th>#</th>
            <th class="sortable" @click="setSort('tanggal_daftar')">
              <div class="cl-th-inner">
                Tanggal
                <span class="cl-sort-icon" :class="{ active: sortField === 'tanggal_daftar' }">
                  <svg viewBox="0 0 10 6" fill="currentColor"><path d="M0 6l5-6 5 6z"/></svg>
                  <svg viewBox="0 0 10 6" fill="currentColor" style="transform:rotate(180deg)"><path d="M0 6l5-6 5 6z"/></svg>
                </span>
              </div>
            </th>
            <th class="sortable" @click="setSort('customer_akun')">
              <div class="cl-th-inner">
                Akun
                <span class="cl-sort-icon" :class="{ active: sortField === 'customer_akun' }">
                  <svg viewBox="0 0 10 6" fill="currentColor"><path d="M0 6l5-6 5 6z"/></svg>
                  <svg viewBox="0 0 10 6" fill="currentColor" style="transform:rotate(180deg)"><path d="M0 6l5-6 5 6z"/></svg>
                </span>
              </div>
            </th>
            <th class="sortable" @click="setSort('customer_nama')">
              <div class="cl-th-inner">
                Nama
                <span class="cl-sort-icon" :class="{ active: sortField === 'customer_nama' }">
                  <svg viewBox="0 0 10 6" fill="currentColor"><path d="M0 6l5-6 5 6z"/></svg>
                  <svg viewBox="0 0 10 6" fill="currentColor" style="transform:rotate(180deg)"><path d="M0 6l5-6 5 6z"/></svg>
                </span>
              </div>
            </th>
            <th class="sortable" @click="setSort('customer_alamat')">
              <div class="cl-th-inner">
                Alamat
                <span class="cl-sort-icon" :class="{ active: sortField === 'customer_alamat' }">
                  <svg viewBox="0 0 10 6" fill="currentColor"><path d="M0 6l5-6 5 6z"/></svg>
                  <svg viewBox="0 0 10 6" fill="currentColor" style="transform:rotate(180deg)"><path d="M0 6l5-6 5 6z"/></svg>
                </span>
              </div>
            </th>
            <th class="sortable" @click="setSort('total_transaksi')">
              <div class="cl-th-inner">
                Total Transaksi
                <span class="cl-sort-icon" :class="{ active: sortField === 'total_transaksi' }">
                  <svg viewBox="0 0 10 6" fill="currentColor"><path d="M0 6l5-6 5 6z"/></svg>
                  <svg viewBox="0 0 10 6" fill="currentColor" style="transform:rotate(180deg)"><path d="M0 6l5-6 5 6z"/></svg>
                </span>
              </div>
            </th>
            <th class="sortable" @click="setSort('customer_notelepon')">
              <div class="cl-th-inner">
                Nomor Telepon
                <span class="cl-sort-icon" :class="{ active: sortField === 'customer_notelepon' }">
                  <svg viewBox="0 0 10 6" fill="currentColor"><path d="M0 6l5-6 5 6z"/></svg>
                  <svg viewBox="0 0 10 6" fill="currentColor" style="transform:rotate(180deg)"><path d="M0 6l5-6 5 6z"/></svg>
                </span>
              </div>
            </th>
            <th>Aksi</th>
          </tr>
        </thead>

        <tbody>
          <tr v-if="paginatedCustomer.length === 0">
            <td colspan="8" class="cl-empty">Tidak ada data customer.</td>
          </tr>
          <tr v-for="(cust, index) in paginatedCustomer" :key="cust.customer_id">
            <td class="cl-col-num">{{ startItem + index }}</td>
            <td>{{ formatDate(cust.tanggal_daftar) }}</td>
            <td>{{ cust.customer_akun || '-' }}</td>
            <td>{{ cust.customer_nama }}</td>
            <td>{{ cust.customer_alamat || '-' }}</td>
            <td class="cl-col-total">{{ formatCurrency(cust.total_transaksi) }}</td>
            <td>{{ cust.customer_notelepon || '-' }}</td>
            <td>
              <div class="cl-actions">
                <NuxtLink
                  :to="`/customer/detail?id=${cust.customer_id}`"
                  class="cl-btn cl-btn-view"
                  @click="saveCustomerId(cust.customer_id)"
                >
                  View
                </NuxtLink>
                <button class="cl-btn cl-btn-delete" @click="deleteCustomer(cust.customer_id, cust.customer_nama)">
                  Delete
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="cl-pagination">
      <div class="cl-pagination-left">
        <span>Menampilkan {{ startItem }} sampai {{ endItem }} dari {{ filteredCustomer.length }}</span>
        <span class="cl-pagination-sep">|</span>
        <span>Tampilkan</span>
        <select v-model="itemsPerPage" class="cl-perpage-select" @change="currentPage = 1">
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
        <span>data</span>
      </div>

      <div class="cl-pagination-pages">
        <button class="cl-page-nav" :disabled="currentPage === 1" @click="currentPage = 1">&#xAB;</button>
        <button class="cl-page-nav" :disabled="currentPage === 1" @click="currentPage--">&#x3C;</button>

        <template v-for="(page, idx) in paginatedPages" :key="idx">
          <button
            v-if="page !== '...'"
            class="cl-page-btn"
            :class="{ 'cl-page-btn--active': currentPage === page }"
            @click="currentPage = page"
          >
            {{ page }}
          </button>
          <span v-else class="cl-page-btn cl-page-btn--dots">...</span>
        </template>

        <button class="cl-page-nav" :disabled="currentPage === totalPages" @click="currentPage++">&#x3E;</button>
        <button class="cl-page-nav" :disabled="currentPage === totalPages" @click="currentPage = totalPages">&#xBB;</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Swal from 'sweetalert2'

const { $api } = useNuxtApp()
const config = useRuntimeConfig()
const url = config.public.apiBase

const customer     = ref([])
const searchQuery  = ref('')
const currentPage  = ref(1)
const itemsPerPage = ref(10)
const sortField    = ref('tanggal_daftar')
const sortDir      = ref('desc')

onMounted(fetchData)

async function fetchData() {
  try {
    const res = await $api.get(`${url}/api/customer`)
    customer.value = res.data.data.map(item => ({
      customer_id:       item.customer_id,
      customer_akun:     item.customer_akun,
      customer_nama:     item.customer_nama,
      customer_alamat:   item.customer_alamat,
      customer_notelepon: item.customer_notelepon,
      tanggal_daftar:    item.tanggal_daftar,
      total_transaksi:   Number(item.total_transaksi) || 0,
    }))
  } catch (e) {
    console.error('Error fetching customer:', e)
  }
}

function setSort(field) {
  if (sortField.value === field) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortDir.value = 'asc'
  }
  currentPage.value = 1
}

const filteredCustomer = computed(() => {
  const q = searchQuery.value.toLowerCase().trim()
  const list = q
    ? customer.value.filter(c =>
        (c.customer_nama     || '').toLowerCase().includes(q) ||
        (c.customer_akun     || '').toLowerCase().includes(q) ||
        (c.customer_alamat   || '').toLowerCase().includes(q) ||
        (c.customer_notelepon || '').toLowerCase().includes(q)
      )
    : customer.value

  return [...list].sort((a, b) => {
    let valA = a[sortField.value] ?? ''
    let valB = b[sortField.value] ?? ''

    if (sortField.value === 'tanggal_daftar') {
      valA = new Date(valA).getTime() || 0
      valB = new Date(valB).getTime() || 0
    } else if (sortField.value === 'total_transaksi') {
      valA = Number(valA)
      valB = Number(valB)
    } else {
      valA = String(valA).toLowerCase()
      valB = String(valB).toLowerCase()
    }

    if (valA < valB) return sortDir.value === 'asc' ? -1 : 1
    if (valA > valB) return sortDir.value === 'asc' ? 1 : -1
    return 0
  })
})

const totalPages = computed(() =>
  Math.max(1, Math.ceil(filteredCustomer.value.length / itemsPerPage.value))
)

const paginatedCustomer = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return filteredCustomer.value.slice(start, start + itemsPerPage.value)
})

const startItem = computed(() =>
  filteredCustomer.value.length ? (currentPage.value - 1) * itemsPerPage.value + 1 : 0
)

const endItem = computed(() =>
  Math.min(currentPage.value * itemsPerPage.value, filteredCustomer.value.length)
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
  d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-'

const formatCurrency = (v) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(Number(v) || 0)

function saveCustomerId(id) {
  sessionStorage.setItem('selected_customer_id', id)
  localStorage.setItem('selected_customer_id', id)
}

async function deleteCustomer(id, nama) {
  const confirm = await Swal.fire({
    title: 'Hapus Customer?',
    text: `Anda yakin ingin menghapus '${nama}'?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal',
    confirmButtonColor: '#EF4444',
  })
  if (!confirm.isConfirmed) return

  try {
    await $api.delete(`${url}/api/customer/deleteCustomer/${id}`)
    customer.value = customer.value.filter(c => c.customer_id !== id)
    Swal.fire('Berhasil!', 'Customer berhasil dihapus.', 'success')
  } catch {
    Swal.fire('Gagal!', 'Tidak dapat menghapus data.', 'error')
  }
}
</script>

<style>
@import '~/assets/css/customer-list.css';
</style>
