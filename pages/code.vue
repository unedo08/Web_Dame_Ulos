<template>
  <div>
    <div class="judul text-xl font-semibold mb-4">Menu Code</div>
    <div class="flex items-center justify-between pt-2">
      <div class="flex-1">
        <input
          class="search-box p-2 border rounded-md"
          v-model="searchQuery"
          type="text"
          placeholder="Search barang..."
        />
      </div>

      <div>
        <button
          class="btn-add bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[104px] h-[34px]"
          @click="openModal"
        >
          + Tambah
        </button>
      </div>
    </div>

    <div>
      <table class="datatable">
        <thead>
          <tr>
            <th>No.</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Jumlah Barang</th>
            <th>Tipe Barang</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(barang, index) in pagination"
            :key="barang.jenisbarang_id"
          >
            <td>{{ index + 1 }}</td>
            <td>{{ barang.jenisbarang_kode }}</td>
            <td>{{ barang.jenisbarang_nama }}</td>
            <td>{{ barang.jenisbarang_jumlah }}</td>
            <td>{{ barang.jenisbarang_tipe }}</td>
            <td class="flex space-x-2">
              <button
                class="flex items-center gap-1 px-2 py-1 bg-green-500 text-white hover:bg-green-600 rounded-[10px] text-sm"
                @click="openModelPrint(barang)"
              >
                Print
              </button>
              <button
                class="flex items-center gap-1 px-2 py-1 bg-red-500 text-white hover:bg-red-600 rounded-[10px] text-sm"
                @click="
                  deleteProduct(barang.jenisbarang_id, barang.jenisbarang_nama)
                "
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="flex justify-between items-center mt-4">
        <div class="flex items-center space-x-2">
          <label for="perPage">Tampilkan:</label>
          <select
            id="perPage"
            v-model="itemsPerPage"
            class="border px-2 py-1 rounded"
          >
            <option :value="5">5</option>
            <option :value="10">10</option>
            <option :value="20">20</option>
            <option :value="50">50</option>
          </select>
        </div>

        <div class="flex items-center space-x-2">
          <button
            class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400"
            :disabled="currentPage === 1"
            @click="currentPage--"
          >
            Sebelumnya
          </button>

          <button
            v-for="(page, index) in paginatedPages"
            :key="index"
            @click="typeof page === 'number' && (currentPage = page)"
            :class="[
              'px-3 py-1 rounded',
              currentPage === page ? 'bg-blue-500 text-white' : 'bg-gray-200',
              page === '...' ? 'cursor-default' : 'cursor-pointer',
            ]"
            :disabled="page === '...'"
          >
            {{ page }}
          </button>

          <button
            class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400"
            :disabled="currentPage === totalPages"
            @click="currentPage++"
          >
            Selanjutnya
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Dialog -->
    <div
      v-if="isModalOpen"
      class="fixed inset-0 flex justify-center items-center bg-gray-800 bg-opacity-50"
    >
      <div class="bg-white p-6 rounded-lg shadow-lg max-w-lg w-full">
        <h3 class="text-xl font-semibold mb-4">Tambah Barang</h3>
        <form @submit.prevent="submitProduct">
          <div class="mb-4">
            <label
              for="jenisbarang_kode"
              class="block text-sm font-medium text-gray-700"
              >Kode Barang</label
            >
            <input
              v-model="newProduct.jenisbarang_kode"
              type="text"
              id="jenisbarang_kode"
              maxlength="5"
              class="mt-1 block w-full border-[1px] pl-3 border-gray rounded-md shadow-sm w-[382px] h-[41px]"
              placeholder=" Masukkan kode barang"
              required
            />
          </div>
          <div class="mb-4">
            <label
              for="jenisbarang_nama"
              class="block text-sm font-medium text-gray-700"
              >Jenis Barang</label
            >
            <input
              v-model="newProduct.jenisbarang_nama"
              type="text"
              id="jenisbarang_nama"
              class="mt-1 block w-full border-[1px] border-gray rounded-md shadow-sm pl-3 w-[382px] h-[41px]"
              placeholder=" Masukkan jenis barang"
              required
            />
          </div>

          <div class="mb-4">
            <div class="flex items-center space-x-4 mt-2">
              <div class="flex items-center">
                <input
                  v-model="newProduct.jenisbarang_tipe"
                  type="radio"
                  id="tunggal"
                  value="tunggal"
                  class="mr-2"
                />
                <label for="tunggal" class="text-sm text-gray-700"
                  >Tunggal</label
                >
              </div>
              <div class="flex items-center">
                <input
                  v-model="newProduct.jenisbarang_tipe"
                  type="radio"
                  id="majemuk"
                  value="majemuk"
                  class="mr-2"
                />
                <label for="majemuk" class="text-sm text-gray-700"
                  >Majemuk</label
                >
              </div>
            </div>
          </div>

          <div class="flex justify-end">
            <button
              type="button"
              @click="closeModal"
              class="mr-4 px-4 py-2 bg-[#D8D8D8] text-white rounded-md hover:bg-[#D8D8D8]"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-bg-[#1C9DBD] text-white rounded-md hover:bg-bg-[#1C9DBD]"
            >
              Save
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
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import { useRuntimeConfig } from "#imports";

// State
const searchQuery = ref("");
const barang = ref([]);
const newProduct = ref({
  jenisbarang_nama: "",
  jenisbarang_kode: "",
  jenisbarang_jumlah: 0,
  jenisbarang_tipe: "tunggal",
});

const isModalOpen = ref(false);
const isModalPrintOpen = ref(false);
const selectedProduct = ref(null);
const printJumlah = ref(1);
const url = ref("");
const currentPage = ref(1);
const itemsPerPage = ref(10);

const fetchData = async () => {
  try {
    const response = await axios.get(`${url.value}/api/jenisbarang`);
    const fetchedData = response.data;

    barang.value = fetchedData.map((item, index) => ({
      jenisbarang_id: item.jenisbarang_id,
      jenisbarang_kode: item.jenisbarang_kode,
      jenisbarang_nama: item.jenisbarang_nama,
      jenisbarang_jumlah: item.jenisbarang_jumlah,
      jenisbarang_tipe: item.jenisbarang_tipe,
      created_at: item.created_at,
    }));
  } catch (error) {
    console.error("Error fetching data:", error);
  }
};

onMounted(() => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;
  fetchData();
  isModalPrintOpen.value = false;
});

