<template>
  <div v-if="visible" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 z-50 pt-[60px]">
    <div class="bg-white rounded-lg shadow-xl max-w-[55%] w-full overflow-y-auto p-6 relative">
      <h2 class="text-xl font-semibold mb-4">Tambah Pre-Order Transaksi</h2>
      <form class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-4">
          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              Kode Barang <span class="required">*</span>
            </label>
            <input ref="platformInput" v-model="form.code_barang" type="text"
              class="kode_po w-full border border-gray-300 bg-gray-500 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
              placeholder="Masukkan Kode Barang" readonly />
            <p v-if="errors.code_barang" class="text-red-500 text-sm mt-1">{{ errors.code_barang }}</p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              Nama Akun <span class="required">*</span>
            </label>
            <input v-model="form.namaAkun" type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
              placeholder="Masukkan Nama Akun" />
            <p v-if="errors.namaAkun" class="text-red-500 text-sm mt-1">{{ errors.namaAkun }}</p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              Target Selesai <span class="required">*</span>
            </label>
            <input v-model="form.targetSelesai" type="date"
              class="w-full border border-gray-300 rounded-md px-4 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100" />
            <p v-if="errors.targetSelesai" class="text-red-500 text-sm mt-1">{{ errors.targetSelesai }}</p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              Uang Muka (DP) <span class="required">*</span>
            </label>
            <div class="flex">
              <div class="bg-gray-100 border border-gray-300 rounded-l-md px-3 flex items-center text-gray-600 text-sm">
                Rp</div>
              <input :value="formattedUangMuka" @input="onInputUangMuka" type="text"
                class="w-full border border-gray-300 px-2 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
                placeholder="Masukkan Uang Muka (DP)" />
            </div>
            <p v-if="errors.dp" class="text-red-500 text-sm mt-1">{{ errors.dp }}</p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              Deskripsi Ulos <span class="required">*</span>
            </label>
            <textarea v-model="form.deskripsiUlos" rows="3"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
              placeholder="Masukkan Deskripsi Ulos"></textarea>
            <p v-if="errors.deskripsiUlos" class="text-red-500 text-sm mt-1">{{ errors.deskripsiUlos }}</p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">Gambar</label>
            <div class="w-full border border-dashed border-gray-400 rounded-md p-4 flex flex-col items-start">
              <input type="file" accept="image/*" @change="handleFileUpload" />
              <p class="text-xs text-gray-500 mt-1">Ukuran Maksimal: 5MB</p>

              <div v-if="imagePreview" class="mt-3">
                <img :src="imagePreview" class="max-h-40 rounded shadow border" />
              </div>

              <div v-if="uploadProgress > 0" class="w-full bg-gray-200 h-2 rounded-full mt-3">
                <div class="bg-green-600 h-2 rounded-full" :style="{ width: uploadProgress + '%' }"></div>
              </div>
              <p v-if="errors.compressImage" class="text-red-500 text-sm mt-1">{{ errors.compressImage }}</p>
            </div>
          </div>
        </div>

        <div class="space-y-4">
          <div>
            <label class="judul-label block text-sm font-medium mb-1">
              Nama Ulos <span class="required">*</span>
            </label>
            <input v-model="form.namaUlos" type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
              placeholder="Masukkan Nama Ulos" />
            <p v-if="errors.namaUlos" class="text-red-500 text-sm mt-1">{{ errors.namaUlos }}</p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium mb-1">
              No Telepon <span class="required">*</span>
            </label>
            <input v-model="form.nomor_telepon" type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
              placeholder="Masukkan Nomor Telepon" />
            <p v-if="errors.nomor_telepon" class="text-red-500 text-sm mt-1">{{ errors.nomor_telepon }}</p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium mb-1">
              Harga Terjual <span class="required">*</span>
            </label>
            <div class="flex">
              <div class="bg-gray-100 border border-gray-300 rounded-l-md px-3 flex items-center text-gray-600 text-sm">
                Rp
              </div>
              <input :value="formattedTotalPembayaran" @input="onInputTotalPembayaran" type="text"
                class="w-full border-t border-b border-r border-gray-300 rounded-r-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
                placeholder="Masukkan Total Pembayaran" />
            </div>
            <p v-if="errors.hargaNet" class="text-red-500 text-sm mt-1">{{ errors.hargaNet }}</p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium mb-1">
              Sisa Pembayaran <span class="required">*</span>
            </label>
            <div class="flex">
              <div class="bg-gray-100 border border-gray-300 rounded-l-md px-3 flex items-center text-gray-600 text-sm">
                Rp
              </div>
              <input :value="formattedSisaPembayaran" type="text"
                class="sisa-pembayaran w-full border border-gray-300 px-2 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
                readonly />
            </div>
            <p v-if="errors.sisaPembayaran" class="text-red-500 text-sm mt-1">{{ errors.sisaPembayaran }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Metode Pembayaran <span class="required">*</span>
            </label>

            <select v-model="form.metodePembayaran"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500">

              <option disabled value="">Pilih metode pembayaran</option>

              <option v-for="cb in caraBayarList" :key="cb.id" :value="cb.carabayar_kode">
                {{ cb.carabayar_nama }}
              </option>
            </select>

            <p v-if="errors.metodePembayaran" class="text-red-500 text-sm mt-1">{{ errors.metodePembayaran }}</p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium mb-1">
              Catatan <span class="required">*</span>
            </label>
            <textarea v-model="form.catatan" rows="3"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
              placeholder="Masukkan Catatan"></textarea>
            <p v-if="errors.catatan" class="text-red-500 text-sm mt-1">{{ errors.catatan }}</p>
          </div>
        </div>

        <div class="md:col-span-2 flex justify-end gap-4 mt-8">
          <button type="button" @click="batalPreOrder()"
            class="bg-gray-300 text-gray-800 px-6 py-2 rounded-md hover:bg-gray-400 transition">
            Batal
          </button>

          <button type="button" @click="submitForm"
            class="bg-cyan-600 text-white px-6 py-2 rounded-md hover:bg-cyan-700 transition disabled:bg-gray-400">
            Tambah
          </button>
        </div>

      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, watch, onMounted } from "vue";
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";
const { $api } = useNuxtApp();
const props = defineProps({ visible: Boolean });
const emit = defineEmits(["close"]);

