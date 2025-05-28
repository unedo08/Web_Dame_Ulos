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
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item, index) in datatableItems" :key="index">
          <td>{{ index + 1 }}</td>
          <td>{{ item.name }}</td>
          <td>{{ item.quantity }}</td>
          <td>{{ item.price }}</td>
        </tr>
      </tbody>
    </table>
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
import { ref, onMounted } from "vue";
import ModalKasir from "../components/ModalKasir.vue";
import axios from "axios";

const searchQueryCustomer = ref("");
const searchQueryPhone = ref("");

const openModalHold = ref(false);
const openModalProcess = ref(false);
const openModalPO = ref(false);
const openModalOnline = ref(false);

const barcodeInput = ref("")
let barcodeTimeout = null

const datatableItems = ref([]) // <-- ini untuk datatable
// Hold modal state
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

function checkoutProcess() {
  if (!processForm.value.paymentMethod) {
    alert("Pilih metode pembayaran");
    return;
  }
  alert(
    `Transaksi selesai dengan pembayaran ${processForm.value.paymentMethod}\nCatatan: ${processForm.value.notes}`
  );
  processForm.value.paymentMethod = "";
  processForm.value.notes = "";
  openModalProcess.value = false;
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

// Online modal state
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
  if (barcodeTimeout) clearTimeout(barcodeTimeout)

  // abaikan jika bukan karakter alfanumerik atau enter
  if (e.key === 'Enter') {
    const scannedCode = barcodeInput.value.trim()
    if (scannedCode) {
      fetchDataByBarcode(scannedCode)
    }
    barcodeInput.value = ''
  } else if (/^[a-zA-Z0-9]$/.test(e.key)) {
    barcodeInput.value += e.key
  }

  // reset jika tidak ada input 300ms
  barcodeTimeout = setTimeout(() => {
    barcodeInput.value = ''
  }, 300)
}

const fetchDataByBarcode = async (code) => {
  try {
    const { data } = await axios.get(`/api/entrybarang/getDataByCode/${code}`)
    if (data) {
      datatableItems.value.push({
        name: data.name,
        quantity: 1,
        price: data.price
      })
    } else {
      alert('Data tidak ditemukan')
    }
  } catch (error) {
    console.error(error)
    alert('Gagal mengambil data barang')
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleBarcodeInput)
})
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
