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
          <th>Status Packaging</th>
          <th>Barang</th>
          <th>Aksi</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="(row, index) in pagination" :key="row.pengirimanBarang_id"
          :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
          <td>{{ index + 1 }}</td>
          <td>{{ row.pengirimanBarang_nama_penerima }}</td>
          <td>{{ row.pengirimanBarang_akun_penerima }}</td>
          <td>{{ row.pengirimanBarang_no_telepon }}</td>
          <td>{{ formatCurrency(row.pengirimanBarang_harga_kirim_barang) }}</td>
          <td>{{ row.pengirimanBarang_jenis_pengiriman_barang }}</td>
          <td>{{ row.pengirimanBarang_alamat_pengiriman_barang }}</td>

          <td>
            <span :class="row.status_pengiriman === 'NOT PACKAGING' ? 'text-red-600' : 'text-blue-600'">
              {{ row.status_pengiriman }}
            </span>
          </td>

          <td>
            <span :class="row.packaging_status === 'Done' ? 'text-green-600' : 'text-gray-500'">
              {{ row.packaging_status ?? '-' }}
            </span>
          </td>

          <td>
            <div v-for="code in row.list_code_nama" :key="code">{{ code }}</div>
          </td>

          <td>
            <div class="flex gap-2">
              <button class="px-2 py-1 bg-blue-500 text-white rounded text-xs" @click="openModalEdit(row)">
                Edit
              </button>

              <button class="px-2 py-1 bg-red-500 text-white rounded text-xs"
                @click="deleteData(row.pengirimanBarang_id)">
                Delete
              </button>

              <button v-if="row.status_pengiriman === 'PACKAGING' && row.packaging_status !== 'Done'"
                class="px-2 py-1 bg-green-600 text-white rounded text-xs" @click="selesaikanPackaging(row)">
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
        <button class="px-3 py-1 bg-gray-300 rounded" @click="currentPage--" :disabled="currentPage === 1">
          Sebelumnya
        </button>

        <button v-for="p in paginatedPages" :key="p" @click="typeof p === 'number' && (currentPage = p)" :class="['px-3 py-1 rounded',
          currentPage === p ? 'bg-blue-500 text-white' : 'bg-gray-200']">
          {{ p }}
        </button>

        <button class="px-3 py-1 bg-gray-300 rounded" @click="currentPage++" :disabled="currentPage === totalPages">
          Selanjutnya
        </button>
      </div>
    </div>


    <ModalEditPackaging v-model:show="isModalOpen" :barang="selectedBarang" :pengiriman="selectedPengiriman"
      @save="handleSave" @close="isModalOpen = false" />
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
  pengirimanData.value = res.data.data;
};

onMounted(fetchData);


const openModalEdit = async (row) => {
  selectedPengiriman.value = row;

  const trx_id = row.pengirimanBarang_transaksi_id;

  try {
    const res = await axios.get(`${url}/api/pengiriman-barang/get-transaksi-detail/${trx_id}`);

    if (!res.data.data || res.data.data.length === 0) {
      Swal.fire({
        icon: "warning",
        title: "Tidak ada barang!",
        text: "Detail barang untuk transaksi ini tidak ditemukan.",
      });
      return;
    }

    selectedBarang.value = res.data.data.map(item => ({
      kode: item.code_nama,
      nama: item.barangentry_nama,
      jumlah: item.transaksidetail_jumlah_barang,
      harga: Number(item.transaksidetail_harga_barang),
      trx_detail_id: item.transaksidetail_id,
      is_check: true,
    }));

    isModalOpen.value = true;

  } catch (err) {
    console.error(err);
    Swal.fire({
      icon: "error",
      title: "Gagal!",
      text: "Barang Tidak Ditemukan",
    });
  }
};

const deleteData = async (id) => {
  const c = await Swal.fire({
    icon: "warning",
    title: "Hapus?",
    text: "Data akan dihapus permanen.",
    showCancelButton: true
  });

  if (!c.isConfirmed) return;

  await axios.delete(`${url}/api/pengiriman-barang/${id}`);
  fetchData();

  Swal.fire("Berhasil", "Data terhapus", "success");
};

const selesaikanPackaging = async (row) => {
  try {
    await axios.post(`${url}/api/packaging/update-status/${row.pengirimanBarang_id}`, {
      packaging_status: "Done"
    });

    await fetchData(); // refresh list

    Swal.fire({
      title: "Berhasil",
      text: "Packaging selesai",
      icon: "success",
      timer: 1000,
      showConfirmButton: false
    });

  } catch (err) {
    console.error(err);
    Swal.fire("Gagal", "Terjadi kesalahan", "error");
  }
};

