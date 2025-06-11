<template>
  <div>
    <div class="judul text-xl font-semibold mb-4">Menu Live Transcation</div>
    <input
      v-model="searchQuery"
      type="text"
      class="search-box mb-4"
      placeholder="Cari Pengiriman Barang..."
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
          v-for="(pengiriman, index) in listpengirimanData"
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
          <td class="flex space-x-2">
            <button
              class="flex items-center gap-1 px-2 py-1 bg-red-500 text-white hover:bg-red-600 rounded-[10px] text-sm"
              @click="deletepengirimanData(pengiriman.pengirimanBarang_id)"
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
import Swal from "sweetalert2";

const config = useRuntimeConfig();
const url = ref(config.public.apiBase);

const pengirimanData = ref([]);
const searchQuery = ref("");

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

const listpengirimanData = computed(() => {
  if (!searchQuery.value) return pengirimanData.value;

  const q = searchQuery.value.toLowerCase();
  return pengirimanData.value.filter((pengiriman) => {
    return (
      pengiriman.pengirimanBarang_nama_penerima?.toLowerCase().includes(q) ||
      pengiriman.pengirimanBarang_akun_penerima?.toLowerCase().includes(q) ||
      pengiriman.pengirimanBarang_no_telepon?.toLowerCase().includes(q) ||
      pengiriman.pengirimanBarang_jenis_pengiriman_barang?.toLowerCase().includes(q) ||
      pengiriman.pengirimanBarang_alamat_pengiriman_barang?.toLowerCase().includes(q) ||
      pengiriman.pengirimanBarang_status?.toLowerCase().includes(q) 
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
