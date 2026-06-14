<template>
  <div>
    <title>Menu Database Penjualan</title>
    <div class="judul text-xl font-semibold mb-4">
      Database Penjualan
    </div>
    <input v-model="searchQuery" type="text" class="search-box mb-4 rounded-md"
      placeholder="Cari transaksi penjualan..." />

    <div class="flex items-center gap-3 mb-4">
      <input type="date" v-model="startDate" class="border rounded px-2 py-1 text-sm" />

      <input type="date" v-model="endDate" class="border rounded px-2 py-1 text-sm" />

      <button class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700 text-sm" @click="exportExcel">
        Export Excel
      </button>
    </div>

    <table class="datatable w-full rounded-md overflow-hidden">
      <thead class="bg-blue-100">
        <tr>
          <th class="px-4 py-2 text-left">Tanggal Transaksi</th>
          <th class="px-4 py-2 text-left">Nama Akun</th>
          <th class="px-4 py-2 text-left">Jenis Transaksi</th>
          <th class="px-4 py-2 text-left">Acara</th>
          <th class="px-4 py-2 text-left">Platform</th>
          <th class="px-4 py-2 text-left">Kode Barang</th>
          <th class="px-4 py-2 text-left">Nama Ulos</th>
          <th class="px-4 py-2 text-left">Jumlah</th>
          <th class="px-4 py-2 text-left">Harga</th>
          <th class="px-4 py-2 text-left">Subtotal</th>
          <th class="px-4 py-2 text-left">Status</th>
          <th class="px-4 py-2 text-left">Pembayaran</th>
          <th class="px-4 py-2 text-left">Catatan</th>
          <th class="px-4 py-2 text-left">Aksi</th>
        </tr>
      </thead>

      <tbody>
        <template v-for="(trx, trxIndex) in paginatedTransaksi" :key="trx.transaksi_id">
          <tr v-for="(item, i) in trx.items" :key="item.transaksidetail_id" :class="[
            trxIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50',
            i === 0 ? 'group-start' : ''
          ]">
            <td v-if="i === 0" :rowspan="trx.items.length" class="text-left align-top px-2 py-1">
              {{ formatDate(trx.created_at) }}
            </td>

            <td v-if="i === 0" :rowspan="trx.items.length" class="text-left align-top px-2 py-1">
              {{ trx.customer_nama }}
            </td>

            <td v-if="i === 0" :rowspan="trx.items.length" class="text-left align-top px-2 py-1">
              {{ trx.transaksi_tipe }}
            </td>

            <td v-if="i === 0" :rowspan="trx.items.length" class="text-left align-top px-2 py-1">
              {{ trx.transaksi_acara || "-" }}
            </td>

            <td class="text-left align-top px-2 py-1">
              {{ trx.transaksi_tipe === 'Offline' ? "Offline" : item.transaksi_platform }}
            </td>
            <td>{{ item.code_nama || "-" }}</td>
            <td>{{ item.barang_nama || "-" }}</td>
            <td>{{ item.jumlah_barang }}</td>
            <td>{{ formatCurrency(Number(item.harga_barang)) }}</td>
            <td>{{ formatCurrency(Number(item.harga_barang) * item.jumlah_barang) }}</td>

            <td>
              <span class="text-status px-2 py-1" :class="statusChipClass(item.transaksi_status)">
                {{ item.transaksi_status === 1 ? "Closed" : "Open" }}
              </span>
            </td>

            <td>{{ trx.cara_bayar }}</td>

            <td v-if="i === 0" :rowspan="trx.items.length">
              {{ trx.catatan || "-" }}
            </td>

            <td>
              <div class="flex space-x-2">
                <button class="px-2 py-1 bg-yellow-500 text-white rounded" @click="openViewDetail(trx.transaksi_id)">
                  View
                </button>

                <button class="px-2 py-1 bg-green-500 text-white rounded" @click="handlePrint(trx.transaksi_id)">
                  Print
                </button>

                <button class="px-2 py-1 bg-red-500 text-white rounded" @click="deleteTransaksi(trx.transaksidetail_id)">
                  Delete
                </button>
              </div>
            </td>
          </tr>
        </template>
      </tbody>
    </table>

    <div class="flex justify-between items-center mt-8 mb-4 text-xs">
      <div class="flex items-center space-x-2 text-xs mb-4">
        <div>
          Menampilkan {{ startItem }}–{{ endItem }} dari {{ totalTransaksi }} transaksi
        </div>
        <span>| Tampilkan</span>

        <select v-model="itemsPerPage" class="border rounded px-2 py-1">
          <option :value="10">10</option>
          <option :value="20">20</option>
          <option :value="50">50</option>
          <option value="all">All</option>
        </select>

        <span>data</span>
      </div>

      <div class="pagination">
        <button class="nav-btn" :disabled="currentPage === 1" @click="currentPage--">
          Sebelumnya
        </button>

        <button v-for="p in paginatedPages" :key="p" class="page-btn"
          :class="{ active: p === currentPage, dots: p === '...' }" :disabled="p === '...'"
          @click="typeof p === 'number' && (currentPage = p)">
          {{ p }}
        </button>

        <button class="nav-btn" :disabled="currentPage === totalPages" @click="currentPage++">
          Selanjutnya
        </button>
      </div>
    </div>

    <ViewDetailModal :show="showDetailModal" :id="trx_id" @close="showDetailModal = false" />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";
