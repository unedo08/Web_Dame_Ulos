<template>
  <div>
    <title>Packaging</title>

    <div class="judul text-xl font-semibold mb-4">
      Daftar Packaging
    </div>

    <div class="page-table-toolbar flex justify-between items-center">
      <input
        v-model="searchQuery"
        type="text"
        class="search-box standard-search-bar rounded-md"
        placeholder="Cari Pengiriman Barang..."
      />

      <button
        @click="exportToExcel"
        class="packaging-export-btn bg-green-500 hover:bg-green-600 text-white rounded"
      >
        Export Excel
      </button>
    </div>

    <div class="packaging-table-wrapper">
      <table class="datatable packaging-table rounded-md">
        <thead class="bg-blue-100">
          <tr>
            <th class="text-left">No</th>
            <th class="text-left">Tanggal</th>
            <th class="text-left">Nama Akun</th>
            <th class="text-left">Nama Penerima</th>
            <th class="text-left">Telepon</th>
            <th class="text-left">Ekspedisi</th>
            <th class="text-left">Alamat</th>
            <th class="text-left">Status Kirim</th>
            <th class="text-left">Barang</th>
            <th class="text-left sticky-action">Aksi</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="(row, index) in pagination"
            :key="row.pengirimanBarang_id"
            :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
          >
            <td>{{ index + 1 }}</td>

            <td>
              {{ formatTanggal(row.created_at) }}
            </td>

            <td>
              {{ row.pengirimanBarang_akun_penerima }}
            </td>

            <td>
              {{ row.pengirimanBarang_nama_penerima }}
            </td>

            <td>
              {{ row.pengirimanBarang_no_telepon }}
            </td>

            <td>
              {{ row.pengirimanBarang_jenis_pengiriman_barang }}
            </td>

            <td>
              {{ row.pengirimanBarang_alamat_pengiriman_barang }}
            </td>

            <td>
              <span
                class="text-status px-1 py-1 rounded-full font-semibold"
                :class="statusChipClass(row.status_pengiriman)"
              >
                {{ row.status_pengiriman || '-' }}
              </span>
            </td>

            <td>
              <div
                v-for="code in row.list_code_nama"
                :key="code"
              >
                {{ code }}
              </div>
            </td>

            <!-- STICKY KOLOM AKSI -->
            <td class="sticky-action">
              <div class="flex gap-2">
                <template v-if="row.status_pengiriman !== 'DONE'">
                  <button
                    class="px-2 py-1 bg-blue-500 text-white rounded text-xs"
                    @click="openModalEdit(row)"
                  >
                    Edit
                  </button>

                  <button
                    class="px-2 py-1 bg-green-600 text-white rounded text-xs"
                    @click="selesaikanPackaging(row)"
                  >
                    Selesai
                  </button>
                </template>

                <button
                  class="px-2 py-1 bg-red-500 text-white rounded text-xs"
                  @click="deleteData(row.pengirimanBarang_id)"
                >
                  Delete
                </button>

                <button
                  v-if="row.status_pengiriman === 'DONE'"
                  class="px-2 py-1 bg-indigo-600 text-white rounded text-xs"
                  @click="printLabel(row)"
                >
                  Print
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- PAGINATION -->
    <div class="cl-pagination">
      <div class="cl-pagination-left">
        <span>
          Menampilkan {{ startItem }} sampai {{ endItem }} dari
          {{ filteredData.length }}
        </span>

        <span class="cl-pagination-sep">|</span>

        <span>Tampilkan</span>

        <select
          v-model="itemsPerPage"
          class="cl-perpage-select"
          @change="currentPage = 1"
        >
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>

        <span>data</span>
      </div>

      <div class="cl-pagination-pages">
        <button
          class="cl-page-nav"
          :disabled="currentPage === 1"
          @click="currentPage = 1"
        >
          &#xAB;
        </button>

        <button
          class="cl-page-nav"
          :disabled="currentPage === 1"
          @click="currentPage--"
        >
          &#x3C;
        </button>

        <template
          v-for="(page, idx) in paginatedPages"
          :key="idx"
        >
          <button
            v-if="page !== '...'"
            class="cl-page-btn"
            :class="{ 'cl-page-btn--active': currentPage === page }"
            @click="currentPage = page"
          >
            {{ page }}
          </button>

          <span
            v-else
            class="cl-page-btn cl-page-btn--dots"
          >
            ...
          </span>
        </template>

        <button
          class="cl-page-nav"
          :disabled="currentPage === totalPages"
          @click="currentPage++"
        >
          &#x3E;
        </button>

        <button
          class="cl-page-nav"
          :disabled="currentPage === totalPages"
          @click="currentPage = totalPages"
        >
          &#xBB;
        </button>
      </div>
    </div>

    <!-- MODAL EDIT -->
    <ModalEditPackaging
      v-model:show="isModalOpen"
      :barang="selectedBarang"
      :pengiriman="selectedPengiriman"
      @save="handleSave"
      @close="isModalOpen = false"
    />
  </div>
</template>

<script setup>
import ModalEditPackaging from "../components/ModalEditPackaging.vue";
import { usePackaging } from "~/composables/usePackaging";

const {
  searchQuery,
  currentPage,
  itemsPerPage,
  isModalOpen,
  selectedPengiriman,
  selectedBarang,
  exportToExcel,
  pagination,
  formatTanggal,
  statusChipClass,
  openModalEdit,
  selesaikanPackaging,
  deleteData,
  printLabel,
  startItem,
  endItem,
  filteredData,
  totalPages,
  paginatedPages,
  handleSave,
} = usePackaging();
</script>

<style scoped src="@/assets/css/packaging-page.css"></style>