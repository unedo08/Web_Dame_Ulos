<template>
  <div v-if="show" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-[55%] w-full overflow-y-auto p-6 relative">
      <button class="absolute top-2 right-2 text-gray-500 hover:text-black" @click="closeModal">✕</button>
      <h2 class="text-xl font-bold mb-6">Edit Barang Ready</h2>

      <form>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label>Kode Barang <span style="color:red">*</span></label>
            <input v-model="form.code" type="text" required class="form-control" />
          </div>
          <div class="hidden">
            <label>Kode Barang <span style="color:red">*</span></label>
            <input v-model="form.barangentry_code_id" type="text" required class="form-control" />
          </div>
          <div class="hidden">
            <label>preOrder ID <span style="color:red">*</span></label>
            <input v-model="form.preOrdeBarang_id" type="text" required class="form-control" />
          </div>

          <div>
            <label>Nama Akun <span style="color:red">*</span></label>
            <input v-model="form.preOrderBarang_nama_akun" type="text" required class="form-control" />
          </div>

          <div>
            <label>Target Selesai <span style="color:red">*</span></label>
            <input v-model="form.preOrderBarang_target_selesai" type="date" required class="form-control" />
          </div>

          <div>
            <label>Uang Muka (DP) <span style="color:red">*</span></label>
            <input v-model="formattedModal" @input="updateModal" type="text" required class="form-control text-right" />
          </div>

          <div>
            <label>Deskripsi Ulos <span style="color:red">*</span></label>
            <textarea v-model="form.preOrderBarang_deskripsi_barang" rows="3" required class="form-control"></textarea>
          </div>

          <!-- <div>
            <label>Gambar</label>
            <div class="flex items-center">
              <input type="file" @change="handleFileUpload" />
              <p class="text-xs text-gray-500 ml-2">Ukuran Maksimal: 5MB</p>
            </div>
          </div> -->

          <div>
            <label>Nama Ulos <span style="color:red">*</span></label>
            <input v-model="form.preOrderBarang_nama_barang" type="text" required class="form-control" />
          </div>

          <div>
            <label>No Telepon <span style="color:red">*</span></label>
            <input v-model="form.preOrderBarang_no_telepon" type="text" required class="form-control" />
          </div>

          <div>
            <label>Total Pembayaran <span style="color:red">*</span></label>
            <input v-model="formattedHargaNet" @input="updateHargaNet" type="text" required
              class="form-control text-right" />
          </div>

          <div>
            <label>Sisa Pembayaran <span style="color:red">*</span></label>
            <input v-model="formattedPriceTag" @input="updatePriceTag" type="text" required
              class="form-control text-right" />
          </div>

          <div>
            <label>Catatan <span style="color:red">*</span></label>
            <textarea v-model="form.preOrderBarang_catatan" rows="3" required class="form-control"></textarea>
          </div>
        </div>

        <div class="flex justify-end mt-6 space-x-4">
          <button type="button" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400" @click="closeModal">
            Batal
          </button>
          <button type="button" @click="submitForm" :disabled="isSubmittingEdit"
            class="bg-cyan-600 text-white px-6 py-2 rounded-md hover:bg-cyan-700 transition disabled:bg-gray-400">
            {{ isSubmittingEdit ? 'Menyimpan...' : 'Tambah' }}
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
const isSubmittingEdit = ref(false);

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
  code: '',
  barangentry_code_id: '',
  barangentry_nama: '',
  barangentry_warna: '',
  barangentry_nama_penenun: '',
  barangentry_nama_panirat: '',
  barangentry_dryer: '',
  barangentry_modal: 0,
  barangentry_price_tag: 0,
  barangentry_harga_net: 0,
  barangentry_ukuran_ulos: '',
  barangentry_ukuran_mandar: '',
  barangentry_jumlah_barang: 0,
  preOrdeBarang_id: 0,
  preOrderBarang_transaksi_id: 0,
  preOrderBarang_nama_barang: '',
  preOrderBarang_nama_akun: '',
  preOrderBarang_no_telepon: '',
  preOrderBarang_target_selesai: 0,
  preOrderBarang_total_pembayaran: 0,
  preOrderBarang_uang_muka: 0,
  preOrderBarang_sisa_pembayaran: 0,
  preOrderBarang_deskripsi_barang: '',
  preOrderBarang_catatan: '',
  preOrderBarang_barang_entry_id: 0
});

