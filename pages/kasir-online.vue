<template>
  <div class="min-h-screen bg-white">
    <div class="py-4 px-4">
      <h1 class="text-2xl font-semibold mb-2">Kasir</h1>
    </div>
    <div class="bg-[#F0F0F0] py-5">
      <h2 class="text-l font-semibold mb-5 px-4">Tambah Online Transaksi</h2>
      <div class="max-w-6xl ml-4 md:ml-8 p-8 bg-white rounded-lg shadow-md">
        <form class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-4">
            <div>
              <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Kode Barang <span
                  class="required">*</span></label>
              <input ref="kodeBarangInput" v-model="form.code_barang" @keyup.enter="handleKodeBarangEnter" type="text"
                class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 autofocus"
                placeholder="Masukkan Kode Barang" />
              <p v-if="errors.code_barang" class="text-red-500 text-sm mt-1">
                {{ errors.code_barang }}
              </p>
            </div>

            <div>
              <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Platform <span
                  class="required">*</span></label>
              <input ref="platformInput" v-model="form.platform" :disabled="!form.code_barang" type="text"
                class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
                placeholder="Masukkan Nama Platform" />
              <p v-if="errors.platform" class="text-red-500 text-sm mt-1">
                {{ errors.platform }}
              </p>
            </div>

            <div>
              <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Nama Penerima <span
                  class="required">*</span></label>
              <input v-model="form.namaPenerima" :disabled="!form.code_barang" type="text"
                class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
                placeholder="Masukkan Nama Penerima" />
              <p v-if="errors.namaPenerima" class="text-red-500 text-sm mt-1">
                {{ errors.namaPenerima }}
              </p>
            </div>

            <div>
              <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Pengiriman <span
                  class="required">*</span></label>
              <input v-model="form.pengiriman" :disabled="!form.code_barang" type="text"
                class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
                placeholder="Masukkan Pengiriman" />
              <p v-if="errors.pengiriman" class="text-red-500 text-sm mt-1">
                {{ errors.pengiriman }}
              </p>
            </div>

            <div>
              <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran <span
                  class="required">*</span></label>
              <select v-model="form.metodePembayaran" :disabled="!form.code_barang"
                class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100">
                <option disabled value="">Pilih metode pembayaran</option>
                <option>Transfer Bank</option>
                <option>COD</option>
                <option>QRIS</option>
                <option>Virtual Account</option>
              </select>
              <p v-if="errors.metodePembayaran" class="text-red-500 text-sm mt-1">
                {{ errors.metodePembayaran }}
              </p>
            </div>

            <div>
              <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Alamat <span
                  class="required">*</span></label>
              <textarea v-model="form.alamat" :disabled="!form.code_barang" rows="3"
                class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
                placeholder="Masukkan Alamat"></textarea>
              <p v-if="errors.alamat" class="text-red-500 text-sm mt-1">
                {{ errors.alamat }}
              </p>
            </div>
          </div>

          <!-- Kolom Kanan -->
          <div class="space-y-4">
            <div>
              <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Nama Akun <span
                  class="required">*</span></label>
              <input v-model="form.namaAkun" :disabled="!form.code_barang" type="text"
                class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
                placeholder="Masukkan Nama Akun" />
              <p v-if="errors.namaAkun" class="text-red-500 text-sm mt-1">
                {{ errors.namaAkun }}
              </p>
            </div>

            <div>
              <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Harga Terjual <span
                  class="required">*</span></label>
              <div class="flex">
                <div
                  class="bg-gray-100 border border-gray-300 rounded-l-md px-3 flex items-center text-gray-600 text-sm">
                  Rp
                </div>
                <input :value="formattedHargaTerjual" @input="onInputHargaTerjual" :disabled="!form.code_barang"
                  type="text"
                  class="w-full border border-gray-300 px-2 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
                  placeholder="Masukkan Harga Terjual" />
                <p v-if="errors.hargaTerjual" class="text-red-500 text-sm mt-1">
                  {{ errors.hargaTerjual }}
                </p>
              </div>
            </div>

            <div>
              <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Nama Telepon <span
                  class="required">*</span></label>
              <input v-model="form.nomor_telepon" :disabled="!form.code_barang" type="text"
                class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
                placeholder="Masukkan Nomor Telepon" />
              <p v-if="errors.nomor_telepon" class="text-red-500 text-sm mt-1">
                {{ errors.nomor_telepon }}
              </p>
            </div>

            <div>
              <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Biaya Pengiriman <span
                  class="required">*</span></label>
              <div class="flex">
                <div
                  class="bg-gray-100 border border-gray-300 rounded-l-md px-3 flex items-center text-gray-600 text-sm">
                  Rp
                </div>
                <input :value="formattedBiayaPengiriman" @input="onInputBiayaPengiriman" :disabled="!form.code_barang"
                  type="text"
                  class="w-full border border-gray-300 px-2 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
                  placeholder="Masukkan Biaya Pengiriman" />
                <p v-if="errors.biayaPengiriman" class="text-red-500 text-sm mt-1">
                  {{ errors.biayaPengiriman }}
                </p>
              </div>
            </div>

            <div class="invisible">
              <label class="block text-sm font-medium">Placeholder</label>
              <input type="text" class="w-full border px-4 py-2 rounded-md" disabled />
            </div>

            <div>
              <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Catatan <span
                  class="required">*</span></label>
              <textarea v-model="form.catatan" :disabled="!form.code_barang" rows="3"
                class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
                placeholder="Masukkan Catatan"></textarea>
              <p v-if="errors.catatan" class="text-red-500 text-sm mt-1">
                {{ errors.catatan }}
              </p>
            </div>
          </div>

          <!-- Tombol -->
          <div class="md:col-span-2 flex justify-end gap-4 mt-8">
            <button type="button" class="bg-gray-300 text-gray-800 px-6 py-2 rounded-md hover:bg-gray-400 transition">
              Batal
            </button>
            <button type="button" @click="submitForm" :disabled="!form.code_barang"
              class="bg-cyan-600 text-white px-6 py-2 rounded-md hover:bg-cyan-700 transition disabled:bg-gray-400">
              Tambah
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from "vue";
import axios from "axios";
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";

