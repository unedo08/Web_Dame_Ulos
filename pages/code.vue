<template>
  <div>
    <div class="text-xl font-semibold mb-4">Menu Code</div>
    <div class="flex items-center justify-between pt-2">
      <div class="flex-1">
        <input
          class="search-box p-2 border rounded-md"
          v-model="searchQuery"
          type="text"
          placeholder="Search products..."
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

    <!-- Product Table -->
    <div>
      <table class="datatable">
        <thead>
          <tr>
            <th>#</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Jumlah Barang</th>
            <th>Tipe Barang</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="barang in listBarang" :key="barang.jenisbarang_id">
            <td>{{ barang.no }}</td>
            <td>{{ barang.jenisbarang_kode }}</td>
            <td>{{ barang.jenisbarang_nama }}</td>
            <td>{{ barang.jenisbarang_jumlah }}</td>
            <td>{{ barang.jenisbarang_tipe }}</td>
            <td class="space-x-2">
              <button
                class="flex items-center gap-1 px-2 py-1 bg-green-500 text-white hover:bg-green-600 rounded-[15px]"
                @click="openModelPrint(barang)"
              >
                <!-- Icon Print -->
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-4 w-4"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  stroke-width="2"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M6 9V2h12v7M6 18h12v4H6v-4zm0 0v-6h12v6H6z"
                  />
                </svg>
                Print
              </button>
              <button
                class="flex items-center gap-1 px-2 py-1 bg-red-500 text-white hover:bg-red-600 rounded-[15px]"
                @click="
                  deleteProduct(barang.jenisbarang_id, barang.jenisbarang_nama)
                "
              >
                <!-- Icon Delete (Trash) -->
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-4 w-4"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  stroke-width="2"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-3h4m-4 0a1 1 0 00-1 1v1h6V5a1 1 0 00-1-1m-4 0h4"
                  />
                </svg>
                Delete
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
              placeholder=" Masukkan nama barang"
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
              placeholder=" Masukkan nama barang"
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
              class="mr-4 text-gray-500"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
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
const url = "https://api-dame-ulos.databasedameulos.com";

const fetchData = async () => {
  try {
    const response = await axios.get(`${url}/api/jenisbarang`);
    const fetchedData = response.data;
    barang.value = fetchedData.map((item, index) => ({
      no: index + 1,
      jenisbarang_id: item.jenisbarang_id,
      jenisbarang_kode: item.jenisbarang_kode,
      jenisbarang_nama: item.jenisbarang_nama,
      jenisbarang_jumlah: item.jenisbarang_jumlah,
      jenisbarang_tipe: item.jenisbarang_tipe,
    }));
  } catch (error) {
    console.error("Error fetching data:", error);
  }
};

onMounted(() => {
  fetchData();
  isModalPrintOpen.value = false;
});

// List with Search
const listBarang = computed(() => {
  if (!searchQuery.value) return barang.value;
  return barang.value.filter(
    (item) =>
      item.jenisbarang_kode
        .toLowerCase()
        .includes(searchQuery.value.toLowerCase()) ||
      item.jenisbarang_nama
        .toLowerCase()
        .includes(searchQuery.value.toLowerCase())
  );
});

// Modal Add
const openModal = () => {
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

const submitProduct = async () => {
  const jumlah = newProduct.value.jenisbarang_tipe === "tunggal" ? 1 : 0;
  const product = {
    no: barang.value.length + 1,
    jenisbarang_kode: newProduct.value.jenisbarang_kode,
    jenisbarang_nama: newProduct.value.jenisbarang_nama,
    jenisbarang_tipe: newProduct.value.jenisbarang_tipe,
    jenisbarang_jumlah: jumlah,
  };
  try {
    // Send POST request to the API
    const response = await axios.post(`${url}/api/jenisbarang`, product);
    if (response.status === 201) {
      const newProductData = response.data;
      barang.value.push({
        no: barang.value.length + 1,
        jenisbarang_id: newProductData.jenisbarang_id,
        jenisbarang_kode: newProductData.jenisbarang_kode,
        jenisbarang_nama: newProductData.jenisbarang_nama,
        jenisbarang_tipe: newProductData.jenisbarang_tipe,
        jenisbarang_jumlah: newProductData.jenisbarang_jumlah,
      });
      closeModal();
      newProduct.value = {
        jenisbarang_kode: "",
        jenisbarang_nama: "",
        jenisbarang_jumlah: 0,
        jenisbarang_tipe: "tunggal",
      };
    }
  } catch (error) {
    console.error("Error adding product:", error);
    alert("An error occurred while adding the product. Please try again.");
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
    console.log("sadsada", jumlah);
    console.log("sadsada", jenisbarang_id);

    const response = await axios.post(`${url}/api/codebarang`, {
      jumlah_barang: jumlah,
      code_jenisbarang_id: jenisbarang_id,
    });
    barcodeData = response.data.data;
    // } else {
    //   barcodeData = [{ code_nama: kodeBarang }];
    // }

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

// Delete Product
// const deleteProduct = async (id, nama_barang) => {
//   if (confirm(`Anda yakin ingin menghapus "${nama_barang}" ini?`)) {
//     try {
//       const responeDeleteCode = await axios.delete(
//         `${url}/api/codebarang/delete/` + id
//       );
//       if (responeDeleteCode.status === 200) {
//         const response = await axios.delete(`${url}/api/jenisbarang/` + id);

//         if (response.status === 200) {
//           barang.value = barang.value.filter(
//             (item) => item.jenisbarang_id !== id
//           );
//           alert(`Produk "${nama_barang}" berhasil dihapus.`);
//         }
//       } else {
//         alert("Gagal menghapus code barang");
//       }
//     } catch (error) {
//       console.error("Error deleting product:", error);
//       alert("Terjadi kesalahan saat menghapus barang. Coba lagi.");
//     }
//   }
// };

const deleteProduct = async (id, nama_barang) => {
  if (confirm(`Anda yakin ingin menghapus "${nama_barang}" ini?`)) {
    try {
      const response = await axios.delete(`${url}/api/jenisbarang/` + id);

      if (response.status === 200) {
        barang.value = barang.value.filter(
          (item) => item.jenisbarang_id !== id
        );
        alert(`Produk "${nama_barang}" berhasil dihapus.`);
      }
    } catch (error) {
      console.error("Error deleting product:", error);
      alert("Terjadi kesalahan saat menghapus barang. Coba lagi.");
    }
  }
};
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
