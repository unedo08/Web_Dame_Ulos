<template>
  <div class="min-h-screen bg-white">
    <div class="py-4 px-4">
      <h1 class="text-2xl font-semibold mb-2">Kasir</h1>
    </div>
    <div class="bg-[#F0F0F0] py-5">
      <h2 class="text-l font-semibold mb-5 px-4">Tambah Pre-Order Transaksi</h2>
      <div class="max-w-6xl ml-4 md:ml-8 p-8 bg-white rounded-lg shadow-md">
        <form class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- KIRI -->
          <div class="space-y-4">
            <div>
              <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Kode Barang <span
                  class="required">*</span></label>
              <input ref="platformInput" v-model="form.code_barang" @keyup.enter="handleKodeBarangEnter" type="text"
                class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 autofocus"
                placeholder="Masukkan Kode Barang" />
              <p v-if="errors.code_barang" class="text-red-500 text-sm mt-1">
                {{ errors.code_barang }}
              </p>
            </div>

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
              <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Target Selesai <span
                  class="required">*</span></label>
              <input v-model="form.targetSelesai" :disabled="!form.code_barang" type="date"
                class="w-full border border-gray-300 rounded-md px-4 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100" />
              <p v-if="errors.targetSelesai" class="text-red-500 text-sm mt-1">
                {{ errors.targetSelesai }}
              </p>
            </div>
            <div>
              <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Uang Muka (DP) <span
                  class="required">*</span></label>
              <div class="flex">
                <div
                  class="bg-gray-100 border border-gray-300 rounded-l-md px-3 flex items-center text-gray-600 text-sm">
                  Rp
                </div>
                <input :value="formattedUangMuka" @input="onInputUangMuka" :disabled="!form.code_barang" type="text"
                  class="w-full border border-gray-300 px-2 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
                  placeholder="Masukkan Uang Muka (DP)" />
                <p v-if="errors.dp" class="text-red-500 text-sm mt-1">
                  {{ errors.dp }}
                </p>
              </div>
            </div>

            <div>
              <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Deskripsi Ulos <span
                  class="required">*</span></label>
              <textarea v-model="form.deskripsiUlos" :disabled="!form.code_barang" rows="3"
                class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
                placeholder="Masukkan Deskripsi Ulos"></textarea>
              <p v-if="errors.deskripsiUlos" class="text-red-500 text-sm mt-1">
                {{ errors.deskripsiUlos }}
              </p>
            </div>

            <div>
              <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Gambar</label>
              <div class="w-full border border-dashed border-gray-400 rounded-md p-4 flex flex-col items-start">
                <input type="file" @change="handleFileUpload" />
                <p class="text-xs text-gray-500 mt-1">Ukuran Maksimal: 5MB</p>
              </div>
            </div>
          </div>

          <!-- KANAN -->
          <div class="space-y-4">
            <div>
              <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Nama Ulos <span
                  class="required">*</span></label>
              <input v-model="form.namaUlos" :disabled="!form.code_barang" type="text"
                class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
                placeholder="Masukkan Nama Ulos" />
              <p v-if="errors.namaUlos" class="text-red-500 text-sm mt-1">
                {{ errors.namaUlos }}
              </p>
            </div>

            <div>
              <label class="judul-label block text-sm font-medium text-gray-700 mb-1">No Telepon <span
                  class="required">*</span></label>
              <input v-model="form.nomor_telepon" :disabled="!form.code_barang" type="text"
                class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
                placeholder="Masukkan Nomor Telepon" />
              <p v-if="errors.nomor_telepon" class="text-red-500 text-sm mt-1">
                {{ errors.nomor_telepon }}
              </p>
            </div>

            <div>
              <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
                Total Pembayaran <span class="required">*</span>
              </label>
              <div class="flex">
                <div
                  class="bg-gray-100 border border-gray-300 rounded-l-md px-3 flex items-center text-gray-600 text-sm">
                  Rp
                </div>
                <input :value="formattedTotalPembayaran" @input="onInputTotalPembayaran" :disabled="!form.code_barang"
                  type="text"
                  class="w-full border-t border-b border-r border-gray-300 rounded-r-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
                  placeholder="Masukkan Total Pembayaran" />
              </div>
              <p v-if="errors.totalPembayaran" class="text-red-500 text-sm mt-1">
                {{ errors.totalPembayaran }}
              </p>
            </div>

            <div>
              <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Sisa Pembayaran <span
                  class="required">*</span></label>
              <div class="flex">
                <div
                  class="bg-gray-100 border border-gray-300 rounded-l-md px-3 flex items-center text-gray-600 text-sm">
                  Rp
                </div>
                <input :value="formattedSisaPembayaran" @input="onInputSisaPembayaran" :disabled="!form.code_barang"
                  type="text"
                  class="w-full border border-gray-300 px-2 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
                  placeholder="Masukkan Sisa Pembayaran" />
                <p v-if="errors.sisaPembayaran" class="text-red-500 text-sm mt-1">
                  {{ errors.sisaPembayaran }}
                </p>
              </div>
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
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";

const kodeBarangInput = ref(null);
const url = ref("");
const platformInput = ref(null);
const errors = reactive({});
const { $api } = useNuxtApp();

onMounted(() => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;
  kodeBarangInput.value?.focus();
});

const form = reactive({
  code_barang: "",
  namaAkun: "",
  targetSelesai: "",
  dp: "",
  deskripsiUlos: "",
  namaUlos: "",
  nomor_telepon: "",
  totalPembayaran: "",
  sisaPembayaran: "",
  catatan: "",
  gambar: null,
});

