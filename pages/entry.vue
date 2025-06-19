<template>
  <div calss="max-w-screen-xl mx-auto px-4">
    <div class="judul text-xl font-semibold mb-4">Wait to Entry</div>
    <div class="flex flex-wrap justify-end gap-4 mb-6">
      <button
        v-if="isSearchActive"
        @click="resetSearch"
        class="mb-4 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
      >
        Reset Pencarian
      </button>
      <button
        class="btn-add bg-yellow-500 text-white text-center rounded-md hover:bg-yellow-600 w-[104px] h-[45px]"
        @click="openSearchModal"
      >
        🔍 Search
      </button>
      <button
        class="btn-add bg-green-500 text-white text-center rounded-md hover:bg-green-600 w-[104px] h-[45px]"
        @click="openModal('desc')"
      >
        + Desc
      </button>
      <button
        class="btn-add bg-green-500 text-white text-center rounded-md hover:bg-green-600 w-[104px] h-[45px]"
        @click="openModal('size')"
      >
        + Size
      </button>
      <button
        class="btn-print bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[125px] h-[45px]"
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

    <div class="overflow-x-auto">
      <table class="min-w-full datatable">
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
            <th>Jumlah</th>
            <!-- <th>Acara</th> -->
            <th>Ukuran Mandar</th>
            <th>Ukuran Ulos</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="barang in isSearchActive ? filteredBarang : pagination"
            :key="barang.kode_barang"
          >
            <td>{{ formatTanggal(barang.created_at) }}</td>
            <td>{{ barang.barangentry_nama }}</td>
            <td>{{ barang.barangentry_warna }}</td>
            <td>{{ barang.barangentry_nama_penenun }}</td>
            <td>{{ barang.barangentry_nama_panirat }}</td>
            <td>{{ barang.barangentry_dryer }}</td>
            <td>{{ formatRupiah(barang.barangentry_modal) }}</td>
            <td>{{ formatRupiah(barang.barangentry_price_tag) }}</td>
            <td>{{ formatRupiah(barang.barangentry_harga_net) }}</td>
            <td>{{ barang.barangentry_jumlah_barang }}</td>
            <!-- <td>{{ barang.barangentry_acara }}</td> -->
            <td>{{ barang.barangentry_ukuran_mandar }}</td>
            <td>{{ barang.barangentry_ukuran_ulos }}</td>
            <td>
              <button
                class="btn-print-click bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[125px] h-[45px]"
                @click="printPriceTag(barang.barangentry_code_id)"
              >
                Print Price Tag
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

    <div
      v-if="showModalAdd"
      class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-50"
    >
      <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg">
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
              v-model="selectedBarang.barangentry_nama"
              type="text"
              class="w-full border rounded px-3 py-2"
              placeholder="..."
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Warna Ulos:</label>
            <input
              v-model="selectedBarang.barangentry_warna"
              type="text"
              class="w-full border rounded px-3 py-2"
              placeholder="..."
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Nama Penenun:</label>
            <input
              v-model="selectedBarang.barangentry_nama_penenun"
              type="text"
              class="w-full border rounded px-3 py-2"
              placeholder="..."
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Nama Panirat:</label>
            <input
              v-model="selectedBarang.barangentry_nama_panirat"
              type="text"
              class="w-full border rounded px-3 py-2"
              placeholder="..."
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Dyer:</label>
            <input
              v-model="selectedBarang.barangentry_dryer"
              type="text"
              class="w-full border rounded px-3 py-2"
              placeholder="..."
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Modal:</label>
            <input
              v-model="selectedBarang.barangentry_modal"
              type="number"
              class="w-full border rounded px-3 py-2"
              placeholder="..."
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Harga Price Tag:</label>
            <input
              v-model="selectedBarang.barangentry_price_tag"
              type="number"
              class="w-full border rounded px-3 py-2"
              placeholder="..."
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Harga Net:</label>
            <input
              v-model="selectedBarang.barangentry_harga_net"
              type="number"
              class="w-full border rounded px-3 py-2"
              placeholder="..."
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Jumlah:</label>
            <input
              v-model="selectedBarang.barangentry_jumlah_barang"
              type="number"
              class="w-full border rounded px-3 py-2"
              placeholder="..."
            />
          </div>
        </div>

        <div class="flex justify-start space-x-3 mt-6">
          <button
            @click="cancelTambahBarang"
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
            @click="cancelSizeBarang"
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

    <!-- Print -->
    <div
      ref="printContent"
      class="hidden print:block p-8 text-sm leading-relaxed"
    >
      <div
        v-for="item in priceTagData"
        :key="item.barangentry_id"
        style="page-break-after: always"
      >
        <div style="display: flex; gap: 40px">
          <div style="flex: 1">
            <h1>{{ item.barangentry_nama }}</h1>
            <p class="text-xl font-semibold mb-4">Horas!</p>
            <p>Mauliate atas dukungan dan pelestarian budaya Batak.</p>
            <p>
              Dengan membeli dan memiliki salah satu karya terbaik dari
              <strong>Dame Ulos</strong>,
            </p>
            <p>
              kamu telah ikut
              <strong>Menjaga Kehidupan dan Tradisi Batak</strong>.
            </p>

            <p class="mt-4">Salam Hangat,</p>
            <p><em>Artisan Dame Ulos</em></p>

            <table
              style="
                margin-top: 20px;
                width: 100%;
                border-collapse: collapse;
                font-size: 0.9rem;
              "
            >
              <tr>
                <td style="padding: 4px 8px; font-weight: bold">
                  Tahun Pembuatan
                </td>
                <td style="padding: 4px 8px">
                  {{ new Date(item.created_at).getFullYear() }}
                </td>
              </tr>
              <tr>
                <td style="padding: 4px 8px; font-weight: bold">
                  Ukuran Tenun
                </td>
                <td style="padding: 4px 8px">
                  {{ item.barangentry_ukuran_ulos ?? "-" }} x
                  {{ item.barangentry_ukuran_mandar ?? "-" }}
                </td>
              </tr>
              <tr>
                <td style="padding: 4px 8px; font-weight: bold">Warna</td>
                <td style="padding: 4px 8px">
                  {{ item.barangentry_warna }}
                </td>
              </tr>
              <tr>
                <td style="padding: 4px 8px; font-weight: bold">Maker</td>
                <td style="padding: 4px 8px">Dame Ulos Collective</td>
              </tr>
              <tr>
                <td style="padding: 4px 8px; padding-left: 1.5rem">
                  <strong>a. Penenun:</strong>
                </td>
                <td style="padding: 4px 8px">
                  {{ item.barangentry_nama_penenun }}
                </td>
              </tr>
              <tr>
                <td style="padding: 4px 8px; padding-left: 1.5rem">
                  <strong>b. Dyer:</strong>
                </td>
                <td style="padding: 4px 8px">
                  {{ item.barangentry_dryer }}
                </td>
              </tr>
            </table>
          </div>

          <div style="flex: 1">
            <p class="font-semibold mb-2">
              BAGAIMANA CARA PERAWATAN KAIN TENUN YANG BENAR?
            </p>
            <ol class="list-decimal list-inside">
              <li>Ulos tidak bisa dicuci/direndam dengan detergen</li>
              <li>
                Setelah dipakai jangan dilipat, cukup digantung dan dianginkan
              </li>
              <li>Jika tidak digunakan lama, jemur kain selama 1 jam</li>
              <li>Hindari tempat lembab dan penyimpanan dalam plastik</li>
              <li>Khusus kain pewarna tekstil bisa di dry clean</li>
            </ol>
            <p class="mt-4">Selamat Pakai</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Search -->
    <div
      v-if="showModalSearch"
      class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-50"
    >
      <div class="bg-white rounded-lg shadow-lg p-6 w-[500px]">
        <h2 class="text-xl font-semibold mb-4 text-center">Cari Barang</h2>
        <input
          v-model="searchCode"
          @keyup.enter="handleSearch"
          type="text"
          class="w-full border rounded px-3 py-2"
          placeholder="Scan atau ketik kode barang..."
        />
        <div class="flex justify-end space-x-3 mt-4">
          <button
            @click="closeSearchModal"
            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
          >
            Batal
          </button>
          <button
            @click="handleSearch"
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
          >
            Cari
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
import { useRuntimeConfig } from "#imports";

