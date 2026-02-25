<template>
  <div>
    <div class="judul text-xl font-semibold mb-4">Daftar Transaksi</div>
    <input v-model="searchQuery" type="text" class="search-box mb-4 rounded-md" placeholder="Cari transaksi
      ..." />

    <table class="datatable">
      <thead>
        <tr>
          <th>No.</th>
          <th>Nama Customer</th>
          <th>No. Telepon</th>
          <th>Jumlah Barang</th>
          <th>Total Harga</th>
          <th>Cara Bayar</th>
          <th>Tipe</th>
          <th>Status</th>
          <th>Catatan</th>
          <th>Tanggal</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(trx, index) in pagination" :key="trx.transaksi_id">
          <td>{{ index + 1 }}</td>
          <td>{{ trx.transaksi_nama_customer }}</td>
          <td>{{ trx.transaksi_nomor_telepon }}</td>
          <td>{{ trx.transaksi_jumlah_barang }}</td>
          <td>{{ formatCurrency(trx.transaksi_total_harga) }}</td>
          <td>{{ trx.transaksi_cara_bayar }}</td>
          <td>{{ trx.transaksi_tipe }}</td>
          <td>{{ trx.transaksi_status }}</td>
          <td>{{ trx.transaksi_catatan }}</td>
          <td>{{ formatDate(trx.created_at) }}</td>
          <td class="align-middle">
            <div class="flex items-center space-x-2">
              <button
                class="flex items-center gap-1 px-2 py-2 bg-green-500 text-white hover:bg-green-600 rounded-[10px] text-sm"
                @click="printStruk(trx.transaksi_id)">
                Print
              </button>
              <button
                class="flex items-center gap-1 px-2 py-2 bg-red-500 text-white hover:bg-red-600 rounded-[10px] text-sm"
                @click="deleteTransaksi(trx.transaksi_id)">
                Delete
              </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
    <div class="flex justify-between items-center mt-8 mb-4">
      <div class="flex items-center space-x-2">
        <label for="perPage">Tampilkan:</label>
        <select id="perPage" v-model="itemsPerPage" class="border px-2 py-1 rounded">
          <option :value="5">5</option>
          <option :value="10">10</option>
          <option :value="20">20</option>
          <option :value="50">50</option>
        </select>
      </div>

      <div class="flex items-center space-x-2">
        <button class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400" :disabled="currentPage === 1"
          @click="currentPage--">
          Sebelumnya
        </button>

        <button v-for="(page, index) in paginatedPages" :key="index"
          @click="typeof page === 'number' && (currentPage = page)" :class="[
            'px-3 py-1 rounded',
            currentPage === page ? 'bg-blue-500 text-white' : 'bg-gray-200',
            page === '...' ? 'cursor-default' : 'cursor-pointer',
          ]" :disabled="page === '...'">
          {{ page }}
        </button>

        <button class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400" :disabled="currentPage === totalPages"
          @click="currentPage++">
          Selanjutnya
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import { useRuntimeConfig } from "#imports";

const config = useRuntimeConfig();
const url = ref(config.public.apiBase);

const transaksi = ref([]);
const searchQuery = ref("");
const currentPage = ref(1);
const itemsPerPage = ref(10);

onMounted(() => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;
  fetchTransaksi();
});

const fetchTransaksi = async () => {
  try {
    const token = sessionStorage.getItem("auth_token")
    const res = await axios.get(`${url.value}/api/transaksi`, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    });
    transaksi.value = res.data.data;
  } catch (error) {
    console.error("Gagal fetch data transaksi:", error);
  }
};

const listTransaksi = computed(() => {
  const sorted = [...transaksi.value].sort((a, b) => {
    return new Date(b.created_at) - new Date(a.created_at);
  });

  if (!searchQuery.value) return sorted;

  const q = searchQuery.value.toLowerCase();
  return sorted.filter((trx) => {
    return (
      trx.transaksi_nama_customer?.toLowerCase().includes(q) ||
      trx.transaksi_nomor_telepon?.toLowerCase().includes(q) ||
      trx.transaksi_tipe?.toLowerCase().includes(q) ||
      trx.transaksi_status?.toLowerCase().includes(q)
    );
  });
});

const pagination = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return listTransaksi.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(listTransaksi.value.length / itemsPerPage.value);
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

const deleteTransaksi = async (id) => {
  if (confirm(`Anda yakin ingin menghapus transaksi" ini?`)) {
    try {
      const token = sessionStorage.getItem("auth_token")
      const response = await axios.delete(`${url.value}/api/transaksi/` + id, {
        headers: {
          "Authorization": `Bearer ${token}`,
          "Content-Type": "application/json",
        }
      });

      if (response.status === 200) {
        transaksi.value = transaksi.value.filter(
          (item) => item.transaksi_id !== id
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

function printStruk(id) {
  const printWindow = window.open(`/print/${id}`, "_blank");
  if (!printWindow) {
    alert("Popup diblokir! Harap izinkan pop-up untuk mencetak struk.");
  }
}

watch(searchQuery, () => {
  currentPage.value = 1;
});

watch(itemsPerPage, () => {
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

.search-box::placeholder {
  color: #888;
}
</style>
