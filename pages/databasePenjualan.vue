<template>
  <div>
    <title>Menu Database Penjualan</title>
    <div class="judul text-xl font-semibold mb-4">
      Database Penjualan
    </div>

    <div class="page-table-toolbar penjualan-toolbar">
      <input v-model="searchQuery" type="text" class="search-box standard-search-bar"
        placeholder="Cari transaksi penjualan..." />

      <div class="penjualan-date-actions">
        <div class="penjualan-date-filters">
          <input type="date" v-model="startDate" class="penjualan-date-input" />
          <input type="date" v-model="endDate" class="penjualan-date-input" />
        </div>

        <button class="penjualan-export-btn" @click="exportExcel">
          Export Excel
        </button>
      </div>
    </div>

    <div class="penjualan-table-wrapper" v-sticky-action-shadow>
      <table class="datatable rounded-md">
        <thead class="bg-blue-100">
          <tr>
            <th class="px-4 py-2 text-left">Tanggal Transaksi</th>
            <th class="px-4 py-2 text-left">Nama Akun</th>
            <th class="px-4 py-2 text-left">Jenis Transaksi</th>
            <th class="px-4 py-2 text-left">Acara</th>
            <th class="px-4 py-2 text-left">Platform</th>
            <th class="px-4 py-2 text-left">Kode Barang</th>
            <th class="px-4 py-2 text-left">Nama Ulos</th>
            <th class="px-4 py-2 text-left">Jumlah</th>
            <th class="px-4 py-2 text-left">Harga</th>
            <th class="px-4 py-2 text-left">Subtotal</th>
            <th class="px-4 py-2 text-left">Status</th>
            <th class="px-4 py-2 text-left">Pembayaran</th>
            <th class="px-4 py-2 text-left">Catatan</th>
            <th class="px-4 py-2 text-left sticky-action">Aksi</th>
          </tr>
        </thead>

        <tbody>
          <template v-for="(trx, trxIndex) in paginatedTransaksi" :key="trx.transaksi_id">
            <tr v-for="(item, i) in trx.items" :key="item.transaksidetail_id" :class="[
              trxIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50',
              i === 0 ? 'group-start' : ''
            ]">
              <td v-if="i === 0" :rowspan="trx.items.length" class="text-left align-top px-2 py-1">
                {{ formatDate(trx.created_at) }}
              </td>

              <td v-if="i === 0" :rowspan="trx.items.length" class="text-left align-top px-2 py-1">
                {{ trx.customer_nama }}
              </td>

              <td v-if="i === 0" :rowspan="trx.items.length" class="text-left align-top px-2 py-1">
                {{ trx.transaksi_tipe }}
              </td>

              <td v-if="i === 0" :rowspan="trx.items.length" class="text-left align-top px-2 py-1">
                {{ trx.transaksi_acara || "-" }}
              </td>

              <td class="text-left align-top px-2 py-1">
                {{ trx.transaksi_tipe === 'Offline' ? "Offline" : item.transaksi_platform }}
              </td>
              <td>{{ item.code_nama || "-" }}</td>
              <td>{{ item.barang_nama || "-" }}</td>
              <td>{{ item.jumlah_barang }}</td>
              <td>{{ formatCurrency(Number(item.harga_barang)) }}</td>
              <td>{{ formatCurrency(Number(item.harga_barang) * item.jumlah_barang) }}</td>

              <td>
                <span class="text-status px-2 py-1" :class="statusChipClass(item.transaksi_status)">
                  {{ item.transaksi_status === 1 ? "Closed" : "Open" }}
                </span>
              </td>

              <td>{{ trx.cara_bayar }}</td>

              <td v-if="i === 0" :rowspan="trx.items.length">
                {{ trx.catatan || "-" }}
              </td>


              <td class="sticky-action">
                <div class="relative inline-block text-left">
                  <button class="er-btn-action"
                    @click.stop="toggleActionDropdown(`${trx.transaksi_id}-${item.transaksidetail_id}`, $event)">
                    Aksi
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>

                  <Teleport to="body">
                    <div v-if="activeDropdown === `${trx.transaksi_id}-${item.transaksidetail_id}`"
                      :ref="setActionDropdownMenu" class="er-action-dropdown" @click.stop>
                      <button class="er-dropdown-item er-dropdown-item--print"
                        @click="closeActionDropdown(); openViewDetail(trx.transaksi_id)">
                        View
                      </button>

                      <button class="er-dropdown-item er-dropdown-item--send"
                        @click="closeActionDropdown(); handlePrint(trx.transaksi_id)">
                        Print
                      </button>

                      <button class="er-dropdown-item er-dropdown-item--delete"
                        @click="closeActionDropdown(); deleteTransaksi(item.transaksidetail_id)">
                        Delete
                      </button>
                    </div>
                  </Teleport>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div class="cl-pagination">
      <div class="cl-pagination-left">
        <span>
          Menampilkan {{ startItem }} sampai {{ endItem }} dari {{ totalTransaksi }}
        </span>

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
        <button class="cl-page-nav" :disabled="currentPage === 1" @click="currentPage = 1">
          &#xAB;
        </button>

        <button class="cl-page-nav" :disabled="currentPage === 1" @click="currentPage--">
          &#x3C;
        </button>

        <template v-for="(page, idx) in paginatedPages" :key="idx">
          <button v-if="page !== '...'" class="cl-page-btn" :class="{ 'cl-page-btn--active': currentPage === page }"
            @click="currentPage = page">
            {{ page }}
          </button>

          <span v-else class="cl-page-btn cl-page-btn--dots">
            ...
          </span>
        </template>

        <button class="cl-page-nav" :disabled="currentPage === totalPages" @click="currentPage++">
          &#x3E;
        </button>

        <button class="cl-page-nav" :disabled="currentPage === totalPages" @click="currentPage = totalPages">
          &#xBB;
        </button>
      </div>
    </div>

    <ViewDetailModal :show="showDetailModal" :id="trx_id" @close="showDetailModal = false" />
  </div>
</template>

<script setup>
import ViewDetailModal from "../components/ModalViewDetail.vue";
import { useDatabasePenjualan } from "~/composables/useDatabasePenjualan";

const {
    startDate,
    endDate,
    searchQuery,
    showDetailModal,
    trx_id,
    currentPage,
    itemsPerPage,

    activeDropdown,
    closeActionDropdown,
    setActionDropdownMenu,
    toggleActionDropdown,

    filteredTransaksi,
    paginatedTransaksi,
    totalTransaksi,
    totalPages,
    startItem,
    endItem,
    paginatedPages,

    formatCurrency,
    formatDate,
    statusChipClass,

    openViewDetail,
    deleteTransaksi,
    exportExcel,
    handlePrint,
} = useDatabasePenjualan();
</script>

<style scoped src="~/assets/css/databasePenjualan.css"></style>