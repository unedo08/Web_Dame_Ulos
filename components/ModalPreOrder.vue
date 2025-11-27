<template>
  <div v-if="visible" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 z-50">
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
            </div>
          </div>

        </div>

        <div class="space-y-4">
          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              Nama Ulos <span class="required">*</span>
            </label>
            <input v-model="form.namaUlos" type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
              placeholder="Masukkan Nama Ulos" />
            <p v-if="errors.namaUlos" class="text-red-500 text-sm mt-1">{{ errors.namaUlos }}</p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              No Telepon <span class="required">*</span>
            </label>
            <input v-model="form.nomor_telepon" type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
              placeholder="Masukkan Nomor Telepon" />
            <p v-if="errors.nomor_telepon" class="text-red-500 text-sm mt-1">{{ errors.nomor_telepon }}</p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              Total Pembayaran <span class="required">*</span>
            </label>
            <div class="flex">
              <div class="bg-gray-100 border border-gray-300 rounded-l-md px-3 flex items-center text-gray-600 text-sm">
                Rp
              </div>
              <input :value="formattedTotalPembayaran" @input="onInputTotalPembayaran" type="text"
                class="w-full border-t border-b border-r border-gray-300 rounded-r-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
                placeholder="Masukkan Total Pembayaran" />
            </div>
            <p v-if="errors.totalPembayaran" class="text-red-500 text-sm mt-1">{{ errors.totalPembayaran }}</p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
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
              <option>Transfer Bank</option>
              <option>COD</option>
              <option>QRIS</option>
              <option>Virtual Account</option>
            </select>
            <p v-if="errors.metodePembayaran" class="text-red-500 text-sm mt-1">{{ errors.metodePembayaran }}</p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
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
import axios from "axios";
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";

const props = defineProps({ visible: Boolean });
const emit = defineEmits(["close"]);

const url = ref("");

onMounted(() => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;
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
  try {
    const kodePO = await axios.get(`${url.value}/api/pre-order-barang/kode-generator`);
    form.code_barang = kodePO.data.data.code_nama;
  } catch (err) {
    console.error("Kode PO Error", err);
  }
}

function parseRupiah(val) {
  return val.toString().replace(/\D/g, "");
}

function formatRupiah(val) {
  if (val === null || val === undefined) return "0";
  const n = parseInt(val.toString().replace(/\D/g, "")) || 0;
  return n.toLocaleString("id-ID");
}

const formattedUangMuka = computed(() => formatRupiah(form.dp));
function onInputUangMuka(e) {
  form.dp = parseRupiah(e.target.value);
}

const formattedTotalPembayaran = computed(() => formatRupiah(form.totalPembayaran));
function onInputTotalPembayaran(e) {
  form.totalPembayaran = parseRupiah(e.target.value);
}

const sisaPembayaran = computed(() => {
  return (parseInt(form.totalPembayaran) || 0) - (parseInt(form.dp) || 0);
});

const formattedSisaPembayaran = computed(() => formatRupiah(sisaPembayaran.value));