import ViewDetailModal from "../components/ModalViewDetail.vue";
import * as XLSX from "xlsx";
import { saveAs } from "file-saver";
const today = new Date().toISOString().slice(0, 10);
const startDate = ref(today);
const endDate = ref(today);

const config = useRuntimeConfig();
const url = ref(config.public.apiBase);
const transaksi = ref([]);
const searchQuery = ref("");
const showDetailModal = ref(false);
const trx_id = ref(null);
const currentPage = ref(1);
const itemsPerPage = ref(10);
const { $api } = useNuxtApp();

onMounted(() => {
  fetchTransaksi();
});

const fetchTransaksi = async () => {
  try {
    const res = await $api.get(`${url.value}/api/transaksi/grouped`);
    transaksi.value = res.data.data;
  } catch (e) {
    Swal.fire("Gagal", "Tidak dapat memuat data transaksi", "error");
  }
};

const filteredTransaksi = computed(() => {
  if (!searchQuery.value) return groupedTransaksi.value;

  const q = searchQuery.value.toLowerCase();

  return groupedTransaksi.value.filter(trx =>
    trx.customer_nama.toLowerCase().includes(q) ||
    trx.items.some(i =>
      (i.code_nama || "").toLowerCase().includes(q)
    )
  );
});

const formatDateKey = (dateStr) => {
  const d = new Date(dateStr);
  const dd = String(d.getDate()).padStart(2, "0");
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const yyyy = d.getFullYear();
  return `${dd}-${mm}-${yyyy}`;
};


const sortedTransaksi = computed(() => {
  return [...transaksi.value].sort(
    (a, b) => new Date(b.created_at) - new Date(a.created_at)
  );
});


const groupedTransaksi = computed(() => {
  const groups = {};

  sortedTransaksi.value.forEach(trx => {
    const dateKey = formatDateKey(trx.created_at);
    const key = `${dateKey}__${trx.customer_nama}__${trx.cara_bayar}`;

    if (!groups[key]) {
      groups[key] = {
        created_at: trx.created_at,
        customer_nama: trx.customer_nama,
        transaksi_tipe: trx.transaksi_tipe,
        transaksi_acara: trx.transaksi_acara,
        cara_bayar: trx.cara_bayar,
        catatan: trx.catatan,
        transaksi_id: trx.transaksi_id,
        items: []
      };
    }

    trx.items.forEach(item => {
      const harga = Number(item.barang_price_tag || 0);
      const jumlah = 1;

      groups[key].items.push({
        ...item,
        harga,
        jumlah,
        subtotal: harga * jumlah
      });
    });
  });

  return Object.values(groups);
});

const paginatedTransaksi = computed(() => {
  if (itemsPerPage.value === "all") return filteredTransaksi.value;

  const start = (currentPage.value - 1) * itemsPerPage.value;
  return filteredTransaksi.value.slice(start, start + itemsPerPage.value);
});