const url = ref("");
const caraBayarList = ref([]);

onMounted(async () => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;

  const res = await $api.get(`${url.value}/api/carabayar`);
  caraBayarList.value = res.data.data;
});

const form = reactive({
  code_barang: "",
  namaAkun: "",
  targetSelesai: "",
  dp: "",
  deskripsiUlos: "",
  namaUlos: "",
  nomor_telepon: "",
  hargaNet: "",
  metodePembayaran: "",
  catatan: "",
  gambar: null,
  gambarCompressed: null,
  barangentryID: "",
});

const errors = reactive({});
const imagePreview = ref(null);
const uploadProgress = ref(0);

async function kode_generator() {
  const kodePO = await $api.get(`${url.value}/api/pre-order-barang/kode-generator`);
  form.code_barang = kodePO.data.data.code_nama;
}

function parseRupiah(val) {
  return val.toString().replace(/\D/g, "");
}

function formatRupiah(val) {
  const n = parseInt(val.toString().replace(/\D/g, "")) || 0;
  return n.toLocaleString("id-ID");
}

const formattedUangMuka = computed(() => formatRupiah(form.dp));
function onInputUangMuka(e) {
  form.dp = parseRupiah(e.target.value);
}

const formattedTotalPembayaran = computed(() => formatRupiah(form.hargaNet));
function onInputTotalPembayaran(e) {
  form.hargaNet = parseRupiah(e.target.value);
}

