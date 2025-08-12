<template>
  <div>
    <div class="judul text-xl font-semibold mb-4">Transaksi Live</div>
    <div class="flex space-x-6">
      <button
        @click="activeTab = 'order'"
        class="pb-1 text-sm relative"
        :class="activeTab === 'order' ? 'text-black' : 'text-gray-500'"
      >
        Order
        <span
          v-if="activeTab === 'order'"
          class="absolute left-0 right-0 -bottom-0.5 h-[2px] bg-red-900 mx-auto"
          style="width: 90%"
        ></span>
      </button>

      <button
        @click="activeTab = 'transaction'"
        class="pb-1 text-sm relative"
        :class="activeTab === 'transaction' ? 'text-black' : 'text-gray-500'"
      >
        Transcation
        <span
          v-if="activeTab === 'transaction'"
          class="absolute left-0 right-0 -bottom-0.5 h-[2px] bg-red-900 mx-auto"
          style="width: 90%"
        ></span>
      </button>
    </div>
    <div class="mx-auto" v-show="activeTab === 'order'">
      <br />
      <div class="flex items-center justify-between pt-2">
        <div class="flex-1">
          <input
            v-model="searchQuery"
            type="text"
            class="search-box p-2 rounded-md"
            placeholder="Cari data live..."
          />
        </div>
        <div>
          <button
            class="btn-add bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[104px] h-[25px]"
            @click="openModalAddOrder"
          >
            + Live
          </button>
        </div>
      </div>
      <table class="datatable">
        <thead>
          <tr>
            <th>No</th>
            <th>Nama Penerima</th>
            <th>Nama Akun</th>
            <th>Nomor Telepon</th>
            <th>Harga Kirim Barang</th>
            <th>Jenis Pengiriman Barang</th>
            <th>Alamat</th>
            <th>Status</th>
            <th>Catatan</th>
            <th>Tanggal</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(pengiriman, index) in pagination"
            :key="pengiriman.pengirimanBarang_id"
          >
            <td>{{ index + 1 }}</td>
            <td>{{ pengiriman.pengirimanBarang_nama_penerima }}</td>
            <td>{{ pengiriman.pengirimanBarang_akun_penerima }}</td>
            <td>{{ pengiriman.pengirimanBarang_no_telepon }}</td>
            <td>
              {{
                formatCurrency(pengiriman.pengirimanBarang_harga_kirim_barang)
              }}
            </td>
            <td>{{ pengiriman.pengirimanBarang_jenis_pengiriman_barang }}</td>
            <td>{{ pengiriman.pengirimanBarang_alamat_pengiriman_barang }}</td>
            <td>{{ pengiriman.pengirimanBarang_status }}</td>
            <td>{{ pengiriman.pengirimanBarang_catatan }}</td>
            <td>{{ formatDate(pengiriman.created_at) }}</td>
            <td>
              <button
                class="flex items-center gap-1 px-2 py-1 bg-red-500 text-white hover:bg-red-600 rounded-[10px] text-s"
                @click="deletepengirimanData(pengiriman.pengirimanBarang_id)"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="flex justify-between items-center mt-4 text-xs">
        <div class="flex items-center space-x-2">
          <label for="perPage">Tampilkan:</label>
          <select
            id="perPage"
            v-model="itemsPerPage"
            class="border px-2 py-1 rounded text-xs"
          >
            <option :value="5">5</option>
            <option :value="10">10</option>
            <option :value="20">20</option>
            <option :value="50">50</option>
          </select>
        </div>

        <div class="flex items-center space-x-2">
          <button
            class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400 text-xs"
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
              'px-3 py-1 rounded text-xs',
              currentPage === page ? 'bg-blue-500 text-white' : 'bg-gray-200',
              page === '...' ? 'cursor-default' : 'cursor-pointer',
            ]"
            :disabled="page === '...'"
          >
            {{ page }}
          </button>

          <button
            class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400 text-xs"
            :disabled="currentPage === totalPages"
            @click="currentPage++"
          >
            Selanjutnya
          </button>
        </div>
      </div>
    </div>

    <div class="mx-auto" v-show="activeTab === 'transaction'">
      <br />
      <input
        v-model="searchQuery"
        type="text"
        class="search-box mb-4"
        placeholder="Cari data live..."
      />
      <table class="datatable">
        <thead>
          <tr>
            <th>No</th>
            <th>Nama Penerima</th>
            <th>Nama Akun</th>
            <th>Nomor Telepon</th>
            <th>Harga Kirim Barang</th>
            <th>Jenis Pengiriman Barang</th>
            <th>Alamat</th>
            <th>Status</th>
            <th>Catatan</th>
            <th>Tanggal</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(pengiriman, index) in pagination"
            :key="pengiriman.pengirimanBarang_id"
          >
            <td>{{ index + 1 }}</td>
            <td>{{ pengiriman.pengirimanBarang_nama_penerima }}</td>
            <td>{{ pengiriman.pengirimanBarang_akun_penerima }}</td>
            <td>{{ pengiriman.pengirimanBarang_no_telepon }}</td>
            <td>
              {{
                formatCurrency(pengiriman.pengirimanBarang_harga_kirim_barang)
              }}
            </td>
            <td>{{ pengiriman.pengirimanBarang_jenis_pengiriman_barang }}</td>
            <td>{{ pengiriman.pengirimanBarang_alamat_pengiriman_barang }}</td>
            <td>{{ pengiriman.pengirimanBarang_status }}</td>
            <td>{{ pengiriman.pengirimanBarang_catatan }}</td>
            <td>{{ formatDate(pengiriman.created_at) }}</td>
            <td>
              <button
                class="flex items-center gap-1 px-2 py-1 bg-red-500 text-white hover:bg-red-600 rounded-[10px] text-s"
                @click="editTranscationLive(pengiriman.pengirimanBarang_id)"
              >
                Edit
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="flex justify-between items-center mt-4 text-xs">
        <div class="flex items-center space-x-2">
          <label for="perPage">Tampilkan:</label>
          <select
            id="perPage"
            v-model="itemsPerPage"
            class="border px-2 py-1 rounded text-xs"
          >
            <option :value="5">5</option>
            <option :value="10">10</option>
            <option :value="20">20</option>
            <option :value="50">50</option>
          </select>
        </div>

        <div class="flex items-center space-x-2">
          <button
            class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400 text-xs"
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
              'px-3 py-1 rounded text-xs',
              currentPage === page ? 'bg-blue-500 text-white' : 'bg-gray-200',
              page === '...' ? 'cursor-default' : 'cursor-pointer',
            ]"
            :disabled="page === '...'"
          >
            {{ page }}
          </button>

          <button
            class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400 text-xs"
            :disabled="currentPage === totalPages"
            @click="currentPage++"
          >
            Selanjutnya
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Add Order -->
  <div
    v-if="isModalOpenAddOrder"
    class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 z-50"
  >
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
      <h3 class="text-lg font-semibold mb-4">Tambah Transaksi Live</h3>
      <form @submit.prevent="submitLiveOrder">
        <div class="mb-4">
          <label
            for="barang"
            class="block text-sm font-medium text-gray-700 mb-1"
          >
            Barang<span class="text-red-500">*</span>
          </label>
          <input
            type="text"
            id="barang"
            v-model="form.barang"
            placeholder="Masukkan nama acara"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400"
            required
          />
        </div>
        <div class="mb-4">
          <label
            for="namaAkun"
            class="block text-sm font-medium text-gray-700 mb-1"
          >
            Nama Akun<span class="text-red-500">*</span>
          </label>
          <input
            type="text"
            id="namaAkun"
            v-model="form.namaAkun"
            placeholder="Masukkan nama akun"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400"
            required
          />
        </div>
        <div class="mb-4">
          <label
            for="platform"
            class="block text-sm font-medium text-gray-700 mb-1"
          >
            Platform<span class="text-red-500">*</span>
          </label>
          <select
            id="platform"
            v-model="form.platform"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400"
            required
          >
            <option value="" disabled>Pilih Platform</option>
            <option value="tiktok">TikTok</option>
            <option value="instagram">Instagram</option>
            <option value="facebook">Facebook</option>
            <option value="whatsapp">WhatsApp</option>
          </select>
        </div>

        <div class="mb-4">
          <label
            for="hargaTotal"
            class="block text-sm font-medium text-gray-700 mb-1"
          >
            Harga Total<span class="text-red-500">*</span>
          </label>
          <div class="flex">
            <span
              class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm"
            >
              Rp
            </span>
            <input
              type="text"
              id="hargaTotal"
              :value="formattedHarga"
              @input="updateHarga($event.target.value)"
              placeholder="Masukkan harga"
              class="w-full border border-gray-300 rounded-r-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400"
              required
            />
          </div>
        </div>

        <div class="flex justify-end space-x-2">
          <button
            type="button"
            @click="closeModalAddOrder"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="px-4 py-2 bg-[#1C9DBD] text-white rounded-md hover:bg-[#17a2b8]"
          >
            {{ isSubmitting ? "Menyimpan..." : "Tambah" }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";

const config = useRuntimeConfig();
const url = ref(config.public.apiBase);

const pengirimanData = ref([]);
const searchQuery = ref("");
const activeTab = ref("order");
const isModalOpenAddOrder = ref(false);
const isSubmitting = ref(false);
const currentPage = ref(1);
const itemsPerPage = ref(10);

const form = ref({
  barang: "",
  namaAkun: "",
  platform: "",
  hargaTotal: "",
});

onMounted(() => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;
  fetchDataPengiriman();
});

