<template>
  <div>
    <title>Packaging</title>

    <div class="judul text-xl font-semibold mb-4">Daftar Packaging</div>

    <div class="flex justify-between items-center mb-4">
      <input v-model="searchQuery" type="text" class="search-box mb-4" placeholder="Cari Pengiriman Barang..." />

      <button @click="exportToExcel" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
        Export Excel
      </button>
    </div>

    <table class="datatable w-full rounded-md overflow-hidden">
      <thead class="bg-blue-100">
        <tr>
          <th>No</th>
          <th>Nama Penerima</th>
          <th>Nama Akun</th>
          <th>Telepon</th>
          <th>Harga Kirim</th>
          <th>Jenis</th>
          <th>Alamat</th>
          <th>Status Kirim</th>
          <th>Barang</th>
          <th>Aksi</th>
        </tr>
      </thead>

      <tbody>
        <tr
          v-for="(row, index) in pagination"
          :key="row.pengirimanBarang_id"
          :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
        >
          <td>{{ index + 1 }}</td>
          <td>{{ row.pengirimanBarang_nama_penerima }}</td>
          <td>{{ row.pengirimanBarang_akun_penerima }}</td>
          <td>{{ row.pengirimanBarang_no_telepon }}</td>
          <td>{{ formatCurrency(row.pengirimanBarang_harga_kirim_barang) }}</td>
          <td>{{ row.pengirimanBarang_jenis_pengiriman_barang }}</td>
          <td>{{ row.pengirimanBarang_alamat_pengiriman_barang }}</td>

          <td>
            <span :class="row.status_pengiriman === 'PACKAGING' ? 'text-blue-600' : 'text-red-600'">
              {{ row.status_pengiriman }}
            </span>
          </td>

          <td>
            <div v-for="code in row.list_code_nama" :key="code">{{ code }}</div>
          </td>

          <td>
            <div class="flex gap-2">
              <button
                class="px-2 py-1 bg-blue-500 text-white rounded text-xs"
                @click="openModalEdit(row)"
              >
                Edit
              </button>

              <button
                class="px-2 py-1 bg-red-500 text-white rounded text-xs"
                @click="deleteData(row.pengirimanBarang_id)"
              >
                Delete
              </button>

              <!-- Tombol SELESAI selalu muncul -->
              <button
                class="px-2 py-1 bg-green-600 text-white rounded text-xs"
                @click="selesaikanPackaging(row)"
              >
                Selesai
              </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <div class="flex justify-between items-center mt-4 text-xs">
      <div>
        <label>Tampilkan:</label>
        <select v-model="itemsPerPage" class="border px-2 py-1 rounded text-xs">
          <option value="5">5</option>
          <option value="10">10</option>
          <option value="20">20</option>
          <option value="50">50</option>
          <option value="all">All</option>
        </select>
      </div>

      <div class="flex space-x-2">
        <button
          class="px-3 py-1 bg-gray-300 rounded"
          @click="currentPage--"
          :disabled="currentPage === 1"
        >
          Sebelumnya
        </button>

        <button
          v-for="p in paginatedPages"
          :key="p"
          @click="typeof p === 'number' && (currentPage = p)"
          :class="[
            'px-3 py-1 rounded',
            currentPage === p ? 'bg-blue-500 text-white' : 'bg-gray-200'
          ]"
        >
          {{ p }}
        </button>

        <button
          class="px-3 py-1 bg-gray-300 rounded"
          @click="currentPage++"
          :disabled="currentPage === totalPages"
        >
          Selanjutnya
        </button>
      </div>
    </div>

    <ModalEditPackaging
      v-model:show="isModalOpen"
      :barang="selectedBarang"
      :pengiriman="selectedPengiriman"
      @save="handleSave"
      @close="isModalOpen = false"
    />
  </div>
</template>


<script setup>
import { ref, onMounted, computed, watch } from "vue";
import axios from "axios";
import Swal from "sweetalert2";
import { useRuntimeConfig } from "#imports";
import ModalEditPackaging from "../components/ModalEditPackaging.vue";
import * as XLSX from "xlsx";
import { saveAs } from "file-saver";

const config = useRuntimeConfig();
const url = config.public.apiBase;

const pengirimanData = ref([]);
const searchQuery = ref("");
const currentPage = ref(1);
const itemsPerPage = ref(10);

const isModalOpen = ref(false);
const selectedPengiriman = ref(null);
const selectedBarang = ref([]);
const fetchData = async () => {
  const res = await axios.get(`${url}/api/pengiriman-barang/get-pengiriman`);
  pengirimanData.value = res.data.data.filter(
    (x) => x.status_pengiriman !== "Done"
  );
};

onMounted(fetchData);

