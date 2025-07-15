<template>
  <div class="judul text-xl font-semibold mb-4">Menu Kasir</div>

  <div class="flex items-start justify-between pt-2">
    <div class="flex flex-col flex-1 space-y-2">
      <input
        class="search-box p-2 border rounded-md"
        v-model="searchQueryCustomer"
        type="text"
        placeholder="Nama Customer"
      />
      <input
        class="search-box p-2 border rounded-md"
        v-model="searchQueryPhone"
        type="text"
        placeholder="Nomor Telepon"
      />
    </div>

    <div class="flex space-x-2 ml-4">
      <button
        class="bg-[#3D8BFD] text-white rounded-md hover:bg-[#2272E7] w-[104px] h-[34px]"
        @click="openModalLive = true"
      >
        Live
      </button>
      <button
        class="bg-[#F97316] text-white rounded-md hover:bg-[#F36E12] w-[104px] h-[34px]"
        @click="openModalPreOrder = true"
      >
        Pre-Order
      </button>
      <button
        class="bg-[#FACC15] text-white rounded-md hover:bg-[#F4C405] w-[132.97px] h-[34px]"
        @click="
          () => {
            openModalHold = true;
            fetchHoldTransactions();
          }
        "
      >
        Pending List
      </button>
      <button
        class="bg-[#404040] text-white rounded-md hover:bg-[#363535] w-[104px] h-[34px]"
        @click="handleHold"
      >
        Hold
      </button>
      <button
        class="bg-[#22C55E] text-white rounded-md hover:bg-[#21B156] w-[104px] h-[34px]"
        @click="openModalProcess = true"
      >
        Checkout
      </button>
    </div>
  </div>

  <div>
    <table class="datatable">
      <thead>
        <tr>
          <th>No</th>
          <th>Nama Item</th>
          <th>Jumlah</th>
          <th>Harga</th>
          <th class="hidden">code</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item, index) in datatableItems" :key="index">
          <td>{{ index + 1 }}</td>
          <td>{{ item.barangentry_nama }}</td>
          <td>
            <input
              type="number"
              v-model.number="item.quantity"
              class="w-16 border px-2 py-1"
              min="1"
            />
            <!-- {{ item.quantity }} -->
          </td>
          <td>
            <input
              type="number"
              v-model.number="item.barangentry_harga_net"
              class="w-28 border rounded px-2 py-1"
              min="0"
            />
            <!-- {{ item.barangentry_harga_net }} -->
          </td>
          <td class="hidden">{{ item.code_nama }}</td>
          <td>
            <button
              @click="removeItem(index)"
              class="text-red-500 px-2 py-1 rounded hover:text-red-600 text-sm"
            >
              <TrashIcon class="w-5 h-5" />
            </button>
          </td>
        </tr>
      </tbody>
    </table>
    <div class="mt-4 text-right font-semibold text-lg">
      Subtotal: Rp. {{ formatRupiahSubtotal(subtotal) }}
    </div>
  </div>

  <Transition name="slide">
    <div
      v-if="openModalHold"
      class="fixed top-0 right-0 w-full sm:w-[400px] h-full bg-white shadow-lg z-50 overflow-y-auto transition-transform"
    >
      <div class="flex justify-between items-center p-4 border-b">
        <h2 class="text-lg font-semibold">Pending List</h2>
        <button
          @click="openModalHold = false"
          class="text-gray-600 hover:text-red-500 text-xl font-bold"
        >
          &times;
        </button>
      </div>

      <div class="p-4">
        <input
          v-model="searchHold"
          type="text"
          placeholder="Cari nama customer..."
          class="w-full border px-3 py-2 rounded mb-4"
        />
        <div v-if="filteredList.length === 0" class="text-gray-500">
          Tidak ada transaksi hold saat ini.
        </div>
        <div v-else class="space-y-2">
          <div
            v-for="(item, index) in paginatedList"
            :key="item.transaksi_id"
            class="relative p-3 border rounded-md bg-gray-100 flex justify-between items-center"
            @click="loadHoldTransaction(item.transaksi_id)"
          >
            <div class="pr-8">
              <div class="font-semibold">
                {{ item.transaksi_nama_customer || "Tanpa Nama" }}
              </div>
              <div class="text-sm text-gray-600">
                Total: {{ formatRupiah(item.transaksi_total_harga) }}
              </div>
              <div class="text-sm text-gray-500">
                {{ formatTanggalHold(item.created_at) }}
              </div>
            </div>
            <div class="flex space-x-2 mt-3">
              <button
                @click="deleteHoldTransaction(item.transaksi_id)"
                class="text-red-500 px-3 py-1 rounded hover:text-red-600"
              >
                <TrashIcon class="w-5 h-5" />
              </button>
            </div>
          </div>
        </div>
        <div v-if="totalPages > 1" class="flex justify-center mt-4 space-x-2">
          <button
            @click="currentPage--"
            :disabled="currentPage === 1"
            class="px-2 py-1 rounded border bg-gray-200 hover:bg-gray-300 disabled:opacity-50"
          >
            &laquo;
          </button>

          <button
            v-for="page in totalPages"
            :key="page"
            @click="currentPage = page"
            :class="[
              'px-3 py-1 rounded border',
              page === currentPage
                ? 'bg-blue-500 text-white'
                : 'bg-gray-100 hover:bg-gray-200',
            ]"
          >
            {{ page }}
          </button>

          <button
            @click="currentPage++"
            :disabled="currentPage === totalPages"
            class="px-2 py-1 rounded border bg-gray-200 hover:bg-gray-300 disabled:opacity-50"
          >
            &raquo;
          </button>
        </div>
      </div>
    </div>
  </Transition>

  <!-- Modal Process -->
  <ModalKasir
    v-if="openModalProcess"
    @close="openModalProcess = false"
    title="Selesaikan Transaksi"
  >
    <label>Metode Pembayaran:</label>
    <select v-model="processForm.paymentMethod" class="input-field mb-2">
      <option disabled value="">Pilih metode pembayaran</option>
      <option>Cash</option>
      <option>Credit Card</option>
      <option>Transfer Bank</option>
      <option>OVO</option>
      <option>Gopay</option>
    </select>

    <label>Catatan:</label>
    <textarea
      v-model="processForm.notes"
      class="input-field mb-4"
      placeholder="Catatan tambahan"
    ></textarea>

    <button
      @click="checkoutProcess"
      class="w-full text-white px-4 py-2 rounded-md transition font-semibold"
      :class="
        isLoading
          ? 'bg-gray-400 cursor-not-allowed'
          : 'bg-green-600 hover:bg-green-700'
      "
      :disabled="isLoading"
    >
      <span
        v-if="isLoading"
        class="loader-border ease-linear rounded-full border-2 border-t-2 h-5 w-5"
      ></span>
      <span v-if="isLoading">Mohon tunggu sebentar...</span>
      <span v-else>Checkout</span>
    </button>
  </ModalKasir>

  <!-- Modal Live -->
  <ModalLive :visible="openModalLive" @close="openModalLive = false" />

  <!-- Modal PreOrder -->
  <ModalPreOrder :visible="openModalPreOrder" @close="openModalPreOrder = false" />
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import ModalKasir from "../components/ModalKasir.vue";
import axios from "axios";
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";
import { TrashIcon } from "@heroicons/vue/24/outline";
import ModalLive from '../components/ModalLive.vue';
import ModalPreOrder from '../components/ModalPreOrder.vue';

