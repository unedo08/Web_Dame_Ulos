<template>
  <div v-if="show" class="fixed inset-0 backdrop-blur-sm bg-white/30 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-full max-w-3xl max-h-[90vh] overflow-y-auto shadow-lg relative">
      <button class="absolute top-2 right-2 text-gray-500 hover:text-black" @click="closeModal">✕</button>
      <h2 class="text-xl font-bold mb-6">Edit Barang Ready</h2>

      <form @submit.prevent="submitForm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label>ID Barang *</label>
            <input v-model="form.id_barang" type="text" required class="form-control" />
          </div>

          <div>
            <label>Nama Ulos *</label>
            <input v-model="form.nama_ulos" type="text" required class="form-control" />
          </div>

          <div>
            <label>Warna Ulos *</label>
            <input v-model="form.warna_ulos" type="text" required class="form-control" />
          </div>

          <div>
            <label>Nama Penenun *</label>
            <input v-model="form.nama_penenun" type="text" required class="form-control" />
          </div>

          <div>
            <label>Nama Panirat *</label>
            <input v-model="form.nama_panirat" type="text" required class="form-control" />
          </div>

          <div>
            <label>Dyer *</label>
            <input v-model="form.dyer" type="text" required class="form-control" />
          </div>

          <div>
            <label>Modal *</label>
            <input v-model="formattedModal" @input="updateModal" type="text" required class="form-control text-right" />
          </div>

          <div>
            <label>Harga Price Tag *</label>
            <input v-model="formattedPriceTag" @input="updatePriceTag" type="text" required class="form-control text-right" />
          </div>

          <div>
            <label>Harga Net *</label>
            <input v-model="formattedHargaNet" @input="updateHargaNet" type="text" required class="form-control text-right" />
          </div>

          <div>
            <label>Ukuran Ulos *</label>
            <input v-model="form.ukuran_ulos" type="text" required class="form-control" />
          </div>

          <div>
            <label>Ukuran Mandar</label>
            <input v-model="form.ukuran_mandar" type="text" class="form-control" />
          </div>
        </div>

        <div class="flex justify-end mt-6 space-x-4">
          <button type="button" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400" @click="closeModal">
            Batal
          </button>
          <button type="submit" class="px-6 py-2 rounded bg-cyan-600 text-white hover:bg-cyan-700" :disabled="loading">
            <span v-if="loading">Menyimpan...</span>
            <span v-else>Simpan</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { useRuntimeConfig } from "#imports";

const url = ref("");

onMounted(async () => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;
});

const props = defineProps({
  id: Number,
  show: Boolean,
});
const emit = defineEmits(['close', 'saved']);

const loading = ref(false);
const form = ref({
  id_barang: '',
  nama_ulos: '',
  warna_ulos: '',
  nama_penenun: '',
  nama_panirat: '',
  dyer: '',
  modal: 0,
  harga_price_tag: 0,
  harga_net: 0,
  ukuran_ulos: '',
  ukuran_mandar: '',
});

// Format angka ke string dengan titik
const formatNumber = (value) => {
  return Number(value || 0).toLocaleString('id-ID');
};

// Parsing dari input ke number
const parseNumber = (val) => {
  return Number(String(val).replace(/\./g, '')) || 0;
};

// Field terformat
const formattedModal = ref('');
const formattedPriceTag = ref('');
const formattedHargaNet = ref('');

const updateModal = (e) => {
  const raw = parseNumber(e.target.value);
  form.value.modal = raw;
  formattedModal.value = formatNumber(raw);
};

const updatePriceTag = (e) => {
  const raw = parseNumber(e.target.value);
  form.value.harga_price_tag = raw;
  formattedPriceTag.value = formatNumber(raw);
};

const updateHargaNet = (e) => {
  const raw = parseNumber(e.target.value);
  form.value.harga_net = raw;
  formattedHargaNet.value = formatNumber(raw);
};

const loadData = async () => {
  try {
    const res = await axios.get(`${url.value}/api/barang-ready/${props.id}`);
    form.value = { ...res.data };
    formattedModal.value = formatNumber(form.value.modal);
    formattedPriceTag.value = formatNumber(form.value.harga_price_tag);
    formattedHargaNet.value = formatNumber(form.value.harga_net);
  } catch (err) {
    Swal.fire("Gagal", "Gagal memuat data!", "error");
  }
};

const submitForm = async () => {
  loading.value = true;
  try {
    await axios.put(`${url.value}/api/barang-ready/${props.id}`, form.value);
    await Swal.fire("Berhasil", "Data berhasil disimpan!", "success");
    emit('saved');
    closeModal();
  } catch (err) {
    Swal.fire("Gagal", "Gagal menyimpan data!", "error");
  } finally {
    loading.value = false;
  }
};

const closeModal = () => {
  emit('close');
};

watch(() => props.show, (newVal) => {
  if (newVal && props.id) {
    loadData();
  }
});
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
</style>
