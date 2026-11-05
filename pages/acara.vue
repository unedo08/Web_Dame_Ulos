<template>
  <div>
    <title>Acara</title>
    <div class="judul text-xl font-semibold mb-2">Acara</div>

    <div class="page-table-toolbar acara-toolbar flex items-center justify-between">
      <input class="search-box standard-search-bar acara-search mb-2 rounded-md" v-model="searchQuery" type="text"
        placeholder="Search acara..." />
      <button class="btn-add mb-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 w-[104px] h-[30px]" @click="openModal">
        + Tambah Acara
      </button>
    </div>

    <!-- Product Table -->
    <div>
      <div class="acara-table-wrapper" v-sticky-action-shadow>
        <table class="datatable acara-table rounded-md">
          <thead class="bg-blue-100">
            <tr>
              <th class="px-4 py-2 text-left">Nama Acara</th>
              <th class="px-4 py-2 text-left">Jumlah Barang</th>
              <th class="px-4 py-2 text-left">Modal Barang</th>
              <th class="px-4 py-2 text-left">Harga Net</th>
              <th class="px-4 py-2 text-left">Harga Price Tag</th>
              <th class="px-4 py-2 text-left">Keterangan</th>
              <th class="px-4 py-2 text-left">Status</th>
              <th class="px-4 py-2 text-left sticky-action">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="acara in pagination" :key="acara.acara_id"
              class="odd:bg-white even:bg-gray-50 hover:bg-gray-100">
              <td class="px-4 py-2">{{ acara.acara_nama }}</td>
              <td class="px-4 py-2">{{ acara.acara_jumlahbarang }}</td>
              <td class="px-4 py-2">
                {{ formatRupiah(acara.acara_modalbarang) }}
              </td>
              <td class="px-4 py-2">
                {{ formatRupiah(acara.acara_harganetbarang) }}
              </td>
              <td class="px-4 py-2">
                {{ formatRupiah(acara.acara_hargapricetagbarang) }}
              </td>
              <td class="px-4 py-2">{{ acara.acara_keterangan }}</td>
              <td class="px-4 py-2">{{ acara.acara_status }}</td>
              <td class="space-x-2 px-4 py-2 sticky-action">
                <template v-if="acara.acara_status !== 'Selesai'">
                  <button class="text-blue-500 hover:text-blue-700" @click="editItem(acara)" title="Edit">
                    <PlusIcon class="w-5 h-5" />
                  </button>

                  <button class="text-green-500 hover:text-green-700" @click="markAsDone(acara)" title="Selesai">
                    <CheckCircleIcon class="w-5 h-5" />
                  </button>

                  <button class="text-yellow-500 hover:text-yellow-700" @click="exportItem(acara.acara_id)"
                    title="Export">
                    <ArrowDownTrayIcon class="w-5 h-5" />
                  </button>

                  <button class="text-red-500 hover:text-red-700"
                    @click="deleteProduct(acara.acara_id, acara.acara_nama)" title="Delete">
                    <TrashIcon class="w-5 h-5" />
                  </button>
                </template>
                <template v-else>
                  <button class="text-yellow-500 hover:text-yellow-700" @click="exportItem(acara.acara_id)"
                    title="Export">
                    <ArrowDownTrayIcon class="w-5 h-5" />
                  </button>
                </template>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="cl-pagination">
        <div class="cl-pagination-left">
          <span>
            Menampilkan {{ startItem }} sampai {{ endItem }} dari {{ sortedListAcara.length }}
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
    </div>

    <!-- Modal Dialog -->
    <div v-if="isModalOpen" class="fixed inset-0 flex justify-center items-center bg-gray-800 bg-opacity-50 z-[2000]" @click.self="closeModal">
      <div class="form-modal-container relative bg-white p-6 rounded-lg shadow-lg max-w-lg w-full">
        <div class="form-modal-header">
          <h3 class="text-xl font-semibold">Tambah Acara</h3>
          <button type="button" class="form-modal-close" aria-label="Tutup form" @click="closeModal"></button>
        </div>
        <form @submit.prevent="submitAcara">
          <div class="mb-4">
            <label for="acara_nama" class="block text-sm font-medium text-gray-700">Nama Acara <span
                class="required">*</span></label>
            <input v-model="newProduct.acara_nama" type="text" id="acara_nama"
              class="mt-1 block w-full border border-gray-300 pl-3 bg-[#FDFDFF] rounded-md shadow-sm h-[41px]"
              placeholder="Masukkan Nama Acara" required />
            <p v-if="errors.acara_nama" class="text-red-500 text-sm mt-1">
              {{ errors.acara_nama }}
            </p>
          </div>
          <div class="mb-4">
            <label for="acara_keterangan" class="block text-sm font-medium text-gray-700">Keterangan <span
                class="required">*</span></label>
            <textarea v-model="newProduct.acara_keterangan" id="acara_keterangan" rows="3"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 bg-[#FDFDFF]"
              placeholder="Masukkan keterangan acara" required></textarea>
            <p v-if="errors.acara_keterangan" class="text-red-500 text-sm mt-1">
              {{ errors.acara_keterangan }}
            </p>
          </div>

          <div class="flex justify-end form-add-actions">
            <button type="button" @click="closeModal"
              class="mr-4 px-4 py-2 bg-[#D8D8D8] text-gray-800 rounded-md hover:bg-[#D8D8D8]">
              Batal
            </button>
            <button type="submit" class="px-4 py-2 bg-[#1C9DBD] text-white rounded-md hover:bg-[#1C9DBD]">
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Print -->
    <div v-if="isModalPrintOpen" class="fixed inset-0 flex justify-center items-center bg-gray-800 bg-opacity-50" @click.self="closePrintModal">
      <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
        <h3 class="text-lg font-semibold mb-4">Print Barcode</h3>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Jumlah Kode</label>
          <input type="number" v-model="printJumlah" min="1"
            class="mt-1 block w-full border border-gray-300 rounded-md p-2" placeholder="Masukkan jumlah kode" />
        </div>
        <div class="flex justify-end">
          <button class="mr-4 text-gray-500" @click="closePrintModal">
            Cancel
          </button>
          <button class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600" @click="handlePrint">
            Print
          </button>
        </div>
      </div>
    </div>

    <div v-if="isEditModalOpen" class="fixed inset-0 flex justify-center items-center bg-gray-800 bg-opacity-50 z-[2000]" @click.self="closeEditModal">
      <div class="form-modal-container relative bg-white p-6 rounded-lg shadow-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="form-modal-header">
          <h3 class="text-lg font-semibold">
            Edit Acara - {{ editForm.acara_nama }}
          </h3>
          <button type="button" class="form-modal-close" aria-label="Tutup form" @click="closeEditModal"></button>
        </div>
        <div class="flex gap-2 mb-3">
          <input v-model="barcodeInput" @keyup.enter="addToTemp" type="text" class="flex-1 border rounded-md px-3 py-2"
            placeholder="Scan / ketik barcode" />
          <button @click="addToTemp" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            Tambah
          </button>
        </div>

        <div class="mb-3">
          <div class="text-xs text-gray-600 mb-1">Barcode siap dikirim:</div>
          <div class="flex flex-wrap gap-2">
            <span v-for="(code, i) in scannedCodes" :key="i" class="bg-gray-200 px-2 py-1 rounded text-xs">
              {{ code }}
            </span>
          </div>
        </div>

        <button @click="addListBarangAcara" class="mb-4 px-4 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600">
          Tambah ke List
        </button>

        <div class="acara-modal-table-wrapper">
          <table class="datatable mb-4">
            <thead class="bg-blue-100">
              <tr>
                <th>Kode</th>
                <th>Modal</th>
                <th>Harga Net</th>
                <th>Price Tag</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in listBarangAcara" :key="item.barangentry_id">
                <td>{{ item.code_nama }}</td>
                <td>{{ formatRupiah(item.barangentry_modal) }}</td>
                <td>{{ formatRupiah(item.barangentry_harga_net) }}</td>
                <td>{{ formatRupiah(item.barangentry_price_tag) }}</td>
                <td>
                  <button class="text-red-500" @click="removeItem(item.barangentry_id)">
                    <TrashIcon class="w-5 h-5" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="flex justify-end gap-2 form-add-actions">
          <button @click="closeEditModal" class="px-4 py-2 bg-gray-300 rounded">
            Batal
          </button>
          <button @click="submitEdit" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
            Submit
          </button>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import {
  CheckCircleIcon,
  ArrowDownTrayIcon,
  TrashIcon,
  PlusIcon
} from "@heroicons/vue/24/solid";

import { useAcara } from "~/composables/useAcara";

const {
  searchQuery,
  pagination,
  sortedListAcara,

  currentPage,
  itemsPerPage,
  totalPages,
  startItem,
  endItem,
  paginatedPages,

  isModalOpen,
  isModalPrintOpen,
  isEditModalOpen,

  newProduct,
  errors,
  editForm,
  barcodeInput,
  scannedCodes,
  listBarangAcara,
  printJumlah,

  formatRupiah,

  openModal,
  closeModal,
  submitAcara,

  editItem,
  closeEditModal,
  addToTemp,
  addListBarangAcara,
  removeItem,
  submitEdit,

  markAsDone,
  deleteProduct,
  exportItem,

  closePrintModal,
  handlePrint,

} = useAcara();
</script>

<style scoped src="~/assets/css/acara.css"></style>
