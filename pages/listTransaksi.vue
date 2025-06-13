<template>
  <div>
    <div class="judul text-xl font-semibold mb-4">Daftar Transaksi</div>
    <input
      v-model="searchQuery"
      type="text"
      class="search-box mb-4"
      placeholder="Cari transaksi
      ..."
    />
    <div>
      <label for="perPage" class="mr-2">Tampilkan:</label>
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
    <table class="datatable">
      <thead>
        <tr>
          <th>No. </th>
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
          <td class="flex space-x-2">
            <button
              class="flex items-center gap-1 px-2 py-1 bg-green-500 text-white hover:bg-green-600 rounded-[10px] text-sm"
              @click="printStruk(trx.transaksi_id)"
            >
              Print
            </button>
            <button
              class="flex items-center gap-1 px-2 py-1 bg-red-500 text-white hover:bg-red-600 rounded-[10px] text-sm"
              @click="deleteTransaksi(trx.transaksi_id)"
            >
              Delete
            </button>
          </td>
        </tr>
      </tbody>
    </table>
    <div class="flex justify-end mt-4 space-x-2">
      <button
        class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400"
        :disabled="currentPage === 1"
        @click="currentPage--"
      >
        Prev
      </button>

      <button
        v-for="page in totalPages"
        :key="page"
        @click="currentPage = page"
        :class="[
          'px-3 py-1 rounded',
          currentPage === page ? 'bg-blue-500 text-white' : 'bg-gray-200',
        ]"
      >
        {{ page }}
      </button>

      <button
        class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400"
        :disabled="currentPage === totalPages"
        @click="currentPage++"
      >
        Next
      </button>
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
    const res = await axios.get(`${url.value}/api/transaksi`);
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
      const response = await axios.delete(`${url.value}/api/transaksi/` + id);

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

// async function printStruk(id) {
//   try {
//     const { data: responsePrint } = await axios.get(
//       `${url.value}/api/transaksi/${id}`
//     );

//     const transaksi = responsePrint.data;
//     const detailWithNames = await Promise.all(
//       transaksi.details.map(async (detail) => {
//         const barangRes = await axios.get(
//           `${url.value}/api/entrybarang/${detail.transaksidetail_barang_id}`
//         );
//         return {
//           ...detail,
//           barangentry_nama:
//             barangRes.data.data.barangentry_nama || "Tidak Diketahui",
//         };
//       })
//     );

//   } catch (err) {
//     console.error("Error mengambil data transaksi", err);
//   }
// }

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
