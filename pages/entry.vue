<template>
  <div>
    <!-- Barcode text at the top -->
    <div class="text-xl font-semibold mb-4">
      Wait to Entry
    </div>
    <div class="flex space-x-4 mb-6">
      <button class="btn-add bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[104px] h-[45px]"
        @click="openModal('desc')">+ Desc</button>
      <button class="btn-add bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[104px] h-[45px]"
        @click="openModal('size')">+ Size</button>
      <button class="btn-add bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[110px] h-[45px]"
        @click="openModal('priceTag')">Print Price Tag</button>
    </div>

    <BaseModal :show="modalOpen" :type="modalType" :barang-database="barangDatabase" @close="modalOpen = false"
      @scanned="tambahBarang" />

    <!-- Product Table -->
    <div>
      <table class="datatable">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Nama Ulos</th>
            <th>Warna Ulos</th>
            <th>Nama Penenun</th>
            <th>Nama Panirat</th>
            <th>Dyer</th>
            <th>Modal</th>
            <th>Price Tag</th>
            <th>Harga Net</th>
            <th>Acara</th>
            <th>Ukuran Mandar</th>
            <th>Ukuran Ulos</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="barang in listBarang" :key="barang.kode_barang">
            <td>{{ barang.no }}</td>
            <td>{{ barang.kode_barang }}</td>
            <td>{{ barang.nama_barang }}</td>
            <td>{{ barang.jumlah_barang }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td class="space-x-2">
              <button class="px-2 py-1 bg-green-500 text-white hover:bg-green-600 rounded-[15px]"
                @click="openModelPrint(barang)">
                Print
              </button>
              <button class="px-2 py-1 bg-red-500 text-white hover:bg-red-600 rounded-[15px]"
                @click="deleteProduct(barang.no)">
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal desc -->
    <div v-if="showModalDesc" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-50">
      <div class="bg-white rounded-lg shadow-lg p-6 w-[500px]">
        <h2 class="text-xl font-semibold mb-4">Scan Barcode</h2>

        <div class="mb-4">
          <label class="block text-gray-700">Scan / Input Kode Barang:</label>
          <input v-model="scannedCode" @keyup.enter="handleBarcodeScan" type="text"
            class="w-full border rounded px-3 py-2" placeholder="Scan barcode atau ketik kode lalu tekan Enter"
            autofocus />
        </div>

        <div v-if="selectedBarang.kode_barang" class="space-y-3">
          <div>
            <label class="block text-gray-700">Kode Barang:</label>
            <input v-model="selectedBarang.kode_barang" type="text" class="w-full border rounded px-3 py-2" disabled />
          </div>
          <div>
            <label class="block text-gray-700">Nama Barang:</label>
            <input v-model="selectedBarang.nama_barang" type="text" class="w-full border rounded px-3 py-2" disabled />
          </div>
          <div>
            <label class="block text-gray-700">Jumlah Barang:</label>
            <input v-model="selectedBarang.jumlah_barang" type="text" class="w-full border rounded px-3 py-2"
              disabled />
          </div>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
          <button @click="closeModalDesc"
            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Close</button>
        </div>
      </div>
    </div>

    <!-- Modal Input Detail Tambahan -->
    <div v-if="showModalAdd" class="modal-overlay">
      <div class="modal-box">
        <h2 class="text-xl font-semibold mb-4">Tambah Detail Barang</h2>

        <div class="mb-4">
          <label class="block text-gray-700">Warna Ulos:</label>
          <input v-model="selectedBarang.warna_ulos" type="text" class="w-full border rounded px-3 py-2" />
        </div>
        <div class="mb-4">
          <label class="block text-gray-700">Nama Penenun:</label>
          <input v-model="selectedBarang.nama_penenun" type="text" class="w-full border rounded px-3 py-2" />
        </div>

        <div class="flex justify-end space-x-3">
          <button @click="showModalAdd = false" class="btn-gray">Close</button>
          <button @click="submitBarang" class="btn-blue">Simpan</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import BaseModal from '../components/Modal.vue'
import { isFunctionDeclaration } from 'typescript'

const modalOpen = ref(false)
const modalType = ref('desc')
const showModalAdd = ref(false)
const selectedBarang = ref({})

const barangDatabase = [
  { kode_barang: 'ULS001', nama_barang: 'Ulos Ragidup', jumlah_barang: 10 },
  { kode_barang: 'ULS002', nama_barang: 'Ulos Sibolang', jumlah_barang: 5 }
]

const listBarang = ref([])

function openModal(type) {
  modalType.value = type
  modalOpen.value = true
}

function tambahBarang(barang) {
  selectedBarang.value = { ...barang }
  showModalAdd.value = true
}

</script>

<style scoped>
.search-box {
  border: 1px solid #ccc;
  padding: 10px;
  width: 385px;
  height: 34px;
}

.datatable {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

.datatable th,
.datatable td {
  padding: 10px;
  border: 1px solid #ddd;
  text-align: left;
}

.datatable th {
  background-color: #f4f4f4;
}

.btn-add {
  background-color: #3D8BFD;
  color: white;
  border-radius: 5px;
  cursor: pointer;
}

.btn-add:hover {
  background-color: #3D8BFD;
}

/* Modal styles */
.fixed {
  position: fixed;
}

.bg-gray-800 {
  background-color: rgba(0, 0, 0, 0.5);
}
</style>
