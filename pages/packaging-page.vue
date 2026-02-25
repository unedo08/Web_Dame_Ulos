<template>
  <div>
    <title>Packaging</title>
    <div class="judul text-xl font-semibold mb-4">Daftar Packaging</div>
    <div class="flex justify-between items-center mb-4">
      <input v-model="searchQuery" type="text" class="search-box mb-4 rounded-md" placeholder="Cari Pengiriman Barang..." />
      <button @click="exportToExcel" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
        Export Excel
      </button>
    </div>
    <table class="datatable w-full rounded-md overflow-hidden">
      <thead class="bg-blue-100">
        <tr>
          <th class="text-left">No</th>
          <th class="text-left">Tanggal</th>
          <th class="text-left">Nama Akun</th>
          <th class="text-left">Nama Penerima</th>
          <th class="text-left">Telepon</th>
          <th class="text-left">Ekspedisi</th>
          <th class="text-left">Alamat</th>
          <th class="text-left">Status Kirim</th>
          <th class="text-left">Barang</th>
          <th class="text-left">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, index) in pagination" :key="row.pengirimanBarang_id"
          :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
          <td>{{ index + 1 }}</td>
          <td>{{ formatTanggal(row.created_at) }}</td>
          <td>{{ row.pengirimanBarang_akun_penerima }}</td>
          <td>{{ row.pengirimanBarang_nama_penerima }}</td>
          <td>{{ row.pengirimanBarang_no_telepon }}</td>
          <td>{{ row.pengirimanBarang_jenis_pengiriman_barang }}</td>
          <td>{{ row.pengirimanBarang_alamat_pengiriman_barang }}</td>
          <td>
            <span class="text-status px-1 py-1 rounded-full font-semibold" :class="statusChipClass(row.status_pengiriman)">
              {{ row.status_pengiriman || '-' }}
            </span>
          </td>
          <td>
            <div v-for="code in row.list_code_nama" :key="code">{{ code }}</div>
          </td>
          <td>
            <div class="flex gap-2">
              <template v-if="row.status_pengiriman !== 'DONE'">
                <button class="px-2 py-1 bg-blue-500 text-white rounded text-xs" @click="openModalEdit(row)">
                  Edit
                </button>

                <button class="px-2 py-1 bg-green-600 text-white rounded text-xs" @click="selesaikanPackaging(row)">
                  Selesai
                </button>
              </template>
              <button class="px-2 py-1 bg-red-500 text-white rounded text-xs"
                @click="deleteData(row.pengirimanBarang_id)">
                Delete
              </button>
              <button v-if="row.status_pengiriman === 'DONE'" class="px-2 py-1 bg-indigo-600 text-white rounded text-xs"
                @click="printLabel(row)">
                Print
              </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <div class="flex justify-between items-center mt-8 mb-4 text-xs">
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
import Swal from "sweetalert2";
import { useRuntimeConfig } from "#imports";
import ModalEditPackaging from "../components/ModalEditPackaging.vue";
import * as XLSX from "xlsx";
import { saveAs } from "file-saver";

const config = useRuntimeConfig();
const url = config.public.apiBase;
const { $api } = useNuxtApp();
const pengirimanData = ref([]);
const searchQuery = ref("");
const currentPage = ref(1);
const itemsPerPage = ref(10);

const isModalOpen = ref(false);
const selectedPengiriman = ref(null);
const selectedBarang = ref([]);


const fetchData = async () => {
  const res = await $api.get(`${url}/api/pengiriman-barang/get-pengiriman`);
  pengirimanData.value = res.data.data;
};

onMounted(fetchData);

function formatTanggal(tanggal) {
  const date = new Date(tanggal);
  return new Intl.DateTimeFormat("id-ID", {
    day: "2-digit",
    month: "long",
    year: "numeric",
  }).format(date);
}

