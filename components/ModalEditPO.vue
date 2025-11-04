<template>
  <div v-if="show" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-[55%] w-full overflow-y-auto p-6 relative">
      <button class="absolute top-3 right-4 text-gray-500 hover:text-black text-2xl" @click="closeModal">✕</button>
      <h2 class="text-xl font-semibold mb-4">Edit Pre-Order Transaksi</h2>

      <form class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- KIRI -->
        <div class="space-y-4">
          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              Kode Barang <span class="required">*</span>
            </label>
            <input v-model="form.code" type="text" readonly
              class="kode_po w-full border border-gray-300 bg-gray-500 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500" />
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              Nama Akun <span class="required">*</span>
            </label>
            <input v-model="form.preOrderBarang_nama_akun" type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
              placeholder="Masukkan Nama Akun" />
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              Target Selesai <span class="required">*</span>
            </label>
            <input v-model="form.preOrderBarang_target_selesai" type="date"
              class="w-full border border-gray-300 rounded-md px-4 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan-500" />
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              Uang Muka (DP) <span class="required">*</span>
            </label>
            <div class="flex">
              <div class="bg-gray-100 border border-gray-300 rounded-l-md px-3 flex items-center text-gray-600 text-sm">
                Rp
              </div>
              <input :value="formattedModal" @input="updateModal" type="text"
                class="w-full border border-gray-300 px-2 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 text-right"
                placeholder="Masukkan Uang Muka (DP)" />
            </div>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              Deskripsi Ulos <span class="required">*</span>
            </label>
            <textarea v-model="form.preOrderBarang_deskripsi_barang" rows="3"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
              placeholder="Masukkan Deskripsi Ulos"></textarea>
          </div>
        </div>

        <!-- KANAN -->
        <div class="space-y-4">
          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              Nama Ulos <span class="required">*</span>
            </label>
            <input v-model="form.preOrderBarang_nama_barang" type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
              placeholder="Masukkan Nama Ulos" />
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              No Telepon <span class="required">*</span>
            </label>
            <input v-model="form.preOrderBarang_no_telepon" type="text"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
              placeholder="Masukkan Nomor Telepon" />
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              Total Pembayaran <span class="required">*</span>
            </label>
            <div class="flex">
              <div class="bg-gray-100 border border-gray-300 rounded-l-md px-3 flex items-center text-gray-600 text-sm">
                Rp
              </div>
              <input :value="formattedHargaNet" @input="updateHargaNet" type="text"
                class="w-full border border-gray-300 px-2 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 text-right"
                placeholder="Masukkan Total Pembayaran" />
            </div>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              Sisa Pembayaran <span class="required">*</span>
            </label>
            <div class="flex">
              <div class="bg-gray-100 border border-gray-300 rounded-l-md px-3 flex items-center text-gray-600 text-sm">
                Rp
              </div>
              <input :value="formattedPriceTag" @input="updatePriceTag" type="text" readonly
                class="sisa-pembayaran w-full border border-gray-300 px-2 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 text-right bg-gray-100"
                placeholder="Masukkan Sisa Pembayaran" />
            </div>
          </div>

          <div>
            <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
              Catatan <span class="required">*</span>
            </label>
            <textarea v-model="form.preOrderBarang_catatan" rows="3"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
              placeholder="Masukkan Catatan"></textarea>
          </div>
        </div>

        <!-- Tombol -->
        <div class="md:col-span-2 flex justify-end gap-4 mt-8">
          <button type="button" class="bg-gray-300 text-gray-800 px-6 py-2 rounded-md hover:bg-gray-400 transition"
            @click="closeModal">
            Batal
          </button>
          <button type="button" @click="submitForm" :disabled="isSubmittingEdit"
            class="bg-cyan-600 text-white px-6 py-2 rounded-md hover:bg-cyan-700 transition disabled:bg-gray-400">
            {{ isSubmittingEdit ? "Menyimpan..." : "Simpan" }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { useRuntimeConfig } from '#imports'

const url = ref('')
const isSubmittingEdit = ref(false)

onMounted(async () => {
  const config = useRuntimeConfig()
  url.value = config.public.apiBase
})

const props = defineProps({
  id: Number,
  show: Boolean,
})
const emit = defineEmits(['close', 'saved'])

const loading = ref(false)
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
  preOrderBarang_barang_entry_id: 0,
})

// ⚙️ Logic format angka & kirim API tetap sama
const formatNumber = (value) => Number(value || 0).toLocaleString('id-ID')
const parseNumber = (val) => Number(String(val).replace(/\./g, '')) || 0

const formattedModal = ref('')
const formattedPriceTag = ref('')
const formattedHargaNet = ref('')

const updateModal = (e) => {
  const raw = parseNumber(e.target.value)
  form.value.preOrderBarang_uang_muka = raw
  formattedModal.value = formatNumber(raw)
}

const updatePriceTag = (e) => {
  const raw = parseNumber(e.target.value)
  form.value.preOrderBarang_sisa_pembayaran = raw
  formattedPriceTag.value = formatNumber(raw)
}

const updateHargaNet = (e) => {
  const raw = parseNumber(e.target.value)
  form.value.preOrderBarang_total_pembayaran = raw
  formattedHargaNet.value = formatNumber(raw)
}

// 🔄 loadData & submitForm tidak diubah
const loadData = async () => {
  try {
    const res = await axios.get(`${url.value}/api/pre-order-barang/preOrderEntry/${props.id}`)
    const resEntry = await axios.get(`${url.value}/api/entrybarang/${props.id}`)
    form.value = { ...res.data.data, ...resEntry.data.data }
    const code = await axios.get(`${url.value}/api/codebarang/${form.value.barangentry_code_id}`)
    form.value.code = code.data.code_nama

    formattedModal.value = formatNumber(form.value.preOrderBarang_uang_muka)
    formattedPriceTag.value = formatNumber(form.value.preOrderBarang_sisa_pembayaran)
    formattedHargaNet.value = formatNumber(form.value.preOrderBarang_total_pembayaran)
  } catch (err) {
    Swal.fire('Gagal', 'Gagal memuat data!', 'error')
  }
}

const submitForm = async () => {
  isSubmittingEdit.value = true
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
    await axios.post(`${url.value}/api/pre-order-barang`, payload)
    await Swal.fire('Berhasil', 'Data berhasil disimpan!', 'success')
    emit('saved')
    closeModal()
  } catch (err) {
    Swal.fire('Gagal', 'Gagal menyimpan data!', 'error')
  } finally {
    isSubmittingEdit.value = false
  }
}

function toDatetimeString(dateStr) {
  const date = new Date(dateStr)
  const pad = (n) => (n < 10 ? '0' + n : n)
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} 00:00:00`
}

const closeModal = () => emit('close')

watch(() => props.show, (newVal) => {
  if (newVal && props.id) loadData()
})
</script>

<style scoped>
.required {
  color: red;
}

.judul-label {
  font-weight: 700;
}

.kode_po {
  background-color: #d1d0d07e !important;
}

.sisa-pembayaran {
  background-color: #d1d0d07e !important;
}

input,
textarea,
select {
  border-color: #e4e6fc !important;
  background-color: #fdfdff;
}

input:focus,
textarea:focus,
select:focus {
  border-color: #e4e6fc !important;
  box-shadow: 0 0 0 2px rgba(228, 230, 252, 0.5);
}

.bg-gray-800 {
  background-color: rgba(0, 0, 0, 0.5);
}
</style>