async function buildRows(row) {
  try {
    const trxId = row.pengirimanBarang_transaksi_id;
    if (!trxId) return [];

    const { data: trx } = await axios.get(`${url}/api/pengiriman-barang/get-transaksi-detail/${trxId}`);

    if (!trx.data || trx.data.length === 0) return [];

    const rows = [];

    for (const d of trx.data) {
      rows.push({
        tgl: new Date(row.created_at),
        penerima: row.pengirimanBarang_nama_penerima ?? "-",
        akun: row.pengirimanBarang_akun_penerima ?? "-",
        alamat: row.pengirimanBarang_alamat_pengiriman_barang ?? "-",
        barang: d.code_nama ?? "-",
        qty: Number(d.transaksidetail_jumlah_barang ?? 0),
        harga: Number(d.transaksidetail_harga_barang ?? 0),
      });
    }

    return rows;
  } catch (err) {
    console.error("Gagal memproses row:", err);
    return [];
  }
}

const exportToExcel = async () => {
  try {
    const source = [...filteredData.value];

    if (!source.length) {
      Swal.fire({
        title: "Tidak ada data",
        text: "Data kosong, tidak bisa diexport.",
        icon: "info",
      });
      return;
    }

    Swal.fire({
      title: "Menyiapkan data…",
      text: "Mohon tunggu sebentar",
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
    });

    let allRows = [];

    for (const row of source) {
      const rows = await buildRows(row);
      allRows.push(...rows);
    }

    if (!allRows.length) {
      Swal.close();
      Swal.fire("Tidak ada data detail!", "", "info");
      return;
    }

    allRows.sort((a, b) => {
      const kA = `${a.penerima}|||${a.alamat}`.toLowerCase();
      const kB = `${b.penerima}|||${b.alamat}`.toLowerCase();
      if (kA !== kB) return kA < kB ? -1 : 1;
      return a.tgl - b.tgl;
    });

    const exportRows = allRows.map(r => ({
      "Tanggal": r.tgl,
      "Nama Penerima": r.penerima,
      "Nama Akun": r.akun,
      "Alamat": r.alamat,
      "Kode Barang": r.barang,
      "Qty": r.qty,
      "Harga": r.harga,
    }));

    const worksheet = XLSX.utils.json_to_sheet(exportRows, { cellDates: true, dateNF: "dd-mmm-yyyy" });
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Packaging");

    const merges = [];
    let start = 0;
    while (start < exportRows.length) {
      const key = `${exportRows[start]["Nama Penerima"]}|||${exportRows[start]["Alamat"]}`;
      let end = start;
      while (
        end + 1 < exportRows.length &&
        `${exportRows[end + 1]["Nama Penerima"]}|||${exportRows[end + 1]["Alamat"]}` === key
      ) {
        end++;
      }

      if (end > start) {
        merges.push({ s: { r: start + 1, c: 1 }, e: { r: end + 1, c: 1 } });
        merges.push({ s: { r: start + 1, c: 3 }, e: { r: end + 1, c: 3 } });
      }

      start = end + 1;
    }

    worksheet["!merges"] = merges;

    worksheet["!cols"] = [
      { wch: 14 },
      { wch: 22 },
      { wch: 22 },
      { wch: 40 },
      { wch: 16 },
      { wch: 6 },
      { wch: 12 },
    ];

    const excelBuffer = XLSX.write(workbook, { bookType: "xlsx", type: "array" });
    const blob = new Blob([excelBuffer], {
      type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    });

    const fileName = `Packaging_${new Date().toISOString().split("T")[0]}.xlsx`;
    saveAs(blob, fileName);

    Swal.close();
    Swal.fire("Berhasil", "Export Excel selesai.", "success");

  } catch (err) {
    console.error(err);
    Swal.close();
    Swal.fire("Gagal", "Terjadi kesalahan saat export.", "error");
  }
};

const handleSave = () => {
  isModalOpen.value = false;
  fetchData();
};

const filteredData = computed(() => {
  const q = searchQuery.value.toLowerCase();

  return pengirimanData.value.filter((x) =>
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
  const pages = [];
  const total = totalPages.value;
  const current = currentPage.value;

  if (total <= 5) return [...Array(total).keys()].map((i) => i + 1);

  if (current <= 3) return [1, 2, 3, "...", total];
  if (current >= total - 2) return [1, "...", total - 2, total - 1, total];

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