const sisaPembayaran = computed(() => (parseInt(form.hargaNet) || 0) - (parseInt(form.dp) || 0));
const formattedSisaPembayaran = computed(() => formatRupiah(sisaPembayaran.value));

async function handleFileUpload(event) {
  const file = event.target.files[0];
  if (!file) return;

  form.gambar = file;
  imagePreview.value = URL.createObjectURL(file);

  form.gambarCompressed = await compressImage(file);
}

function compressImage(file) {
  return new Promise((resolve) => {
    const img = new Image();
    img.src = URL.createObjectURL(file);

    img.onload = () => {
      const canvas = document.createElement("canvas");
      const maxWidth = 1000;
      const scale = maxWidth / img.width;

      canvas.width = maxWidth;
      canvas.height = img.height * scale;

      const ctx = canvas.getContext("2d");
      ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

      canvas.toBlob(
        (blob) => {
          resolve(renameImage(blob));
        },
        "image/jpeg",
        0.75
      );
    };
  });
}

function renameImage(blob) {
  const timestamp = Date.now();
  const filename = `PO-${form.code_barang}-${timestamp}.jpg`;
  return new File([blob], filename, { type: "image/jpeg" });
}

function validate() {
  errors.code_barang = !form.code_barang ? "Kode barang wajib diisi" : "";
  errors.namaAkun = !form.namaAkun ? "Nama akun wajib diisi" : "";
  errors.targetSelesai = !form.targetSelesai ? "Tanggal wajib diisi" : "";
  errors.dp = !form.dp ? "DP wajib diisi" : "";
  errors.deskripsiUlos = !form.deskripsiUlos ? "Deskripsi wajib diisi" : "";
  errors.namaUlos = !form.namaUlos ? "Nama ulos wajib diisi" : "";
  errors.nomor_telepon = !form.nomor_telepon ? "Telepon wajib diisi" : "";
  errors.hargaNet = !form.hargaNet ? "Total wajib diisi" : "";
  errors.metodePembayaran = !form.metodePembayaran ? "Pilih metode pembayaran" : "";
  errors.catatan = !form.catatan ? "Catatan wajib diisi" : "";
  errors.compressImage = !form.gambarCompressed ? "Please Input Image" : "";

  return Object.values(errors).every((e) => !e);
}

