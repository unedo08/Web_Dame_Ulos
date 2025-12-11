<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white border border-gray-300 shadow-lg rounded-[10px] w-[800px] max-h-[90vh] overflow-y-auto">
      <div class="flex justify-between items-center px-6 py-4">
        <h2 class="text-lg font-bold">Form Marketing</h2>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>

      <div class="p-6">
        <table class="w-full border border-gray-200 rounded-md mb-4 text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-3 py-2">Pilih</th>
              <th class="px-3 py-2">Kode Barang</th>
              <th class="px-3 py-2">Nama Barang</th>
              <th class="px-3 py-2 text-center">Jumlah</th>
              <th class="px-3 py-2 text-right">Harga</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in barang" :key="index">
              <td class="px-3 py-2 text-center">
                <input type="checkbox" v-model="item.is_check" class="h-4 w-4" />
              </td>
              <td class="px-3 py-2 text-center">{{ item.kode }}</td>
              <td class="px-3 py-2 text-center">{{ item.nama }}</td>
              <td class="px-3 py-2 text-center">{{ item.jumlah }}</td>
              <td class="px-3 py-2 text-right">{{ formatCurrency(item.harga) }}</td>
            </tr>
          </tbody>
        </table>

        <div class="font-semibold mb-4">Nama Akun: {{ namaAkun }}</div>

        <div class="grid grid-cols-2 gap-4">

          <div>
            <label class="block text-sm font-medium mb-1">Nama Penerima <span style="color:red">*</span></label>
            <input v-model="form.nama_penerima" type="text" class="w-full border rounded-md px-3 py-2"
              placeholder="Masukkan nama penerima" />
            <p v-if="errors.nama_penerima" class="text-red-500 text-sm mt-1">{{ errors.nama_penerima }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">No Telepon/WA <span style="color:red">*</span></label>
            <input v-model="form.no_telepon" type="text" class="w-full border rounded-md px-3 py-2"
              placeholder="Masukkan nomor telepon" />
            <p v-if="errors.no_telepon" class="text-red-500 text-sm mt-1">{{ errors.no_telepon }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Metode Pembayaran <span style="color:red">*</span></label>

            <select v-model="form.metode" class="w-full border rounded-md px-3 py-2">
              <option value="" disabled>Pilih metode pembayaran</option>

              <option v-for="cb in caraBayarList" :key="cb.id" :value="cb.carabayar_kode">
                {{ cb.carabayar_nama }}
              </option>
            </select>

            <p v-if="errors.metode" class="text-red-500 text-sm mt-1">{{ errors.metode }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Biaya Pengiriman <span style="color:red">*</span></label>
            <input :value="formattedBiayaPengiriman" @input="onInputBiayaPengiriman" type="text"
              class="w-full border rounded-md px-3 py-2" placeholder="Masukkan biaya pengiriman" />
            <p v-if="errors.biaya_pengiriman" class="text-red-500 text-sm mt-1">{{ errors.biaya_pengiriman }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Alamat <span style="color:red">*</span></label>
            <textarea v-model="form.alamat" rows="2" class="w-full border rounded-md px-3 py-2"
              placeholder="Masukkan alamat lengkap"></textarea>
            <p v-if="errors.alamat" class="text-red-500 text-sm mt-1">{{ errors.alamat }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Pengiriman <span style="color:red">*</span></label>
            <input v-model="form.pengiriman" type="text" class="w-full border rounded-md px-3 py-2"
              placeholder="Masukkan jenis pengiriman" />
            <p v-if="errors.pengiriman" class="text-red-500 text-sm mt-1">{{ errors.pengiriman }}</p>
          </div>

        </div>
      </div>

      <div class="flex justify-end gap-2 px-6 py-4">
        <button @click="closeModal" class="px-4 py-2 rounded-md border border-gray-300 text-gray-800">Batal</button>

        <button @click="submitForm" :disabled="isSubmitting"
          class="px-4 py-2 rounded-md bg-blue-500 text-white hover:bg-blue-600">
          {{ isSubmitting ? "Menyimpan..." : "Simpan" }}
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from "vue";
import axios from "axios";
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";

const props = defineProps({
  show: Boolean,
  namaAkun: String,
  barang: Array,
});

const emit = defineEmits(["close", "save"]);

const url = ref("");
const caraBayarList = ref([]);

const isSubmitting = ref(false);

const form = ref({
  nama_penerima: "",
  no_telepon: "",
  metode: "",
  biaya_pengiriman: "",
  alamat: "",
  pengiriman: "",
});

const errors = reactive({});

onMounted(async () => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;

  try {
    const token = sessionStorage.getItem("auth_token")
    const res = await axios.get(`${url.value}/api/carabayar`, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    });
    caraBayarList.value = res.data.data;
  } catch (err) { }
});

const jumlahBarang = computed(() =>
  props.barang.filter(i => i.is_check).reduce((a, b) => a + Number(b.jumlah), 0)
);

const subtotal = computed(() =>
  props.barang.filter(i => i.is_check).reduce((a, b) => a + b.harga * b.jumlah, 0)
);

function parseRupiah(v) {
  return v ? v.toString().replace(/\D/g, "") : "";
}

const formattedBiayaPengiriman = computed(() =>
  form.value.biaya_pengiriman
    ? Number(form.value.biaya_pengiriman).toLocaleString("id-ID")
    : ""
);

function onInputBiayaPengiriman(e) {
  form.value.biaya_pengiriman = parseRupiah(e.target.value);
}

function validate() {
  errors.nama_penerima = form.value.nama_penerima ? "" : "Nama Penerima wajib diisi";
  errors.no_telepon = form.value.no_telepon ? "" : "No Telepon wajib diisi";
  errors.metode = form.value.metode ? "" : "Metode Pembayaran wajib diisi";
  errors.biaya_pengiriman = form.value.biaya_pengiriman ? "" : "Biaya Pengiriman wajib diisi";
  errors.alamat = form.value.alamat ? "" : "Alamat wajib diisi";
  errors.pengiriman = form.value.pengiriman ? "" : "Jenis Pengiriman wajib diisi";

  return Object.values(errors).every(e => !e);
}

async function submitForm() {
  if (!validate()) {
    Swal.fire("Gagal!", "Silakan lengkapi semua field wajib.", "error");
    return;
  }

  const barangTerpilih = props.barang.filter(i => i.is_check);
  if (barangTerpilih.length === 0) {
    Swal.fire("Gagal!", "Pilih minimal satu barang!", "warning");
    return;
  }

  isSubmitting.value = true;

  try {
    const token = sessionStorage.getItem("auth_token")
    const trx = await axios.post(`${url.value}/api/transaksi`, {
      transaksi_nama_customer: form.value.nama_penerima,
      transaksi_nomor_telepon: form.value.no_telepon,
      transaksi_jumlah_barang: jumlahBarang.value,
      transaksi_total_harga: subtotal.value,
      transaksi_cara_bayar: form.value.metode,
      transaksi_tipe: "PREORDER",
      transaksi_status: "PREORDER",
      transaksi_catatan: "",
    }, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    });

    const transaksi_id = trx.data.data.transaksi_id;

    for (const item of barangTerpilih) {
      const barangRes = await axios.get(`${url.value}/api/entrybarang/getDataByCode/${item.kode}`, {
        headers: {
          "Authorization": `Bearer ${token}`,
          "Content-Type": "application/json",
        }
      });
      const barangData = barangRes.data;

      await axios.post(`${url.value}/api/transaksi-detail`, {
        transaksidetail_transaksi_id: transaksi_id,
        transaksidetail_barang_id: barangData.data.barangentry_id,
        transaksidetail_jumlah_barang: item.jumlah,
        transaksidetail_harga_barang: Number(item.harga),
      }, {
        headers: {
          "Authorization": `Bearer ${token}`,
          "Content-Type": "application/json",
        }
      });

      await axios.patch(`${url.value}/api/live-barang/${item.live_order_id}/check`, {
        headers: {
          "Authorization": `Bearer ${token}`,
          "Content-Type": "application/json",
        }
      });
    }

    await axios.post(`${url.value}/api/pengiriman-barang`, {
      pengirimanBarang_transaksi_id: transaksi_id,
      pengirimanBarang_nama_penerima: form.value.nama_penerima,
      pengirimanBarang_akun_penerima: props.namaAkun,
      pengirimanBarang_no_telepon: form.value.no_telepon,
      pengirimanBarang_harga_kirim_barang: form.value.biaya_pengiriman,
      pengirimanBarang_jenis_pengiriman_barang: form.value.pengiriman,
      pengirimanBarang_alamat_pengiriman_barang: form.value.alamat,
      pengirimanBarang_catatan: "",
      pengirimanBarang_status: "Proses",
    }, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    });

    Swal.fire("Berhasil!", "Transaksi berhasil disimpan!", "success");
    emit("save");
    emit("close");

  } catch (err) {
    Swal.fire("Gagal!", "Terjadi kesalahan saat menyimpan.", "error");
  } finally {
    isSubmitting.value = false;
  }
}

function closeModal() {
  emit("close");
  form.value = {
    nama_penerima: "",
    no_telepon: "",
    metode: "",
    biaya_pengiriman: "",
    alamat: "",
    pengiriman: "",
  };
}

function formatCurrency(v) {
  return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR" }).format(v);
}
</script>

<style scoped>
* {
  font-family: "Poppins", "Inter", sans-serif;
}

table {
  border: 1px solid #E4E6FC;
}

th {
  background: #F8F8FB;
}

td {
  border-top: 1px solid #E4E6FC;
}

input,
textarea,
select {
  border: 1px solid #E4E6FC;
  background: #FDFDFF;
  padding: 8px 10px;
  border-radius: 6px;
}

input:focus,
textarea:focus,
select:focus {
  border-color: #0A84FF;
  box-shadow: 0 0 0 2px rgba(10, 132, 255, 0.15);
}

button.bg-blue-500:hover {
  background-color: #0077e6;
}
</style>
