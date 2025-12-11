<template>
  <div>
    <title>Packaging - Barang</title>
    <div class="judul text-xl font-semibold mb-4">
      Packaging - Barang yang sudah dikirim
    </div>

    <div class="flex justify-between items-center mb-4">
      <input v-model="searchQuery" type="text" class="search-box" placeholder="Cari Pengiriman Barang..." />

      <button @click="exportToExcel" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
        Export Excel
      </button>
    </div>

    <table class="datatable w-full rounded-md overflow-hidden">
      <thead class="bg-blue-100">
        <tr>
          <th class="px-4 py-2 text-left">No</th>
          <th class="px-4 py-2 text-left">Tanggal</th>
          <th class="px-4 py-2 text-left">Nama Akun</th>
          <th class="px-4 py-2 text-left">Alamat</th>
          <th class="px-4 py-2 text-left">Status</th>
          <th class="px-4 py-2 text-left">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(pengiriman, index) in pagination" :key="pengiriman.packaging_id"
          :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
          <td class="px-4 py-2">{{ index + 1 }}</td>
          <td class="px-4 py-2">{{ formatDate(pengiriman.created_at) }}</td>
          <td class="px-4 py-2">{{ pengiriman.packaging_nama_akun }}</td>
          <td class="px-4 py-2">{{ pengiriman.packaging_alamat }}</td>
          <td class="px-4 py-2">{{ pengiriman.packaging_status }}</td>
          <td class="px-4 py-2">
            <div class="flex space-x-2">
              <button v-if="pengiriman.packaging_status === 'Done'"
                class="btn-selesai flex items-center gap-1 px-2 py-1 text-white rounded-md text-s" :disabled="true"
                :hidden="true">
                Selesai
              </button>
              <template v-else>
                <button class="btn-selesai flex items-center gap-1 px-2 py-1 text-white rounded-md text-s"
                  @click="selesaiPackaging(pengiriman.packaging_id)">
                  Selesai
                </button>
              </template>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination -->
    <div class="flex justify-between items-center mt-4 text-xs">
      <div class="flex items-center space-x-2">
        <label for="perPage">Tampilkan:</label>
        <select id="perPage" v-model="itemsPerPage" class="border px-2 py-1 rounded text-xs">
          <option :value="5">5</option>
          <option :value="10">10</option>
          <option :value="20">20</option>
          <option :value="50">50</option>
          <option value="all">All</option>
        </select>
      </div>

      <div class="flex items-center space-x-2">
        <button class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400 text-xs" :disabled="currentPage === 1"
          @click="currentPage--">
          Sebelumnya
        </button>

        <button v-for="(page, index) in paginatedPages" :key="index"
          @click="typeof page === 'number' && (currentPage = page)" :class="[
            'px-3 py-1 rounded text-xs',
            currentPage === page ? 'bg-blue-500 text-white' : 'bg-gray-200',
            page === '...' ? 'cursor-default' : 'cursor-pointer',
          ]" :disabled="page === '...'">
          {{ page }}
        </button>

        <button class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400 text-xs" :disabled="currentPage === totalPages"
          @click="currentPage++">
          Selanjutnya
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
import axios from "axios";
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";
import * as XLSX from "xlsx";
import { saveAs } from "file-saver";

const config = useRuntimeConfig();
const url = ref("");

const pengirimanData = ref([]);
const searchQuery = ref("");
const currentPage = ref(1);
const itemsPerPage = ref(10);

onMounted(() => {
  url.value = config.public.apiBase;
  fetchDataPengiriman();
});

// 🔹 Fetch Data
const fetchDataPengiriman = async () => {
  try {
    const token = sessionStorage.getItem("auth_token")
    const res = await axios.get(`${url.value}/api/packaging`, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    });
    pengirimanData.value = res.data.data;
  } catch (error) {
    console.error("Gagal fetch data pengiriman:", error);
  }
};

// 🔹 Filter & Sorting
const listpengirimanData = computed(() => {
  const sorted = [...pengirimanData.value].sort(
    (a, b) => new Date(b.created_at) - new Date(a.created_at)
  );

  if (!searchQuery.value) return sorted;

  const q = searchQuery.value.toLowerCase();
  return sorted.filter((pengiriman) => {
    return (
      pengiriman.packaging_nama_akun?.toLowerCase().includes(q) ||
      pengiriman.packaging_alamat?.toLowerCase().includes(q) ||
      pengiriman.packaging_status?.toLowerCase().includes(q)
    );
  });
});