const formatNumber = (value) => {
  return Number(value || 0).toLocaleString('id-ID');
};

const parseNumber = (val) => {
  return Number(String(val).replace(/\./g, '')) || 0;
};

const formattedModal = ref('');
const formattedPriceTag = ref('');
const formattedHargaNet = ref('');

const updateModal = (e) => {
  const raw = parseNumber(e.target.value);
  form.value.preOrderBarang_uang_muka = raw;
  formattedModal.value = formatNumber(raw);
};

const updatePriceTag = (e) => {
  const raw = parseNumber(e.target.value);
  form.value.preOrderBarang_sisa_pembayaran = raw;
  formattedPriceTag.value = formatNumber(raw);
};

const updateHargaNet = (e) => {
  const raw = parseNumber(e.target.value);
  form.value.preOrderBarang_total_pembayaran = raw;
  formattedHargaNet.value = formatNumber(raw);
};

const loadData = async () => {
  try {
    const res = await axios.get(`${url.value}/api/pre-order-barang/preOrderEntry/${props.id}`);

    const resEntry = await axios.get(`${url.value}/api/entrybarang/${props.id}`);
    form.value = { ...res.data.data, ...resEntry.data.data };

    const code = await axios.get(`${url.value}/api/codebarang/${form.value.barangentry_code_id}`)
    form.value.code = code.data.code_nama;

    formattedModal.value = formatNumber(form.value.preOrderBarang_uang_muka);
    formattedPriceTag.value = formatNumber(form.value.preOrderBarang_sisa_pembayaran);
    formattedHargaNet.value = formatNumber(form.value.preOrderBarang_total_pembayaran);
  } catch (err) {
    Swal.fire("Gagal", "Gagal memuat data!", "error");
  }
};

const submitForm = async () => {
  isSubmittingEdit.value = true;
  try {

    const payload = {
      preOrdeBarang_id: form.value.preOrdeBarang_id,
      preOrderBarang_transaksi_id: '',
      preOrderBarang_nama_barang: form.value.barangentry_nama,
      preOrderBarang_nama_akun: form.value.preOrderBarang_nama_akun,
      preOrderBarang_no_telepon: form.value.preOrderBarang_no_telepon,
      preOrderBarang_target_selesai: toDatetimeString(form.value.preOrderBarang_target_selesai),
      preOrderBarang_total_pembayaran: Number(form.value.preOrderBarang_total_pembayaran),
      preOrderBarang_uang_muka: Number(form.value.preOrderBarang_uang_muka),
      preOrderBarang_sisa_pembayaran: Number(form.value.preOrderBarang_sisa_pembayaran),
      preOrderBarang_deskripsi_barang: form.value.preOrderBarang_deskripsi_barang,
      preOrderBarang_catatan: form.value.preOrderBarang_catatan,
      preOrderBarang_barang_entry_id: String(form.value.preOrderBarang_barang_entry_id),
    }
    await axios.post(`${url.value}/api/pre-order-barang`, payload);
    await Swal.fire("Berhasil", "Data berhasil disimpan!", "success");
    emit('saved');
    closeModal();
  } catch (err) {
    Swal.fire("Gagal", "Gagal menyimpan data!", "error");
  } finally {
    isSubmittingEdit.value = false;
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

.bg-gray-800 {
  background-color: rgba(0, 0, 0, 0.5);
}
</style>