const searchQueryCustomer = ref("");
const searchQueryPhone = ref("");
const searchHold = ref("");
const currentPage = ref(1);
const itemsPerPage = 5;
const url = ref("");

const openModalHold = ref(false);
const openModalProcess = ref(false);
const openModalLive = ref(false);
const openModalPreOrder = ref(false);

const barcodeInput = ref("");
let barcodeTimeout = null;

const datatableItems = ref([]);
const isLoading = ref(false);
const currentTransaksiId = ref(null);

onMounted(() => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;
  window.addEventListener("keydown", handleBarcodeInput);
});

const subtotal = computed(() =>
  datatableItems.value.reduce((total, item) => {
    const qty = Number(item.quantity) || 0;
    const harga = Number(item.barangentry_harga_net) || 0;
    return total + qty * harga;
  }, 0)
);

function formatRupiah(number) {
  return number.toLocaleString("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  });
}

function formatRupiahSubtotal(value) {
  if (!value) return "";
  const number = parseInt(value.toString().replace(/\D/g, ""));
  return number.toLocaleString("id-ID");
}

function parseRupiah(value) {
  if (!value) return "";
  return value.toString().replace(/\D/g, "");
}

const formatTanggalHold = (dateStr) => {
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

const waitingList = ref([]);

async function fetchHoldTransactions() {
  try {
    const response = await axios.get(`${url.value}/api/transaksi/status/hold`);
    waitingList.value = response.data.data;
  } catch (error) {
    console.error("Gagal mengambil data hold:", error);
    Swal.fire("Gagal", "Tidak bisa mengambil daftar transaksi hold", "error");
  }
}

async function loadHoldTransaction(id) {
  try {
    const { data } = await axios.get(`${url.value}/api/transaksi/${id}`);
    const transaksi = data.data;

    searchQueryCustomer.value = transaksi.transaksi_nama_customer;
    searchQueryPhone.value = transaksi.transaksi_nomor_telepon;
    currentTransaksiId.value = transaksi.transaksi_id;
    const detailList = transaksi.details || [];

    datatableItems.value = await Promise.all(
      detailList.map(async (detail) => {
        const res = await axios.get(
          `${url.value}/api/entrybarang/${detail.transaksidetail_barang_id}`
        );

        const resCode = await axios.get(
          `${url.value}/api/codebarang/` + res.data.data.barangentry_code_id
        );
        return {
          barangentry_nama: res.data.data.barangentry_nama,
          quantity: detail.transaksidetail_jumlah_barang,
          barangentry_harga_net: detail.transaksidetail_harga_barang,
          code_nama: resCode.data.code_nama,
        };
      })
    );

    openModalHold.value = false;
  } catch (err) {
    console.error("Gagal memuat transaksi hold:", err);
    Swal.fire("Gagal", "Tidak bisa memuat transaksi hold", "error");
  }
}

const filteredList = computed(() =>
  waitingList.value
    .filter((item) =>
      (item.transaksi_nama_customer || "Tanpa Nama")
        .toLowerCase()
        .includes(searchHold.value.toLowerCase())
    )
    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
);

const paginatedList = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredList.value.slice(start, end);
});