const fetchDataPengiriman = async () => {
  try {
    const res = await axios.get(`${url.value}/api/pengiriman-barang`);
    pengirimanData.value = res.data.data;    
  } catch (error) {
    console.error("Gagal fetch data pengiriman:", error);
  }
};

const resetForm = () => {
  form.value = {
    barang: "",
    namaAkun: "",
    platform: "",
    hargaTotal: "",
  };
};

const listpengirimanData = computed(() => {
  const sorted = [...pengirimanData.value].sort((a, b) => {
    return new Date(b.created_at) - new Date(a.created_at);
  });
  if (!searchQuery.value) return sorted;

  const q = searchQuery.value.toLowerCase();
  return sorted.filter((pengiriman) => {
    return (
      pengiriman.pengirimanBarang_nama_penerima?.toLowerCase().includes(q) ||
      pengiriman.pengirimanBarang_akun_penerima?.toLowerCase().includes(q) ||
      pengiriman.pengirimanBarang_no_telepon?.toLowerCase().includes(q) ||
      pengiriman.pengirimanBarang_jenis_pengiriman_barang
        ?.toLowerCase()
        .includes(q) ||
      pengiriman.pengirimanBarang_alamat_pengiriman_barang
        ?.toLowerCase()
        .includes(q) ||
      pengiriman.pengirimanBarang_status?.toLowerCase().includes(q)
    );
  });
});

