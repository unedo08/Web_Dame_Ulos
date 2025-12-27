<template>
  <title>Kasir</title>
  <div class="judul text-xl font-semibold mb-4">Menu Kasir</div>

  <div class="flex items-start justify-between pt-2">
    <div class="flex flex-col flex-1 space-y-2">
      <input class="search-box p-2 border rounded-md" v-model="searchQueryCustomer" type="text"
        placeholder="Nama Customer" />
      <input class="search-box p-2 border rounded-md" v-model="searchQueryPhone" type="text"
        placeholder="Nomor Telepon" />
    </div>

    <div class="flex space-x-2 ml-4">
      <button class="bg-[#3D8BFD] text-white rounded-md hover:bg-[#2272E7] w-[104px] h-[34px]"
        @click="openModalLive = true">
        Online
      </button>
      <button class="bg-[#F97316] text-white rounded-md hover:bg-[#F36E12] w-[104px] h-[34px]"
        @click="openModalPreOrder = true">
        Pre-Order
      </button>
      <button class="bg-[#FACC15] text-white rounded-md hover:bg-[#F4C405] w-[132.97px] h-[34px]" @click="
        () => {
          openModalHold = true;
          fetchHoldTransactions();
        }
      ">
        Pending List
      </button>
      <button class="bg-[#404040] text-white rounded-md hover:bg-[#363535] w-[104px] h-[34px]" @click="handleHold">
        Hold
      </button>
      <button class="bg-[#22C55E] text-white rounded-md hover:bg-[#21B156] w-[104px] h-[34px]"
        @click="openModalProcess = true">
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
        <tr v-for="(item, index) in datatableItems" :key="index" :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
          <td class="px-4 py-2">{{ index + 1 }}</td>
          <td class="px-4 py-2">{{ item.barangentry_nama }}</td>
          <td class="px-4 py-2">
            <template v-if="item.barangentry_jumlah_barang > 1">
              <input type="number" v-model.number="item.quantity" class="w-16 border px-2 py-1" min="1" />
            </template>
            <template v-else>
              {{ item.quantity }}
            </template>
          </td>
          <td class="px-4 py-2">
            <input type="text" v-model="item.barangentry_harga_net"
              @input="item.barangentry_harga_net = $event.target.value.replace(/\D/g, '')"
              class="w-28 border rounded px-2 py-1 text-right" />


          </td>
          <td class="hidden">{{ item.code_nama }}</td>
          <td class="hidden">{{ item.transaksi_id }}</td>
          <td class="px-4 py-2">
            <button @click="removeItem(index)" class="text-red-500 px-2 py-1 rounded hover:text-red-600 text-s">
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

  <div v-if="openModalHold" class="fixed inset-0 bg-black/40 z-40" @click="openModalHold = false"></div>
  <Transition name="slide-right">
    <div v-if="openModalHold"
      class="fixed top-0 right-0 w-full sm:w-[400px] h-full bg-white shadow-lg z-50 overflow-y-auto flex flex-col">
      <div class="sticky top-0 z-20 bg-white border-b p-4">
        <div class="flex justify-between items-center">
          <h2 class="text-md font-semibold">Pending List</h2>
          <button @click="openModalHold = false" class="text-gray-600 hover:text-red-500 text-xl font-bold">
            &times;
          </button>
        </div>
        <div class="mt-4">
          <input v-model="searchHold" type="text" placeholder="Cari nama customer..."
            class="w-full border border-gray-300 px-3 py-2 rounded" />
        </div>
      </div>
      <div class="p-4">
        <div v-if="filteredList.length === 0" class="text-gray-500">
          Tidak ada transaksi hold saat ini.
        </div>
        <div v-else class="space-y-2 transition-slide">
          <div v-for="(item, index) in paginatedList" :key="item.transaksi_id" :class="[
            'relative p-3 border rounded-md flex justify-between items-center cursor-pointer transition-all duration-200',
            item.transaksi_id === currentTransaksiId
              ? 'bg-gray-200 border-gray-500'
              : 'bg-[#F7F7F7] border-gray-300 hover:bg-gray-200 hover:border-gray-400'
          ]" @click="loadHoldTransaction(item.transaksi_id)">
            <div class="pr-8">
              <div class="font-semibold">
                {{ item.transaksi_nama_customer || 'Tanpa Nama' }}
              </div>
              <div class="text-xs text-gray-600">
                Total: {{ formatRupiah(Number(item.transaksi_total_harga)) }}
              </div>
              <div class="text-xs text-gray-500">
                {{ formatTanggalHold(item.created_at) }}
              </div>
            </div>

            <button @click.stop="deleteHoldTransaction(item.transaksi_id)"
              class="text-red-500 px-3 py-1 rounded hover:text-red-600">
              <TrashIcon class="w-5 h-5" />
            </button>
          </div>
        </div>

        <div v-if="totalPages > 1" class="flex justify-center mt-4 space-x-2">
          <button @click="currentPage--" :disabled="currentPage === 1"
            class="px-2 py-1 rounded border bg-gray-200 hover:bg-gray-300 disabled:opacity-50">
            «
          </button>

          <button v-for="page in totalPages" :key="page" @click="currentPage = page" :class="[
            'px-3 py-1 rounded border',
            page === currentPage
              ? 'bg-blue-500 text-white'
              : 'bg-gray-100 hover:bg-gray-200'
          ]">
            {{ page }}
          </button>

          <button @click="currentPage++" :disabled="currentPage === totalPages"
            class="px-2 py-1 rounded border bg-gray-200 hover:bg-gray-300 disabled:opacity-50">
            »
          </button>
        </div>
      </div>
    </div>
  </Transition>

  <ModalKasir v-if="openModalProcess" @close="openModalProcess = false" title="Selesaikan Transaksi">
    <label>Metode Pembayaran:</label>
    <select v-model="processForm.paymentMethod" class="input-field mb-2">
      <option disabled value="">Pilih metode pembayaran</option>
      <option v-for="cb in caraBayarList" :key="cb.id" :value="cb.carabayar_kode">
        {{ cb.carabayar_nama }}
      </option>
    </select>

    <label>Catatan:</label>
    <textarea v-model="processForm.notes" class="input-field mb-4" placeholder="Catatan tambahan"></textarea>

    <button @click="checkoutProcess" class="w-full text-white px-4 py-2 rounded-md transition font-semibold" :class="isLoading
      ? 'bg-gray-400 cursor-not-allowed'
      : 'bg-green-600 hover:bg-green-700'
      " :disabled="isLoading">
      <span v-if="isLoading" class="loader-border ease-linear rounded-full border-2 border-t-2 h-5 w-5"></span>
      <span v-if="isLoading">Mohon tunggu sebentar...</span>
      <span v-else>Checkout</span>
    </button>
  </ModalKasir>

  <ModalLive :visible="openModalLive" @close="openModalLive = false" />

  <ModalPreOrder :visible="openModalPreOrder" @close="openModalPreOrder = false" />
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
const caraBayarList = ref([]);

