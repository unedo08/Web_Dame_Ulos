<template>
  <div>
    <div class="judul text-xl font-semibold mb-4">Acara</div>

    <div class="flex items-center justify-between pt-2">
      <div>
        <button
          class="btn-add bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[140px] h-[38px]"
          @click="openModal"
        >
          + Tambah Acara
        </button>
      </div>
    </div>

    <!-- Product Table -->
    <div>
      <table class="datatable">
        <thead>
          <tr>
            <th>Nama Acara</th>
            <th>Jumlah Barang</th>
            <th>Modal Barang</th>
            <th>Harga Net</th>
            <th>Harga Prive Tag</th>
            <th>Keterangan</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="acara in listAcara" :key="acara.acara_id">
            <td>{{ acara.nama_acara }}</td>
            <td>{{ acara.jenisbarang_jumlah }}</td>
            <td>{{ acara.modal_barang }}</td>
            <td>{{ acara.harga_net }}</td>
            <td>{{ acara.harga_pricetag }}</td>
            <td>{{ acara.keterangan }}</td>
            <td>{{ acara.status }}</td>
            <td class="flex space-x-2">
              <button
                class="text-blue-500 hover:text-blue-700"
                @click="editItem(acara)"
                title="Edit"
              >
                <PencilIcon class="w-5 h-5" />
              </button>

              <button
                class="text-green-500 hover:text-green-700"
                @click="markAsDone(acara)"
                title="Selesai"
              >
                <CheckCircleIcon class="w-5 h-5" />
              </button>

              <button
                class="text-yellow-500 hover:text-yellow-700"
                @click="exportItem(acara)"
                title="Export"
              >
                <ArrowDownTrayIcon class="w-5 h-5" />
              </button>

              <button
                class="text-red-500 hover:text-red-700"
                @click="deleteProduct(acara.acara_id, acara.nama_acara)"
                title="Delete"
              >
                <TrashIcon class="w-5 h-5" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Dialog -->
    <div
      v-if="isModalOpen"
      class="fixed inset-0 flex justify-center items-center bg-gray-800 bg-opacity-50"
    >
      <div class="bg-white p-6 rounded-lg shadow-lg max-w-lg w-full">
        <h3 class="text-xl font-semibold mb-4">Form Tambah Acara</h3>
        <form @submit.prevent="submitProduct">
          <h5 class="text-xl mb-2">Event Form</h5>
          <div class="mb-4">
            <label
              for="nama_acara"
              class="block text-sm font-medium text-gray-700"
              >Nama Acara</label
            >
            <input
              v-model="newProduct.jenisbarang_kode"
              type="text"
              id="nama_acara"
              maxlength="5"
              class="mt-1 block w-full border-[1px] pl-3 border-gray rounded-md shadow-sm w-[382px] h-[41px]"
              placeholder=" ..."
              required
            />
          </div>
          <div class="mb-4">
            <label
              for="keterangan"
              class="block text-sm font-medium text-gray-700"
              >Keterangan</label
            >
            <input
              v-model="newProduct.jenisbarang_nama"
              type="text"
              id="keterangan"
              class="mt-1 block w-full border-[1px] border-gray rounded-md shadow-sm pl-3 w-[382px] h-[41px]"
              placeholder=" ..."
              required
            />
          </div>

          <div class="flex justify-end">
            <button
              type="button"
              @click="closeModal"
              class="mr-4 text-gray-500"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
            >
              Submit
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Print -->
    <div
      v-if="isModalPrintOpen"
      class="fixed inset-0 flex justify-center items-center bg-gray-800 bg-opacity-50"
    >
      <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
        <h3 class="text-lg font-semibold mb-4">Print Barcode</h3>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700"
            >Jumlah Kode</label
          >
          <input
            type="number"
            v-model="printJumlah"
            min="1"
            class="mt-1 block w-full border border-gray-300 rounded-md p-2"
            placeholder="Masukkan jumlah kode"
          />
        </div>
        <div class="flex justify-end">
          <button class="mr-4 text-gray-500" @click="closePrintModal">
            Cancel
          </button>
          <button
            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
            @click="handlePrint"
          >
            Print
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import {
  PencilIcon,
  CheckCircleIcon,
  ArrowDownTrayIcon,
  TrashIcon
} from '@heroicons/vue/24/solid'

// State
const searchQuery = ref("");
const barang = ref([]);
const acara = ref([]);
const acaraCounter = ref(1);

const newProduct = ref({
  acara_id: 0,
  nama_acara: "",
  jenisbarang_jumlah: 0,
  modal_barang: "",
  harga_net: 0,
  harga_pricetag: 0,
  keterangan: "",
  status: "Draft",
});

const isModalOpen = ref(false);
const isModalPrintOpen = ref(false);
const selectedProduct = ref(null);
const printJumlah = ref(1);

// Load local acara data
onMounted(() => {
  const savedAcara = localStorage.getItem("acaraList");
  const savedCounter = localStorage.getItem("acaraIdCounter");
  if (savedAcara) acara.value = JSON.parse(savedAcara);
  if (savedCounter) acaraCounter.value = parseInt(savedCounter);
  isModalPrintOpen.value = false;
});

// Watcher: update localStorage when data changes
watch(
  acara,
  (val) => {
    localStorage.setItem("acaraList", JSON.stringify(val));
  },
  { deep: true }
);

watch(acaraCounter, (val) => {
  localStorage.setItem("acaraIdCounter", val.toString());
});

// List for datatable
const listAcara = computed(() => acara.value);

// Modal actions
const openModal = () => {
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

// Submit acara (local only)
const submitProduct = () => {
  acara.value.push({
    acara_id: acaraCounter.value++,
    nama_acara: newProduct.value.nama_barang,
    keterangan: newProduct.value.keterangan,
    status: "Belum Selesai",
    modal_barang: "-",
    harga_net: "-",
    harga_pricetag: "-",
    jenisbarang_jumlah: 0,
  });

  newProduct.value = {
    acara_id: 0,
    nama_acara: "",
    keterangan: "",
    status: "",
    modal_barang: "",
    harga_net: "",
    harga_pricetag: "",
    jenisbarang_jumlah: 0,
  };
  closeModal();
};

// Delete acara from local list
const deleteProduct = (id, nama_acara) => {
  if (confirm(`Anda yakin ingin menghapus "${nama_acara}"?`)) {
    acara.value = acara.value.filter((item) => item.acara_id !== id);
  }
};

// Dummy print logic (if needed for acara)
const openModelPrint = (product) => {
  selectedProduct.value = { ...product };
  printJumlah.value = 1;
  isModalPrintOpen.value = true;
};

const closePrintModal = () => {
  isModalPrintOpen.value = false;
  selectedProduct.value = null;
};

const handlePrint = () => {
  alert("Simulasi print acara: " + selectedProduct.value.nama_acara);
  closePrintModal();
};
</script>

<style scoped>
* {
  font-family: "Nunito", sans-serif;
}

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
