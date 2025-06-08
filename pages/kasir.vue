<template>
  <div class="judul text-xl font-semibold mb-4">Menu Kasir</div>

  <div class="flex items-start justify-between pt-2">
    <!-- Input Section -->
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
        class="bg-yellow-500 text-white rounded-md hover:bg-yellow-600 w-[104px] h-[34px]"
        @click="openModalHold = true"
      >
        Hold
      </button>
      <button
        class="bg-red-500 text-white rounded-md hover:bg-red-600 w-[120px] h-[48px]"
        @click="openModalProcess = true"
      >
        Process Transaction
      </button>
      <button
        class="bg-green-500 text-white rounded-md hover:bg-green-600 w-[104px] h-[34px]"
        @click="openModalOnline = true"
      >
        Online
      </button>
      <button
        class="bg-green-500 text-white rounded-md hover:bg-green-600 w-[104px] h-[34px]"
        @click="openModalPO = true"
      >
        Pre-Order
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
        </tr>
      </tbody>
    </table>
    <div class="mt-4 text-right font-semibold text-lg">
      Subtotal: {{ formatRupiah(subtotal) }}
    </div>
  </div>

  <ModalKasir
    v-if="openModalHold"
    @close="openModalHold = false"
    title="Hold Transaksi"
  >
    <p class="mb-4">Menyimpan transaksi sebelum transaksi diselesaikan.</p>
    <label>Nama Customer:</label>
    <input
      v-model="holdForm.customerName"
      class="input-field mb-2"
      placeholder="Masukkan nama customer"
    />

    <label>Keranjang (Barang):</label>
    <textarea
      v-model="holdForm.cartItems"
      class="input-field mb-2"
      placeholder="Daftar barang (pisahkan dengan koma)"
    ></textarea>

    <button @click="saveHold" class="btn-yellow w-full">
      Simpan ke Waiting List
    </button>

    <hr class="my-4" />
    <h3 class="font-semibold mb-2">Waiting List</h3>
    <ul>
      <li
        v-for="(hold, index) in waitingList"
        :key="index"
        class="mb-2 cursor-pointer hover:underline"
        @click="selectHold(index)"
      >
        {{ hold.customerName }}
      </li>
    </ul>

    <div
      v-if="selectedHold !== null"
      class="mt-4 p-4 border rounded bg-gray-50"
    >
      <h4 class="font-semibold mb-2">
        Detail Belanjaan {{ waitingList[selectedHold].customerName }}
      </h4>
      <p>Barang: {{ waitingList[selectedHold].cartItems }}</p>
    </div>
  </ModalKasir>

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

    <button @click="checkoutProcess" class="btn-green w-full">Checkout</button>
  </ModalKasir>

  <!-- Modal Pre-Order -->
  <ModalKasir v-if="openModalPO" @close="openModalPO = false" title="Pre-Order">
    <label>Id Barang:</label>
    <input
      v-model="poForm.itemId"
      class="input-field mb-2"
      placeholder="Id barang"
    />

    <label>Nama Ulos:</label>
    <input
      v-model="poForm.ulosName"
      class="input-field mb-2"
      placeholder="Nama ulos"
    />

    <label>Nama Akun:</label>
    <input
      v-model="poForm.accountName"
      class="input-field mb-2"
      placeholder="Nama akun"
    />

    <label>No Telepon:</label>
    <input
      v-model="poForm.phone"
      class="input-field mb-2"
      placeholder="Nomor telepon"
    />

    <label>Total Pembayaran:</label>
    <input
      v-model.number="poForm.totalPayment"
      type="number"
      class="input-field mb-2"
      placeholder="Total pembayaran"
    />

    <label>DP:</label>
    <input
      v-model.number="poForm.dp"
      type="number"
      class="input-field mb-2"
      placeholder="DP"
    />

    <label>Sisa Pembayaran:</label>
    <input
      v-model.number="poForm.remainingPayment"
      type="number"
      class="input-field mb-2"
      placeholder="Sisa pembayaran"
    />

    <label>Deskripsi Ulos:</label>
    <textarea
      v-model="poForm.description"
      class="input-field mb-2"
      placeholder="Deskripsi ulos"
    ></textarea>

    <label>Catatan:</label>
    <textarea
      v-model="poForm.notes"
      class="input-field mb-2"
      placeholder="Catatan"
    ></textarea>

    <label>Target Selesai:</label>
    <input v-model="poForm.targetDate" type="date" class="input-field mb-4" />

    <button @click="checkoutPO" class="btn-green w-full">Checkout</button>
  </ModalKasir>

  <!-- Modal Online -->
  <ModalKasir
    v-if="openModalOnline"
    @close="openModalOnline = false"
    title="Online Transaction"
  >
    <label>Nama Akun:</label>
    <input
      v-model="onlineForm.accountName"
      class="input-field mb-2"
      placeholder="Nama akun"
    />

    <label>Platform:</label>
    <input
      v-model="onlineForm.platform"
      class="input-field mb-2"
      placeholder="Platform"
    />

    <label>Harga Terjual:</label>
    <input
      v-model.number="onlineForm.soldPrice"
      type="number"
      class="input-field mb-2"
      placeholder="Harga terjual"
    />

    <label>Nama Penerima:</label>
    <input
      v-model="onlineForm.receiverName"
      class="input-field mb-2"
      placeholder="Nama penerima"
    />

    <label>Alamat:</label>
    <textarea
      v-model="onlineForm.address"
      class="input-field mb-2"
      placeholder="Alamat"
    ></textarea>

    <label>No Telepon:</label>
    <input
      v-model="onlineForm.phone"
      class="input-field mb-2"
      placeholder="Nomor telepon"
    />

    <label>Pengiriman:</label>
    <input
      v-model="onlineForm.delivery"
      class="input-field mb-2"
      placeholder="Pengiriman"
    />

    <label>Jumlah Ongkos Kirim:</label>
    <input
      v-model.number="onlineForm.shippingCost"
      type="number"
      class="input-field mb-2"
      placeholder="Jumlah ongkos kirim"
    />

    <label>Jenis Pembayaran:</label>
    <select v-model="onlineForm.paymentMethod" class="input-field mb-2">
      <option disabled value="">Pilih jenis pembayaran</option>
      <option>Cash</option>
      <option>Credit Card</option>
      <option>Transfer Bank</option>
      <option>OVO</option>
      <option>Gopay</option>
    </select>

    <label>Catatan:</label>
    <textarea
      v-model="onlineForm.notes"
      class="input-field mb-4"
      placeholder="Catatan"
    ></textarea>

    <button @click="checkoutOnline" class="btn-green w-full">Checkout</button>
  </ModalKasir>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import ModalKasir from "../components/ModalKasir.vue";