const totalPages = computed(() =>
  Math.ceil(filteredList.value.length / itemsPerPage)
);

async function deleteHoldTransaction(id) {
  const konfirmasi = await Swal.fire({
    title: "Yakin?",
    text: "Transaksi ini akan dihapus dari daftar hold!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#aaa",
    confirmButtonText: "Ya, hapus",
    cancelButtonText: "Batal",
  });

  if (konfirmasi.isConfirmed) {
    try {
      await axios.delete(`${url.value}/api/transaksi/${id}`);
      await fetchHoldTransactions();
      Swal.fire("Berhasil", "Transaksi hold berhasil dihapus.", "success");
    } catch (err) {
      console.error("Gagal hapus transaksi hold:", err);
      Swal.fire("Gagal", "Tidak bisa menghapus transaksi hold", "error");
    }
  }
}

async function handleHold() {
  if (!searchQueryCustomer.value || datatableItems.value.length === 0) {
    Swal.fire({
      title: "Gagal",
      text: "Isi Nama Customer dan Nomor Telepon terlebih dahulu",
      icon: "error",
      timer: 3000,
      timerProgressBar: true,
    });
    return;
  }

  const jumlahBarang = datatableItems.value.reduce(
    (total, item) => total + (item.quantity || 0),
    0
  );

  const payload = {
    transaksi_nama_customer: searchQueryCustomer.value,
    transaksi_nomor_telepon: searchQueryPhone.value,
    transaksi_jumlah_barang: jumlahBarang,
    transaksi_total_harga: parseRupiah(subtotal.value),
    transaksi_cara_bayar: "Belum Dipilih",
    transaksi_tipe: "offline",
    transaksi_status: "hold",
    transaksi_catatan: "Transaksi ditahan sementara",
  };

  try {
    const { data } = await axios.post(`${url.value}/api/transaksi`, payload);
    const transaksi_id = data.data.transaksi_id;

    for (const item of datatableItems.value) {
      const { data: barangResponse } = await axios.get(
        `${url.value}/api/entrybarang/getDataByCode/${item.code_nama}`
      );

      const barangData = barangResponse.data;
      if (!barangData || !barangData.barangentry_id) continue;

      const detailPayload = {
        transaksidetail_transaksi_id: transaksi_id,
        transaksidetail_barang_id: barangData.barangentry_id,
        transaksidetail_jumlah_barang: item.quantity,
        transaksidetail_harga_barang: item.barangentry_harga_net,
      };

      await axios.post(`${url.value}/api/transaksi-detail`, detailPayload);
    }

    Swal.fire({
      title: "Disimpan",
      text: "Transaksi berhasil ditambahkan ke hold.",
      icon: "info",
      confirmButtonText: "OK",
      timer: 3000,
      timerProgressBar: true,
    });

    datatableItems.value = [];
    searchQueryCustomer.value = "";
    searchQueryPhone.value = "";
  } catch (error) {
    console.error("Gagal menahan transaksi:", error);
    Swal.fire({
      title: "Gagal",
      text: "Tidak bisa menyimpan transaksi hold",
      icon: "error",
      timer: 3000,
      timerProgressBar: true,
    });
  }
}

