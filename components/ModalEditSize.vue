<template>
  <div v-if="show" class="po-size-overlay fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 z-[2000]" @click.self="closeModal">
    <div class="po-size-modal bg-white rounded-lg p-6 w-full max-w-3xl max-h-[90vh] overflow-y-auto shadow-lg relative">
      <button class="absolute top-2 right-2 text-gray-500 hover:text-black" @click="closeModal">✕</button>
      <h2 class="text-xl font-bold mb-6">Edit Barang Ready</h2>

      <form>
        <div class="po-size-fields grid grid-cols-1 gap-4">
          <div>
            <label>Kode Barang <span style="color:red">*</span></label>
            <input v-model="form.code" type="text" required class="form-control bg-gray-100 border-gray-300" readonly
              disabled />
          </div>
          <div class="hidden">
            <label>Kode Barang <span style="color:red">*</span></label>
            <input v-model="form.barangentry_code_id" type="text" required class="form-control" />
          </div>

          <div>
            <label>Ukuran Ulos <span style="color:red">*</span></label>
            <div class="po-size-input-group">
              <input v-model="form.barangentry_ukuran_ulos" type="text" required class="form-control" />
              <div class="po-size-unit">cm</div>
            </div>
          </div>

          <div>
            <label>Ukuran Mandar</label>
            <div class="po-size-input-group">
              <input v-model="form.barangentry_ukuran_mandar" type="text" class="form-control" />
              <div class="po-size-unit">cm</div>
            </div>
          </div>
        </div>

        <div class="po-size-actions flex justify-end mt-6 space-x-4">
          <button type="button" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400" @click="closeModal">
            Batal
          </button>
          <button type="button" @click="submitForm" :disabled="isSubmittingEdit"
            class="bg-cyan-600 text-white px-6 py-2 rounded-md hover:bg-cyan-700 transition disabled:bg-gray-400">
            {{ isSubmittingEdit ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import Swal from 'sweetalert2';
import { useRuntimeConfig } from "#imports";

const url = ref("");
const isSubmittingEdit = ref(false);
const { $api } = useNuxtApp();

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
  barangentry_ukuran_ulos: '',
  barangentry_ukuran_mandar: '',
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
  form.value.barangentry_modal = raw;
  formattedModal.value = formatNumber(raw);
};

const updatePriceTag = (e) => {
  const raw = parseNumber(e.target.value);
  form.value.barangentry_price_tag = raw;
  formattedPriceTag.value = formatNumber(raw);
};

const updateHargaNet = (e) => {
  const raw = parseNumber(e.target.value);
  form.value.barangentry_harga_net = raw;
  formattedHargaNet.value = formatNumber(raw);
};

const loadData = async () => {
  try {
    const res = await $api.get(`${url.value}/api/entrybarang/${props.id}`);
    form.value = { ...res.data.data };
    const code = await $api.get(`${url.value}/api/codebarang/${form.value.barangentry_code_id}`)
    form.value.code = code.data.code_nama;

  } catch (err) {
    Swal.fire("Gagal", "Gagal memuat data!", "error");
  }
};

const submitForm = async () => {
  isSubmittingEdit.value = true;
  try {
    if (!form.value.barangentry_ukuran_ulos || form.value.barangentry_ukuran_ulos.trim() === "") {
      Swal.fire("Gagal!", "Ukuran Ulos wajib diisi.", "error");
      isSubmittingEdit.value = false;
      return;
    }

    const payload = {
      barangentry_code_id: String(form.value.barangentry_code_id),
      barangentry_ukuran_ulos: form.value.barangentry_ukuran_ulos,
      barangentry_ukuran_mandar: form.value.barangentry_ukuran_mandar,
    }
    await $api.put(`${url.value}/api/entrybarang/ready-stock-size/${props.id}`, payload);
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

.po-size-input-group {
  display: flex;
  min-width: 0;
}

.po-size-input-group .form-control {
  min-width: 0;
  flex: 1;
  margin-bottom: 0;
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
}

.po-size-unit {
  display: flex;
  flex: 0 0 auto;
  align-items: center;
  border: 1px solid #d1d5db;
  border-left: 0;
  border-radius: 0 0.375rem 0.375rem 0;
  background-color: #f3f4f6;
  padding: 0 12px;
  color: #4b5563;
  font-size: 13px;
}

.bg-gray-800 {
  background-color: rgba(0, 0, 0, 0.5);
}

@media (max-width: 767px) {
  .po-size-overlay {
    align-items: center !important;
    justify-content: center !important;
    padding: 12px !important;
    overflow-y: auto !important;
  }

  .po-size-modal {
    position: relative !important;
    top: auto !important;
    left: auto !important;
    transform: none !important;
    box-sizing: border-box;
    width: calc(100% - 24px) !important;
    max-width: 560px !important;
    max-height: calc(100vh - 24px) !important;
    max-height: calc(100dvh - max(24px, env(safe-area-inset-top) + 12px)) !important;
    margin: 0 !important;
    padding: 16px !important;
    overflow-y: auto !important;
  }

  .po-size-modal h2 {
    margin: 0 36px 16px 0 !important;
    line-height: 1.35;
  }

  .po-size-fields {
    grid-template-columns: minmax(0, 1fr) !important;
    gap: 12px !important;
  }

  .po-size-fields > div:not(.hidden) {
    min-width: 0;
  }

  .po-size-fields .form-control {
    box-sizing: border-box;
    margin-bottom: 0;
  }

  .po-size-actions {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px !important;
    margin-top: 16px !important;
  }

  .po-size-actions button {
    width: 100%;
    min-width: 0;
    padding-left: 8px;
    padding-right: 8px;
    white-space: normal;
  }
}
</style>