// List with Search
const listBarang = computed(() => {
  const sorted = [...barang.value].sort((a, b) => {
    return new Date(b.created_at) - new Date(a.created_at);
  });

  if (!searchQuery.value) return sorted;
  const q = searchQuery.value.toLowerCase();
  return sorted.filter((item) => {
    return (
      item.jenisbarang_nama?.toLowerCase().includes(q) ||
      item.jenisbarang_kode?.toLowerCase().includes(q)
    );
  });
});

const pagination = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return listBarang.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(listBarang.value.length / itemsPerPage.value);
});

// Modal Add
const openModal = () => {
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

const submitProduct = async () => {
  const jumlah = newProduct.value.jenisbarang_tipe === "tunggal" ? 1 : 1;
  const product = {
    no: barang.value.length + 1,
    jenisbarang_kode: newProduct.value.jenisbarang_kode,
    jenisbarang_nama: newProduct.value.jenisbarang_nama,
    jenisbarang_tipe: newProduct.value.jenisbarang_tipe,
    jenisbarang_jumlah: jumlah,
  };
  try {
    // Send POST request to the API
    const response = await axios.post(`${url.value}/api/jenisbarang`, product);
    if (response.status === 201) {
      const newProductData = response.data;
      barang.value.push({
        no: barang.value.length + 1,
        jenisbarang_id: newProductData.jenisbarang_id,
        jenisbarang_kode: newProductData.jenisbarang_kode,
        jenisbarang_nama: newProductData.jenisbarang_nama,
        jenisbarang_tipe: newProductData.jenisbarang_tipe,
        jenisbarang_jumlah: jumlah,
      });
      closeModal();
      newProduct.value = {
        jenisbarang_kode: "",
        jenisbarang_nama: "",
        jenisbarang_jumlah: 1,
        jenisbarang_tipe: "tunggal",
      };
    }
  } catch (error) {
    console.error("Error adding product:", error);
  }
};

// Modal Print
const openModelPrint = (product) => {
  selectedProduct.value = { ...product };
  const tipe = product.jenisbarang_tipe;

  if (tipe === "tunggal") {
    printJumlah.value = 1;
    handlePrint();
  } else {
    printJumlah.value = product.jenisbarang_jumlah;
    isModalPrintOpen.value = true;
  }
};

const closePrintModal = () => {
  isModalPrintOpen.value = false;
  selectedProduct.value = null;
};

// Print Barcode
const handlePrint = async () => {
  const kodeBarang = selectedProduct.value.jenisbarang_kode;
  const jenisbarang_id = selectedProduct.value.jenisbarang_id;
  const tipeBarang = selectedProduct.value.jenisbarang_tipe;

  const jumlah = tipeBarang === "majemuk" ? printJumlah.value : 1;
  try {
    let barcodeData = [];
    // if (tipeBarang === "majemuk") {
    // console.log("sadsada", jumlah);
    // console.log("sadsada", jenisbarang_id);

    const response = await axios.post(`${url.value}/api/codebarang`, {
      jumlah_barang: jumlah,
      code_jenisbarang_id: jenisbarang_id,
    });
    barcodeData = response.data.data;

    const win = window.open("", "", "width=800,height=600");
    if (!win) return;

    win.document.write(`
      <!DOCTYPE html>
      <html>
        <head>
          <title>Print Barcode</title>
          <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
          <style>
            @media print {
              @page {
                size: A4 landscape;
                margin: 0;
              }
              body {
                margin: 0;
              }
              .page {
                page-break-after: always;
                width: 100vw;
                height: 100vh;
                position: relative;
              }
            }
            .barcode-container {
              position: absolute;
              bottom: 20px;
              right: 20px;
              text-align: center;
              font-size: 10px;
            }
          </style>
        </head>
        <body>
          ${barcodeData
            .map(
              (item, i) => `
            <div class="page">
              <div class="barcode-container">
                <div>${item.code_nama}</div>
                <svg id="barcode-${i}"></svg>
              </div>
            </div>
          `
            )
            .join("")}

          <script>
            window.onload = function() {
              const barcodes = ${JSON.stringify(barcodeData)};
              for (let i = 0; i < barcodes.length; i++) {
                JsBarcode("#barcode-" + i, barcodes[i].code_nama, {
                  format: "CODE128",
                  lineColor: "#000",
                  width: 2,
                  height: 60,
                  displayValue: false
                });
              }
              window.print();
            }
          <\/script>
        </body>
      </html>
    `);

    win.document.close();
    closePrintModal();
  } catch (error) {
    console.error("Gagal update jumlah code barang:", error);
    alert("Gagal melakukan update jumlah barcode. Coba lagi.");
  }
};

const deleteProduct = async (id, nama_barang) => {
  if (confirm(`Anda yakin ingin menghapus "${nama_barang}" ini?`)) {
    try {
      const response = await axios.delete(`${url.value}/api/jenisbarang/` + id);

      if (response.status === 200) {
        barang.value = barang.value.filter(
          (item) => item.jenisbarang_id !== id
        );
      }
    } catch (error) {
      console.error("Error deleting product:", error);
    }
  }
};

const paginatedPages = computed(() => {
  const total = totalPages.value;
  const current = currentPage.value;
  const pages = [];

  if (total <= 5) {
    for (let i = 1; i <= total; i++) {
      pages.push(i);
    }
  } else {
    if (current <= 3) {
      pages.push(1, 2, 3, "...", total);
    } else if (current >= total - 2) {
      pages.push(1, "...", total - 2, total - 1, total);
    } else {
      pages.push(1, "...", current - 1, current, current + 1, "...", total);
    }
  }

  return pages;
});

watch(searchQuery, () => {
  currentPage.value = 1;
});

watch(currentPage, (val) => {
  if (val < 1) currentPage.value = 1;
  if (val > totalPages.value) currentPage.value = totalPages.value;
});
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
