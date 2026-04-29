<template>
  <div v-if="visible" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 z-50 pt-[60px]">
    <div class="bg-white rounded-lg shadow-xl max-w-[55%] w-full overflow-y-auto p-6 relative">
      <h2 class="text-xl font-semibold mb-4">Tambah Online Transaksi</h2>

      <form class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-4">
          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              Platform <span class="required">*</span>
            </label>
            <input v-model="form.platform" type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-cyan-500"
              placeholder="Masukkan Nama Platform" />
            <p v-if="errors.platform" class="text-red-500 text-sm mt-1">
              {{ errors.platform }}
            </p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              Nama Penerima <span class="required">*</span>
            </label>
            <input v-model="form.namaPenerima" type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-cyan-500"
              placeholder="Masukkan Nama Penerima" />
            <p v-if="errors.namaPenerima" class="text-red-500 text-sm mt-1">
              {{ errors.namaPenerima }}
            </p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              Pengiriman <span class="required">*</span>
            </label>
            <input v-model="form.pengiriman" type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-cyan-500"
              placeholder="Masukkan Pengiriman" />
            <p v-if="errors.pengiriman" class="text-red-500 text-sm mt-1">
              {{ errors.pengiriman }}
            </p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              Metode Pembayaran <span class="required">*</span>
            </label>
            <select v-model="form.metodePembayaran"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-cyan-500">
              <option disabled value="">Pilih metode pembayaran</option>
              <option v-for="cb in caraBayarList" :key="cb.id" :value="cb.carabayar_kode">
                {{ cb.carabayar_nama }}
              </option>
            </select>
            <p v-if="errors.metodePembayaran" class="text-red-500 text-sm mt-1">
              {{ errors.metodePembayaran }}
            </p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              Alamat <span class="required">*</span>
            </label>
            <textarea v-model="form.alamat" rows="3"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-cyan-500"
              placeholder="Masukkan Alamat"></textarea>
            <p v-if="errors.alamat" class="text-red-500 text-sm mt-1">
              {{ errors.alamat }}
            </p>
          </div>
        </div>

        <div class="space-y-4">
          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              Nama Akun <span class="required">*</span>
            </label>
            <input v-model="form.namaAkun" type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-cyan-500"
              placeholder="Masukkan Nama Akun" />
            <p v-if="errors.namaAkun" class="text-red-500 text-sm mt-1">
              {{ errors.namaAkun }}
            </p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              Nomor Telepon <span class="required">*</span>
            </label>
            <input v-model="form.nomor_telepon" type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-cyan-500"
              placeholder="Masukkan Nomor Telepon" />
            <p v-if="errors.nomor_telepon" class="text-red-500 text-sm mt-1">
              {{ errors.nomor_telepon }}
            </p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              Biaya Pengiriman <span class="required">*</span>
            </label>
            <div class="flex">
              <span class="bg-gray-100 border border-gray-300 rounded-l-md px-3 flex items-center text-gray-600">
                Rp
              </span>
              <input :value="formattedBiayaPengiriman" @input="onInputBiayaPengiriman" type="text"
                class="w-full border border-gray-300 px-2 py-2 focus:ring-2 focus:ring-cyan-500"
                placeholder="Masukkan Biaya Pengiriman" />
            </div>
            <p v-if="errors.biayaPengiriman" class="text-red-500 text-sm mt-1">
              {{ errors.biayaPengiriman }}
            </p>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              Catatan <span class="required">*</span>
            </label>
            <textarea v-model="form.catatan" rows="3"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-cyan-500"
              placeholder="Masukkan Catatan"></textarea>
            <p v-if="errors.catatan" class="text-red-500 text-sm mt-1">
              {{ errors.catatan }}
            </p>
          </div>
        </div>
        <div class="md:col-span-2 flex justify-end gap-4 mt-8">
          <button type="button" @click="batalLive" class="btn btn-neutral btn-md">Batal</button>
          <button type="button" @click="submitForm" :disabled="isSubmitting" class="btn btn-primary btn-md">
            {{ isSubmitting ? 'Memproses...' : 'Tambah' }}
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
  items: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(["close", "success"]);

