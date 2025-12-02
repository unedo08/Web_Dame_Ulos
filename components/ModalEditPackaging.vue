<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white border border-gray-300 shadow-lg rounded-[10px] w-[800px] max-h-[90vh] overflow-y-auto">

      <div class="flex justify-between items-center px-6 py-4">
        <h2 class="text-lg font-bold">Form Packaging</h2>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>

      <div class="p-6">
        <table class="w-full border border-gray-200 rounded-md mb-4 text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th class="text-center">Pilih</th>
              <th class="text-center">Kode Barang</th>
              <th class="text-center">Nama Barang</th>
              <th class="text-center">Jumlah</th>
              <th class="text-right">Harga</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in barang" :key="index">
              <td class="text-center">
                <input type="checkbox" v-model="item.is_check" class="h-4 w-4" />
              </td>
              <td class="text-center">{{ item.kode }}</td>
              <td class="text-center">{{ item.nama }}</td>
              <td class="text-center">{{ item.jumlah }}</td>
              <td class="text-right">{{ formatCurrency(item.harga) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex justify-end gap-2 px-6 py-4">
        <button @click="closeModal" class="px-4 py-2 rounded-md border border-gray-300 text-gray-800">
          Batal
        </button>
        <button @click="submitForm" class="px-4 py-2 rounded-md bg-blue-500 text-white hover:bg-blue-600">
          Simpan
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import Swal from "sweetalert2";
import { useRuntimeConfig } from "#imports";

const config = useRuntimeConfig();
const url = config.public.apiBase;

const props = defineProps({
  show: Boolean,
  barang: Array,
  pengiriman: Object
});

const emit = defineEmits(["close", "save", "openPreview"]);

const submitForm = async () => {
  const barangTerpilih = props.barang.filter(item => item.is_check);
  if (barangTerpilih.length === 0) {
    Swal.fire("Gagal!", "Pilih minimal satu barang.", "warning");
    return;
  }

  try {
    for (const item of barangTerpilih) {
      await axios.post(`${url}/api/packaging`, {
        packaging_transactiondetail_id: item.trx_detail_id,
        packaging_nama_akun: props.pengiriman.pengirimanBarang_nama_penerima,
        packaging_alamat: props.pengiriman.pengirimanBarang_alamat_pengiriman_barang
      });
    }

    emit("save");

    emit("openPreview", {
      nama: props.pengiriman.pengirimanBarang_nama_penerima,
      telp: props.pengiriman.pengirimanBarang_no_telepon,
      alamat: props.pengiriman.pengirimanBarang_alamat_pengiriman_barang
    });

    emit("close");

  } catch (error) {
    console.error(error);
    Swal.fire("Error!", "Gagal menyimpan data", "error");
  }
};

function closeModal() {
  emit("close");
}

function formatCurrency(value) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR"
  }).format(value);
}
</script>
