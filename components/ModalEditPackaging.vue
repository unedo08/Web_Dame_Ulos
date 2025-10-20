<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white border border-gray-300 shadow-lg rounded-[10px] w-[800px] max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="flex justify-between items-center px-6 py-4">
        <h2 class="text-lg font-bold">Form Packaging</h2>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
          ✕
        </button>
      </div>

      <!-- Table Barang -->
      <div class="p-6">
        <table class="w-full border border-gray-200 rounded-md mb-4 text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-3 py-2 text-left">Pilih</th>
              <th class="px-3 py-2 text-left">Kode Barang</th>
              <th class="px-3 py-2 text-left">Nama Barang</th>
              <th class="px-3 py-2 text-center">Jumlah</th>
              <th class="px-3 py-2 text-right">Harga</th>
              <!-- <th class="px-3 py-2 text-center">Action</th> -->
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in barang" :key="index">
              <!-- <pre>{{ item }}</pre> -->

              <td class="px-3 py-2">
                <input type="checkbox" v-model="item.is_check"
                  class="h-4 w-4 text-cyan-600 border-gray-300 rounded focus:ring-cyan-500" />
              </td>
              <td class="px-3 py-2 hidden">{{ item.trx_detail_id }}</td>
              <td class="px-3 py-2">{{ item.kode }}</td>
              <td class="px-3 py-2">{{ item.nama }}</td>
              <td class="px-3 py-2 text-center">{{ item.jumlah }}</td>
              <td class="px-3 py-2 text-right">
                {{ formatCurrency(item.harga) }}
              </td>
              <!-- <td class="px-3 py-2 text-center">
                <button
                  @click="$emit('removeItem', index)"
                  class="text-red-500 hover:text-red-700"
                >
                  🗑
                </button>
              </td> -->
            </tr>
          </tbody>
        </table>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Alamat <span style="color:red">*</span></label>
            <textarea v-model="form.alamat" class="w-full border border-gray-300 rounded-md px-3 py-2"
              placeholder="Masukkan alamat"></textarea>
            <p v-if="errors.alamat" class="text-red-500 text-sm mt-1">
              {{ errors.alamat }}
            </p>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex justify-end gap-2 px-6 py-4">
        <button @click="closeModal" class="px-4 py-2 rounded-md border border-gray-300 text-gray-800">
          Batal
        </button>
        <button @click="submitForm" :disabled="isSubmitting"
          class="px-4 py-2 rounded-md bg-blue-500 text-white hover:bg-blue-600">
          {{ isSubmitting ? "Menyimpan..." : "Simpan" }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from "vue";
import axios from "axios";
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";

const url = ref("");
const props = defineProps({
  show: Boolean,
  barang: Array,
});
const emit = defineEmits(["close", "save", "removeItem"]);
const errors = reactive({});

const isSubmitting = ref(false);
const form = ref({
  alamat: "",
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

function validate() {
  errors.alamat = !form.value.alamat ? "Alamat wajib diisi" : "";

  return Object.values(errors).every((err) => !err);
}

const submitForm = async () => {

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
  isSubmitting.value = true;

  const barangTerpilih = props.barang.filter(item => item.is_check);
  if (barangTerpilih.length === 0) {
    Swal.fire({
      title: "Gagal!",
      text: "Pilih minimal satu barang untuk transaksi.",
      icon: "warning",
      confirmButtonText: "OK"
    });
    return;
  }

  try {
    for (const item of props.barang.filter(item => item.is_check)) {
      const detailPayload = {
        packaging_transactiondetail_id: item.trx_detail_id,
        packaging_nama_akun: form.value.alamat,
        packaging_alamat: form.value.alamat,
      };
      await axios.post(`${url.value}/api/packaging`, detailPayload);

    }
    emit("save");
    emit("close");
    Swal.fire("Berhasil", "Packaging berhasil disimpan!", "success");
  } catch (err) {
    console.error("Gagal menyimpan Packaging:", err);
    Swal.fire("Gagal", "Gagal menyimpan data!", "error");
  } finally {
    isSubmitting.value = false;
  }
};

const closeModal = () => {
  emit("close");
  resetForm();
};

const resetForm = () => {
  form.value = {
    nama_penerima: "",
    no_telepon: "",
    metode: "",
    biaya_pengiriman: "",
    alamat: "",
    pengiriman: "",
  };
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
