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
              <option value="transfer">Transfer</option>
              <option value="cod">COD</option>
              <option value="qris">QRIS</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1"
              >Biaya Pengiriman *</label
            >
            <input
              v-model="form.biaya_pengiriman"
              type="number"
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
        <button @click="$emit('close')" class="px-4 py-2 rounded-md border">
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
import { ref } from "vue";

const props = defineProps({
  show: Boolean,
  namaAkun: String,
  barang: Array, // [{kode, nama, jumlah, harga}]
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

const submitForm = async () => {
  isSubmitting.value = true;
  try {
    emit("save", { ...form.value });
  } finally {
    isSubmitting.value = false;
  }
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
