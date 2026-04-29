<template>
  <div>
    <title>Menu Code</title>
    <div class="judul text-xl font-semibold mb-4">Menu Code</div>
    <div class="flex items-center justify-between pt-2">
      <input class="search-box rounded-md" v-model="searchQuery" type="text"
        placeholder="Search barang..." />

      <button class="btn btn-primary btn-sm" @click="openModal">+ Tambah</button>
    </div>

    <table class="datatable w-full rounded-md overflow-hidden mt-4">
      <thead class="bg-blue-100">
        <tr>
          <th class="px-4 py-2 text-left">No.</th>
          <th class="px-4 py-2 text-left">Kode Barang</th>
          <th class="px-4 py-2 text-left">Nama Barang</th>
          <th class="px-4 py-2 text-left">Jumlah Barang</th>
          <th class="px-4 py-2 text-left">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(barang, index) in pagination" :key="barang.jenisbarang_id"
          :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
          <td class="px-4 py-2">{{ index + 1 }}</td>
          <td class="px-4 py-2">{{ barang.jenisbarang_kode }}</td>
          <td class="px-4 py-2">{{ barang.jenisbarang_nama }}</td>
          <td class="px-4 py-2">{{ barang.jenisbarang_jumlah }}</td>
          <td class="px-4 py-2">
            <div class="flex space-x-2">
              <button class="btn btn-print-h btn-xs" @click="openModelPrint(barang)">Print</button>
              <button class="btn btn-danger btn-xs" @click="deleteProduct(barang.jenisbarang_id, barang.jenisbarang_kode)">Delete</button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <div class="flex justify-between items-center mt-8 mb-4 text-xs">
      <div class="flex items-center space-x-2">
        <label for="perPage">Tampilkan:</label>
        <select id="perPage" v-model="itemsPerPage" class="border px-2 py-1 rounded text-xs">
          <option :value="5">5</option>
          <option :value="10">10</option>
          <option :value="20">20</option>
          <option :value="50">50</option>
          <option value="all">All</option>
        </select>
      </div>

      <div class="flex items-center space-x-2">
        <button class="pg-btn" :disabled="currentPage === 1" @click="currentPage--">‹ Prev</button>
        <button v-for="(page, index) in paginatedPages" :key="index"
          @click="typeof page === 'number' && (currentPage = page)"
          :class="['pg-btn', currentPage === page ? 'active' : '', page === '...' ? 'dots' : '']"
          :disabled="page === '...'">{{ page }}</button>
        <button class="pg-btn" :disabled="currentPage === totalPages" @click="currentPage++">Next ›</button>
      </div>
    </div>

    <div v-if="isModalOpen" class="fixed inset-0 flex justify-center items-center bg-gray-800 z-50">
      <div class="bg-white p-6 rounded-lg max-w-lg w-full">
        <h3 class="text-xl font-semibold mb-4">Tambah Barang</h3>

        <form @submit.prevent="submitProduct">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">
              Kode Barang <span style="color:red">*</span>
            </label>
            <input ref="kodebarang" v-model="newProduct.jenisbarang_kode" type="text" maxlength="5"
              class="mt-1 block w-full border border-gray-300 rounded-md p-2" placeholder="Masukkan kode barang" />
            <p v-if="errors.jenisbarang_kode" class="text-red-500 text-xs mt-1">
              {{ errors.jenisbarang_kode }}
            </p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">
              Jenis Barang <span style="color:red">*</span>
            </label>
            <input v-model="newProduct.jenisbarang_nama" type="text"
              class="mt-1 block w-full border border-gray-300 rounded-md p-2" placeholder="Masukkan jenis barang" />
            <p v-if="errors.jenisbarang_nama" class="text-red-500 text-xs mt-1">
              {{ errors.jenisbarang_nama }}
            </p>
          </div>

          <div class="flex justify-end">
            <button type="button" @click="closeModal" class="btn btn-neutral btn-md">Cancel</button>
            <button type="submit" :disabled="isSubmitting" class="btn btn-primary btn-md">
              {{ isSubmitting ? "Saving…" : "Save" }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="isModalPrintOpen" class="fixed inset-0 flex justify-center items-center bg-gray-800 bg-opacity-50 z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
        <h3 class="text-lg font-semibold mb-4">Print Barcode</h3>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Jumlah Kode</label>
          <input type="number" v-model="printJumlah" min="1"
            class="mt-1 block w-full border border-gray-300 rounded-md p-2" placeholder="Masukkan jumlah kode" />
        </div>
        <div class="flex justify-end">
          <button class="btn btn-neutral btn-md" @click="closePrintModal">Cancel</button>
          <button class="btn btn-print-h btn-md" @click="handlePrint">🖨 Print</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from "vue";
import Swal from "sweetalert2";
import { useRuntimeConfig } from "#imports";
const { $api } = useNuxtApp();
const kodebarang = ref(null);
const searchQuery = ref("");
const barang = ref([]);
const newProduct = ref({
  jenisbarang_nama: "",
  jenisbarang_kode: "",
  jenisbarang_jumlah: 0,
});
const errors = ref({});
const isModalOpen = ref(false);
const isModalPrintOpen = ref(false);
const selectedProduct = ref(null);
const printJumlah = ref(1);
const url = ref("");
const currentPage = ref(1);
const itemsPerPage = ref(10);
const isSubmitting = ref(false);

const fetchData = async () => {
  try {
    const response = await $api.get(`${url.value}/api/jenisbarang`);
    barang.value = response.data.data.map((item) => ({
      jenisbarang_id: item.jenisbarang_id,
      jenisbarang_kode: item.jenisbarang_kode,
      jenisbarang_nama: item.jenisbarang_nama,
      jenisbarang_jumlah: item.jumlah_barang,
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
});

const listBarang = computed(() => {
  const sorted = [...barang.value].sort(
    (a, b) => new Date(b.created_at) - new Date(a.created_at)
  );
  const q = searchQuery.value.toLowerCase();
  return q
    ? sorted.filter(
      (item) =>
        item.jenisbarang_nama?.toLowerCase().includes(q) ||
        item.jenisbarang_kode?.toLowerCase().includes(q)
    )
    : sorted;
});

const pagination = computed(() => {
  if (itemsPerPage.value === "all") {
    return listBarang.value;
  }

  const start = (currentPage.value - 1) * itemsPerPage.value;
  return listBarang.value.slice(start, start + itemsPerPage.value);
});


const totalPages = computed(() => {
  if (itemsPerPage.value === "all") return 1;
  return Math.ceil(listBarang.value.length / itemsPerPage.value);
});

const openModal = () => {
  isModalOpen.value = true;
  errors.value = {};
};

const closeModal = () => {
  isModalOpen.value = false;
  newProduct.value = { jenisbarang_nama: "", jenisbarang_kode: "", jenisbarang_jumlah: 0 };
  errors.value = {};
};

function validateProduct() {
  errors.value = {};
  if (!newProduct.value.jenisbarang_kode.trim())
    errors.value.jenisbarang_kode = "Kode barang wajib diisi";
  else if (newProduct.value.jenisbarang_kode.length > 5)
    errors.value.jenisbarang_kode = "Kode barang maksimal 5 karakter";

  if (!newProduct.value.jenisbarang_nama.trim())
    errors.value.jenisbarang_nama = "Nama barang wajib diisi";

  return Object.keys(errors.value).length === 0;
}

const submitProduct = async () => {
  if (isSubmitting.value) return;
  if (!validateProduct()) {
    Swal.fire("Gagal!", "Silakan lengkapi semua field wajib.", "error");
    return;
  }

  isSubmitting.value = true;
  try {
    const response = await $api.post(`${url.value}/api/jenisbarang`, {
      jenisbarang_kode: newProduct.value.jenisbarang_kode,
      jenisbarang_nama: newProduct.value.jenisbarang_nama,
      jenisbarang_jumlah: 0,
    });

    if (response.status === 201) {
      await Swal.fire("Berhasil!", "Produk berhasil ditambahkan.", "success");
      closeModal();
      fetchData();
    }
  } catch (error) {
    console.error("Error adding product:", error);
    Swal.fire("Gagal!", "Terjadi kesalahan saat menambahkan produk.", "error");
  } finally {
    isSubmitting.value = false;
  }
};

watch(newProduct, () => {
  errors.value = {};
}, { deep: true });

const deleteProduct = async (id, kode_barang) => {
  const result = await Swal.fire({
    title: "Konfirmasi Hapus",
    text: `Anda yakin ingin menghapus "${kode_barang}"?`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, Hapus!",
    cancelButtonText: "Batal",
    reverseButtons: true,
  });

  if (result.isConfirmed) {
    try {
      await $api.delete(`${url.value}/api/jenisbarang/${id}`);
      barang.value = barang.value.filter((item) => item.jenisbarang_id !== id);
      Swal.fire("Berhasil!", `"${kode_barang}" telah dihapus.`, "success");

    } catch (error) {
      Swal.fire("Gagal", "Terjadi kesalahan saat menghapus data.", "error");
    }
  }
};

const paginatedPages = computed(() => {
  if (itemsPerPage.value === "all") return [1];

  const total = totalPages.value;
  const current = currentPage.value;

  if (total <= 5) return Array.from({ length: total }, (_, i) => i + 1);
  if (current <= 3) return [1, 2, 3, "...", total];
  if (current >= total - 2) return [1, "...", total - 2, total - 1, total];
  return [1, "...", current - 1, current, current + 1, "...", total];
});

const openModelPrint = (product) => {
  selectedProduct.value = { ...product };
  const tipe = "tunggal";

  printJumlah.value = product.jenisbarang_jumlah;
  isModalPrintOpen.value = true;
};

const handlePrint = async () => {
  const kodeBarang = selectedProduct.value.jenisbarang_kode;
  const jenisbarang_id = selectedProduct.value.jenisbarang_id;
  const tipeBarang = selectedProduct.value.jenisbarang_tipe;

  // const jumlah = tipeBarang === "majemuk" ? printJumlah.value : 1;
  const jumlah = Number(printJumlah.value);
  if (!jumlah || jumlah < 1) {
    alert("Jumlah harus minimal 1!");
    return;
  }

  try {
    let barcodeData = [];
    const response = await $api.post(`${url.value}/api/codebarang`, {
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

const closePrintModal = () => {
  isModalPrintOpen.value = false;
  selectedProduct.value = null;
};

watch(isModalOpen, async (val) => {
  if (val) {
    await nextTick();
    kodebarang.value.focus();
  }
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
  text-align: left;
  font-size: 12px;
}

.datatable th {
  background-color: #f4f4f4;
}


.fixed {
  position: fixed;
}

.bg-gray-800 {
  background-color: rgba(0, 0, 0, 0.5);
}

.bg-white input:focus {
  outline: none;
  border-color: #8e9196;
  color: #111827;
}
</style>