const datatableItems = ref([]);
const isLoading = ref(false);
const currentTransaksiId = ref(null);

onMounted(() => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;
  window.addEventListener("keydown", handleBarcodeInput);
});

onMounted(async () => {
  const token = sessionStorage.getItem("auth_token")
  const res = await axios.get(`${url.value}/api/carabayar`, {
    headers: {
      "Authorization": `Bearer ${token}`,
      "Content-Type": "application/json",
    }
  });
  caraBayarList.value = res.data.data;
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
  if (value === null || value === undefined || value === "") return "0";

  const cleaned = value.toString().replace(/\D/g, "");
  return cleaned === "" ? "0" : cleaned;
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
    const token = sessionStorage.getItem("auth_token")
    const response = await axios.get(`${url.value}/api/transaksi/status/hold`, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    });
    waitingList.value = Array.isArray(response.data?.data) ? response.data.data : [];
    currentPage.value = 1;
  } catch (error) {
    console.error("Gagal mengambil data hold:", error);
    waitingList.value = [];
  }
}

async function loadHoldTransaction(id) {
  try {
    const token = sessionStorage.getItem("auth_token")
    const { data } = await axios.get(`${url.value}/api/transaksi/${id}`, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    });
    const transaksi = data?.data;
    if (!transaksi) {
      Swal.fire("Gagal", "Data transaksi tidak ditemukan", "error");
      return;
    }

    searchQueryCustomer.value = transaksi.transaksi_nama_customer || "";
    searchQueryPhone.value = transaksi.transaksi_nomor_telepon || "";
    currentTransaksiId.value = transaksi.transaksi_id || null;

    const detailList = Array.isArray(transaksi.details) ? transaksi.details : [];

    const mapped = await Promise.all(
      detailList.map(async (detail) => {
        try {
          const resEntry = await axios.get(
            `${url.value}/api/entrybarang/${detail.transaksidetail_barang_id}`, {
            headers: {
              "Authorization": `Bearer ${token}`,
              "Content-Type": "application/json",
            }
          }
          );

          const entry = resEntry?.data?.data;
          let codeNama = "";
          if (entry?.barangentry_code_id) {
            const resCode = await axios.get(
              `${url.value}/api/codebarang/${entry.barangentry_code_id}`, {
              headers: {
                "Authorization": `Bearer ${token}`,
                "Content-Type": "application/json",
              }
            }
            );
            codeNama = resCode?.data?.code_nama || "";
          }

          return {
            barangentry_nama: entry?.barangentry_nama || detail?.barangentry_nama || "Tidak Diketahui",
            quantity: detail?.transaksidetail_jumlah_barang ?? 1,
            barangentry_jumlah_barang: entry?.barangentry_jumlah_barang ?? 1,
            barangentry_harga_net: 0,
            isEditing: false,
            code_nama: codeNama,
            transaksi_id: transaksi.transaksi_id || null,
          };
        } catch (err) {
          console.warn("Gagal memetakan detail transaksi:", err);
          return {
            barangentry_nama: detail?.nama_barang || "Tidak Diketahui",
            quantity: detail?.transaksidetail_jumlah_barang ?? 1,
            barangentry_jumlah_barang: 1,
            barangentry_harga_net: 0,
            isEditing: false,
            code_nama: detail?.kode_barang || "",
            transaksi_id: transaksi.transaksi_id || null,
          };
        }
      })
    );

    datatableItems.value = mapped;

    openModalHold.value = false;
  } catch (err) {
    console.error("Gagal memuat transaksi hold:", err);
    Swal.fire("Gagal", "Tidak bisa memuat transaksi hold", "error");
  }
}

