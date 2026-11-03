<template>
  <div>
    <title>Transaksi Live</title>

    <div class="judul text-xl font-semibold mb-4">Transaksi Live</div>

    <div class="page-submenu flex space-x-6">
      <button @click="activeTab = 'order'" class="pb-1 text-sm relative"
        :class="{ 'text-red-900': activeTab === 'order', 'text-gray-500': activeTab !== 'order', 'is-active': activeTab === 'order' }">
        Order
        <span v-if="activeTab === 'order'" class="absolute left-0 right-0 -bottom-0.5 h-[2px] bg-red-900 mx-auto"
          style="width: 90%"></span>
      </button>

      <button @click="activeTab = 'transaction'" class="pb-1 text-sm relative"
        :class="{ 'text-red-900': activeTab === 'transaction', 'text-gray-500': activeTab !== 'transaction', 'is-active': activeTab === 'transaction' }">
        Transaction
        <span v-if="activeTab === 'transaction'" class="absolute left-0 right-0 -bottom-0.5 h-[2px] bg-red-900 mx-auto"
          style="width: 90%"></span>
      </button>
    </div>

    <!-- TAB ORDER -->
    <div class="mx-auto" v-show="activeTab === 'order'">
      <div class="page-table-toolbar flex items-center justify-between">
        <div class="flex-1">
          <input v-model="searchQuery" type="text" class="search-box rounded-md" placeholder="Cari data live..." />
        </div>

        <div>
          <button class="btn-add bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[104px] h-[25px]"
            @click="openModalAddOrder">
            + Live
          </button>
        </div>
      </div>

      <div class="live-table-wrapper">
        <table class="datatable live-order-table rounded-md">
          <thead class="bg-blue-100">
            <tr>
              <th class="px-4 py-2 text-left">Nama Akun</th>
              <th class="px-4 py-2 text-left">Tanggal</th>
              <th class="px-4 py-2 text-left">Kode Barang</th>
              <th class="px-4 py-2 text-left">Nama Barang</th>
              <th class="px-4 py-2 text-left">Nama Platform</th>
              <th class="px-4 py-2 text-left">Harga Terjual</th>
              <th class="px-4 py-2 text-left">Total Harga Terjual (Akun)</th>
              <th class="px-4 py-2 text-left sticky-action">Aksi</th>
            </tr>
          </thead>

          <tbody>
            <template v-for="(items, akun) in paginatedGroupedData" :key="akun">
              <tr v-for="(pengiriman, index) in items" :class="[
                index % 2 === 0 ? 'bg-white' : 'bg-gray-50',
                index === items.length - 1 ? 'border-b-2 border-gray-200' : '',
              ]">
                <td class="px-4 py-2" v-if="index === 0" :rowspan="items.length">
                  {{ akun }}
                </td>

                <td class="px-4 py-2">
                  {{ formatTanggal(pengiriman.created_at) }}
                </td>

                <td class="px-4 py-2">
                  {{ pengiriman.code_nama }}
                </td>

                <td class="px-4 py-2">
                  {{ pengiriman.barangentry_nama }}
                </td>

                <td class="px-4 py-2">
                  {{ capitalizeFirst(pengiriman.live_order_platform) }}
                </td>

                <td class="px-4 py-2">
                  {{ formatCurrency(pengiriman.live_order_harga_terjual) }}
                </td>

                <td class="px-4 py-2" v-if="index === 0" :rowspan="items.length">
                  {{ formatCurrency(totalHargaPerAkun[akun]) }}
                </td>

                <td class="px-4 py-2 sticky-action">
                  <div class="flex space-x-2">
                    <button
                      class="flex items-center gap-1 px-2 py-1 bg-green-500 text-white hover:bg-green-600 rounded-[10px] text-s"
                      @click="editOrderLive(pengiriman.live_order_id)">
                      Edit
                    </button>

                    <button
                      class="flex items-center gap-1 px-2 py-1 bg-red-500 text-white hover:bg-red-600 rounded-[10px] text-s"
                      @click="deleteOrder(pengiriman.live_order_id)">
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <!-- PAGINATION ORDER -->
      <div class="cl-pagination">
        <div class="cl-pagination-left">
          <span>
            Menampilkan {{ startItem }} sampai {{ endItem }} dari {{ totalGroups }}
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

    <!-- TAB TRANSACTION -->
    <div class="mx-auto" v-show="activeTab === 'transaction'">
      <div class="page-table-toolbar">
        <input v-model="searchQuery" type="text" class="search-box rounded-md" placeholder="Cari data live..." />
      </div>

      <div class="live-table-wrapper">
        <table class="datatable live-transaction-table rounded-md">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Akun</th>
              <th>Jumlah</th>
              <th class="sticky-action">Aksi</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="(pengiriman, index) in pagination" :key="pengiriman.pengirimanBarang_id">
              <td>{{ index + 1 }}</td>
              <td>{{ pengiriman.live_order_nama_akun }}</td>
              <td>{{ pengiriman.jumlah }}</td>

              <td class="sticky-action">
                <button class="bg-green-500 hover:bg-green-800 text-white px-3 py-1 rounded-md"
                  @click="openModalEditTransaksi(pengiriman.live_order_nama_akun)">
                  Edit
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- PAGINATION TRANSACTION -->
      <div class="cl-pagination">
        <div class="cl-pagination-left">
          <span>
            Menampilkan {{ transactionStartItem }} sampai {{ transactionEndItem }} dari {{ filteredTransaction.length }}
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

          <template v-for="(page, idx) in transactionPaginatedPages" :key="idx">
            <button v-if="page !== '...'" class="cl-page-btn" :class="{ 'cl-page-btn--active': currentPage === page }"
              @click="currentPage = page">
              {{ page }}
            </button>

            <span v-else class="cl-page-btn cl-page-btn--dots">
              ...
            </span>
          </template>

          <button class="cl-page-nav" :disabled="currentPage === transactionTotalPages" @click="currentPage++">
            &#x3E;
          </button>

          <button class="cl-page-nav" :disabled="currentPage === transactionTotalPages"
            @click="currentPage = transactionTotalPages">
            &#xBB;
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL ADD ORDER -->
  <div v-if="isModalOpenAddOrder"
    class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 z-[2000]"
    @click.self="closeModalAddOrder">
    <div class="form-modal-container relative bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
      <button type="button" class="form-modal-close" aria-label="Tutup form" @click="closeModalAddOrder"></button>
      <h3 class="text-lg font-semibold mb-4">Tambah Transaksi Live</h3>

      <form @submit.prevent="submitLiveOrder">
        <div class="mb-4">
          <label for="barang" class="block text-sm font-medium text-gray-700 mb-1">
            Barang<span class="text-red-500">*</span>
          </label>

          <input ref="barangInput" type="text" id="barang" v-model="form.barang" placeholder="Masukkan nama barang"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400"
            required />

          <p v-if="errors.barang" class="text-red-500 text-sm mt-1">
            {{ errors.barang }}
          </p>
        </div>

        <div class="mb-4">
          <label for="namaAkun" class="block text-sm font-medium text-gray-700 mb-1">
            Nama Akun<span class="text-red-500">*</span>
          </label>

          <input type="text" id="namaAkun" v-model="form.namaAkun" placeholder="Masukkan nama akun"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400"
            required />

          <p v-if="errors.namaAkun" class="text-red-500 text-sm mt-1">
            {{ errors.namaAkun }}
          </p>
        </div>

        <div class="mb-4">
          <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
            Platform <span class="required">*</span>
          </label>

          <select v-model="form.platform"
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-cyan-500">
            <option disabled value="">Pilih Jenis Platform</option>
            <option v-for="platform in jenisPlatform" :key="platform.id" :value="platform.platform_nama">
              {{ platform.platform_nama }}
            </option>
          </select>

          <p v-if="errors.platform" class="text-red-500 text-sm mt-1">
            {{ errors.platform }}
          </p>
        </div>

        <div class="mb-4">
          <label for="hargaTotal" class="block text-sm font-medium text-gray-700 mb-1">
            Harga Total<span class="text-red-500">*</span>
          </label>

          <div class="flex">
            <span
              class="modal-currency-prefix inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
              Rp
            </span>

            <input type="text" id="hargaTotal" :value="formattedHarga" @input="updateHarga($event.target.value)"
              placeholder="Masukkan harga"
              class="w-full border border-gray-300 rounded-r-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400"
              required />

            <p v-if="errors.hargaTotal" class="text-red-500 text-sm mt-1">
              {{ errors.hargaTotal }}
            </p>
          </div>
        </div>

        <div class="flex justify-end space-x-2 form-add-actions">
          <button type="button" @click="closeModalAddOrder"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
            Batal
          </button>

          <button type="submit" :disabled="isSubmitting"
            class="px-4 py-2 bg-cyan-600 text-white rounded-md hover:bg-cyan-700">
            {{ isSubmitting ? "Menyimpan..." : "Simpan" }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL EDIT ORDER -->
  <div v-if="isModalOpenEditOrder"
    class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 z-[2000]"
    @click.self="closeModalEditOrder">
    <div class="form-modal-container relative bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
      <button type="button" class="form-modal-close" aria-label="Tutup form" @click="closeModalEditOrder"></button>
      <h3 class="text-lg font-semibold mb-4">Edit Transaksi Live</h3>

      <form @submit.prevent="submitLiveEditOrder">
        <div class="mb-4">
          <label for="barang" class="block text-sm font-medium text-gray-700 mb-1">
            Barang<span class="text-red-500">*</span>
          </label>

          <input type="text" id="barang" v-model="form.barang" placeholder="Masukkan nama barang"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400"
            required />

          <p v-if="errors.barang" class="text-red-500 text-sm mt-1">
            {{ errors.barang }}
          </p>
        </div>

        <div class="mb-4">
          <label for="namaAkun" class="block text-sm font-medium text-gray-700 mb-1">
            Nama Akun<span class="text-red-500">*</span>
          </label>

          <input type="text" id="namaAkun" v-model="form.namaAkun" placeholder="Masukkan nama akun"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400"
            required />

          <p v-if="errors.namaAkun" class="text-red-500 text-sm mt-1">
            {{ errors.namaAkun }}
          </p>
        </div>

        <div class="mb-4">
          <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
            Platform <span class="required">*</span>
          </label>

          <select v-model="form.platform"
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-cyan-500">
            <option disabled value="">Pilih Jenis Platform</option>
            <option v-for="platform in jenisPlatform" :key="platform.id" :value="platform.platform_nama">
              {{ platform.platform_nama }}
            </option>
          </select>

          <p v-if="errors.platform" class="text-red-500 text-sm mt-1">
            {{ errors.platform }}
          </p>
        </div>

        <div class="mb-4">
          <label for="hargaTotal" class="block text-sm font-medium text-gray-700 mb-1">
            Harga Total<span class="text-red-500">*</span>
          </label>

          <div class="flex">
            <span
              class="modal-currency-prefix inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
              Rp
            </span>

            <input type="text" id="hargaTotal" :value="formattedHarga" @input="updateHarga($event.target.value)"
              placeholder="Masukkan harga"
              class="w-full border border-gray-300 rounded-r-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400"
              required />

            <p v-if="errors.hargaTotal" class="text-red-500 text-sm mt-1">
              {{ errors.hargaTotal }}
            </p>
          </div>
        </div>

        <div class="flex justify-end space-x-2 form-add-actions">
          <button type="button" @click="closeModalEditOrder"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
            Batal
          </button>

          <button type="submit" :disabled="isSubmittingEdit"
            class="px-4 py-2 bg-cyan-600 text-white rounded-md hover:bg-cyan-700">
            {{ isSubmittingEdit ? "Menyimpan..." : "Simpan" }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <ModalLiveTransaksi v-model:show="isModalOpen" :namaAkun="selected.namaAkun" :barang="selected.barang"
    @save="handleSave" @close="isModalOpen = false" @removeItem="handleRemoveItem" />
</template>

<script setup>
import ModalLiveTransaksi from "../components/ModalLiveTransaksi.vue";
import { useLive } from "~/composables/useLive";

const {
  pengirimanData,
  transactionData,
  searchQuery,
  activeTab,
  currentPage,
  itemsPerPage,
  isModalOpenAddOrder,
  isModalOpenEditOrder,
  isSubmitting,
  isSubmittingEdit,
  jenisPlatform,
  selected,
  errors,
  isModalOpen,
  form,
  barangInput,
  fetchDataPengiriman,
  flatData,
  sortedFlatData,
  paginatedFlatData,
  filteredFlatData,
  groupedSortedData,
  sortedGroupedEntries,
  groupedData,
  filteredGroupedData,
  paginatedGroupedData,
  totalGroups,
  startItem,
  endItem,
  totalHargaPerAkun,
  totalPages,
  paginatedPages,
  filteredTransaction,
  pagination,
  transactionTotalPages,
  transactionStartItem,
  transactionEndItem,
  transactionPaginatedPages,
  formatCurrency,
  capitalizeFirst,
  handleSave,
  handleRemoveItem,
  openModalEditTransaksi,
  closeModalEditOrder,
  resetForm,
  openModalAddOrder,
  closeModalAddOrder,
  submitLiveOrder,
  editOrderLive,
  formatTanggal,
  submitLiveEditOrder,
  deleteOrder,
  formattedHarga,
  updateHarga,
} = useLive();
</script>

<style scoped src="~/assets/css/live.css"></style>
<style scoped>
.page-submenu button.is-active {
  font-weight: 600;
}
</style>