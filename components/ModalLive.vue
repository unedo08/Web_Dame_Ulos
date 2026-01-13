<template>
  <div v-if="visible" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-[55%] w-full overflow-y-auto p-6 relative">
      <h2 class="text-xl font-semibold mb-4">Tambah Online Transaksi</h2>

      <form class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-4">
          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Kode Barang <span
                class="required">*</span></label>
            <input ref="kodeBarangInput" v-model="form.code_barang" @keyup.enter="handleKodeBarangEnter" type="text"
              :disabled="isKodeBarangDisabled"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-cyan-500"
              placeholder="Masukkan Kode Barang" />
            <p v-if="errors.code_barang" class="text-red-500 text-sm mt-1">{{ errors.code_barang }}</p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Platform <span
                class="required">*</span></label>
            <input ref="platformInput" v-model="form.platform" :disabled="!form.code_barang" type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
              placeholder="Masukkan Nama Platform" />
            <p v-if="errors.platform" class="text-red-500 text-sm mt-1">{{ errors.platform }}</p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Nama Penerima <span
                class="required">*</span></label>
            <input v-model="form.namaPenerima" :disabled="!form.code_barang" type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-cyan-500"
              placeholder="Masukkan Nama Penerima" />
            <p v-if="errors.namaPenerima" class="text-red-500 text-sm mt-1">{{ errors.namaPenerima }}</p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Pengiriman <span
                class="required">*</span></label>
            <input v-model="form.pengiriman" :disabled="!form.code_barang" type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-cyan-500"
              placeholder="Masukkan Pengiriman" />
            <p v-if="errors.pengiriman" class="text-red-500 text-sm mt-1">{{ errors.pengiriman }}</p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran <span
                class="required">*</span></label>

            <select v-model="form.metodePembayaran" :disabled="!form.code_barang"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100">
              <option disabled value="">Pilih metode pembayaran</option>

              <option v-for="cb in caraBayarList" :key="cb.id" :value="cb.carabayar_kode">
                {{ cb.carabayar_nama }}
              </option>
            </select>

            <p v-if="errors.metodePembayaran" class="text-red-500 text-sm mt-1">{{ errors.metodePembayaran }}</p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Alamat <span
                class="required">*</span></label>
            <textarea v-model="form.alamat" :disabled="!form.code_barang" rows="3"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-cyan-500"
              placeholder="Masukkan Alamat"></textarea>
            <p v-if="errors.alamat" class="text-red-500 text-sm mt-1">{{ errors.alamat }}</p>
          </div>
        </div>

        <div class="space-y-4">
          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Nama Akun <span
                class="required">*</span></label>
            <input v-model="form.namaAkun" :disabled="!form.code_barang" type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-cyan-500"
              placeholder="Masukkan Nama Akun" />
            <p v-if="errors.namaAkun" class="text-red-500 text-sm mt-1">{{ errors.namaAkun }}</p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Harga Terjual <span
                class="required">*</span></label>
            <div class="flex">
              <span
                class="bg-gray-100 border border-gray-300 rounded-l-md px-3 flex items-center text-gray-600">Rp</span>
              <input :value="formattedHargaTerjual" @input="onInputHargaTerjual" :disabled="!form.code_barang"
                type="text" class="w-full border border-gray-300 px-2 py-2 focus:ring-2 focus:ring-cyan-500"
                placeholder="Masukkan Harga Terjual" />
            </div>
            <p v-if="errors.hargaTerjual" class="text-red-500 text-sm mt-1">{{ errors.hargaTerjual }}</p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Nomor Telepon <span
                class="required">*</span></label>
            <input v-model="form.nomor_telepon" :disabled="!form.code_barang" type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-cyan-500"
              placeholder="Masukkan Nomor Telepon" />
            <p v-if="errors.nomor_telepon" class="text-red-500 text-sm mt-1">{{ errors.nomor_telepon }}</p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Biaya Pengiriman <span
                class="required">*</span></label>
            <div class="flex">
              <span
                class="bg-gray-100 border border-gray-300 rounded-l-md px-3 flex items-center text-gray-600">Rp</span>
              <input :value="formattedBiayaPengiriman" @input="onInputBiayaPengiriman" :disabled="!form.code_barang"
                type="text" class="w-full border border-gray-300 px-2 py-2 focus:ring-2 focus:ring-cyan-500"
                placeholder="Masukkan Biaya Pengiriman" />
            </div>
            <p v-if="errors.biayaPengiriman" class="text-red-500 text-sm mt-1">{{ errors.biayaPengiriman }}</p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Catatan <span
                class="required">*</span></label>
            <textarea v-model="form.catatan" :disabled="!form.code_barang" rows="3"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-cyan-500"
              placeholder="Masukkan Catatan"></textarea>
            <p v-if="errors.catatan" class="text-red-500 text-sm mt-1">{{ errors.catatan }}</p>
          </div>
        </div>
        <div class="md:col-span-2 flex justify-end gap-4 mt-8">
          <button type="button" @click="batalLive()"
            class="bg-gray-300 text-gray-800 px-6 py-2 rounded-md hover:bg-gray-400 transition">
            Batal
          </button>

          <button type="button" @click="submitForm" :disabled="!form.code_barang"
            class="bg-cyan-600 text-white px-6 py-2 rounded-md hover:bg-cyan-700 transition disabled:bg-gray-400">
            <span v-if="!isSubmitting">Tambah</span>
            <span v-else>Memproses...</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from "vue";
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";
const { $api } = useNuxtApp();
const props = defineProps({
  visible: Boolean,
});

