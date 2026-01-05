<template>
  <div>
    <title>Menu Database Penjualan</title>
    <div class="judul text-xl font-semibold mb-4">Database Penjualan</div>
    <input v-model="searchQuery" type="text" class="search-box mb-4 rounded-md"
      placeholder="Cari transaksi penjualan..." />

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
        <template v-for="(group, gIndex) in paginatedGroups" :key="gIndex">
          <tr v-for="(item, i) in group.details" :key="item.transaksidetail_id"
            :class="i % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
            <td v-if="i === 0" :rowspan="group.details.length" class="px-4 py-2 align-top font-semibold">
              {{ formatDate(group.tanggal) }}
            </td>
            <td v-if="i === 0" :rowspan="group.details.length" class="px-4 py-2 align-top font-semibold">
              {{ group.nama }}
            </td>
            <td v-if="i === 0" :rowspan="group.details.length" class="px-4 py-2 align-top">
              {{ group.jenis }}
            </td>
            <td v-if="i === 0" :rowspan="group.details.length" class="px-4 py-2 align-top">
              {{ group.acara || '-' }}
            </td>
            <td v-if="i === 0" :rowspan="group.details.length" class="px-4 py-2 align-top">
              {{ group.platform || '-' }}
            </td>
            <td class="px-4 py-2">{{ item.kode_barang || '-' }}</td>
            <td class="px-4 py-2">{{ item.nama_barang || '-' }}</td>
            <td class="px-4 py-2">{{ item.transaksidetail_jumlah_barang }}</td>
            <td class="px-4 py-2">
              {{ formatCurrency(item.transaksidetail_harga_barang) }}
            </td>
            <td class="px-4 py-2">
              {{ formatCurrency(item.subtotal) }}
            </td>
            <td>
              <span class="text-status px-2 py-1"
                :class="statusChipClass(item.transaksidetail_status_penjualan)">
                {{ item.transaksidetail_status_penjualan == 1 ? 'Closed' : 'Open' }}
              </span>
            </td>
            <td class="px-4 py-2">
              {{ group.cara_bayar }}
            </td>
            <td v-if="i === 0" :rowspan="group.details.length" class="px-4 py-2 align-top">
              {{ group.catatan || '-' }}
            </td>
            <td class="px-4 py-2">
              <div class="flex space-x-2">
                <button class="px-2 py-1 bg-yellow-500 text-white rounded"
                  @click="openViewDetail(item.transaksidetail_id)">
                  View
                </button>
                <button class="px-2 py-1 bg-green-500 text-white rounded" @click="handlePrint(item.transaksidetail_id)">
                  Print
                </button>
                <button class="px-2 py-1 bg-red-500 text-white rounded"
                  @click="deleteTransaksi(item.transaksidetail_id)">
                  Delete
                </button>
              </div>
            </td>
          </tr>
        </template>
      </tbody>
    </table>

    <div class="flex justify-between items-center mt-8 mb-4 text-xs">
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
        <button class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400" :disabled="currentPage === 1"
          @click="currentPage--">
          Sebelumnya
        </button>

        <button v-for="(page, index) in paginatedPages" :key="index"
          @click="typeof page === 'number' && (currentPage = page)" :class="[
            'px-3 py-1 rounded',
            currentPage === page ? 'bg-blue-500 text-white' : 'bg-gray-200',
            page === '...' ? 'cursor-default' : 'cursor-pointer'
          ]" :disabled="page === '...'">
          {{ page }}
        </button>

        <button class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400" :disabled="currentPage === totalPages"
          @click="currentPage++">
          Selanjutnya
        </button>
      </div>
    </div>

    <ViewDetailModal :show="showDetailModal" :id="trx_id" @close="showDetailModal = false" />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
import axios from "axios";
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";
import ViewDetailModal from "../components/ModalViewDetail.vue";

const config = useRuntimeConfig();
const url = ref(config.public.apiBase);
const transaksi = ref([]);
const searchQuery = ref("");
const showDetailModal = ref(false);
const trx_id = ref(null);
const currentPage = ref(1);
const itemsPerPage = ref(10);

