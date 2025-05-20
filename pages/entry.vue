<template>
  <div>
    <!-- Barcode text at the top -->
    <div class="text-xl font-semibold mb-4">Wait to Entry</div>
    <div class="flex space-x-4 mb-6">
      <button
        class="btn-add bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[104px] h-[45px]"
        @click="openModal('desc')"
      >
        + Desc
      </button>
      <button
        class="btn-add bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[104px] h-[45px]"
        @click="openModal('size')"
      >
        + Size
      </button>
      <button
        class="btn-add bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[110px] h-[45px]"
        @click="openModal('priceTag')"
      >
        Print Price Tag
      </button>
    </div>

    <BaseModal
      :show="modalOpen"
      :type="modalType"
      :barang-database="barangDatabase"
      @close="modalOpen = false"
      @scanned="tambahBarang"
      @sizeSubmitted="handleSizeSubmitted"
    />

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
            <td>{{ formatTanggal(barang.created_at) }}</td>
            <td>{{ barang.barangentry_temp_nama }}</td>
            <td>{{ barang.barangentry_temp_warna }}</td>
            <td>{{ barang.barangentry_temp_nama_penenun }}</td>
            <td>{{ barang.barangentry_temp_nama_panirat }}</td>
            <td>{{ barang.barangentry_temp_dryer }}</td>
            <td>{{ barang.barangentry_temp_modal }}</td>
            <td>{{ barang.barangentry_temp_price_tag }}</td>
            <td>{{ barang.barangentry_temp_harga_net }}</td>
            <td>{{ barang.barangentry_temp_acara }}</td>
            <td>{{ barang.barangentry_temp_ukuran_mandar }}</td>
            <td>{{ barang.barangentry_temp_ukuran_ulos }}</td>
            <td class="space-x-2">
              <button
                class="px-2 py-1 bg-green-500 text-white hover:bg-green-600 rounded-[15px]"
                @click="openModelPrint(barang)"
              >
                Print
              </button>
              <button
                class="px-2 py-1 bg-red-500 text-white hover:bg-red-600 rounded-[15px]"
                @click="deleteProduct(barang.no)"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Add -->
    <div
      v-if="showModalAdd"
      class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-50"
    >
      <div class="bg-white rounded-lg shadow-lg p-6 w-[700px]">
        <h2 class="text-xl font-semibold mb-6 text-left">
          Tambah Barang Masuk
        </h2>
        <div class="grid grid-cols-2 gap-4">
          <div hidden>
            <label class="block text-gray-700 mb-1">Code ID:</label>
            <input
              v-model="selectedBarang.code_id"
              type="text"
              class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed"
              :readonly="true"
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Kode Barang:</label>
            <input
              v-model="selectedBarang.code_nama"
              type="text"
              class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed"
              :readonly="true"
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Nama Ulos:</label>
            <input
              v-model="selectedBarang.barangentry_temp_nama"
              type="text"
              class="w-full border rounded px-3 py-2"
              placeholder="..."
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Warna Ulos:</label>
            <input
              v-model="selectedBarang.barangentry_temp_warna"
              type="text"
              class="w-full border rounded px-3 py-2"
              placeholder="..."
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Nama Penenun:</label>
            <input
              v-model="selectedBarang.barangentry_temp_nama_penenun"
              type="text"
              class="w-full border rounded px-3 py-2"
              placeholder="..."
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Nama Panirat:</label>
            <input
              v-model="selectedBarang.barangentry_temp_nama_panirat"
              type="text"
              class="w-full border rounded px-3 py-2"
              placeholder="..."
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Dyer:</label>
            <input
              v-model="selectedBarang.barangentry_temp_dryer"
              type="text"
              class="w-full border rounded px-3 py-2"
              placeholder="..."
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Modal:</label>
            <input
              v-model="selectedBarang.barangentry_temp_modal"
              type="text"
              class="w-full border rounded px-3 py-2"
              placeholder="..."
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Harga Price Tag:</label>
            <input
              v-model="selectedBarang.barangentry_temp_price_tag"
              type="text"
              class="w-full border rounded px-3 py-2"
              placeholder="..."
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Harga Net:</label>
            <input
              v-model="selectedBarang.barangentry_temp_harga_net"
              type="text"
              class="w-full border rounded px-3 py-2"
              placeholder="..."
            />
          </div>
        </div>

        <div class="flex justify-start space-x-3 mt-6">
          <button
            @click="showModalAdd = false"
            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
          >
            Batal
          </button>
          <button
            @click="submitBarang"
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
          >
            Simpan
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Add Size  -->
    <div
      v-if="showModalAddSize"
      class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-50"
    >
      <div class="bg-white rounded-lg shadow-lg p-6 w-[700px]">
        <h2 class="text-xl font-semibold mb-6 text-left">Tambah Size</h2>
        <div class="grid gap-4">
          <div hidden>
            <label class="block text-gray-700 mb-1">Code ID:</label>
            <input
              v-model="selectedBarang.code_id"
              type="text"
              class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed"
              :readonly="true"
            />
          </div>
          <div hidden>
            <label class="block text-gray-700 mb-1">Kode Barang:</label>
            <input
              v-model="selectedBarang.kode_barang"
              type="text"
              class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed"
              :readonly="true"
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Ukuran Ulos:</label>
            <input
              v-model="selectedBarang.ukuran_ulos"
              type="text"
              class="w-full border rounded px-3 py-2"
              placeholder="..."
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Ukuran Mandar:</label>
            <input
              v-model="selectedBarang.ukuran_mandar"
              type="text"
              class="w-full border rounded px-3 py-2"
              placeholder="..."
            />
          </div>
        </div>
        <div class="flex justify-start space-x-3 mt-6">
          <button
            @click="showModalAddSize = false"
            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
          >
            Batal
          </button>
          <button
            @click="submitSizeBarang"
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
          >
            Simpan
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import BaseModal from "../components/Modal.vue";
import axios from "axios";

