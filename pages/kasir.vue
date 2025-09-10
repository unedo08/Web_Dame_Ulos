<template>
  <title>Kasir</title>
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
        Online
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
    <table class="datatable w-full rounded-md overflow-hidden">
      <thead class="bg-blue-100">
        <tr>
          <th class="px-4 py-2 text-left">No</th>
          <th class="px-4 py-2 text-left">Nama Item</th>
          <th class="px-4 py-2 text-left">Jumlah</th>
          <th class="px-4 py-2 text-left">Harga</th>
          <th class="hidden px-4 py-2 text-left">code</th>
          <th class="hidden px-4 py-2 text-left">trx_id</th>
          <th class="px-4 py-2 text-left">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(item, index) in datatableItems"
          :key="index"
          :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
        >
          <td class="px-4 py-2">{{ index + 1 }}</td>
          <td class="px-4 py-2">{{ item.barangentry_nama }}</td>
          <td class="px-4 py-2">
            <template v-if="item.quantity > 1">
              <input
                type="number"
                v-model.number="item.quantity"
                class="w-16 border px-2 py-1"
                min="1"
              />
            </template>
            <template v-else>
              {{ item.quantity }}
            </template>
          </td>
          <td class="px-4 py-2">
          <!-- v-if="item.quantity > 1" -->
            <template>
              <input
                type="text"
                :value="formatRupiahInput(Number(item.barangentry_harga_net))"
                @input="updateHargaNet($event, item)"
                class="w-28 border rounded px-2 py-1 text-right"
              />
            </template>
            <!-- <template v-else>
              {{ formatRupiahInput(item.barangentry_harga_net) }}
            </template> -->
          </td>
          <td class="hidden">{{ item.code_nama }}</td>
          <td class="hidden">{{ item.transaksi_id }}</td>
          <td class="px-4 py-2">
            <button
              @click="removeItem(index)"
              class="text-red-500 px-2 py-1 rounded hover:text-red-600 text-s"
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
        <h2 class="text-md font-semibold">Pending List</h2>
        <button
          @click="openModalHold = false"
          class="text-gray-600 hover:text-red-500 text-xl font-bold"
        >
          &times;
        </button>
      </div>

      <div class="transition-slide p-4">
        <input
          v-model="searchHold"
          type="text"
          placeholder="Cari nama customer..."
          class="w-full border border-gray-300 px-3 py-2 rounded mb-4"
        />
        <div v-if="filteredList.length === 0" class="text-gray-500">
          Tidak ada transaksi hold saat ini.
        </div>
        <div v-else class="space-y-2">
          <div
            v-for="(item, index) in paginatedList"
            :key="item.transaksi_id"
            class="relative p-3 border border-gray-300 rounded-md bg-[#F7F7F7] flex justify-between items-center"
            @click="loadHoldTransaction(item.transaksi_id)"
          >
            <div class="pr-8">
              <div class="font-semibold">
                {{ item.transaksi_nama_customer || "Tanpa Nama" }}
              </div>
              <div class="text-xs text-gray-600">
                Total: {{ formatRupiah(Number(item.transaksi_total_harga)) }}
              </div>
              <div class="text-xs text-gray-500">
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
  <ModalPreOrder
    :visible="openModalPreOrder"
    @close="openModalPreOrder = false"
  />
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import ModalKasir from "../components/ModalKasir.vue";
import axios from "axios";
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";
import { TrashIcon } from "@heroicons/vue/24/outline";
import ModalLive from "../components/ModalLive.vue";
import ModalPreOrder from "../components/ModalPreOrder.vue";

const searchQueryCustomer = ref("");
const searchQueryPhone = ref("");
const searchHold = ref("");
const currentPage = ref(1);
const itemsPerPage = 10;
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
  const optionsTanggal = { day: "2-digit", month: "long", year: "numeric" };
  const tanggal = date.toLocaleDateString("id-ID", optionsTanggal);

  const jam = String(date.getHours()).padStart(2, "0");
  const menit = String(date.getMinutes()).padStart(2, "0");
  const waktu = `${jam}.${menit}`;

  return `${tanggal} • ${waktu} WIB`;
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
          transaksi_id: id
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
    transaksi_id: datatableItems.value[0].transaksi_id ? String(datatableItems.value[0].transaksi_id) : "",
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
    openModalProcess.value = false;
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
        const kodeBarang = await axios.get(`${url.value}/api/codebarang/`+barangRes.data.data.barangentry_code_id)
        
        return {
          ...detail,
          barangentry_nama:
            barangRes.data.data.barangentry_nama || "Tidak Diketahui",
          barangentry_code: kodeBarang.data.code_nama,
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

function formatRupiahInput(angka) {
  if (angka === null || angka === undefined) return '';
  return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function updateHargaNet(event, item) {
  const value = event.target.value.replace(/[^\d]/g, '');
  item.barangentry_harga_net = parseInt(value) || 0;
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
          quantity: item.barangentry_jumlah_barang,
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
  /* border: 1px solid #ddd; */
  text-align: left;
  font-size: 12px;
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

.transition-slide {
  font-size: 12px;
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