function toDatetimeString(dateStr) {
  const date = new Date(dateStr);
  const pad = (n) => (n < 10 ? "0" + n : n);
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} 00:00:00`;
}


async function submitForm() {
  if (!validate()) {
    Swal.fire("Gagal!", "Lengkapi semua field wajib", "error");
    return;
  }

  try {
    const jenis = await $api.post(`${url.value}/api/jenisbarang`, {
      jenisbarang_kode: form.code_barang,
      jenisbarang_nama: "PO Barang",
      jenisbarang_jumlah: 0,
    });

    const kode = await $api.post(`${url.value}/api/codebarang`, {
      jumlah_barang: 1,
      code_jenisbarang_id: jenis.data.jenisbarang_id,
    });

    const entry = await $api.post(`${url.value}/api/entrybarang/storeDescription`, {
      barangentry_code_id: String(kode.data.data[0].code_id),
      barangentry_nama: form.namaUlos,
      barangentry_warna: "PO",
      barangentry_nama_penenun: "PO",
      barangentry_nama_panirat: "PO",
      barangentry_dryer: "PO",
      barangentry_modal: 0,
      barangentry_price_tag: Number(form.hargaNet),
      barangentry_harga_net: Number(form.hargaNet),
      barangentry_jumlah_barang: 1,
      barangentry_status: "PREORDER",
    });

    form.barangentryID = entry.data.data.barangentry_id;

    const payloadTransaksi = {
      transaksi_nama_customer: form.namaAkun,
      transaksi_nomor_telepon: form.nomor_telepon,
      transaksi_jumlah_barang: 1,
      transaksi_total_harga: parseInt(form.hargaNet),
      transaksi_cara_bayar: form.metodePembayaran,
      transaksi_tipe: "Pre Order",
      transaksi_status: "Pre Order",
      transaksi_catatan: "",
    };

    const { data } = await $api.post(
      `${url.value}/api/transaksi`,
      payloadTransaksi);
    const transaksi_id = data.data.transaksi_id;

    const barang = await $api.get(`${url.value}/api/entrybarang/${form.barangentryID}`)

    const code = await $api.get(`${url.value}/api/codebarang/${barang.data.data.barangentry_code_id}`);

    const { data: barangResponse } = await $api.get(
      `${url.value}/api/entrybarang/getDataByCode/${code.data.code_nama}`);
    const barangData = barangResponse.data;

    const detailPayload = {
      transaksidetail_transaksi_id: transaksi_id,
      transaksidetail_barang_id: barangData.barangentry_id,
      transaksidetail_jumlah_barang: 1,
      transaksidetail_harga_barang: Number(barangData.barangentry_harga_net),
      transaksidetail_status_penjualan: 0,
      transaksidetail_platform: '-'
    };
    await $api.post(`${url.value}/api/transaksi-detail`, detailPayload);

    const formData = new FormData();
    formData.append("preOrdeBarang_id", "");
    formData.append("preOrderBarang_transaksi_id", transaksi_id);
    formData.append("preOrderBarang_nama_barang", form.namaUlos);
    formData.append("preOrderBarang_nama_akun", form.namaAkun);
    formData.append("preOrderBarang_no_telepon", form.nomor_telepon);
    formData.append(
      "preOrderBarang_target_selesai",
      toDatetimeString(form.targetSelesai)
    );

    formData.append("preOrderBarang_total_pembayaran", form.hargaNet);
    formData.append("preOrderBarang_uang_muka", form.dp);
    formData.append("preOrderBarang_sisa_pembayaran", sisaPembayaran.value);
    formData.append("preOrderBarang_deskripsi_barang", form.deskripsiUlos);
    formData.append("preOrderBarang_catatan", form.catatan);
    formData.append("preOrderBarang_barang_entry_id", String(form.barangentryID));
    formData.append("preOrderBarang_cara_bayar", form.metodePembayaran);

    if (form.gambarCompressed) {
      formData.append("preOrderBarang_path_gambar", form.gambarCompressed);
    }

    await $api.post(`${url.value}/api/pre-order-barang`, formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
      onUploadProgress: (e) => {
        uploadProgress.value = Math.round((e.loaded / e.total) * 100);
      },
    });

    Swal.fire("Berhasil!", "Pre-order berhasil ditambahkan", "success");
    batalPreOrder();

  } catch (err) {
    console.error(err);
    Swal.fire("Gagal", "Terjadi kesalahan server", "error");
  }
}

function batalPreOrder() {
  emit("close");

  Object.assign(form, {
    code_barang: "",
    namaAkun: "",
    targetSelesai: "",
    dp: "",
    deskripsiUlos: "",
    namaUlos: "",
    nomor_telepon: "",
    hargaNet: "",
    metodePembayaran: "",
    catatan: "",
    gambar: null,
    gambarCompressed: null,
    barangentryID: "",
  });

  imagePreview.value = null;
  uploadProgress.value = 0;
}

watch(
  () => props.visible,
  (val) => {
    if (val) kode_generator();
  }
);
</script>

<style>
.required {
  color: red;
}

* {
  font-family: "Nunito", sans-serif;
}

.judul-label {
  font-weight: 700;
}

.kode_po {
  background-color: #d1d0d07e !important;
}

.sisa-pembayaran {
  background-color: #d1d0d07e !important;
}

input,
textarea,
select {
  border-color: #e4e6fc !important;
  background-color: #fdfdff;
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

.bg-gray-800 {
  background-color: rgba(0, 0, 0, 0.5);
}
</style>