const kodeBarangInput = ref(null);
const url = ref("");
const platformInput = ref(null);
const errors = reactive({});

onMounted(() => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;
  kodeBarangInput.value?.focus();
});

const form = reactive({
  code_barang: "",
  platform: "",
  namaPenerima: "",
  pengiriman: "",
  metodePembayaran: "",
  alamat: "",
  namaAkun: "",
  hargaTerjual: "",
  nomor_telepon: "",
  biayaPengiriman: "",
  catatan: "",
});

function handleKodeBarangEnter() {
  platformInput.value?.focus();
}

function formatRupiah(value) {
  if (!value) return "";
  const number = parseInt(value.toString().replace(/\D/g, ""));
  return number.toLocaleString("id-ID");
}

function parseRupiah(value) {
  if (!value) return "";
  return value.toString().replace(/\D/g, "");
}

const formattedHargaTerjual = computed(() => {
  return form.hargaTerjual ? formatRupiah(form.hargaTerjual) : "";
});

function onInputHargaTerjual(e) {
  const raw = parseRupiah(e.target.value);
  form.hargaTerjual = raw;
}

const formattedBiayaPengiriman = computed(() => {
  return form.biayaPengiriman ? formatRupiah(form.biayaPengiriman) : "";
});

function onInputBiayaPengiriman(e) {
  const raw = parseRupiah(e.target.value);
  form.biayaPengiriman = raw;
}

function validate() {
  errors.code_barang = !form.code_barang ? "Kode barang wajib diisi" : "";
  errors.platform = !form.platform ? "Platform wajib diisi" : "";
  errors.namaPenerima = !form.namaPenerima ? "Nama Penerima wajib diisi" : "";
  errors.pengiriman = !form.pengiriman ? "Pengiriman wajib diisi" : "";
  errors.metodePembayaran = !form.metodePembayaran
    ? "Metode Pembayaran wajib diisi"
    : "";
  errors.alamat = !form.alamat ? "Alamat wajib diisi" : "";
  errors.namaAkun = !form.namaAkun ? "Nama Akun wajib diisi" : "";
  errors.hargaTerjual = !form.hargaTerjual ? "Harga Terjual wajib diisi" : "";
  errors.nomor_telepon = !form.nomor_telepon ? "Nomor Telepon wajib diisi" : "";
  errors.biayaPengiriman = !form.biayaPengiriman
    ? "Biaaya pembayaran wajib diisi"
    : "";
  errors.catatan = !form.catatan ? "Catatan wajib diisi" : "";

  return Object.values(errors).every((err) => !err);
}