const url = ref("");
const isSubmitting = ref(false);
const caraBayarList = ref([]);
const errors = reactive({});

onMounted(async () => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;

  const res = await $api.get(`${url.value}/api/carabayar`);
  caraBayarList.value = res.data.data;
});

const form = reactive({
  platform: "",
  namaPenerima: "",
  pengiriman: "",
  metodePembayaran: "",
  alamat: "",
  namaAkun: "",
  nomor_telepon: "",
  biayaPengiriman: "",
  catatan: "",
});

const totalHarga = computed(() =>
  props.items.reduce(
    (t, i) => t + i.quantity * Number(i.barangentry_harga_net || 0),
    0
  )
);

const formattedBiayaPengiriman = computed(() =>
  form.biayaPengiriman
    ? Number(form.biayaPengiriman).toLocaleString("id-ID")
    : ""
);

function onInputBiayaPengiriman(e) {
  form.biayaPengiriman = e.target.value.replace(/\D/g, "");
}

function validate() {
  errors.platform = form.platform ? "" : "Platform wajib diisi";
  errors.namaPenerima = form.namaPenerima ? "" : "Nama penerima wajib diisi";
  errors.pengiriman = form.pengiriman ? "" : "Pengiriman wajib diisi";
  errors.metodePembayaran = form.metodePembayaran ? "" : "Metode pembayaran wajib diisi";
  errors.alamat = form.alamat ? "" : "Alamat wajib diisi";
  errors.namaAkun = form.namaAkun ? "" : "Nama akun wajib diisi";
  errors.nomor_telepon = form.nomor_telepon ? "" : "Nomor telepon wajib diisi";
  errors.biayaPengiriman = form.biayaPengiriman ? "" : "Biaya pengiriman wajib diisi";
  errors.catatan = form.catatan ? "" : "Catatan wajib diisi";

  return Object.values(errors).every(e => !e);
}

async function submitForm() {
  if (isSubmitting.value) return;
  if (!validate()) return;

  if (props.items.length === 0) {
    Swal.fire("Gagal", "Tidak ada barang di kasir", "warning");
    return;
  }

  isSubmitting.value = true;

  try {
    const transaksiRes = await $api.post(`${url.value}/api/transaksi`, {
      transaksi_nama_customer: form.namaPenerima,
      transaksi_nomor_telepon: form.nomor_telepon,
      transaksi_jumlah_barang: props.items.reduce((t, i) => t + i.quantity, 0),
      transaksi_total_harga: totalHarga.value,
      transaksi_cara_bayar: form.metodePembayaran,
      transaksi_tipe: "online",
      transaksi_status: "pending",
      transaksi_catatan: form.catatan
    });

    const transaksi_id = transaksiRes.data.data.transaksi_id;

    for (const item of props.items) {
      const barangRes = await $api.get(
        `${url.value}/api/entrybarang/getDataByCode/${item.code_nama}`
      );

      await $api.post(`${url.value}/api/transaksi-detail`, {
        transaksidetail_transaksi_id: transaksi_id,
        transaksidetail_barang_id: barangRes.data.data.barangentry_id,
        transaksidetail_jumlah_barang: item.quantity,
        transaksidetail_harga_barang: item.barangentry_harga_net,
        transaksidetail_status_penjualan: 1,
        transaksidetail_platform: form.platform,
      });
    }

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

    Swal.fire("Sukses!", "Transaksi Online berhasil", "success");
    emit("success");
    emit("close");
  } catch (err) {
    console.error(err);
    Swal.fire("Error", "Gagal menyimpan transaksi online", "error");
  }

  isSubmitting.value = false;
}

function batalLive() {
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
</style>