const modalOpen = ref(false);
const modalType = ref("desc");
const showModalAdd = ref(false);
const showModalAddSize = ref(false);
const selectedBarang = ref({});
const url = ref("");

const barangDatabase = ref([]);
const listBarang = ref([]);
const priceTagData = ref([]);

const showModalSearch = ref(false);
const searchCode = ref("");
const filteredBarang = ref([]);
const isSearchActive = ref(false);
const currentPage = ref(1);
const itemsPerPage = ref(10);

onMounted(async () => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;
  try {
    const response = await axios.get(`${url.value}/api/codebarang`);
    barangDatabase.value = response.data;
    await getListBarangTemp();
  } catch (error) {
    console.error("Gagal mengambil data barang: ", error);
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
    const response = await axios.get(`${url.value}/api/entrybarang`);

    listBarang.value = response.data.data;
  } catch (error) {
    console.error("Gagal Memuat Data Barang: ", error);
  }
}

const pagination = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return listBarang.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(listBarang.value.length / itemsPerPage.value);
});

function openModal(type) {
  modalType.value = type;
  modalOpen.value = true;
}

function tambahBarang(barang) {
  selectedBarang.value = { ...barang };
  showModalAdd.value = true;
}

function formatRupiah(value) {
  const number = parseInt(value);
  return 'Rp. ' + number.toLocaleString('id-ID');
}