const openModalEdit = async (row) => {
  selectedPengiriman.value = row;

  const trxId = row.pengirimanBarang_transaksi_id;

  try {
    const res = await axios.get(
      `${url}/api/pengiriman-barang/get-transaksi-detail/${trxId}`
    );

    selectedBarang.value = res.data.data.map((item) => ({
      kode: item.code_nama,
      nama: item.barangentry_nama,
      jumlah: item.transaksidetail_jumlah_barang,
      harga: Number(item.transaksidetail_harga_barang),
      trx_detail_id: item.transaksidetail_id,
      is_check: true,
    }));

    isModalOpen.value = true;
  } catch (err) {
    Swal.fire("Gagal!", "Detail barang tidak ditemukan!", "error");
  }
};

const deleteData = async (id) => {
  const c = await Swal.fire({
    icon: "warning",
    title: "Hapus?",
    text: "Data akan hilang permanen!",
    showCancelButton: true,
  });

  if (!c.isConfirmed) return;

  await axios.delete(`${url}/api/pengiriman-barang/${id}`);

  setTimeout(fetchData, 10);

  Swal.fire("Berhasil!", "Data terhapus.", "success");
};

const selesaikanPackaging = async (row) => {
  try {
    const trxId = row.pengirimanBarang_transaksi_id;
    const res = await axios.get(
      `${url}/api/pengiriman-barang/get-transaksi-detail/${trxId}`
    );
    const barangList = res.data.data;
    for (const item of barangList) {
      await axios.post(`${url}/api/packaging`, {
        packaging_transactiondetail_id: item.transaksidetail_id,
        packaging_nama_akun: row.pengirimanBarang_nama_penerima,
        packaging_alamat: row.pengirimanBarang_alamat_pengiriman_barang,
      });
    }

    await axios.post(
      `${url}/api/packaging/update-status/${row.pengirimanBarang_id}`,
      { packaging_status: "Done" }
    );

    setTimeout(fetchData, 20);

    Swal.fire({
      title: "Selesai!",
      text: "Semua barang telah dipindahkan ke packaging.",
      icon: "success",
      timer: 900,
      showConfirmButton: false,
    });
  } catch (err) {
    Swal.fire("Error!", "Gagal memproses packaging.", "error");
  }
};

const exportToExcel = async () => {
  try {
    const list = [...filteredData.value];

    if (!list.length) {
      Swal.fire("Tidak ada data!", "", "info");
      return;
    }

    Swal.fire({
      title: "Memproses…",
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
    });

    setTimeout(fetchData, 30);
    let allRows = [];

    for (const row of list) {
      const trx = await axios.get(
        `${url}/api/pengiriman-barang/get-transaksi-detail/${row.pengirimanBarang_transaksi_id}`
      );

      trx.data.data.forEach((item) => {
        allRows.push({
          Tanggal: new Date(row.created_at),
          "Nama Penerima": row.pengirimanBarang_nama_penerima,
          "Nama Akun": row.pengirimanBarang_akun_penerima,
          Alamat: row.pengirimanBarang_alamat_pengiriman_barang,
          "Kode Barang": item.code_nama,
          Qty: item.transaksidetail_jumlah_barang,
          Harga: item.transaksidetail_harga_barang,
        });
      });
    }

    const WS = XLSX.utils.json_to_sheet(allRows);
    const WB = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(WB, WS, "Packaging");

    const buf = XLSX.write(WB, { bookType: "xlsx", type: "array" });
    saveAs(new Blob([buf]), `Packaging_${new Date().toISOString().split("T")[0]}.xlsx`);

    Swal.close();
    Swal.fire("Export selesai!", "", "success");
  } catch (err) {
    Swal.close();
    Swal.fire("Gagal export!", "Terjadi kesalahan.", "error");
  }
};

const filteredData = computed(() => {
  const q = searchQuery.value.toLowerCase();
  return pengirimanData.value.filter(
    (x) =>
      x.pengirimanBarang_nama_penerima?.toLowerCase().includes(q) ||
      x.pengirimanBarang_akun_penerima?.toLowerCase().includes(q) ||
      x.pengirimanBarang_alamat_pengiriman_barang?.toLowerCase().includes(q)
  );
});

const pagination = computed(() => {
  if (itemsPerPage.value === "all") return filteredData.value;

  const start = (currentPage.value - 1) * itemsPerPage.value;
  return filteredData.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() =>
  itemsPerPage.value === "all"
    ? 1
    : Math.ceil(filteredData.value.length / itemsPerPage.value)
);

const paginatedPages = computed(() => {
  const total = totalPages.value;
  const current = currentPage.value;

  if (total <= 5) return [...Array(total).keys()].map((i) => i + 1);

  if (current <= 3) return [1, 2, 3, "...", total];
  if (current >= total - 2)
    return [1, "...", total - 2, total - 1, total];

  return [1, "...", current - 1, current, current + 1, "...", total];
});

watch(currentPage, (v) => {
  if (v < 1) currentPage.value = 1;
  if (v > totalPages.value) currentPage.value = totalPages.value;
});

const formatCurrency = (val) =>
  new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR" }).format(val);
</script>


<style scoped>
.search-box {
  border: 1px solid #ccc;
  padding: 8px;
  width: 350px;
}

.datatable th,
.datatable td {
  padding: 10px;
  font-size: 12px;
}

.datatable th {
  background-color: #f4f4f4;
}
</style>