const modalOpen = ref(false);
const modalType = ref("desc");
const showModalAdd = ref(false);
const showModalAddSize = ref(false);
const selectedBarang = ref({});
const url = "https://api-dame-ulos.databasedameulos.com";
const isLoading = ref(false);

const barangDatabase = ref([]);
const listBarang = ref([]);

onMounted(async () => {
  try {
    const response = await axios.get(`${url}/api/codebarang`);
    barangDatabase.value = response.data;
    await getListBarangTemp();
  } catch (error) {
    console.log("Gagal mengambil data barang: ", error);
  }
});

function formatTanggal(tanggal) {
  const date = new Date(tanggal);
  return new Intl.DateTimeFormat("id-ID", {
    day: "2-digit",
    month: "long",
    year: "numeric",
  }).format(date);
}

async function getListBarangTemp() {
  try {
    const response = await axios.get(`${url}/api/entrybarangtemp/getDataTable`);
    console.log("asdsad", response);

    listBarang.value = response.data.data;
  } catch (error) {
    console.error("Gagal Memuat Data Barang: ", error);
  }
}
function openModal(type) {
  modalType.value = type;
  modalOpen.value = true;
}

function tambahBarang(barang) {
  selectedBarang.value = { ...barang };
  showModalAdd.value = true;
}

async function submitBarang() {
  try {
    const payload = {
      barangentry_temp_code_id: String(selectedBarang.value.code_id),
      barangentry_temp_nama: selectedBarang.value.barangentry_temp_nama,
      barangentry_temp_warna: selectedBarang.value.barangentry_temp_warna,
      barangentry_temp_nama_penenun:
        selectedBarang.value.barangentry_temp_nama_penenun,
      barangentry_temp_nama_panirat:
        selectedBarang.value.barangentry_temp_nama_panirat,
      barangentry_temp_dryer: selectedBarang.value.barangentry_temp_dryer,
      barangentry_temp_modal: selectedBarang.value.barangentry_temp_modal,
      barangentry_temp_price_tag:
        selectedBarang.value.barangentry_temp_price_tag,
      barangentry_temp_harga_net:
        selectedBarang.value.barangentry_temp_harga_net,
    };

    await axios.post(`${url}/api/entrybarangtemp/storeDescription`, payload);
    await getListBarangTemp();

    selectedBarang.value = {};
    showModalAdd.value = false;
    modalOpen.value = false;

    alert("Barang berhasil ditambahkan!");
  } catch (error) {
    console.error("Gagal meyimpan barang:", error);
    alert("Gagal menambahkan barang masuk");
  }
}

function handleSizeSubmitted(barang) {  
  selectedBarang.value = {...barang};
  showModalAddSize.value = true;
}

async function submitSizeBarang() {
  try {
    const payload = {
      barangentry_temp_code_id: String(selectedBarang.value.code_id),
      barangentry_temp_ukuran_mandar: selectedBarang.value.ukuran_mandar,
      barangentry_temp_ukuran_ulos: selectedBarang.value.ukuran_ulos,
    };

    await axios.post(`${url}/api/entrybarangtemp/storeSize`, payload);
    
    const index = listBarang.value.findIndex(
      (item) => item.barangentry_temp_code_id === selectedBarang.value.code_id
    );

    if (index !== -1) {
      listBarang.value[index].barangentry_temp_ukuran_mandar =
        payload.barangentry_temp_ukuran_mandar;
      listBarang.value[index].barangentry_temp_ukuran_ulos =
        payload.barangentry_temp_ukuran_ulos;
    } else {
      listBarang.value.push({
        no: listBarang.value.length + 1,
        ...selectedBarang.value,
        barangentry_temp_ukuran_ulos: payload.barangentry_temp_ukuran_ulos,
        barangentry_temp_ukuran_mandar: payload.barangentry_temp_ukuran_mandar,
      });
    }

    alert("Ukuran barang berhasil ditambahkan");
    selectedBarang.value = {};
    showModalAddSize.value = false;
    modalOpen.value = false;
  } catch (error) {
    console.error("Gagal menyimpan size:", error);
    alert("Gagal menyimpan barang");
  }
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