const processForm = ref({
  paymentMethod: "",
  notes: "",
});

async function checkoutProcess() {
  if (!searchQueryCustomer.value || datatableItems.value.length === 0) {
    Swal.fire({
      title: "Gagal",
      text: "Isi Nama Customer dan Nomor Telepon terlebih dahulu",
      icon: "error",
      timer: 3000,
      timerProgressBar: true,
    });
    return;
  }

  if (!processForm.value.paymentMethod) {
    alert("Pilih metode pembayaran");
    return;
  }

  isLoading.value = true;

  const jumlahBarang = datatableItems.value.reduce(
    (total, item) => total + (item.quantity || 0),
    0
  );

  const payload = {
    transaksi_nama_customer: searchQueryCustomer.value,
    transaksi_nomor_telepon: searchQueryPhone.value,
    transaksi_jumlah_barang: jumlahBarang,
    transaksi_total_harga: parseRupiah(subtotal.value),
    transaksi_cara_bayar: processForm.value.paymentMethod,
    transaksi_tipe: "offline",
    transaksi_status: "pending",
    transaksi_catatan: processForm.value.notes,
  };

  try {
    const { data } = await axios.post(`${url.value}/api/transaksi`, payload);
    const transaksi_id = data.data.transaksi_id;

    for (const item of datatableItems.value) {
      try {
        const { data: barangResponse } = await axios.get(
          `${url.value}/api/entrybarang/getDataByCode/${item.code_nama}`
        );

        const barangData = barangResponse.data;
        if (!barangData || !barangData.barangentry_id) {
          console.warn(`Barang tidak ditemukan untuk kode: ${item.code_nama}`);
          continue;
        }

        const detailPayload = {
          transaksidetail_transaksi_id: transaksi_id,
          transaksidetail_barang_id: barangData.barangentry_id,
          transaksidetail_jumlah_barang: item.quantity,
          transaksidetail_harga_barang: parseFloat(item.barangentry_harga_net),
        };

        await axios.post(`${url.value}/api/transaksi-detail`, detailPayload);
      } catch (innerErr) {
        console.error("Gagal melakukan transaksi", innerErr);
      }
    }

    const { data: responsePrint } = await axios.get(
      `${url.value}/api/transaksi/${transaksi_id}`
    );

    const transaksi = responsePrint.data;
    const detailWithNames = await Promise.all(
      transaksi.details.map(async (detail) => {
        const barangRes = await axios.get(
          `${url.value}/api/entrybarang/${detail.transaksidetail_barang_id}`
        );
        return {
          ...detail,
          barangentry_nama:
            barangRes.data.data.barangentry_nama || "Tidak Diketahui",
        };
      })
    );

    Swal.fire({
      title: "Sukses!",
      text: "Berhasil Melakukan Pembelian",
      icon: "success",
      confirmButtonText: "OK",
      timer: 3000,
      timerProgressBar: true,
    });

    printToNewTab(transaksi, detailWithNames);

    processForm.value.paymentMethod = "";
    processForm.value.notes = "";
    searchQueryCustomer.value = "";
    searchQueryPhone.value = "";
    datatableItems.value = [];
    openModalProcess.value = false;
  } catch (err) {
    console.error("Gagal menyimpan transaksi:", err);
  } finally {
    isLoading.value = false;
  }
}