onMounted(() => {
  fetchTransaksi();
});

const fetchTransaksi = async () => {
  const token = sessionStorage.getItem("auth_token");
  const res = await axios.get(`${url.value}/api/transaksi`, {
    headers: { Authorization: `Bearer ${token}` },
  });

  transaksi.value = await Promise.all(
    res.data.data.map(async (trx) => {
      const details = await Promise.all(
        trx.details.map(async (d) => {
          const token = sessionStorage.getItem("auth_token");

          const barang = await axios.get(
            `${url.value}/api/entrybarang/${d.transaksidetail_barang_id}`,
            { headers: { Authorization: `Bearer ${token}` } }
          );

          const kode = await axios.get(
            `${url.value}/api/codebarang/${barang.data.data.barangentry_code_id}`,
            { headers: { Authorization: `Bearer ${token}` } }
          );

          return {
            ...d,
            nama_barang: barang.data.data.barangentry_nama,
            kode_barang: kode.data.code_nama,
            subtotal:
              d.transaksidetail_harga_barang *
              d.transaksidetail_jumlah_barang,
          };
        })
      );
      return { ...trx, details };
    })
  );
};

const groupedTransaksi = computed(() => {
  const groups = {};

  transaksi.value.forEach(trx => {
    const dateOnly = new Date(trx.created_at).toISOString().split("T")[0];
    const key = [
      dateOnly,
      trx.transaksi_nama_customer || "",
      trx.transaksi_tipe || "",
      trx.transaksi_acara || "",
      trx.transaksi_platform || ""
    ].join("__");

    if (!groups[key]) {
      groups[key] = {
        tanggal: dateOnly,
        nama: trx.transaksi_nama_customer,
        jenis: trx.transaksi_tipe,
        acara: trx.transaksi_acara || "-",
        platform: trx.transaksi_platform || "-",
        status: trx.transaksi_status,
        cara_bayar: trx.transaksi_cara_bayar,
        catatan: trx.transaksi_catatan,
        id: trx.transaksi_id,
        details: []
      };
    }
    groups[key].details.push(...trx.details);
  });

  return Object.values(groups);
});

const paginatedGroups = computed(() => {
  if (itemsPerPage.value === "all") return groupedTransaksi.value;
  const start = (currentPage.value - 1) * itemsPerPage.value;
  return groupedTransaksi.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => {
  if (itemsPerPage.value === "all") return 1;
  return Math.ceil(groupedTransaksi.value.length / itemsPerPage.value);
});

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
        const token = sessionStorage.getItem("auth_token")
        const response = await axios.delete(`${url.value}/api/transaksi/${id}`, {
          headers: {
            "Authorization": `Bearer ${token}`,
            "Content-Type": "application/json",
          }
        });

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

const statusChipClass = (status) => {
  switch (status) {
    case 1:
      return "bg-green-100 text-green-700 border border-green-300 rounded";

    default:
      return "bg-yellow-100 text-yellow-700 border border-yellow-300 rounded"
  }
};

async function handlePrint(transaksi_id) {
  const token = sessionStorage.getItem("auth_token")
  const { data: responsePrint } = await axios.get(
    `${url.value}/api/transaksi/${transaksi_id}`, {
    headers: {
      "Authorization": `Bearer ${token}`,
      "Content-Type": "application/json",
    }
  }
  );

  const transaksi = responsePrint.data;
  const detailWithNames = await Promise.all(
    transaksi.details.map(async (detail) => {
      const barangRes = await axios.get(
        `${url.value}/api/entrybarang/${detail.transaksidetail_barang_id}`, {
        headers: {
          "Authorization": `Bearer ${token}`,
          "Content-Type": "application/json",
        }
      }
      );
      const kodeBarang = await axios.get(
        `${url.value}/api/codebarang/` + barangRes.data.data.barangentry_code_id, {
        headers: {
          "Authorization": `Bearer ${token}`,
          "Content-Type": "application/json",
        }
      }
      );

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

.text-status{
  font-size: 10px !important;
}
</style>