const pagination = computed(() => {
  if (itemsPerPage.value === "all") {
    return listpengirimanData.value;
  }

  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return listpengirimanData.value.slice(start, end);
});

const totalPages = computed(() => {
  if (itemsPerPage.value === "all") return 1;
  return Math.ceil(listpengirimanData.value.length / itemsPerPage.value);
});

const paginatedPages = computed(() => {
  const total = totalPages.value;
  const current = currentPage.value;
  const pages = [];

  if (total <= 5) {
    for (let i = 1; i <= total; i++) pages.push(i);
  } else {
    if (current <= 3) pages.push(1, 2, 3, "...", total);
    else if (current >= total - 2) pages.push(1, "...", total - 2, total - 1, total);
    else pages.push(1, "...", current - 1, current, current + 1, "...", total);
  }
  return pages;
});

watch(currentPage, (val) => {
  if (val < 1) currentPage.value = 1;
  if (val > totalPages.value) currentPage.value = totalPages.value;
});

// 🔹 Format Tanggal
const formatDate = (date) => {
  return new Date(date).toLocaleDateString("id-ID", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

// 🔹 Update Status “Selesai”
const selesaiPackaging = async (id) => {
  const result = await Swal.fire({
    title: "Konfirmasi Selesai",
    text: `Anda yakin ingin selesaikan data ini?`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, Selesaikan!",
    cancelButtonText: "Batal",
    reverseButtons: true,
  });

  if (result.isConfirmed) {
    try {
      const token = sessionStorage.getItem("auth_token")
      const payload = { packaging_status: "Done" };
      const response = await axios.post(
        `${url.value}/api/packaging/update-status/${id}`,
        payload, {
        headers: {
          "Authorization": `Bearer ${token}`,
          "Content-Type": "application/json",
        }
      }
      );

      if (response.status === 200) {
        await fetchDataPengiriman();
        Swal.fire({
          title: "Berhasil",
          text: "Data berhasil diselesaikan",
          icon: "success",
        });
      }
    } catch (error) {
      console.error("Error:", error);
      Swal.fire({
        title: "Gagal",
        text: "Terjadi kesalahan saat menyelesaikan data.",
        icon: "error",
      });
    }
  }
};

// 🔹 Export Excel
// const exportToExcel = () => {
//   if (!listpengirimanData.value.length) {
//     Swal.fire({
//       title: "Tidak ada data",
//       text: "Data pengiriman kosong, tidak bisa diexport.",
//       icon: "info",
//     });
//     return;
//   }

//   const dataToExport = listpengirimanData.value.map((item, index) => ({
//     No: index + 1,
//     Tanggal: formatDate(item.created_at),
//     "Nama Akun": item.packaging_nama_akun,
//     Alamat: item.packaging_alamat,
//     Status: item.packaging_status,
//   }));

//   const worksheet = XLSX.utils.json_to_sheet(dataToExport);
//   const workbook = XLSX.utils.book_new();
//   XLSX.utils.book_append_sheet(workbook, worksheet, "Data Packaging");

//   const excelBuffer = XLSX.write(workbook, { bookType: "xlsx", type: "array" });
//   const blob = new Blob([excelBuffer], {
//     type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
//   });

//   const fileName = `Data_Packaging_${new Date()
//     .toISOString()
//     .split("T")[0]}.xlsx`;
//   saveAs(blob, fileName);
// };

async function buildRowsForPackaging(pkg) {
  try {
    // 1) Get packaging → ambil transaksi_id
    const token = sessionStorage.getItem("auth_token")
    const { data: pkgDetailResp } = await axios.get(`${url.value}/api/packaging/${pkg.packaging_id}`, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    });
    const transaksiId = pkgDetailResp?.data?.packaging_transactiondetail_id;
    if (!transaksiId) return [];

    const { data: trxResp } = await axios.get(`${url.value}/api/transaksi/${transaksiId}`, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    });

    const details = Array.isArray(trxResp?.data?.details) ? trxResp.data.details : [];

    const rows = [];
    for (const d of details) {
      const { data: ebResp } = await axios.get(`${url.value}/api/entrybarang/${d.transaksidetail_barang_id}`, {
        headers: {
          "Authorization": `Bearer ${token}`,
          "Content-Type": "application/json",
        }
      });
      const entry = ebResp?.data || {};

      let codeNama = "-";
      if (entry.barangentry_code_id) {
        const { data: codeResp } = await axios.get(`${url.value}/api/codebarang/${entry.barangentry_code_id}`, {
          headers: {
            "Authorization": `Bearer ${token}`,
            "Content-Type": "application/json",
          }
        });
        codeNama = codeResp?.code_nama ?? "-";
      }

      rows.push({
        tgl: new Date(pkg.created_at),
        nama: pkg.packaging_nama_akun ?? "-",
        alamat: pkg.packaging_alamat ?? "-",
        barang: codeNama,
        qty: Number(d.transaksidetail_jumlah_barang ?? 0),
        harga: Number(d.transaksidetail_harga_barang ?? 0),
      });
    }

    return rows;
  } catch (e) {
    console.error("Gagal bangun baris untuk packaging:", pkg.packaging_id, e);
    return [];
  }
}