const pagination = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return listpengirimanData.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(listpengirimanData.value.length / itemsPerPage.value);
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(value);
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString("id-ID", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

const openModalAddOrder = () => {
  isModalOpenAddOrder.value = true;
};

const closeModalAddOrder = () => {
  isModalOpenAddOrder.value = false;
  resetForm();
};

const formattedHarga = computed(() => {
  if (!form.value.hargaTotal) return "";
  return form.value.hargaTotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
});

const updateHarga = (val) => {
  const number = val.replace(/\./g, "");
  form.value.hargaTotal = number;
};

const deletepengirimanData = async (id) => {
  if (confirm(`Anda yakin ingin menghapus pengirimanData" ini?`)) {
    try {
      const response = await axios.delete(
        `${url.value}/api/pengiriman-barang/` + id
      );

      if (response.status === 200) {
        pengirimanData.value = pengirimanData.value.filter(
          (item) => item.pengirimanBarang_id !== id
        );
      }
      Swal.fire({
        title: "Berhasil",
        text: "Data berhasil di delete",
        icon: "info",
        confirmButtonText: "OK",
      });
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

watch(activeTab, () => {
  fetchDataPengiriman();
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
  height: 25px;
  font-size: 12px;
}

.datatable {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

.datatable th,
.datatable td {
  padding: 10px;
  /* border: 1px solid #ddd; */
  text-align: left;
  font-size: 12px;
}

.datatable th {
  background-color: #f4f4f4;
}

.search-box::placeholder {
  color: #888;
}

.btn-add {
  background-color: #3d8bfd;
  color: white;
  border-radius: 5px;
  cursor: pointer;
  font-size: 12px;
}

.btn-add:hover {
  background-color: #3d8bfd;
}

.bg-gray-800 {
  background-color: rgba(0, 0, 0, 0.5);
}
</style>
