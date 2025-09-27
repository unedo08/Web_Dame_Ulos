<template>
  <div
    v-if="visible"
    class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 z-50"
  >
    <div
      class="bg-white rounded-lg shadow-xl max-w-[55%] w-full overflow-y-auto p-6 relative"
    >
      <h2 class="text-xl font-semibold mb-4">Tambah Pre-Order Transaksi</h2>
      <form class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- KIRI -->
        <div class="space-y-4">
          <div>
            <label
              class="judul-label block text-sm font-medium text-gray-700 mb-1"
              >Kode Barang <span class="required">*</span></label
            >
            <input
              ref="platformInput"
              v-model="form.code_barang"
              type="text"
              class="kode_po w-full border border-gray-300 bg-gray-500 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
              placeholder="Masukkan Kode Barang"
              readonly
            />
            <p v-if="errors.code_barang" class="text-red-500 text-sm mt-1">
              {{ errors.code_barang }}
            </p>
            <!-- <div class="flex items-center mt-3">
              <input
                id="barangPo"
                type="checkbox"
                v-model="form.is_po"
                class="h-4 w-4 text-cyan-600 border-gray-300 rounded focus:ring-cyan-500"
              />
              <label
                for="barangPo"
                class="ml-2 text-xs text-gray-700 font-bold italic"
              >
                Barang termasuk Pre-Order
              </label>
            </div> -->
          </div>

          <div>
            <label
              class="judul-label block text-sm font-medium text-gray-700 mb-1"
              >Nama Akun <span class="required">*</span></label
            >
            <input
              v-model="form.namaAkun"
              type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
              placeholder="Masukkan Nama Akun"
            />
            <p v-if="errors.namaAkun" class="text-red-500 text-sm mt-1">
              {{ errors.namaAkun }}
            </p>
          </div>

          <div>
            <label
              class="judul-label block text-sm font-medium text-gray-700 mb-1"
              >Target Selesai <span class="required">*</span></label
            >
            <input
              v-model="form.targetSelesai"
              type="date"
              class="w-full border border-gray-300 rounded-md px-4 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
            />
            <p v-if="errors.targetSelesai" class="text-red-500 text-sm mt-1">
              {{ errors.targetSelesai }}
            </p>
          </div>
          <div>
            <label
              class="judul-label block text-sm font-medium text-gray-700 mb-1"
              >Uang Muka (DP) <span class="required">*</span></label
            >
            <div class="flex">
              <div
                class="bg-gray-100 border border-gray-300 rounded-l-md px-3 flex items-center text-gray-600 text-sm"
              >
                Rp
              </div>
              <input
                :value="formattedUangMuka"
                @input="onInputUangMuka"
                type="text"
                class="w-full border border-gray-300 px-2 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
                placeholder="Masukkan Uang Muka (DP)"
              />
            </div>
            <p v-if="errors.dp" class="text-red-500 text-sm mt-1">
              {{ errors.dp }}
            </p>
          </div>

          <div>
            <label
              class="judul-label block text-sm font-medium text-gray-700 mb-1"
              >Deskripsi Ulos <span class="required">*</span></label
            >
            <textarea
              v-model="form.deskripsiUlos"
              rows="3"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
              placeholder="Masukkan Deskripsi Ulos"
            ></textarea>
            <p v-if="errors.deskripsiUlos" class="text-red-500 text-sm mt-1">
              {{ errors.deskripsiUlos }}
            </p>
          </div>

          <div>
            <label
              class="judul-label block text-sm font-medium text-gray-700 mb-1"
              >Gambar</label
            >
            <div
              class="w-full border border-dashed border-gray-400 rounded-md p-4 flex flex-col items-start"
            >
              <input type="file" @change="handleFileUpload" />
              <p class="text-xs text-gray-500 mt-1">Ukuran Maksimal: 5MB</p>
            </div>
          </div>
        </div>

        <!-- KANAN -->
        <div class="space-y-4">
          <div>
            <label
              class="judul-label block text-sm font-medium text-gray-700 mb-1"
              >Nama Ulos <span class="required">*</span></label
            >
            <input
              v-model="form.namaUlos"
              type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
              placeholder="Masukkan Nama Ulos"
            />
            <p v-if="errors.namaUlos" class="text-red-500 text-sm mt-1">
              {{ errors.namaUlos }}
            </p>
          </div>

          <div>
            <label
              class="judul-label block text-sm font-medium text-gray-700 mb-1"
              >No Telepon <span class="required">*</span></label
            >
            <input
              v-model="form.nomor_telepon"
              type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
              placeholder="Masukkan Nomor Telepon"
            />
            <p v-if="errors.nomor_telepon" class="text-red-500 text-sm mt-1">
              {{ errors.nomor_telepon }}
            </p>
          </div>

          <div>
            <label
              class="judul-label block text-sm font-medium text-gray-700 mb-1"
            >
              Total Pembayaran <span class="required">*</span>
            </label>
            <div class="flex">
              <div
                class="bg-gray-100 border border-gray-300 rounded-l-md px-3 flex items-center text-gray-600 text-sm"
              >
                Rp
              </div>
              <input
                :value="formattedTotalPembayaran"
                @input="onInputTotalPembayaran"
                type="text"
                class="w-full border-t border-b border-r border-gray-300 rounded-r-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
                placeholder="Masukkan Total Pembayaran"
              />
            </div>
            <p v-if="errors.totalPembayaran" class="text-red-500 text-sm mt-1">
              {{ errors.totalPembayaran }}
            </p>
          </div>

          <div>
            <label
              class="judul-label block text-sm font-medium text-gray-700 mb-1"
              >Sisa Pembayaran <span class="required">*</span></label
            >
            <div class="flex">
              <div
                class="bg-gray-100 border border-gray-300 rounded-l-md px-3 flex items-center text-gray-600 text-sm"
              >
                Rp
              </div>
              <input
                :value="formattedSisaPembayaran"
                type="text"
                class="sisa-pembayaran w-full border border-gray-300 px-2 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
                placeholder="Masukkan Sisa Pembayaran"
                readonly
              />
            </div>
            <p v-if="errors.sisaPembayaran" class="text-red-500 text-sm mt-1">
              {{ errors.sisaPembayaran }}
            </p>
          </div>
          <div>
            <label
              class="judul-label block text-sm font-medium text-gray-700 mb-1"
              >Catatan <span class="required">*</span></label
            >
            <textarea
              v-model="form.catatan"
              rows="3"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
              placeholder="Masukkan Catatan"
            ></textarea>
            <p v-if="errors.catatan" class="text-red-500 text-sm mt-1">
              {{ errors.catatan }}
            </p>
          </div>
        </div>

        <!-- Tombol -->
        <div class="md:col-span-2 flex justify-end gap-4 mt-8">
          <button
            type="button"
            @click="batalPreOrder()"
            class="bg-gray-300 text-gray-800 px-6 py-2 rounded-md hover:bg-gray-400 transition"
          >
            Batal
          </button>
          <button
            type="button"
            @click="submitForm"
            class="bg-cyan-600 text-white px-6 py-2 rounded-md hover:bg-cyan-700 transition disabled:bg-gray-400"
          >
            Tambah
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted, watch } from "vue";
import axios from "axios";
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";