const exportToExcel = async () => {
  try {
    const source = [...listpengirimanData.value];
    if (!source.length) {
      Swal.fire({
        title: "Tidak ada data",
        text: "Data pengiriman kosong, tidak bisa diexport.",
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

    const allRows = [];
    for (const pkg of source) {
      const rows = await buildRowsForPackaging(pkg);
      allRows.push(...rows);
    }

    if (!allRows.length) {
      Swal.close();
      Swal.fire({
        title: "Tidak ada detail",
        text: "Tidak ditemukan transaksi-detail untuk data terpilih.",
        icon: "info",
      });
      return;
    }

    allRows.sort((a, b) => {
      const kA = `${a.nama}|||${a.alamat}`.toLowerCase();
      const kB = `${b.nama}|||${b.alamat}`.toLowerCase();
      if (kA !== kB) return kA < kB ? -1 : 1;
      return a.tgl - b.tgl;
    });

    const dataToExport = allRows.map((r, i) => ({
      "Tgl": r.tgl,
      "Nama": r.nama,
      "Alamat": r.alamat,
      "Kode Barang": r.barang,
      "Qty": r.qty,
      "Harga": r.harga,
    }));

    const worksheet = XLSX.utils.json_to_sheet(dataToExport, { cellDates: true, dateNF: "dd-mmm-yyyy" });

    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Packaging");
    const merges = [];

    let start = 0;
    while (start < dataToExport.length) {
      const key = `${dataToExport[start]["Nama"]}|||${dataToExport[start]["Alamat"]}`;
      let end = start;
      while (
        end + 1 < dataToExport.length &&
        `${dataToExport[end + 1]["Nama"]}|||${dataToExport[end + 1]["Alamat"]}` === key
      ) {
        end++;
      }

      if (end > start) {
        const wsStartRow = start + 1;
        const wsEndRow = end + 1;

        merges.push({ s: { r: wsStartRow, c: 1 }, e: { r: wsEndRow, c: 1 } });
        merges.push({ s: { r: wsStartRow, c: 2 }, e: { r: wsEndRow, c: 2 } });
      }

      start = end + 1;
    }
    worksheet["!merges"] = merges;

    worksheet["!cols"] = [
      { wch: 12 },
      { wch: 24 },
      { wch: 36 },
      { wch: 12 },
      { wch: 6 },
      { wch: 12 },
    ];

    const excelBuffer = XLSX.write(workbook, { bookType: "xlsx", type: "array" });
    const blob = new Blob([excelBuffer], {
      type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    });
    const fileName = `Packaging_Detail_${new Date().toISOString().split("T")[0]}.xlsx`;

    saveAs(blob, fileName);
    Swal.close();
    Swal.fire({
      title: "Berhasil",
      text: "Export Excel selesai.",
      icon: "success",
      timer: 1500,
      showConfirmButton: false,
    });
  } catch (err) {
    console.error("Export Excel error:", err);
    Swal.close();
    Swal.fire({
      title: "Gagal",
      text: "Terjadi kesalahan saat export.",
      icon: "error",
    });
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
  text-align: left;
  font-size: 12px;
}

.datatable th {
  background-color: #f4f4f4;
}

.search-box::placeholder {
  color: #888;
}

.btn-selesai {
  background-color: #26d04b;
  color: white;
  border-radius: 5px;
  cursor: pointer;
  font-size: 12px;
}

.btn-selesai:hover {
  background-color: #6bdd84;
}
</style>