async function handleFileUpload(event) {
  const file = event.target.files[0];
  if (!file) return;

  const allowedTypes = ["image/png", "image/jpeg", "image/jpg"];
  if (!allowedTypes.includes(file.type)) {
    Swal.fire("Format Tidak Valid", "Gunakan JPG atau PNG", "error");
    return;
  }

  if (file.size > 5 * 1024 * 1024) {
    Swal.fire("File Terlalu Besar", "Maksimal 5MB", "error");
    return;
  }

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
  errors.totalPembayaran = !form.totalPembayaran ? "Total wajib diisi" : "";
  errors.metodePembayaran = !form.metodePembayaran ? "Pilih metode pembayaran" : "";
  errors.catatan = !form.catatan ? "Catatan wajib diisi" : "";

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
    const jenis = await axios.post(`${url.value}/api/jenisbarang`, {
      jenisbarang_kode: form.code_barang,
      jenisbarang_nama: "PO Barang",
      jenisbarang_jumlah: 0,
    });

    const kode = await axios.post(`${url.value}/api/codebarang`, {
      jumlah_barang: 1,
      code_jenisbarang_id: jenis.data.jenisbarang_id,
    });

    const entry = await axios.post(`${url.value}/api/entrybarang/storeDescription`, {
      barangentry_code_id: String(kode.data.data[0].code_id),
      barangentry_nama: form.namaUlos,
      barangentry_warna: "PO",
      barangentry_nama_penenun: "PO",
      barangentry_nama_panirat: "PO",
      barangentry_dryer: "PO",
      barangentry_modal: 0,
      barangentry_price_tag: Number(form.totalPembayaran),
      barangentry_harga_net: 0,
      barangentry_jumlah_barang: 1,
      barangentry_status: "PREORDER",
    });

    form.barangentryID = entry.data.data.barangentry_id;

    const formData = new FormData();
    formData.append("preOrdeBarang_id", "");
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
    formData.append("preOrderBarang_sisa_pembayaran", sisaPembayaran.value);
    formData.append("preOrderBarang_deskripsi_barang", form.deskripsiUlos);
    formData.append("preOrderBarang_catatan", form.catatan);
    formData.append("preOrderBarang_barang_entry_id", String(form.barangentryID));
    formData.append("preOrderBarang_cara_bayar", form.metodePembayaran);

    if (form.gambarCompressed) {
      formData.append("preOrderBarang_path_gambar", form.gambarCompressed.name);
    }

    await axios.post(`${url.value}/api/pre-order-barang`, formData, {
      headers: { "Content-Type": "multipart/form-data" },
      onUploadProgress: (e) => {
        uploadProgress.value = Math.round((e.loaded / e.total) * 100);
      },
    });

    Swal.fire("Berhasil!", "Pre-order berhasil ditambahkan", "success");

    printPreOrder({
      code: form.code_barang,
      nama_ulos: form.namaUlos,
      no_telp: form.nomor_telepon,
      deskripsi: form.deskripsiUlos,
      created_at: new Date().toISOString(),
      target_selesai: form.targetSelesai,
      author: "Tari",
      total: form.totalPembayaran,
      dp: form.dp,
      sisa: sisaPembayaran.value
    });

    batalPreOrder();
  } catch (err) {
    console.error(err);
    Swal.fire("Gagal", "Terjadi kesalahan server", "error");
  }
}

function printPreOrder(data) {
  console.log('zxczxc', data);

  const printWindow = window.open("", "_blank");
  if (!printWindow) {
    alert("Pop-up blocker menghalangi membuka tab baru.");
    return;
  }

  const tanggalFormatted = (dateStr) => {
    const d = new Date(dateStr);
    return d.toLocaleDateString("id-ID", {
      day: "2-digit",
      month: "long",
      year: "numeric",
    });
  };

  const rupiah = (v) =>
    new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(v);

  const htmlContent = String.raw`
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Pre Order Invoice</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 40px; }
    .header { text-align: center; margin-bottom: 20px; }
    .logo { width: 100%; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 10px; border: 1px solid #ddd; }
    .footer { margin-top: 40px; text-align: center; color: #555; }
  </style>
</head>
<body>
  <div class="header">
    <img src="/image/DameUlosPO.png" class="logo" />
    <h1>Pre Order</h1>
  </div>

  <div class="info">
    <p>Kode PO : <b>${data.code}</b></p>
    <p>Nama Ulos : <b>${data.nama_ulos}</b></p>
    <p>No Telepon : <b>${data.no_telp}</b></p>
    <p>Tanggal Dimulai : <b>-</b></p>
    <p>Tanggal Selesai : <b>${data.target_selesai}</b></p>
    <p>Author : <b>${data.author}</b></p>
  </div>

  <table>
    <thead>
      <tr>
        <th>Total Pembayaran</th>
        <th>Down Payment (DP)</th>
        <th>Pelunasan</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Rp.${formatRupiah(data.total)}</td>
        <td>Rp.${formatRupiah(data.dp)}</td>
        <td>Rp.${formatRupiah(data.pelunasan)}</td>
      </tr>
    </tbody>
  </table>

  <div style="display:flex; justify-content:space-between; margin-top:60px;">
    <div style="border:1px solid #ccc; width:150px; height:150px; text-align:center; padding-top:60px;">
      Tanda Masuk
    </div>
    <div style="border:1px solid #ccc; width:150px; height:150px; text-align:center; padding-top:60px;">
      Tanda Pengambilan
    </div>
  </div>

  <div class="footer">
    <p>Transaksi ini diproses berdasarkan Purchase Order yang berlaku.</p>
    <p>Terima kasih telah mempercayakan kebutuhan Anda kepada kami.</p>
  </div>

  <script>
    window.onload = () => {
      window.focus();
      setTimeout(() => {
        window.print();
      }, 150);
    };
  <\/script>
</body>
</html>
`;


  printWindow.document.write(htmlContent);
  printWindow.document.close();
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
    totalPembayaran: "",
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
