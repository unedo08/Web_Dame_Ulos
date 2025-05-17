<template>
  <div>
    <!-- Barcode text at the top -->
    <div class="text-xl font-semibold mb-4">Wait to Entry</div>
    <div class="flex space-x-4 mb-6">
      <button class="btn-add bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[104px] h-[45px]"
        @click="openModal('desc')">
        + Desc
      </button>
      <button class="btn-add bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[104px] h-[45px]"
        @click="openModal('size')">
        + Size
      </button>
      <button class="btn-add bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[110px] h-[45px]"
        @click="openModal('priceTag')">
        Print Price Tag
      </button>
    </div>

    <BaseModal :show="modalOpen" :type="modalType" :barang-database="barangDatabase" @close="modalOpen = false"
      @scanned="tambahBarang" @sizeSubmitted="handleSizeSubmitted" />

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
            <td>{{ barang.nama_ulos }}</td>
            <td>{{ barang.warna_ulos }}</td>
            <td>{{ barang.nama_penenun }}</td>
            <td>{{ barang.nama_panirat }}</td>
            <td>{{ barang.dyer }}</td>
            <td>{{ barang.modal }}</td>
            <td>{{ barang.harga_price_tag }}</td>
            <td>{{ barang.harga_net }}</td>
            <td>{{ barang.acara }}</td>
            <td>{{ barang.ukuran_mandar }}</td>
            <td>{{ barang.ukuran_ulos }}</td>
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
    <!-- <div v-if="showModalDesc" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-50">
      <div class="bg-white rounded-lg shadow-lg p-6 w-[500px]">
        <h2 class="text-xl font-semibold mb-4">Scan Barcode</h2>

        <div class="mb-4">
          <label class="block text-gray-700">Scan / Input Kode Barang:</label>
          <input v-model="scannedCode" @keyup.enter="handleBarcodeScan" type="text"
            class="w-full border rounded px-3 py-2" placeholder="Scan barcode atau ketik kode lalu tekan Enter"
            autofocus />
        </div>

        <div class="mt-6 flex justify-end space-x-3">
          <button @click="closeModalDesc"
            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Close</button>
        </div>
      </div>
    </div> -->

    <!-- Modal Add -->
    <div v-if="showModalAdd" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-50">
      <div class="bg-white rounded-lg shadow-lg p-6 w-[700px]">
        <h2 class="text-xl font-semibold mb-6 text-left">
          Tambah Barang Masuk
        </h2>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-gray-700 mb-1">Kode Barang:</label>
            <input v-model="selectedBarang.kode_barang" type="text"
              class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed" :readonly="true" />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Nama Ulos:</label>
            <input v-model="selectedBarang.nama_ulos" type="text" class="w-full border rounded px-3 py-2"
              placeholder="..." />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Warna Ulos:</label>
            <input v-model="selectedBarang.warna_ulos" type="text" class="w-full border rounded px-3 py-2"
              placeholder="..." />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Nama Penenun:</label>
            <input v-model="selectedBarang.nama_penenun" type="text" class="w-full border rounded px-3 py-2"
              placeholder="..." />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Nama Panirat:</label>
            <input v-model="selectedBarang.nama_panirat" type="text" class="w-full border rounded px-3 py-2"
              placeholder="..." />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Dyer:</label>
            <input v-model="selectedBarang.dyer" type="text" class="w-full border rounded px-3 py-2"
              placeholder="..." />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Modal:</label>
            <input v-model="selectedBarang.modal" type="text" class="w-full border rounded px-3 py-2"
              placeholder="..." />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Harga Price Tag:</label>
            <input v-model="selectedBarang.harga_price_tag" type="text" class="w-full border rounded px-3 py-2"
              placeholder="..." />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Harga Net:</label>
            <input v-model="selectedBarang.harga_net" type="text" class="w-full border rounded px-3 py-2"
              placeholder="..." />
          </div>
        </div>

        <!-- Tombol aksi -->
        <div class="flex justify-start space-x-3 mt-6">
          <button @click="showModalAdd = false" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
            Batal
          </button>
          <button @click="submitBarang" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            Simpan
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Add Size  -->
    <div v-if="showModalAddSize" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-50">
      <div class="bg-white rounded-lg shadow-lg p-6 w-[700px]">
        <h2 class="text-xl font-semibold mb-6 text-left">Tambah Size</h2>
        <div class="grid gap-4">
          <div>
            <label class="block text-gray-700 mb-1">Kode Barang:</label>
            <input v-model="selectedBarang.kode_barang" type="text"
              class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed" :readonly="true" />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Ukuran Ulos:</label>
            <input v-model="selectedBarang.ukuran_ulos" type="text" class="w-full border rounded px-3 py-2"
              placeholder="..." />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Ukuran Mandar:</label>
            <input v-model="selectedBarang.ukuran_mandar" type="text" class="w-full border rounded px-3 py-2"
              placeholder="..." />
          </div>
        </div>
        <div class="flex justify-start space-x-3 mt-6">
          <button @click="showModalAddSize = false" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
            Batal
          </button>
          <button @click="submitSizeBarang" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            Simpan
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import BaseModal from "../components/Modal.vue";

const modalOpen = ref(false);
const modalType = ref("desc");
const showModalAdd = ref(false);
const showModalAddSize = ref(false);
const selectedBarang = ref({});

const barangDatabase = [
  { kode_barang: "ULS001", nama_ulos: "Ulos Ragidup", jumlah_barang: 10 },
  { kode_barang: "ULS002", nama_ulos: "Ulos Sibolang", jumlah_barang: 5 },
];

const listBarang = ref([]);

function openModal(type) {
  console.log("asdasdaszx", type);
  modalType.value = type;
  modalOpen.value = true;
}

function tambahBarang(barang) {
  selectedBarang.value = { ...barang };
  showModalAdd.value = true;
}

function submitBarang() {
  listBarang.value.push({
    no: listBarang.value.length + 1,
    ...selectedBarang.value,
  });
  selectedBarang.value = {};
  showModalAdd.value = false;
  modalOpen.value = false;
}

function handleSizeSubmitted() {
  showModalAddSize.value = true;
  // const index = listBarang.value.findIndex(item => item.kode_barang === barangWithSize.kode_barang)

  // if (index !== -1) {
  //   listBarang.value[index].ukuran_mandar = barangWithSize.ukuran_mandar
  //   listBarang.value[index].ukuran_ulos = barangWithSize.ukuran_ulos
  // } else {s
  //   listBarang.value.push({
  //     no: listBarang.value.length + 1,
  //     ...barangWithSize
  //   })
  // }
}

function submitSizeBarang() {
  listBarang.value.push({
    no: listBarang.value.length + 1,
    ...selectedBarang.value,
  });
  selectedBarang.value = {};
  showModalAddSize.value = false;
  modalOpen.value = false;
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
  background-color: #3d8bfd;
  color: white;
  border-radius: 5px;
  cursor: pointer;
}

.btn-add:hover {
  background-color: #3d8bfd;
}

/* Modal styles */
.fixed {
  position: fixed;
}

.bg-gray-800 {
  background-color: rgba(0, 0, 0, 0.5);
}
</style>