function handleKodeBarangEnter() {
  platformInput.value?.focus();
}
function handleFileUpload(event) {
  form.gambar = event.target.files[0];
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

const formattedUangMuka = computed(() => {
  return form.dp ? formatRupiah(form.dp) : "";
});

function onInputUangMuka(e) {
  const raw = parseRupiah(e.target.value);
  form.dp = raw;
}

const formattedTotalPembayaran = computed(() => {
  return form.totalPembayaran ? formatRupiah(form.totalPembayaran) : "";
});

function onInputTotalPembayaran(e) {
  const raw = parseRupiah(e.target.value);
  form.totalPembayaran = raw;
}

const formattedSisaPembayaran = computed(() => {
  return form.sisaPembayaran ? formatRupiah(form.sisaPembayaran) : "";
});

function onInputSisaPembayaran(e) {
  const raw = parseRupiah(e.target.value);
  form.sisaPembayaran = raw;
}

function toDatetimeString(dateStr) {
  const date = new Date(dateStr);
  const pad = (n) => (n < 10 ? "0" + n : n);
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(
    date.getDate()
  )} 00:00:00`;
}

function validate() {
  errors.code_barang = !form.code_barang ? "Kode barang wajib diisi" : "";
  errors.namaAkun = !form.namaAkun ? "Nama akun wajib diisi" : "";
  errors.targetSelesai = !form.targetSelesai
    ? "Target selesai wajib diisi"
    : "";
  errors.dp = !form.dp ? "Uang muka wajib diisi" : "";
  errors.deskripsiUlos = !form.deskripsiUlos
    ? "Deskripsi ulos wajib diisi"
    : "";
  errors.namaUlos = !form.namaUlos ? "Nama ulos wajib diisi" : "";
  errors.nomor_telepon = !form.nomor_telepon ? "No telepon wajib diisi" : "";
  errors.totalPembayaran = !form.totalPembayaran
    ? "Total pembayaran wajib diisi"
    : "";
  errors.sisaPembayaran = !form.sisaPembayaran
    ? "Sisa pembayaran wajib diisi"
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

  // const payloadTransaksi = {
  //   transaksi_nama_customer: form.namaAkun,
  //   transaksi_nomor_telepon: form.nomor_telepon,
  //   transaksi_jumlah_barang: 1,
  //   transaksi_total_harga: form.totalPembayaran,
  //   transaksi_cara_bayar: form.metodePembayaran,
  //   transaksi_tipe: "Pre-Order",
  //   transaksi_status: "Pending",
  //   transaksi_catatan: form.catatan,
  // };

  try {
    // const responseData = await $api.get(
    //   `${url.value}/api/entrybarang/getDataKasir/` + form.code_barang
    // );
    // const dataEntry = responseData.data.data[0];

    // const transaksiData = await $api.post(
    //   `${url.value}/api/transaksi`,
    //   payloadTransaksi
    // );
    // const transaksi_id = transaksiData.data.data.transaksi_id;
    // try {
    // const payloadTransaksiDetail = {
    //   transaksidetail_transaksi_id: transaksi_id,
    //   transaksidetail_barang_id: dataEntry.barangentry_id,
    //   transaksidetail_jumlah_barang: 1,
    //   transaksidetail_harga_barang: dataEntry.barangentry_harga_net,
    // };

    // await $api.post(
    //   `${url.value}/api/transaksi-detail`,
    //   payloadTransaksiDetail
    // );
    const formData = new FormData();
    formData.append("preOrderBarang_transaksi_id", "");
    formData.append("preOrderBarang_nama_barang", form.namaUlos);
    formData.append("preOrderBarang_nama_akun", form.namaAkun);
    formData.append("preOrderBarang_no_telepon", form.nomor_telepon);
    formData.append(
      "preOrderBarang_target_selesai",
      toDatetimeString(form.targetSelesai)
    );
    formData.append("preOrderBarang_total_pembayaran", form.totalPembayaran);
    formData.append("preOrderBarang_uang_muka", form.dp);
    formData.append("preOrderBarang_sisa_pembayaran", form.sisaPembayaran);
    formData.append("preOrderBarang_deskripsi_barang", form.deskripsiUlos);
    formData.append("preOrderBarang_catatan", form.catatan);
    formData.append("preOrderBarang_path_gambar", "test");

    await $api.post(`${url.value}/api/pre-order-barang`, formData);

    Swal.fire({
      title: "Sukses!",
      text: "Berhasil Melakukan Pre-Order",
      icon: "success",
      confirmButtonText: "OK",
      timer: 3000,
      timerProgressBar: true,
    });

    // const pengirimanPayload = {
    //   pengirimanBarang_transaksi_id: transaksi_id,
    //   pengirimanBarang_nama_penerima: form.namaAkun,
    //   pengirimanBarang_akun_penerima: form.namaAkun,
    //   pengirimanBarang_no_telepon: form.nomor_telepon,
    //   pengirimanBarang_harga_kirim_barang: form.biayaPengiriman,
    //   pengirimanBarang_jenis_pengiriman_barang: form.pengiriman,
    //   pengirimanBarang_alamat_pengiriman_barang: form.alamat,
    //   pengirimanBarang_catatan: form.catatan,
    //   pengirimanBarang_status: "Proses",
    // };

    // await $api.post(`${url.value}/api/pengiriman-barang`, pengirimanPayload);
    // } catch (err) {
    //   console.error("Error melakukan transaksi", err);
    // }
    form.code_barang = "";
    form.namaAkun = "";
    form.targetSelesai = "";
    form.dp = "";
    form.deskripsiUlos = "";
    form.namaUlos = "";
    form.nomor_telepon = "";
    form.totalPembayaran = "";
    form.sisaPembayaran = "";
    form.catatan = "";
    form.alamat = "";
    form.gambar = null;
  } catch (err) {
    console.error("Error", err);
  }
}
</script>

<style>
.required {
  color: red;
}
</style>
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