const handleBarcodeInput = (e) => {
  if (barcodeTimeout) clearTimeout(barcodeTimeout);

  if (e.key === "Enter") {
    const scannedCode = barcodeInput.value.trim();
    if (scannedCode) {
      fetchDataByBarcode(scannedCode);
    }
    barcodeInput.value = "";
  } else if (/^[a-zA-Z0-9]$/.test(e.key)) {
    barcodeInput.value += e.key;
  }
  barcodeTimeout = setTimeout(() => {
    barcodeInput.value = "";
  }, 300);
};

function removeItem(index) {
  const itemCount = datatableItems.value.length;
  Swal.fire({
    title: itemCount === 1 ? "Hapus Transaksi?" : "Hapus Item?",
    text:
      itemCount === 1
        ? "Item terakhir akan dihapus. Transaksi ini juga akan dihapus."
        : "Item ini akan dihapus dari daftar.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#aaa",
    confirmButtonText: itemCount === 1 ? "Ya, hapus transaksi" : "Ya, hapus",
    cancelButtonText: "Batal",
  }).then(async (result) => {
    if (!result.isConfirmed) return;

    if (itemCount === 1) {
      if (currentTransaksiId.value) {
        try {
          await axios.delete(
            `${url.value}/api/transaksi/${currentTransaksiId.value}`
          );
          Swal.fire("Terhapus", "Transaksi berhasil dihapus.", "success");
        } catch (err) {
          console.error("Gagal menghapus transaksi:", err);
          Swal.fire("Gagal", "Gagal menghapus transaksi.", "error");
        }
      }
      currentTransaksiId.value = null;
      datatableItems.value = [];
      searchQueryCustomer.value = "";
      searchQueryPhone.value = "";
    } else {
      datatableItems.value.splice(index, 1);
      Swal.fire({
        title: "Item Dihapus",
        icon: "success",
        timer: 1200,
        showConfirmButton: false,
      });
    }
  });
}