const filteredList = computed(() => {
  const q = (searchHold.value || "").toString().toLowerCase().trim();

  const data = waitingList.value
    .filter((item) => {
      const name = (item.transaksi_nama_customer || "Tanpa Nama").toString().toLowerCase();
      return name.includes(q);
    })
    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

  return data;
});

const paginatedList = computed(() => {
  const perPage = Number(itemsPerPage) || 10;
  const start = (currentPage.value - 1) * perPage;
  const end = start + perPage;
  return filteredList.value.slice(start, end);
});

const totalPages = computed(() => {
  const perPage = Number(itemsPerPage) || 10;
  return Math.max(1, Math.ceil(filteredList.value.length / perPage));
});

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

  if (!konfirmasi.isConfirmed) return;

  try {
    const token = sessionStorage.getItem("auth_token")
    await axios.delete(`${url.value}/api/transaksi/${id}`, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    });
    await fetchHoldTransactions();

    if (currentTransaksiId.value === id) {
      currentTransaksiId.value = null;
      datatableItems.value = [];
      searchQueryCustomer.value = "";
      searchQueryPhone.value = "";
    }

    Swal.fire("Berhasil", "Transaksi hold berhasil dihapus.", "success");
  } catch (err) {
    console.error("Gagal hapus transaksi hold:", err);
    Swal.fire("Gagal", "Tidak bisa menghapus transaksi hold", "error");
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

  if (!validateHargaNonZero()) return;

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
    const token = sessionStorage.getItem("auth_token")
    const { data } = await axios.post(`${url.value}/api/transaksi`, payload, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    });
    const transaksi_id = data.data.transaksi_id;

    for (const item of datatableItems.value) {
      const { data: barangResponse } = await axios.get(
        `${url.value}/api/entrybarang/getDataByCode/${item.code_nama}`, {
        headers: {
          "Authorization": `Bearer ${token}`,
          "Content-Type": "application/json",
        }
      }
      );

      const barangData = barangResponse.data;
      if (!barangData || !barangData.barangentry_id) continue;

      const detailPayload = {
        transaksidetail_transaksi_id: transaksi_id,
        transaksidetail_barang_id: barangData.barangentry_id,
        transaksidetail_jumlah_barang: item.quantity,
        transaksidetail_harga_barang: item.barangentry_harga_net,
      };

      await axios.post(`${url.value}/api/transaksi-detail`, detailPayload, {
        headers: {
          "Authorization": `Bearer ${token}`,
          "Content-Type": "application/json",
        }
      });
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
    });
    return;
  }

  if (!processForm.value.paymentMethod) {
    openModalProcess.value = false;
    Swal.fire({
      title: "Gagal",
      text: "Isi Nama Customer dan Nomor Telepon terlebih dahulu",
      icon: "error",
    });
    return;
  }

  if (!validateHargaNonZero()) return;

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

  let transaksi_id = null;

  try {
    const token = sessionStorage.getItem("auth_token")
    if (currentTransaksiId.value) {
      transaksi_id = currentTransaksiId.value;
      const payload_hold = {
        transaksi_id: String(transaksi_id),
        transaksi_nama_customer: searchQueryCustomer.value,
        transaksi_nomor_telepon: searchQueryPhone.value,
        transaksi_jumlah_barang: jumlahBarang,
        transaksi_total_harga: parseRupiah(subtotal.value),
        transaksi_cara_bayar: processForm.value.paymentMethod,
        transaksi_tipe: "offline",
        transaksi_status: "pending",
        transaksi_catatan: processForm.value.notes,
      };

      await axios.post(`${url.value}/api/transaksi`, payload_hold, {
        headers: {
          "Authorization": `Bearer ${token}`,
          "Content-Type": "application/json",
        }
      });
    } else {
      const { data } = await axios.post(`${url.value}/api/transaksi`, payload, {
        headers: {
          "Authorization": `Bearer ${token}`,
          "Content-Type": "application/json",
        }
      });
      transaksi_id = data.data.transaksi_id;
    }
    for (const item of datatableItems.value) {
      const { data: barangResponse } = await axios.get(
        `${url.value}/api/entrybarang/getDataByCode/${item.code_nama}`, {
        headers: {
          "Authorization": `Bearer ${token}`,
          "Content-Type": "application/json",
        }
      }
      );

      const barangData = barangResponse.data;
      if (!barangData || !barangData.barangentry_id) continue;

      const detailPayload = {
        transaksidetail_transaksi_id: transaksi_id,
        transaksidetail_barang_id: barangData.barangentry_id,
        transaksidetail_jumlah_barang: item.quantity,
        transaksidetail_harga_barang: parseFloat(item.barangentry_harga_net),
      };

      await axios.post(`${url.value}/api/transaksi-detail`, detailPayload, {
        headers: {
          "Authorization": `Bearer ${token}`,
          "Content-Type": "application/json",
        }
      });
    }
    const { data: responsePrint } = await axios.get(`${url.value}/api/transaksi/${transaksi_id}`, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    });

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
          barangentry_nama: barangRes.data.data.barangentry_nama || "-",
          barangentry_code: kodeBarang.data.code_nama,
        };
      })
    );

    openModalProcess.value = false;
    await nextTick();
    Swal.fire("Sukses!", "Checkout berhasil", "success");
    printToNewTab(transaksi, detailWithNames);

    await fetchHoldTransactions();
    currentTransaksiId.value = null;
    processForm.value.paymentMethod = "";
    processForm.value.notes = "";
    searchQueryCustomer.value = "";
    searchQueryPhone.value = "";
    datatableItems.value = [];

  } catch (err) {
    console.error("Gagal menyimpan transaksi:", err);
    openModalProcess.value = false;
    Swal.fire("Gagal", "Terjadi kesalahan saat Checkout", "error");
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
          const token = sessionStorage.getItem("auth_token")
          await axios.delete(
            `${url.value}/api/transaksi/${currentTransaksiId.value}`, {
            headers: {
              "Authorization": `Bearer ${token}`,
              "Content-Type": "application/json",
            }
          }
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
  if (angka === null || angka === undefined) return "";
  return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function updateHargaNet(event, item) {
  let value = event.target.value.replace(/[^\d]/g, "");
  item.barangentry_harga_net = value ? Number(value) : 0;
}

const fetchDataByBarcode = async (code) => {
  try {
    const configURL = useRuntimeConfig();
    const baseURL = configURL.public.apiBase;
    const token = sessionStorage.getItem("auth_token")
    const { data } = await axios.get(`${baseURL}/api/entrybarang/getDataKasir/` + code, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    });

    if (!data || !Array.isArray(data.data) || data.data.length === 0) {
      Swal.fire("Tidak ditemukan", "Kode barang tidak ditemukan", "warning");
      return;
    }

    const item = data.data[0];

    const existingItem = datatableItems.value.find(
      (i) => i.barangentry_nama === item.barangentry_nama
    );

    if (item.barangentry_jumlah_barang === 0) {
      Swal.fire({
        title: "Tidak dapat scan",
        text: "Barang ini memiliki jumlah 0 dan tidak bisa discan.",
        icon: "warning",
      });
      return;
    }

    if (item.barangentry_jumlah_barang === 1) {
      if (existingItem) {
        Swal.fire({
          title: "Barang sudah discan",
          text: "Barang dengan jumlah 1 hanya bisa discan sekali.",
          icon: "info",
        });
        return;
      }

      datatableItems.value.push({
        barangentry_nama: item.barangentry_nama,
        quantity: 1,
        barangentry_jumlah_barang: item.barangentry_jumlah_barang,
        barangentry_harga_net: 0,
        isEditing: false,
        code_nama: code,
      });

      return;
    }

    if (existingItem) {
      existingItem.quantity += 1;
    } else {
      datatableItems.value.push({
        barangentry_nama: item.barangentry_nama,
        quantity: 1,
        barangentry_jumlah_barang: item.barangentry_jumlah_barang,
        barangentry_harga_net: 0,
        isEditing: false,
        code_nama: code,
      });
    }

  } catch (error) {
    console.error(error);
    Swal.fire("Error", "Gagal mengambil data barang", "error");
  }
};

function validateHargaNonZero() {
  const invalids = datatableItems.value
    .filter(i => {
      const v = i.barangentry_harga_net;
      if (v === "" || v === null || v === undefined) return true;

      if (isNaN(Number(v))) return true;

      return false;
    })
    .map(i => `${i.code_nama || '-'} • ${i.barangentry_nama}`);

  if (invalids.length > 0) {
    Swal.fire({
      title: "Harga tidak valid",
      html: `
        <div class="text-left">
          <p class="mb-2">Harga tidak boleh kosong atau bukan angka:</p>
          <ul style="list-style:disc;margin-left:18px">
            ${invalids.map(x => `<li>${x}</li>`).join("")}
          </ul>
        </div>`,
      icon: "warning",
      confirmButtonText: "OK",
    });
    openModalProcess.value = false;
    return false;
  }

  return true;
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

watch(searchHold, () => {
  currentPage.value = 1;
});

watch(
  datatableItems,
  (val) => {
    val.forEach((item) => {
      if (item.barangentry_harga_net === "" || item.barangentry_harga_net == null) {
        item.barangentry_harga_net = 0;
      }
    });
  },
  { deep: true }
);

watch(openModalHold, (val) => {
  if (val) {
    document.body.classList.add("bg-gray-200");
  } else {
    document.body.classList.remove("bg-gray-200");
  }
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