async function submitForm() {
  if (!validate()) {
    Swal.fire({
      title: "Gagal!",
      text: "Silakan lengkapi semua field wajib.",
      icon: "error",
      confirmButtonText: "OK",
      timer: 3000,
      timerProgressBar: true,
    });
    return;
  }

  const payloadTransaksi = {
    transaksi_nama_customer: form.namaPenerima,
    transaksi_nomor_telepon: form.nomor_telepon,
    transaksi_jumlah_barang: 1,
    transaksi_total_harga: form.hargaTerjual,
    transaksi_cara_bayar: form.metodePembayaran,
    transaksi_tipe: "Online",
    transaksi_status: "Pending",
    transaksi_catatan: form.catatan,
  };

  try {
    const token = sessionStorage.getItem("auth_token")
    const responseData = await axios.get(
      `${url.value}/api/entrybarang/getDataKasir/` + form.code_barang, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    }
    );
    const dataEntry = responseData.data.data[0];

    const transaksiData = await axios.post(
      `${url.value}/api/transaksi`,
      payloadTransaksi, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    }
    );
    const transaksi_id = transaksiData.data.data.transaksi_id;
    try {
      const payloadTransaksiDetail = {
        transaksidetail_transaksi_id: transaksi_id,
        transaksidetail_barang_id: dataEntry.barangentry_id,
        transaksidetail_jumlah_barang: 1,
        transaksidetail_harga_barang: dataEntry.barangentry_harga_net,
      };

      await axios.post(
        `${url.value}/api/transaksi-detail`,
        payloadTransaksiDetail, {
        headers: {
          "Authorization": `Bearer ${token}`,
          "Content-Type": "application/json",
        }
      }
      );

      const pengirimanPayload = {
        pengirimanBarang_transaksi_id: transaksi_id,
        pengirimanBarang_nama_penerima: form.namaPenerima,
        pengirimanBarang_akun_penerima: form.namaAkun,
        pengirimanBarang_no_telepon: form.nomor_telepon,
        pengirimanBarang_harga_kirim_barang: form.biayaPengiriman,
        pengirimanBarang_jenis_pengiriman_barang: form.pengiriman,
        pengirimanBarang_alamat_pengiriman_barang: form.alamat,
        pengirimanBarang_catatan: form.catatan,
        pengirimanBarang_status: "Proses",
      };

      await axios.post(`${url.value}/api/pengiriman-barang`, pengirimanPayload, {
        headers: {
          "Authorization": `Bearer ${token}`,
          "Content-Type": "application/json",
        }
      });

      Swal.fire({
        title: "Sukses!",
        text: "Berhasil Melakukan Online Transaksi",
        icon: "success",
        confirmButtonText: "OK",
        timer: 3000,
        timerProgressBar: true,
      });
    } catch (err) {
      console.error("Error melakukan transaksi", err);
    }

    form.code_barang = "";
    form.namaPenerima = "";
    form.nomor_telepon = "";
    form.platform = "";
    form.pengiriman = "";
    form.metodePembayaran = "";
    form.alamat = "";
    form.namaAkun = "";
    form.hargaTerjual = "";
    form.namaAkun = "";
    form.biayaPengiriman = "";
    form.catatan = "";
  } catch (err) {
    console.error("Error", err);
  }
}
</script>

<style scoped>
* {
  font-family: "Nunito", sans-serif;
}

.judul-label {
  font-weight: 700;
}

input,
textarea,
select {
  border-color: #e4e6fc !important;
  background-color: #fdfdff !important;
}

input:focus,
textarea:focus,
select:focus {
  border-color: #e4e6fc !important;
  box-shadow: 0 0 0 2px rgba(228, 230, 252, 0.5);
}

input:disabled,
textarea:disabled,
select:disabled {
  background-color: #f3f4f6 !important;
  color: #6b7280 !important;
  border-color: #e5e7eb !important;
}
</style>