const emit = defineEmits(["close"]);

const kodeBarangInput = ref(null);
const platformInput = ref(null);
const isKodeBarangDisabled = ref(false);
const isSubmitting = ref(false);
const caraBayarList = ref([]);
const url = ref("");

const errors = reactive({});

onMounted(async () => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;

  kodeBarangInput.value?.focus();

  try {
    const res = await $api.get(`${url.value}/api/carabayar`);
    caraBayarList.value = res.data.data;
  } catch (err) {
    console.error("Gagal fetch cara bayar:", err);
  }
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
  if (form.code_barang.trim() !== "") {
    isKodeBarangDisabled.value = true;
    platformInput.value?.focus();
  }
}

function formatRupiah(value) {
  if (!value) return "";
  const number = parseInt(value.toString().replace(/\D/g, ""));
  return number.toLocaleString("id-ID");
}

function parseRupiah(value) {
  return value ? value.toString().replace(/\D/g, "") : "";
}

const formattedHargaTerjual = computed(() =>
  form.hargaTerjual ? formatRupiah(form.hargaTerjual) : ""
);

function onInputHargaTerjual(e) {
  form.hargaTerjual = parseRupiah(e.target.value);
}

const formattedBiayaPengiriman = computed(() =>
  form.biayaPengiriman ? formatRupiah(form.biayaPengiriman) : ""
);

function onInputBiayaPengiriman(e) {
  form.biayaPengiriman = parseRupiah(e.target.value);
}

function validate() {
  errors.code_barang = form.code_barang ? "" : "Kode barang wajib diisi";
  errors.platform = form.platform ? "" : "Platform wajib diisi";
  errors.namaPenerima = form.namaPenerima ? "" : "Nama Penerima wajib diisi";
  errors.pengiriman = form.pengiriman ? "" : "Pengiriman wajib diisi";
  errors.metodePembayaran = form.metodePembayaran ? "" : "Metode Pembayaran wajib diisi";
  errors.alamat = form.alamat ? "" : "Alamat wajib diisi";
  errors.namaAkun = form.namaAkun ? "" : "Nama Akun wajib diisi";
  errors.hargaTerjual = form.hargaTerjual ? "" : "Harga Terjual wajib diisi";
  errors.nomor_telepon = form.nomor_telepon ? "" : "Nomor Telepon wajib diisi";
  errors.biayaPengiriman = form.biayaPengiriman ? "" : "Biaya Pengiriman wajib diisi";
  errors.catatan = form.catatan ? "" : "Catatan wajib diisi";

  return Object.values(errors).every((e) => !e);
}

async function submitForm() {
  if (isSubmitting.value) return;
  isSubmitting.value = true;

  if (!validate()) {
    Swal.fire("Gagal!", "Silakan lengkapi semua field wajib.", "error");
    isSubmitting.value = false;
    return;
  }

  try {
    const entry = await $api.get(`${url.value}/api/entrybarang/getDataKasir/${form.code_barang}`);
    const barangData = entry.data.data[0];

    const transaksi = await $api.post(`${url.value}/api/transaksi`, {
      transaksi_nama_customer: form.namaPenerima,
      transaksi_nomor_telepon: form.nomor_telepon,
      transaksi_jumlah_barang: 1,
      transaksi_total_harga: form.hargaTerjual,
      transaksi_cara_bayar: form.metodePembayaran,
      transaksi_tipe: "Online",
      transaksi_status: "Pending",
      transaksi_catatan: form.catatan,
    });

    const transaksi_id = transaksi.data.data.transaksi_id;

    await $api.post(`${url.value}/api/transaksi-detail`, {
      transaksidetail_transaksi_id: transaksi_id,
      transaksidetail_barang_id: barangData.barangentry_id,
      transaksidetail_jumlah_barang: 1,
      transaksidetail_harga_barang: barangData.barangentry_harga_net,
    });

    await $api.post(`${url.value}/api/pengiriman-barang`, {
      pengirimanBarang_transaksi_id: transaksi_id,
      pengirimanBarang_nama_penerima: form.namaPenerima,
      pengirimanBarang_akun_penerima: form.namaAkun,
      pengirimanBarang_no_telepon: form.nomor_telepon,
      pengirimanBarang_harga_kirim_barang: form.biayaPengiriman,
      pengirimanBarang_jenis_pengiriman_barang: form.pengiriman,
      pengirimanBarang_alamat_pengiriman_barang: form.alamat,
      pengirimanBarang_catatan: form.catatan,
      pengirimanBarang_status: "Proses",
    });

    Swal.fire({
      title: "Sukses!",
      text: "Berhasil Melakukan Online Transaksi",
      icon: "success",
      timer: 2000,
    }).then(() => {
      emit("close");
    });

    resetForm();

  } catch (err) {
    console.error(err);
    Swal.fire("Gagal!", "Terjadi kesalahan pada server.", "error");
  }

  isSubmitting.value = false;
}

function resetForm() {
  Object.assign(form, {
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

  isKodeBarangDisabled.value = false;
}

function batalLive() {
  resetForm();
  emit("close");
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
  border-color: #e4e6fc;
  background-color: #fdfdff;
}

input:focus,
textarea:focus,
select:focus {
  border-color: #cdd4ff;
  box-shadow: 0 0 0 2px rgba(205, 212, 255, 0.5);
}

.disabled,
select:disabled {
  background-color: #f3f4f6;
}
</style>