const props = defineProps({
  visible: Boolean,
});

const emit = defineEmits(["close"]);

const kodeBarangInput = ref(null);
const url = ref("");
const platformInput = ref(null);
const errors = reactive({});
onMounted(() => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;
  
  // kodeBarangInput.value?.focus();
  //  if (props.visible) {
  //   kode_generator();
  // }
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
  barangentryID: ''
  // is_po: false,
});

async function kode_generator (){
  try {
    const kodePO = await axios.get(`${url.value}/api/pre-order-barang/kode-generator`);    
    form.code_barang = kodePO.data.data.code_nama;
  } catch(err){
    console.error('Error pengambilan data Kode PO', err);
  }
}


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

const sisaPembayaran = computed(() => {
  const total = parseInt(form.totalPembayaran) || 0;
  const dp = parseInt(form.dp) || 0;
  const sisa = total - dp;

  return sisa >= 0 ? sisa : 0;
});

const formattedSisaPembayaran = computed(() => {
  return sisaPembayaran.value ? formatRupiah(sisaPembayaran.value) : "Rp 0";
});


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
    // const responseData = await axios.get(
    //   `${url.value}/api/entrybarang/getDataKasir/` + form.code_barang
    // );
    // const dataEntry = responseData.data.data[0];

    // const transaksiData = await axios.post(
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

    // await axios.post(
    //   `${url.value}/api/transaksi-detail`,
    //   payloadTransaksiDetail
    // );

    // if (form.is_po == true) {

    const barangentryID = ref('');
      const product = {
        jenisbarang_kode: form.code_barang,
        jenisbarang_nama: "PO Barang",
        jenisbarang_jumlah: 0,
      };
      const response = await axios.post(
        `${url.value}/api/jenisbarang`,
        product
      );

      if (response.status === 201) {
        const responseCode = await axios.post(`${url.value}/api/codebarang`, {
          jumlah_barang: 1,
          code_jenisbarang_id: response.data.jenisbarang_id,
        });

        if (responseCode.status === 201) {
          const payload = {
            barangentry_code_id: String(responseCode.data.data[0].code_id),
            barangentry_nama: form.namaUlos,
            barangentry_warna: "PO",
            barangentry_nama_penenun: "PO",
            barangentry_nama_panirat: "PO",
            barangentry_dryer: "PO",
            barangentry_modal: 0,
            barangentry_price_tag: Number(form.totalPembayaran),
            barangentry_harga_net: 0,
            barangentry_jumlah_barang: 1,
            barangentry_status: "PREORDER"
          };

          const responseBarang = await axios.post(
            `${url.value}/api/entrybarang/storeDescription`,
            payload
          );
          barangentryID.value = responseBarang.data.data.barangentry_id;


          if(responseBarang.status === 201){
            await axios.patch(`${url.value}/api/entrybarang/${responseBarang.data.data.barangentry_id}/updateStatus`,{
              status: "PREORDER"
            })
          }
        }
      }
    // }

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
    formData.append("preOrderBarang_barang_entry_id", String(barangentryID.value))

    await axios.post(`${url.value}/api/pre-order-barang`, formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });

    batalPreOrder();
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

    // await axios.post(`${url.value}/api/pengiriman-barang`, pengirimanPayload);
    // } catch (err) {
    //   console.error("Error melakukan transaksi", err);
    // }
  } catch (err) {
    console.error("Error", err);
  }
}

watch(
  () => props.visible,
  (newVal) => {
    if (newVal) {
      kode_generator();
    } else {      
      form.code_barang = "";
    }
  }
);

function batalPreOrder() {
  emit("close");
  resetForm();
}

function resetForm() {
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
  form.gambar = null;
  Object.keys(errors).forEach(key => (errors[key] = ""));
}


watch([() => form.totalPembayaran, () => form.dp], ([total, dp]) => {
  if (parseInt(dp) > parseInt(total)) {
    errors.dp = "Uang Muka (DP) tidak boleh lebih besar dari Total Pembayaran";
  } else {
    errors.dp = "";
  }
});

watch(sisaPembayaran, (newValue) => {
  form.sisaPembayaran = newValue;
});

// STL00005 -> 197
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
.kode_po{
  background-color: #d1d0d07e !important;
}
.sisa-pembayaran{
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
