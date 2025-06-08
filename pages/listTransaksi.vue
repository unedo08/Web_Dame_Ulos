<template>
  <div>
    <div class="judul text-xl font-semibold mb-4">Daftar Transaksi</div>
    <input
      v-model="searchQuery"
      type="text"
      class="search-box mb-4"
      placeholder="Cari transaksi..."
    />
    <table class="datatable">
      <thead>
        <tr>
          <th>No</th>
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
        <tr v-for="(trx, index) in listTransaksi" :key="trx.transaksi_id">
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
              class="flex items-center gap-1 px-2 py-1 bg-red-500 text-white hover:bg-red-600 rounded-[10px] text-sm"
              @click="deleteTransaksi(trx.transaksi_id)"
            >
              Delete
            </button>
          </td>
        </tr>
      </tbody>
    </table>
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
  if (!searchQuery.value) return transaksi.value;

  const q = searchQuery.value.toLowerCase();
  return transaksi.value.filter((trx) => {
    return (
      trx.transaksi_nama_customer?.toLowerCase().includes(q) ||
      trx.transaksi_nomor_telepon?.toLowerCase().includes(q) ||
      trx.transaksi_tipe?.toLowerCase().includes(q) ||
      trx.transaksi_status?.toLowerCase().includes(q)
    );
  });
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
