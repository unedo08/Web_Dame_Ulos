<template>
  <div>
    <title>Packaging</title>
    <div class="judul text-xl font-semibold mb-4">Packaging</div>
    <input
      v-model="searchQuery"
      type="text"
      class="search-box mb-4"
      placeholder="Cari Pengiriman Barang..."
    />
    <table class="datatable w-full rounded-md overflow-hidden">
      <thead class="bg-blue-100">
        <tr>
          <th class="px-4 py-2 text-left">No</th>
          <th class="px-4 py-2 text-left">Nama Penerima</th>
          <th class="px-4 py-2 text-left">Nama Akun</th>
          <th class="px-4 py-2 text-left">Nomor Telepon</th>
          <th class="px-4 py-2 text-left">Harga Kirim Barang</th>
          <th class="px-4 py-2 text-left">Jenis Pengiriman Barang</th>
          <th class="px-4 py-2 text-left">Alamat</th>
          <th class="px-4 py-2 text-left">Status</th>
          <th class="px-4 py-2 text-left">Catatan</th>
          <th class="px-4 py-2 text-left">Tanggal</th>
          <th class="px-4 py-2 text-left">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(pengiriman, index) in pagination"
          :key="pengiriman.pengirimanBarang_id"
          :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
        >
          <td class="px-4 py-2">{{ index + 1 }}</td>
          <td class="px-4 py-2">
            {{ pengiriman.pengirimanBarang_nama_penerima }}
          </td>
          <td class="px-4 py-2">
            {{ pengiriman.pengirimanBarang_akun_penerima }}
          </td>
          <td class="px-4 py-2">
            {{ pengiriman.pengirimanBarang_no_telepon }}
          </td>
          <td class="px-4 py-2">
            {{ formatCurrency(pengiriman.pengirimanBarang_harga_kirim_barang) }}
          </td>
          <td class="px-4 py-2">
            {{ pengiriman.pengirimanBarang_jenis_pengiriman_barang }}
          </td>
          <td class="px-4 py-2">
            {{ pengiriman.pengirimanBarang_alamat_pengiriman_barang }}
          </td>
          <td class="px-4 py-2">{{ pengiriman.pengirimanBarang_status }}</td>
          <td class="px-4 py-2">{{ pengiriman.pengirimanBarang_catatan }}</td>
          <td class="px-4 py-2">{{ formatDate(pengiriman.created_at) }}</td>
          <td class="px-4 py-2">
            <div class="flex space-x-2">
              <button
                class="flex items-center gap-1 px-2 py-1 bg-[#FBBF24] text-white hover:bg-[#FFD15A] rounded-md text-s"
                @click="openViewDetail(pengiriman.pengirimanBarang_id)"
              >
                View
              </button>
              <button
                class="flex items-center gap-1 px-2 py-1 bg-[#3D8BFD] text-white hover:bg-[#5C9EFF] rounded-md text-s"
                @click="deletepengirimanData(pengiriman.pengirimanBarang_id)"
              >
                Print
              </button>
              <button
                class="flex items-center gap-1 px-2 py-1 bg-[#DC3545] text-white hover:bg-[#FF4456] rounded-md text-s"
                @click="deletepengirimanData(pengiriman.pengirimanBarang_id)"
              >
                Delete
              </button>
            </div>
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
  <!-- Modal -->
  <ViewDetailModal
    :show="showDetailModal"
    :id="selectedPengirimanId"
    @close="showDetailModal = false"
  />
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";
import ViewDetailModal from '../components/ModalViewDetail.vue'

const config = useRuntimeConfig();
const url = ref('');

const pengirimanData = ref([]);
const searchQuery = ref("");
const currentPage = ref(1);
const itemsPerPage = ref(10);
const showDetailModal = ref(false);
const selectedPengirimanId = ref(null);

onMounted(() => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;
  fetchDataPengiriman();
});
// /api/pengiriman-barang/1
const fetchDataPengiriman = async () => {
  try {
    const res = await axios.get(`${url.value}/api/pengiriman-barang`);
    pengirimanData.value = res.data.data;
  } catch (error) {
    console.error("Gagal fetch data pengiriman:", error);
  }
};

const openViewDetail = (id) => {
  selectedPengirimanId.value = id;
  showDetailModal.value = true;
};

const listpengirimanData = computed(() => {
  if (!searchQuery.value) return pengirimanData.value;

  const q = searchQuery.value.toLowerCase();
  return pengirimanData.value.filter((pengiriman) => {
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

const deletepengirimanData = async (id) => {
  const result = await Swal.fire({
    title: "Konfirmasi Hapus",
    text: `Anda yakin ingin menghapus data ini?`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, Hapus!",
    cancelButtonText: "Batal",
    reverseButtons: true,
  });

  if (result.isConfirmed) {
    try {
      const response = await axios.delete(`${url.value}/api/pengiriman-barang/${id}`);

      if (response.status === 200) {
        pengirimanData.value = pengirimanData.value.filter(
          (item) => item.pengirimanBarang_id !== id
        );

        Swal.fire({
          title: "Berhasil",
          text: "Data berhasil di delete",
          icon: "info",
          confirmButtonText: "OK",
        });
      }
    } catch (error) {
      console.error("Error deleting product:", error);
      Swal.fire({
        title: "Gagal",
        text: "Terjadi kesalahan saat menghapus data.",
        icon: "error",
      });
    }
  }
};

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
</style>