import axios from "axios";
import { useRuntimeConfig } from "#imports";

const searchQueryCustomer = ref("");
const searchQueryPhone = ref("");
const url = ref("");

const openModalHold = ref(false);
const openModalProcess = ref(false);
const openModalPO = ref(false);
const openModalOnline = ref(false);

const barcodeInput = ref("");
let barcodeTimeout = null;

const datatableItems = ref([]);

onMounted(() => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;
  window.addEventListener("keydown", handleBarcodeInput);
});

const subtotal = computed(() => {
  return datatableItems.value.reduce((total, item) => {
    const qty = item.quantity || 0;
    const harga = parseFloat(item.barangentry_harga_net) || 0;
    return total + qty * harga;
  }, 0);
});

function formatRupiah(number) {
  return number.toLocaleString("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  });
}

const holdForm = ref({
  customerName: "",
  cartItems: "",
});
const waitingList = ref([]);
const selectedHold = ref(null);

function saveHold() {
  if (holdForm.value.customerName && holdForm.value.cartItems) {
    waitingList.value.push({
      customerName: holdForm.value.customerName,
      cartItems: holdForm.value.cartItems,
    });
    holdForm.value.customerName = "";
    holdForm.value.cartItems = "";
    selectedHold.value = null;
    alert("Transaksi disimpan ke waiting list");
  } else {
    alert("Isi nama customer dan keranjang barang dulu");
  }
}

function selectHold(index) {
  selectedHold.value = index;
}

const processForm = ref({
  paymentMethod: "",
  notes: "",
});

async function checkoutProcess() {
  if (!processForm.value.paymentMethod) {
    alert("Pilih metode pembayaran");
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
    transaksi_total_harga: subtotal.value,
    transaksi_cara_bayar: processForm.value.paymentMethod,
    transaksi_tipe: "offline",
    transaksi_status: "pending",
    transaksi_catatan: processForm.value.notes,
  };

  try {
    const { data } = await axios.post(`${url.value}/api/transaksi`, payload);

    const transaksi_id = data.data.transaksi_id;
    console.log("asdasdsa", transaksi_id);

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
          transaksidetail_harga_barang: item.barangentry_harga_net,
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

    printToNewTab(transaksi, detailWithNames);

    processForm.value.paymentMethod = "";
    processForm.value.notes = "";
    searchQueryCustomer.value = "";
    searchQueryPhone.value = "";
    datatableItems.value = [];
    openModalProcess.value = false;

    // alert("Transaksi dan detail berhasil disimpan!");
  } catch (err) {
    console.error("Gagal menyimpan transaksi:", err);
    // alert("Terjadi kesalahan saat menyimpan transaksi.");
  }
}

// Pre-Order modal state
const poForm = ref({
  itemId: "",
  ulosName: "",
  accountName: "",
  phone: "",
  totalPayment: 0,
  dp: 0,
  remainingPayment: 0,
  description: "",
  notes: "",
  targetDate: "",
});

function checkoutPO() {
  alert(`Pre-order untuk ${poForm.value.ulosName} dicatat!`);
  Object.keys(poForm.value).forEach((key) => (poForm.value[key] = ""));
  openModalPO.value = false;
}

// Online modal
const onlineForm = ref({
  accountName: "",
  platform: "",
  soldPrice: 0,
  receiverName: "",
  address: "",
  phone: "",
  delivery: "",
  shippingCost: 0,
  paymentMethod: "",
  notes: "",
});

function checkoutOnline() {
  if (!onlineForm.value.paymentMethod) {
    alert("Pilih jenis pembayaran");
    return;
  }
  alert(
    `Transaksi online untuk akun ${onlineForm.value.accountName} berhasil!`
  );
  Object.keys(onlineForm.value).forEach(
    (key) =>
      (onlineForm.value[key] =
        key.includes("Cost") || key === "soldPrice" ? 0 : "")
  );
  openModalOnline.value = false;
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

  // Helper fungsi format Rupiah
  const formatRupiah = (number) => {
    if (!number) return "Rp0";
    return number.toLocaleString("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    });
  };

  // Helper fungsi format Tanggal
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
        <p><strong>Total:</strong> ${formatRupiah(
          data.transaksi_total_harga ||
            items.reduce(
              (acc, i) =>
                acc +
                i.transaksidetail_jumlah_barang *
                  i.transaksidetail_harga_barang,
              0
            )
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
  <div>Subtotal: ${formatRupiah(
    data.transaksi_total_harga ||
      items.reduce(
        (acc, i) =>
          acc +
          i.transaksidetail_jumlah_barang * i.transaksidetail_harga_barang,
        0
      )
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
</style>