async function submitBarang() {
  try {
    const payload = {
      barangentry_code_id: String(selectedBarang.value.code_id),
      barangentry_nama: selectedBarang.value.barangentry_nama,
      barangentry_warna: selectedBarang.value.barangentry_warna,
      barangentry_nama_penenun: selectedBarang.value.barangentry_nama_penenun,
      barangentry_nama_panirat: selectedBarang.value.barangentry_nama_panirat,
      barangentry_dryer: selectedBarang.value.barangentry_dryer,
      barangentry_modal: selectedBarang.value.barangentry_modal,
      barangentry_price_tag: selectedBarang.value.barangentry_price_tag,
      barangentry_harga_net: selectedBarang.value.barangentry_harga_net,
      barangentry_jumlah_barang: selectedBarang.value.barangentry_jumlah_barang,
    };

    await axios.post(`${url.value}/api/entrybarang/storeDescription`, payload);
    await getListBarangTemp();

    selectedBarang.value = {};
    showModalAdd.value = false;
    modalOpen.value = false;
  } catch (error) {
    console.error("Gagal meyimpan barang:", error);
  }
}

function cancelTambahBarang() {
  selectedBarang.value = {};
  showModalAdd.value = false;
}

function handleSizeSubmitted(barang) {
  selectedBarang.value = { ...barang };
  showModalAddSize.value = true;
}

async function submitSizeBarang() {
  try {
    const payload = {
      barangentry_code_id: String(selectedBarang.value.code_id),
      barangentry_ukuran_mandar: selectedBarang.value.ukuran_mandar,
      barangentry_ukuran_ulos: selectedBarang.value.ukuran_ulos,
    };

    await axios.post(`${url.value}/api/entrybarang/storeSize`, payload);

    const index = listBarang.value.findIndex(
      (item) => item.barangentry_code_id === selectedBarang.value.code_id
    );

    if (index !== -1) {
      listBarang.value[index].barangentry_ukuran_mandar =
        payload.barangentry_ukuran_mandar;
      listBarang.value[index].barangentry_ukuran_ulos =
        payload.barangentry_ukuran_ulos;
    } else {
      listBarang.value.push({
        no: listBarang.value.length + 1,
        ...selectedBarang.value,
        barangentry_ukuran_ulos: payload.barangentry_ukuran_ulos,
        barangentry_ukuran_mandar: payload.barangentry_ukuran_mandar,
      });
    }

    selectedBarang.value = {};
    showModalAddSize.value = false;
    modalOpen.value = false;
  } catch (error) {
    console.error("Gagal menyimpan size:", error);
  }
}

function cancelSizeBarang() {
  selectedBarang.value == {};
  showModalAddSize.value = false;
}

async function printPriceTag(id) {
  // try {
  const results = [];
  try {
    const responseCode = await axios.get(`${url.value}/api/codebarang/` + id);
    const code = responseCode.data.code_nama;
    const res = await axios.get(
      `${url.value}/api/entrybarang/getDataByCode/` + code
    );
    if (res.data) results.push(res.data);
  } catch (err) {
    console.error(`Gagal ambil data untuk ${code}`, err);
  }
  priceTagData.value = results;

  nextTick(() => {
    const content = printContent.value;
    if (!content) return;

    const printWindow = window.open("", "", "width=800,height=600");
    printWindow.document.write(`
      <html>
        <head>
          <title>Price Tag</title>
          <style>
            body { font-family: sans-serif; padding: 20px; line-height: 1.6; }
            ol { padding-left: 1rem; }
          </style>
        </head>
        <body>
          ${content.innerHTML}
        </body>
      </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
  });
}

function openSearchModal() {
  showModalSearch.value = true;
  searchCode.value = "";
}

function closeSearchModal() {
  showModalSearch.value = false;
  searchCode.value = "";
}

async function handleSearch() {
  const keyword = searchCode.value.trim();
  if (!keyword) return;

  try {
    const response = await axios.get(
      `${url.value}/api/entrybarang/getDataByCode/${keyword}`
    );
    const code = response.data.data.barangentry_code_id;
    console.log("asdsa", code);

    if (!code) {
      alert("Kode tidak ditemukan dari server.");
      return;
    }

    filteredBarang.value = listBarang.value.filter((item) =>
      String(item.barangentry_code_id).includes(String(code))
    );
    console.log("sadas", filteredBarang.value);

    isSearchActive.value = true;
    showModalSearch.value = false;

    if (filteredBarang.value.length === 0) {
      alert("Barang tidak ditemukan di daftar.");
    }
  } catch (error) {
    console.error("Gagal mencari data kode:", error);
    alert("Terjadi kesalahan saat mencari kode.");
  }
}

function resetSearch() {
  isSearchActive.value = false;
  filteredBarang.value = [];
  searchCode.value = "";
}

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
</script>

<style>
.judul {
  font-size: 40px;
}
</style>
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

.btn-print {
  background-color: #12c90e;
  color: white;
  border-radius: 5px;
  cursor: pointer;
}

.btn-print:hover {
  background-color: #7df67b;
}

.btn-print-click {
  background-color: #12c90e;
  color: white;
  border-radius: 5px;
  cursor: pointer;
}

.btn-print-click:hover {
  background-color: #7df67b;
}

.btn-add {
  background-color: #2e26d0;
  color: white;
  border-radius: 5px;
  cursor: pointer;
}

.btn-add:hover {
  background-color: #665eed;
}

/* Modal styles */
.fixed {
  position: fixed;
}

.bg-gray-800 {
  background-color: rgba(0, 0, 0, 0.5);
}
</style>