const totalTransaksi = computed(() => filteredTransaksi.value.length);

const startItem = computed(() => {
  if (itemsPerPage.value === "all") return 1;
  if (totalTransaksi.value === 0) return 0;
  return (currentPage.value - 1) * Number(itemsPerPage.value) + 1;
});

const endItem = computed(() => {
  if (itemsPerPage.value === "all") return totalTransaksi.value;
  return Math.min(
    currentPage.value * Number(itemsPerPage.value),
    totalTransaksi.value
  );
});

const totalPages = computed(() =>
  itemsPerPage.value === "all"
    ? 1
    : Math.ceil(totalTransaksi.value / itemsPerPage.value)
);

watch(searchQuery, () => {
  currentPage.value = 1;
});

const formatCurrency = (v) =>
  new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(v);

const formatDate = (d) =>
  new Date(d).toLocaleDateString("id-ID", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });

const openViewDetail = (id) => {
  trx_id.value = id;
  showDetailModal.value = true;
};

const deleteTransaksi = async (id) => {
  Swal.fire({
    title: "Apakah Anda yakin?",
    text: "Data transaksi yang dihapus tidak dapat dikembalikan!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Ya, hapus!",
    cancelButtonText: "Batal",
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const response = await $api.delete(`${url.value}/api/transaksi-detail/${id}`);

        if (response.status === 200) {
          transaksi.value = transaksi.value.filter(
            (item) => item.transaksi_id !== id
          );

          Swal.fire({
            title: "Terhapus!",
            text: "Data transaksi berhasil dihapus.",
            icon: "success",
            timer: 2000,
            showConfirmButton: false,
          });
        }
      } catch (error) {
        console.error("Gagal menghapus transaksi:", error);
        Swal.fire({
          title: "Gagal!",
          text: "Terjadi kesalahan saat menghapus data.",
          icon: "error",
          confirmButtonText: "OK",
        });
      }
    }
  });
};

const exportExcel = async () => {
  try {
    const res = await $api.get(
      `${url.value}/api/transaksi/report`,
      {
        params: {
          start_date: startDate.value,
          end_date: endDate.value,
        },
      }
    );

    if (!res.data.success || !res.data.data.length) {
      Swal.fire("Info", "Data tidak tersedia", "info");
      return;
    }

    const rows = res.data.data;

    const excelData = rows.map((r, i) => ({
      No: i + 1,
      "Tanggal Transaksi": r.created_at,
      "Nama Customer": r.customer_nama,
      "Jenis Transaksi": r.transaksi_tipe,
      Platform: r.transaksi_platform || "-",
      "Kode Barang": r.code_nama,
      "Nama Barang": r.barangentry_nama,
      "Harga Modal": Number(r.barangentry_modal),
      "Price Tag": Number(r.barangentry_price_tag),
      "Harga Net": Number(r.barangentry_harga_net),
      Jumlah: Number(r.transaksidetail_jumlah_barang),
      "Harga Jual": Number(r.transaksidetail_harga_barang),
      Subtotal: Number(r.subtotal),
      Pembayaran: r.carabayar_nama,
      Catatan: r.transaksi_catatan || "-",
    }));

    const ws = XLSX.utils.json_to_sheet(excelData);
    const headerCells = Object.keys(excelData[0]).length;
    for (let C = 0; C < headerCells; C++) {
      const cell = XLSX.utils.encode_cell({ r: 0, c: C });
      ws[cell].s = { font: { bold: true } };
    }

    const rupiahFormat = '"Rp" #,##0';
    const range = XLSX.utils.decode_range(ws["!ref"]);

    const COL_RUPIAH = [6, 7, 8, 10, 11];

    for (let R = range.s.r + 1; R <= range.e.r; ++R) {
      COL_RUPIAH.forEach((C) => {
        const cell = XLSX.utils.encode_cell({ r: R, c: C });
        if (ws[cell]) {
          ws[cell].t = "n";
          ws[cell].z = rupiahFormat;
        }
      });
    }

    const totalRow = range.e.r + 1;
    ws[`A${totalRow + 1}`] = { v: "TOTAL", t: "s" };
    ws[`L${totalRow + 1}`] = {
      f: `SUM(L2:L${totalRow})`,
      t: "n",
      z: rupiahFormat,
    };

    ws["!freeze"] = { xSplit: 0, ySplit: 1 };

    ws["!cols"] = [
      { wch: 5 },
      { wch: 18 },
      { wch: 20 },
      { wch: 15 },
      { wch: 10 },
      { wch: 15 },
      { wch: 15 },
      { wch: 16 },
      { wch: 16 },
      { wch: 16 },
      { wch: 10 },
      { wch: 16 },
      { wch: 16 },
      { wch: 18 },
      { wch: 25 },
    ];

    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Report Penjualan");

    const buffer = XLSX.write(wb, {
      bookType: "xlsx",
      type: "array",
      cellStyles: true,
    });

    saveAs(
      new Blob([buffer], {
        type:
          "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
      }),
      `Report_Penjualan_${startDate.value}_${endDate.value}.xlsx`
    );
    Swal.fire({
      icon: "success",
      title: "Berhasil",
      text: "File Excel berhasil diunduh",
      timer: 1500,
      showConfirmButton: false,
    });
  } catch (err) {
    console.error(err);
    Swal.fire("Error", "Gagal export Excel", "error");
  }
};

