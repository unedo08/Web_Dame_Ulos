<template>
  <div
    class="fixed inset-0 bg-opacity-50 z-40 flex items-center justify-center"
  >
    <div
      class="max-w-6xl mx-auto p-8 bg-white rounded-xl shadow-2xl border border-gray-300 z-50"
    >
      <h2 class="text-2xl font-semibold mb-6">Tambah Online Transaksi</h2>

      <form
        @submit.prevent="submitForm"
        class="grid grid-cols-1 md:grid-cols-2 gap-6"
      >
        <div class="space-y-4">
          <!-- <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >ID Barang *</label
            >
            <input
              v-model="form.idBarang"
              type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
            />
          </div> -->

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Platform *</label
            >
            <input
              v-model="form.platform"
              type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Nama Penerima *</label
            >
            <input
              v-model="form.namaPenerima"
              type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Pengiriman *</label
            >
            <input
              v-model="form.pengiriman"
              type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Metode Pembayaran *</label
            >
            <select
              v-model="form.metodePembayaran"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
            >
              <option disabled value="">Pilih metode pembayaran</option>
              <option>Transfer Bank</option>
              <option>COD</option>
              <option>QRIS</option>
              <option>Virtual Account</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Alamat *</label
            >
            <textarea
              v-model="form.alamat"
              rows="3"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
            ></textarea>
          </div>
        </div>

        <!-- Kolom Kanan -->
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Nama Akun *</label
            >
            <input
              v-model="form.namaAkun"
              type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Harga Terjual *</label
            >
            <div class="relative">
              <span class="absolute left-3 top-2.5 text-gray-500">Rp</span>
              <input
                v-model="form.hargaTerjual"
                type="number"
                class="w-full border border-gray-300 rounded-md px-10 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Nama Telepon *</label
            >
            <input
              v-model="form.nomor_telepon"
              type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Biaya Pengiriman *</label
            >
            <div class="relative">
              <span class="absolute left-3 top-2.5 text-gray-500">Rp</span>
              <input
                v-model="form.biayaPengiriman"
                type="number"
                class="w-full border border-gray-300 rounded-md px-10 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Catatan *</label
            >
            <textarea
              v-model="form.catatan"
              rows="3"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
            ></textarea>
          </div>
        </div>

        <!-- Tombol -->
        <div class="md:col-span-2 flex justify-end gap-4 mt-8">
          <button
            type="button"
            class="bg-gray-300 text-gray-800 px-6 py-2 rounded-md hover:bg-gray-400 transition"
            @click="emit('close')"
          >
            Batal
          </button>
          <button
            type="submit"
            class="bg-cyan-600 text-white px-6 py-2 rounded-md hover:bg-cyan-700 transition"
          >
            Tambah
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";

const emit = defineEmits(["close", "submit-online"]);

const form = reactive({
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

function submitForm() {
  emit('submit-online', {...form});
}
</script>
