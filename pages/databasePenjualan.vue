<template>
  <div>
    <title>Menu Database Penjualan</title>
    <div class="judul text-xl font-semibold mb-4">Database Penjualan</div>
    <input v-model="searchQuery" type="text" class="search-box mb-4" placeholder="Cari transaksi
      ..." />

    <table class="datatable w-full rounded-md overflow-hidden">
      <thead class="bg-blue-100">
        <tr>
          <th class="px-4 py-2 text-left">No.</th>
          <th class="px-4 py-2 text-left">Nama Customer</th>
          <th class="px-4 py-2 text-left">No. Telepon</th>
          <th class="px-4 py-2 text-left">Jumlah Barang</th>
          <th class="px-4 py-2 text-left">Total Harga</th>
          <th class="px-4 py-2 text-left">Cara Bayar</th>
          <th class="px-4 py-2 text-left">Tipe</th>
          <th class="px-4 py-2 text-left">Status</th>
          <th class="px-4 py-2 text-left">Catatan</th>
          <th class="px-4 py-2 text-left">Tanggal</th>
          <th class="px-4 py-2 text-left">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <template v-for="(group, gIndex) in groupedTransaksi" :key="gIndex">
          <tr v-for="(trx, tIndex) in group.items" :key="trx.transaksi_id"
            :class="tIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
            <td v-if="tIndex === 0" :rowspan="group.items.length" class="px-4 py-2 align-top text-center">
              {{ gIndex + 1 }}
            </td>
            <td v-if="tIndex === 0" :rowspan="group.items.length" class="px-4 py-2 align-top font-semibold">
              {{ trx.transaksi_nama_customer }}
            </td>
            <!-- <td
              v-if="tIndex === 0"
              :rowspan="group.items.length"
              class="px-4 py-2 align-top"
            >
              {{ group.telepon }}
            </td> -->
            <td class="px-4 py-2">{{ trx.transaksi_nomor_telepon }}</td>
            <td class="px-4 py-2">{{ trx.transaksi_jumlah_barang }}</td>
            <td class="px-4 py-2">
              {{ formatCurrency(trx.transaksi_total_harga) }}
            </td>
            <td class="px-4 py-2">{{ trx.transaksi_cara_bayar }}</td>
            <td class="px-4 py-2">{{ trx.transaksi_tipe }}</td>
            <td class="px-4 py-2">{{ trx.transaksi_status }}</td>
            <td class="px-4 py-2">{{ trx.transaksi_catatan }}</td>
            <td class="px-4 py-2">{{ formatDate(trx.created_at) }}</td>
            <td class="px-4 py-2">
              <div class="flex space-x-2">
                <button
                  class="flex items-center gap-1 px-2 py-1 bg-[#FBBF24] text-white hover:bg-[#FFD15A] rounded-md text-s"
                  @click="openViewDetail(trx.transaksi_id)">
                  View
                </button>
                <button
                  class="flex items-center gap-1 px-2 py-2 bg-green-500 text-white hover:bg-green-600 rounded-[10px] text-sm"
                  @click="handlePrint(trx.transaksi_id)">
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
        </template>
      </tbody>
    </table>
    <div class="flex justify-between items-center mt-4">
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

    <ViewDetailModal :show="showDetailModal" :id="trx_id" @close="showDetailModal = false" />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
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

const openViewDetail = (id) => {
  trx_id.value = id;
  showDetailModal.value = true;
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

// nomor telepon saja
const groupedTransaksi = computed(() => {
  const groups = [];
  const map = {};

  const sortedList = [...listTransaksi.value].sort(
    (a, b) => new Date(b.created_at) - new Date(a.created_at)
  );

  sortedList.forEach((trx) => {
    if (!map[trx.transaksi_nama_customer]) {
      map[trx.transaksi_nama_customer] = {
        nama: trx.transaksi_nama_customer,
        items: [],
      };
      groups.push(map[trx.transaksi_nama_customer]);
    }
    map[trx.transaksi_nama_customer].items.push(trx);
  });

  return groups;
});

// nama customer dan nomor telepon merge
// const groupedTransaksi = computed(() => {
//   const groups = {};
//   transaksi.value.forEach((trx) => {
//     const key = `${trx.transaksi_nama_customer}-${trx.transaksi_nomor_telepon}`;
//     if (!groups[key]) {
//       groups[key] = {
//         nama: trx.transaksi_nama_customer,
//         telepon: trx.transaksi_nomor_telepon,
//         items: [],
//       };
//     }
//     groups[key].items.push(trx);
//   });
//   return Object.values(groups);
// });

// const pagination = computed(() => {
//   const start = (currentPage.value - 1) * itemsPerPage.value;
//   const end = start + itemsPerPage.value;
//   return listTransaksi.value.slice(start, end);
// });

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
        const response = await axios.delete(`${url.value}/api/transaksi/${id}`);

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

async function handlePrint(transaksi_id) {
  const { data: responsePrint } = await axios.get(
    `${url.value}/api/transaksi/${transaksi_id}`
  );

  const transaksi = responsePrint.data;
  const detailWithNames = await Promise.all(
    transaksi.details.map(async (detail) => {
      const barangRes = await axios.get(
        `${url.value}/api/entrybarang/${detail.transaksidetail_barang_id}`
      );
      const kodeBarang = await axios.get(
        `${url.value}/api/codebarang/` + barangRes.data.data.barangentry_code_id
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
</style>