const openModalEdit = async (row) => {
  selectedPengiriman.value = row;
  const trx_id = row.pengirimanBarang_transaksi_id;
  try {
    
    const res = await $api.get(`${url}/api/pengiriman-barang/get-transaksi-detail/${trx_id}`);

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
  
  await $api.delete(`${url}/api/pengiriman-barang/${id}`);
  fetchData();

  Swal.fire("Berhasil", "Data terhapus", "success");
};

const selesaikanPackaging = async (row) => {
  try {
    
    const trxId = row.pengirimanBarang_transaksi_id;
    const res = await $api.get(`${url}/api/pengiriman-barang/get-transaksi-detail/${trxId}`);
    const barangList = res.data.data;

    if (!barangList || barangList.length === 0) {
      Swal.fire("Tidak ada barang!", "Transaksi ini tidak memiliki barang.", "warning");
      return;
    }

    for (const item of barangList) {
      await $api.post(`${url}/api/packaging`, {
        packaging_transactiondetail_id: item.transaksidetail_id,
        packaging_nama_akun: row.pengirimanBarang_nama_penerima,
        packaging_alamat: row.pengirimanBarang_alamat_pengiriman_barang,
      });
    }

    setTimeout(() => {
      fetchData();
    }, 50);

    Swal.fire({
      title: "Berhasil!",
      text: "Semua barang telah dimasukkan ke packaging.",
      icon: "success",
      timer: 900,
      showConfirmButton: false
    });

  } catch (err) {
    console.error(err);
    Swal.fire("Error!", "Gagal memproses packaging.", "error");
  }
};

const exportToExcel = async () => {
  try {
    
    Swal.fire({
      title: "Menyiapkan data…",
      text: "Mohon tunggu sebentar",
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
    });

    const res = await $api.get(`${url}/api/pengiriman-barang/export-data`);

    const data = res.data.data;
    if (!data || !data.length) {
      Swal.close();
      Swal.fire("Tidak ada data", "Data kosong", "info");
      return;
    }

    data.sort((a, b) => {
      const keyA =
        `${a.pengirimanBarang_nama_penerima}|||${a.pengirimanBarang_alamat_pengiriman_barang}`.toLowerCase();
      const keyB =
        `${b.pengirimanBarang_nama_penerima}|||${b.pengirimanBarang_alamat_pengiriman_barang}`.toLowerCase();

      if (keyA !== keyB) return keyA < keyB ? -1 : 1;
      return new Date(a.created_at) - new Date(b.created_at);
    });


    const exportRows = data.map((r) => ({
      Tanggal: new Date(r.created_at),
      "Nama Penerima": r.pengirimanBarang_nama_penerima,
      "Nama Akun": r.pengirimanBarang_akun_penerima,
      Alamat: r.pengirimanBarang_alamat_pengiriman_barang,
      "Kode Barang": r.code_nama,
      Qty: r.transaksidetail_jumlah_barang,
      Harga: Number(r.transaksidetail_harga_barang),
    }));

    const worksheet = XLSX.utils.json_to_sheet(exportRows, {
      cellDates: true,
      dateNF: "dd-mmm-yyyy",
    });

    Object.keys(worksheet).forEach((cell) => {
      if (cell.startsWith("G") && cell !== "G1") {
        worksheet[cell].z = '"Rp" #,##0';
      }
    });

    const merges = [];
    let start = 0;

    while (start < exportRows.length) {
      const key =
        `${exportRows[start]["Nama Penerima"]}|||${exportRows[start]["Alamat"]}`;
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
      { wch: 14 },
    ];

    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Packaging");

    const buffer = XLSX.write(workbook, {
      bookType: "xlsx",
      type: "array",
    });

    const blob = new Blob([buffer], {
      type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    });

    saveAs(
      blob,
      `Packaging_${new Date().toISOString().split("T")[0]}.xlsx`
    );
    Swal.close();
    Swal.fire("Berhasil", "Export Excel selesai.", "success");
    fetchData();
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

const sortedPengirimanData = computed(() => {
  return [...pengirimanData.value].sort(
    (a, b) => b.pengirimanBarang_id - a.pengirimanBarang_id
  );
});

const filteredData = computed(() => {
  const q = searchQuery.value.toLowerCase();

  return sortedPengirimanData.value.filter((x) =>
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

const statusChipClass = (status) => {
  switch (status) {
    case "DONE":
      return "bg-green-100 text-green-700 border border-green-300";

    case "IN PROGRESS":
      return "bg-yellow-100 text-yellow-700 border border-yellow-300";

    default:
      return "bg-gray-100 text-gray-600 border border-gray-300";
  }
};

const printLabel = (row) => {
  const printWindow = window.open("", "_blank");

  const logoPath = `/image/DameUlosLogo2.png`;

  printWindow.document.write(`
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Label Pengiriman</title>

<style>
  @page{
    size: 100mm 150mm;
    margin: 0;
  }

  body{
    font-family: Arial, sans-serif;
    margin:0;
    padding: 14mm 10mm;
  }

  .container{
    width: 100%;
    height: 100%;
    box-sizing: border-box;
  }

  .logo{
    text-align:center;
    margin-bottom: 8px;
  }

  .logo img{
    width:130px;
  }

  .section{
    margin-top: 6px;
    font-size: 14px;
  }

  .label{
    font-weight:bold;
  }

  .value{
    margin-top:2px;
    margin-bottom:8px;
  }

  .divider{
    border-top:2px solid #000;
    margin:10px 0;
  }

  .store{
    font-size: 13px;
  }

  .quote{
    margin-top: 10px;
    font-style: italic;
    font-size: 12px;
    line-height: 1.45;
  }

  .courier{
    text-align:right;
    font-weight:bold;
    font-size:16px;
    margin-top: 8px;
  }
</style>
</head>

<body>

<div class="container">

  <div class="logo">
    <img src="${logoPath}" />
  </div>

  <div class="section">
    <div class="label">Nama :</div>
    <div class="value">${row.pengirimanBarang_nama_penerima || "-"}</div>

    <div class="label">Nomor HP :</div>
    <div class="value">${row.pengirimanBarang_no_telepon || "-"}</div>

    <div class="label">Alamat :</div>
    <div class="value">${row.pengirimanBarang_alamat_pengiriman_barang || "-"}</div>
  </div>

  <div class="divider"></div>

  <div class="store">
    <b>Galeri Dame Ulos</b><br>
    081262804500<br>
    Jl. Gereja HKBP Dame, Desa Lumban Matio, Saitnihuta, Kec. Tarutung, Kab. Tapanuli Utara
  </div>

  <div class="quote">
    “Terima kasih telah memilih Dame Ulos. Semoga ulos ini menjadi simbol kasih, kehangatan, dan doa baik dalam setiap kesempatan istimewa.”
  </div>

  <div class="courier">
    ${row.pengirimanBarang_jenis_pengiriman_barang || ""}
  </div>

</div>

<script>
  window.onload = function(){
    window.print();
    window.onafterprint = () => window.close();
  }
<\/script>

</body>
</html>
  `);

  printWindow.document.close();
};

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
  padding: 10px;
  width: 385px;
  height: 25px;
  font-size: 12px;
}

.datatable th,
.datatable td {
  padding: 10px;
  font-size: 12px;
}

.datatable th {
  background-color: #f4f4f4;
}

.text-status{
  font-size: 8px !important;
}
</style>