const statusChipClass = (status) => {
  return status === 1
    ? "bg-green-100 text-green-700 border border-green-300 rounded"
    : "bg-yellow-100 text-yellow-700 border border-yellow-300 rounded";
};

async function handlePrint(transaksi_id) {
  const { data: responsePrint } = await $api.get(
    `${url.value}/api/transaksi/${transaksi_id}`);

  const transaksi = responsePrint.data;
  const detailWithNames = await Promise.all(
    transaksi.details.map(async (detail) => {
      const barangRes = await $api.get(
        `${url.value}/api/entrybarang/${detail.transaksidetail_barang_id}`);
      const kodeBarang = await $api.get(
        `${url.value}/api/codebarang/` + barangRes.data.data.barangentry_code_id);

      return {
        ...detail,
        barangentry_nama:
          barangRes.data.data.barangentry_nama || "Tidak Diketahui",
        barangentry_code: kodeBarang.data.code_nama,
      };
    })
  );

  printToNewTab(transaksi, detailWithNames);
}

function printToNewTab(data, items) {
  const printWindow = window.open("", "_blank");
  if (!printWindow) {
    alert("Pop-up blocker menghalangi membuka tab baru.");
    return;
  }

  const formatTanggal = (dateStr) => {
    if (!dateStr) return "-";
    const date = new Date(dateStr);
    return date.toLocaleString("id-ID", {
      day: "2-digit",
      month: "long",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  };

  const formatRupiah = (value) => {
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(value || 0);
  };

  const htmlContent = `
  <!DOCTYPE html>
  <html lang="id">
  <head>
    <meta charset="UTF-8" />
    <title>Invoice Transaksi</title>
    <style>
      body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 40px;
        background: #fff;
        color: #000;
      }

      .print-area {
        max-width: 800px;
        margin: auto;
        box-sizing: border-box;
      }

      .header {
        text-align: center;
        margin-bottom: 20px;
      }

      .logo {
        width: 100%;
        max-width: 800px;
      }

      .info {
        margin-top: 10px;
        font-size: 14px;
      }

      .info-row {
        display: flex;
        margin-bottom: 4px;
      }

      .info-label {
        width: 180px;
        font-weight: normal;
      }

      .info-separator {
        width: 10px;
      }

      .info-value {
        font-weight: bold;
        flex: 1;
      }

      h3 {
        margin-top: 30px;
        font-size: 15px;
      }

      table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        margin-top: 12px;
        border: 1px solid #eee;
        border-radius: 8px;
        overflow: hidden;
      }

      thead {
        background-color: #f9f9f9;
        font-weight: bold;
      }

      th, td {
        padding: 8px;
        text-align: left;
      }

      th:nth-child(5),
      th:nth-child(6),
      td:nth-child(5),
      td:nth-child(6) {
        text-align: right;
      }

      tbody tr:not(:last-child) {
        border-bottom: 1px dashed #ccc;
      }

      .total-row td {
        font-weight: bold;
        text-align: right;
        padding-top: 12px;
        border-top: 1px solid #ddd;
      }

      .footer {
        text-align: center;
        margin-top: 40px;
        font-size: 13px;
        color: #555;
      }

      @media print {
        body {
          margin: 0;
          padding: 0;
        }
      }
    </style>
  </head>
  <body>
    <div class="print-area">
      <div class="header">
        <img src="/image/DameUlosHeaderPrint.jpg" alt="Logo" class="logo" />
      </div>

      <div class="info">
        <div class="info-row">
          <div class="info-label">Nama Customer</div>
          <div class="info-separator">:</div>
          <div class="info-value">${data.transaksi_nama_customer || "-"}</div>
        </div>
        <div class="info-row">
          <div class="info-label">No Telepon</div>
          <div class="info-separator">:</div>
          <div class="info-value">${data.transaksi_nomor_telepon || "-"}</div>
        </div>
        <div class="info-row">
          <div class="info-label">Tanggal Pemesanan</div>
          <div class="info-separator">:</div>
          <div class="info-value">${formatTanggal(data.created_at)}</div>
        </div>
        <div class="info-row">
          <div class="info-label">Metode Pembayaran</div>
          <div class="info-separator">:</div>
          <div class="info-value">${data.transaksi_cara_bayar || "-"}</div>
        </div>
      </div>

      <h3>Rincian Pembelian</h3>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Jumlah</th>
            <th>Harga</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
          ${items
      .map(
        (item, index) => `
                <tr>
                  <td>${index + 1}</td>
                  <td>${item.barangentry_code || "-"}</td>
                  <td>${item.barangentry_nama || "-"}</td>
                  <td>${item.transaksidetail_jumlah_barang} pcs</td>
                  <td>${formatRupiah(item.transaksidetail_harga_barang)}</td>
                  <td>${formatRupiah(
          item.transaksidetail_jumlah_barang *
          item.transaksidetail_harga_barang
        )}</td>
                </tr>
              `
      )
      .join("")}
          <tr class="total-row">
            <td colspan="5">Total :</td>
            <td>${formatRupiah(data.transaksi_total_harga)}</td>
          </tr>
        </tbody>
      </table>

      <div class="footer">
        <p>Terima kasih telah menjadi bagian dari pelanggan kami.</p>
        <p>Selamat menggunakan produk Anda!</p>
      </div>
    </div>

    <script>
      window.onload = function () {
        window.print();
      };
    <\/script>
  </body>
  </html>
  `;

  printWindow.document.write(htmlContent);
  printWindow.document.close();
}

const paginatedPages = computed(() => {
  if (itemsPerPage.value === "all") return [1];

  const total = totalPages.value;
  const current = currentPage.value;

  if (total <= 5) return Array.from({ length: total }, (_, i) => i + 1);
  if (current <= 3) return [1, 2, 3, "...", total];
  if (current >= total - 2) return [1, "...", total - 2, total - 1, total];
  return [1, "...", current - 1, current, current + 1, "...", total];
});

watch(itemsPerPage, () => {
  currentPage.value = 1;
});

watch(itemsPerPage, () => {
  currentPage.value = 1;
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

.datatable tbody tr:first-child td {
  border-top: 1px solid #e5e7eb;
}

.group-start td {
  border-top: 2px solid #d1d5db;
}

.datatable th {
  background-color: #f4f4f4;
}

.search-box::placeholder {
  color: #888;
}

.text-status {
  font-size: 10px !important;
}

.pagination {
  display: flex;
  align-items: center;
  gap: 6px;
}

.page-btn,
.nav-btn {
  min-width: 32px;
  height: 32px;
  padding: 0 10px;
  border-radius: 6px;
  border: 1px solid #d1d5db;
  background: #fff;
  font-size: 12px;
  cursor: pointer;
}

.page-btn:hover,
.nav-btn:hover {
  background: #f3f4f6;
}

.page-btn.active {
  background: #2563eb;
  color: #fff;
  border-color: #2563eb;
  font-weight: 600;
}

.page-btn.dots {
  cursor: default;
  border: none;
  background: transparent;
}

.page-btn:disabled,
.nav-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