const fetchDataByBarcode = async (code) => {
  try {
    const configURL = useRuntimeConfig();
    const baseURL = configURL.public.apiBase;
    const { data } = await axios.get(
      `${baseURL}/api/entrybarang/getDataKasir/` + code
    );

    if (data && Array.isArray(data.data) && data.data.length > 0) {
      const item = data.data[0];
      const existingItem = datatableItems.value.find(
        (i) => i.barangentry_nama === item.barangentry_nama
      );

      if (existingItem) {
        existingItem.quantity += 1;
      } else {
        datatableItems.value.push({
          barangentry_nama: item.barangentry_nama,
          quantity: 1,
          barangentry_harga_net: item.barangentry_harga_net,
          isEditing: false,
          code_nama: code,
        });
      }
    } else {
      alert("Data tidak ditemukan");
    }
  } catch (error) {
    console.error(error);
    alert("Gagal mengambil data barang");
  }
};

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

  const htmlContent = `
  <!DOCTYPE html>
  <html lang="id">
  <head>
    <meta charset="UTF-8" />
    <title>Print Transaksi</title>
    <style>
      /* Reset & base */
      body {
        font-family: Arial, sans-serif;
        margin: 0; padding: 20px;
        background: #fff;
        color: #000;
      }
      .print-area {
        max-width: 600px;
        margin: 0 auto;
        padding: 20px;
        box-sizing: border-box;
      }
      .text-center {
        text-align: center;
      }
      .mb-2 { margin-bottom: 0.5rem; }
      .mb-4 { margin-bottom: 1rem; }
      .my-4 { margin-top: 1rem; margin-bottom: 1rem; }
      .font-bold { font-weight: 700; }
      .font-semibold { font-weight: 600; }
      .text-sm { font-size: 0.875rem; }
      .text-base { font-size: 1rem; }
      .w-24 { width: 96px; }
      .h-auto { height: auto; }
      .mx-auto { margin-left: auto; margin-right: auto; }
      .table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
      }
      table th, table td {
        padding: 8px 6px;
        border-bottom: 1px solid #ccc;
      }
      table th {
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #444;
      }
      table td {
        vertical-align: top;
      }
      table td.text-left {
        text-align: left;
      }
      .table tbody tr:last-child td {
        border-bottom: 2px solid #000;
      }
      .total-bayar {
        text-align: right;
        font-weight: 700;
        font-size: 1.1rem;
        margin-top: 20px;
      }
      /* Print styles */
      @media print {
        body {
          margin: 0; padding: 0;
        }
        .print-area {
          box-shadow: none;
          width: 100%;
          max-width: none;
          margin: 0;
          padding: 0;
        }
      }
    </style>
  </head>
  <body>
    <div class="print-area">

      <div class="text-center mb-4">
        <img src="/image/DameUlosLogo2.png" alt="Logo" class="w-24 h-auto mx-auto mb-2" />
        <h2 class="font-bold text-base">Dame Ulos Tarutung</h2>
      </div>

      <h2 class="font-bold text-center mb-2">Struk Transaksi</h2>
      <p class="text-center text-sm mb-4">Terima kasih telah berbelanja!</p>

      <div class="mb-4 text-sm">
        <p><strong>Nama Customer:</strong> ${
          data.transaksi_nama_customer || "-"
        }</p>
        <p><strong>No Telepon:</strong> ${
          data.transaksi_nomor_telepon || "-"
        }</p>
        <p><strong>Metode Pembayaran:</strong> ${
          data.transaksi_cara_bayar || "-"
        }</p>
        <p><strong>Jumlah Barang:</strong> ${
          data.transaksi_jumlah_barang ||
          items.reduce((acc, i) => acc + i.transaksidetail_jumlah_barang, 0)
        }</p>
        <p><strong>Total:</strong> Rp. ${formatRupiahSubtotal(
          parseFloat(data.transaksi_total_harga)
        )}</p>
        <p><strong>Waktu:</strong> ${formatTanggal(data.created_at)}</p>
      </div>

      <div class="my-4">
        <h3 class="font-semibold mb-2">Detail Barang:</h3>
        <table class="table">
          <thead>
            <tr>
              <th>Nama</th>
              <th class="text-left">Qty</th>
              <th class="text-left">Harga</th>
              <th class="text-left">Total</th>
            </tr>
          </thead>
          <tbody>
            ${items
              .map(
                (item) => `
              <tr>
                <td>${item.barangentry_nama}</td>
                <td class="text-left">${item.transaksidetail_jumlah_barang}</td>
                <td class="text-left">${formatRupiah(
                  item.transaksidetail_harga_barang
                )}</td>
                <td class="text-left">${formatRupiah(
                  item.transaksidetail_jumlah_barang *
                    item.transaksidetail_harga_barang
                )}</td>
              </tr>
            `
              )
              .join("")}
          </tbody>
        </table>
      </div>

      <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 1.1rem; margin-top: 20px;">
  <div>Jumlah Barang: ${items.reduce(
    (acc, i) => acc + i.transaksidetail_jumlah_barang,
    0
  )}</div>
  <div>Subtotal: Rp. ${formatRupiahSubtotal(
    parseFloat(data.transaksi_total_harga)
  )}</div>
</div>

    </div>

    <script>
      window.onload = function() {
        window.print();
      };
    <\/script>
  </body>
  </html>
  `;

  printWindow.document.write(htmlContent);
  printWindow.document.close();
}

watch(searchHold, () => {
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
  border: 1px solid #ddd;
  text-align: left;
}

.datatable th {
  background-color: #f4f4f4;
}

.input-field {
  border: 1px solid #ccc;
  border-radius: 4px;
  padding: 8px;
  width: 100%;
  box-sizing: border-box;
}

.btn-yellow {
  background-color: #f59e0b;
  color: white;
  padding: 10px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: background-color 0.3s;
}

.btn-yellow:hover {
  background-color: #b45309;
}

.btn-red {
  background-color: #ef4444;
  color: white;
  padding: 10px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: background-color 0.3s;
}

.btn-red:hover {
  background-color: #b91c1c;
}

.btn-green {
  background-color: #22c55e;
  color: white;
  padding: 10px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: background-color 0.3s;
}

.btn-green:hover {
  background-color: #166534;
}

.loader-border {
  border-top-color: #fff;
  animation: spinner 0.6s linear infinite;
}
@keyframes spinner {
  to {
    transform: rotate(360deg);
  }
}

.slide-enter-active,
.slide-leave-active {
  transition: transform 0.3s ease;
}
.slide-enter-from {
  transform: translateX(100%);
}
.slide-leave-to {
  transform: translateX(100%);
}
</style>
