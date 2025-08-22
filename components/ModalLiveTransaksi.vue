<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
  >
    <div
      class="bg-white border border-gray-300 shadow-lg rounded-[10px] w-[800px] max-h-[90vh] overflow-y-auto"
    >
      <!-- Header -->
      <div class="flex justify-between items-center px-6 py-4">
        <h2 class="text-lg font-bold">Form Marketing</h2>
        <button
          @click="$emit('close')"
          class="text-gray-500 hover:text-gray-700"
        >
          ✕
        </button>
      </div>

      <!-- Table Barang -->
      <div class="p-6">
        <table class="w-full border border-gray-200 rounded-md mb-4 text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-3 py-2 text-left">#</th>
              <th class="px-3 py-2 text-left">Kode Barang</th>
              <th class="px-3 py-2 text-left">Nama Barang</th>
              <th class="px-3 py-2 text-center">Jumlah</th>
              <th class="px-3 py-2 text-right">Harga</th>
              <th class="px-3 py-2 text-center">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in barang" :key="index">
              <td class="px-3 py-2">{{ index + 1 }}</td>
              <td class="px-3 py-2">{{ item.kode }}</td>
              <td class="px-3 py-2">{{ item.nama }}</td>
              <td class="px-3 py-2 text-center">{{ item.jumlah }}</td>
              <td class="px-3 py-2 text-right">
                {{ formatCurrency(item.harga) }}
              </td>
              <td class="px-3 py-2 text-center">
                <button
                  @click="$emit('removeItem', index)"
                  class="text-red-500 hover:text-red-700"
                >
                  🗑
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Nama Akun -->
        <div class="font-semibold mb-4">Nama Akun: {{ namaAkun }}</div>

        <!-- Form Input -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1"
              >Nama Penerima *</label
            >
            <input
              v-model="form.nama_penerima"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2"
              placeholder="Masukkan nama penerima"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1"
              >No Telepon/Wa *</label
            >
            <input
              v-model="form.no_telepon"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2"
              placeholder="Masukkan nomor telepon"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1"
              >Metode Pembayaran *</label
            >
            <select
              v-model="form.metode"
              class="w-full border border-gray-300 rounded-md px-3 py-2"
              required
            >
              <option value="" disabled>Pilih metode pembayaran</option>
              <option value="Transfer Bank">Transfer Bank</option>
              <option value="Credit Card">Credit Card</option>
              <option value="Cash">Cash</option>
              <option value="OVO">OVO</option>
              <option value="Gopay">Gopay</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1"
              >Biaya Pengiriman *</label
            >
            <input
              :value="formattedBiayaPengiriman"
              @input="onInputBiayaPengiriman"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2"
              placeholder="Masukkan biaya pengiriman"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Alamat</label>
            <textarea
              v-model="form.alamat"
              class="w-full border border-gray-300 rounded-md px-3 py-2"
              placeholder="Masukkan alamat"
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Pengiriman *</label>
            <input
              v-model="form.pengiriman"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2"
              placeholder="Masukkan jenis pengiriman"
              required
            />
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex justify-end gap-2 px-6 py-4">
        <button
          @click="closeModal"
          class="px-4 py-2 rounded-md border border-gray-300 text-gray-800"
        >
          Batal
        </button>
        <button
          @click="submitForm"
          :disabled="isSubmitting"
          class="px-4 py-2 rounded-md bg-blue-500 text-white hover:bg-blue-600"
        >
          {{ isSubmitting ? "Menyimpan..." : "Simpan" }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";

const url = ref("");
const props = defineProps({
  show: Boolean,
  namaAkun: String,
  barang: Array,
});
const emit = defineEmits(["close", "save", "removeItem"]);

const isSubmitting = ref(false);
const form = ref({
  nama_penerima: "",
  no_telepon: "",
  metode: "",
  biaya_pengiriman: "",
  alamat: "",
  pengiriman: "",
});

onMounted(async () => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;
});

const jumlahBarang = computed(() =>
  props.barang.reduce((sum, item) => sum + Number(item.jumlah), 0)
);
const subtotal = computed(() =>
  props.barang.reduce(
    (sum, item) => sum + Number(item.harga) * Number(item.jumlah),
    0
  )
);

function formatRupiah(value) {
  if (!value) return "";
  const number = parseInt(value.toString().replace(/\D/g, ""));
  return number.toLocaleString("id-ID");
}

function parseRupiah(value) {
  if (!value) return "";
  return value.toString().replace(/\D/g, "");
}

const formattedBiayaPengiriman = computed(() => {
  return form.value.biaya_pengiriman
    ? formatRupiah(form.value.biaya_pengiriman)
    : "";
});

function onInputBiayaPengiriman(e) {
  const raw = parseRupiah(e.target.value);
  form.value.biaya_pengiriman = raw;
}

const submitForm = async () => {
  isSubmitting.value = true;
  try {
    const payloadTransaksi = {
      transaksi_nama_customer: form.value.nama_penerima,
      transaksi_nomor_telepon: form.value.no_telepon,
      transaksi_jumlah_barang: jumlahBarang.value,
      transaksi_total_harga: subtotal.value,
      transaksi_cara_bayar: form.value.metode,
      transaksi_tipe: "PREORDER",
      transaksi_status: "pending",
      transaksi_catatan: "",
    };

    const { data } = await axios.post(
      `${url.value}/api/transaksi`,
      payloadTransaksi
    );
    const transaksi_id = data.data.transaksi_id;
    for (const item of props.barang) {
      const { data: barangResponse } = await axios.get(
        `${url.value}/api/entrybarang/getDataByCode/${item.kode}`
      );
      const barangData = barangResponse.data;
      if (!barangData || !barangData.barangentry_id) continue;

      const detailPayload = {
        transaksidetail_transaksi_id: transaksi_id,
        transaksidetail_barang_id: barangData.barangentry_id,
        transaksidetail_jumlah_barang: item.jumlah,
        transaksidetail_harga_barang: parseFloat(item.harga),
      };
      await axios.post(`${url.value}/api/transaksi-detail`, detailPayload);
    }

    const pengirimanPayload = {
      pengirimanBarang_transaksi_id: transaksi_id,
      pengirimanBarang_nama_penerima: form.value.nama_penerima,
      pengirimanBarang_akun_penerima: props.namaAkun,
      pengirimanBarang_no_telepon: form.value.no_telepon,
      pengirimanBarang_harga_kirim_barang: form.value.biaya_pengiriman,
      pengirimanBarang_jenis_pengiriman_barang: form.value.pengiriman,
      pengirimanBarang_alamat_pengiriman_barang: form.value.alamat,
      pengirimanBarang_catatan: "",
      pengirimanBarang_status: "Proses",
    };
    await axios.post(`${url.value}/api/pengiriman-barang`, pengirimanPayload);

    emit("save");
    emit("close");
    Swal.fire("Berhasil", "Transaksi berhasil disimpan!", "success");
  } catch (err) {
    console.error("Gagal menyimpan transaksi:", err);
    Swal.fire("Gagal", "Gagal menyimpan data!", "error");
  } finally {
    isSubmitting.value = false;
  }
};

const closeModal = () => {
  emit("close");
};

const formatCurrency = (value) => {
  if (!value) return "Rp 0";
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
  }).format(value);
};
</script>

<style scoped>
.form-control {
  width: 100%;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  padding: 0.5rem 0.75rem;
  margin-bottom: 1rem;
  outline: none;
}
.form-control:focus {
  border-color: #06b6d4;
  box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.3);
}
.bg-gray-800 {
  background-color: rgba(0, 0, 0, 0.5);
}
</style>
