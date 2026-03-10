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
              Harga Net <span class="required">*</span>
            </label>
            <div class="flex">
              <div class="bg-gray-100 border border-gray-300 rounded-l-md px-3 flex items-center text-gray-600 text-sm">
                Rp
              </div>
              <input :value="formattedHargaNet" @input="updateHargaNet" type="text"
                class="w-full border border-gray-300 px-2 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 text-right"
                placeholder="Masukkan Harga Net" />
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

        <div class="md:col-span-2 mt-6">
          <label class="judul-label block text-sm font-medium mb-2">
            Gambar Pre Order
          </label>
          <div class="border border-dashed border-gray-400 rounded-lg p-4">
            <div v-if="previewImage" class="mb-3">
              <img :src="previewImage" class="max-h-48 rounded shadow border" />
            </div>
            <input type="file" accept="image/*" @change="handleImageUpload" />
            <div class="flex gap-3 mt-3">
              <button type="button" class="bg-cyan-600 text-white px-4 py-2 rounded hover:bg-cyan-700"
                @click="uploadImage">
                Upload / Replace
              </button>
              <button type="button" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                @click="viewImage">
                View
              </button>
              <!-- <button type="button" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
                @click="deleteImage">
                Delete
              </button> -->
            </div>
            <div v-if="uploadProgress > 0" class="w-full bg-gray-200 h-2 rounded-full mt-3">
              <div class="bg-green-600 h-2 rounded-full" :style="{ width: uploadProgress + '%' }"></div>
            </div>
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

  <div v-if="showImageModal" class="fixed inset-0 bg-opacity-70 flex items-center justify-center z-50">
    <div class="bg-white p-4 rounded-lg max-w-2xl w-full relative">
      <button class="absolute right-3 top-2 text-xl text-gray-500 hover:text-black" @click="showImageModal = false">
        ✕
      </button>
      <h3 class="text-lg font-semibold mb-4">
        Preview Gambar
      </h3>
      <div class="flex justify-center">
        <img :src="serverImageUrl" class="max-h-[600px] rounded shadow border" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import Swal from 'sweetalert2'
import { useRuntimeConfig } from '#imports'

const url = ref('')
const isSubmittingEdit = ref(false)
const { $api } = useNuxtApp();
const selectedImage = ref(null)
const previewImage = ref(null)
const showImageModal = ref(false)
const serverImageUrl = ref('')
const uploadProgress = ref(0)

onMounted(async () => {
  const config = useRuntimeConfig()
  url.value = config.public.apiBase
})

const props = defineProps({
  id: Number,
  preorderId: Number,
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
  preOrderBarang_path_gambar: ''
})

const formatNumber = (value) => Number(value || 0).toLocaleString('id-ID')
const parseNumber = (val) => Number(String(val).replace(/\./g, '')) || 0

const formattedModal = ref('')
const formattedPriceTag = ref('')
const formattedHargaNet = ref('')

const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (!file) return
  selectedImage.value = file
  previewImage.value = URL.createObjectURL(file)

}

const uploadImage = async () => {
  if (!selectedImage.value) {
    Swal.fire("Warning", "Pilih gambar terlebih dahulu", "warning")
    return
  }
  try {
    const formData = new FormData()
    formData.append("image", selectedImage.value)
    await $api.post(
      `${url.value}/api/pre-order-barang/${props.preorderId}/image`,
      formData,
      {
        headers: {
          "Content-Type": "multipart/form-data"
        },
        onUploadProgress: (e) => {
          uploadProgress.value =
            Math.round((e.loaded * 100) / e.total)
        }
      }
    )
    Swal.fire("Berhasil", "Gambar berhasil diupload", "success")
    uploadProgress.value = 0
  } catch (err) {
    Swal.fire("Error", "Upload gambar gagal", "error")
  }
}

const viewImage = async () => {
  try {
    const res = await $api.get(
      `${url.value}/api/pre-order-barang/${props.preorderId}/image`
    )
    serverImageUrl.value = res.data.image_url
    showImageModal.value = true
  } catch (err) {
    Swal.fire("Error", "Gagal mengambil gambar", "error")
  }
}

const loadImage = async () => {
  try {
    const res = await $api.get(
      `${url.value}/api/pre-order-barang/${props.preorderId}/image`
    )

    previewImage.value = res.data.image_url;
  } catch (err) {
    previewImage.value = null
  }
}

const deleteImage = async () => {
  const confirm = await Swal.fire({
    title: "Hapus gambar?",
    text: "Gambar akan dihapus permanen",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya Hapus"
  })

  if (!confirm.isConfirmed) return
  try {
    await $api.delete(
      `${url.value}/api/pre-order-barang/${props.preorderId}/image`
    )
    Swal.fire("Berhasil", "Gambar berhasil dihapus", "success")
    previewImage.value = null
    serverImageUrl.value = ""
  } catch (err) {
    Swal.fire("Error", "Gagal menghapus gambar", "error")
  }
}

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

const loadData = async () => {
  try {
    const res = await $api.get(`${url.value}/api/pre-order-barang/preOrderEntry/${props.id}`)
    const resEntry = await $api.get(`${url.value}/api/entrybarang/${props.id}`)
    form.value = { ...res.data.data, ...resEntry.data.data }
    const code = await $api.get(`${url.value}/api/codebarang/${form.value.barangentry_code_id}`)
    form.value.code = code.data.code_nama

    formattedModal.value = formatNumber(form.value.preOrderBarang_uang_muka)
    formattedPriceTag.value = formatNumber(form.value.preOrderBarang_sisa_pembayaran)
    formattedHargaNet.value = formatNumber(form.value.preOrderBarang_total_pembayaran)

    await loadImage();

  } catch (err) {
    Swal.fire('Gagal', 'Gagal memuat data!', 'error')
  }
}

const submitForm = async () => {
  isSubmittingEdit.value = true

  try {

    const formData = new FormData()

    formData.append("preOrdeBarang_id", form.value.preOrdeBarang_id)
    formData.append("preOrderBarang_transaksi_id", "")
    formData.append("preOrderBarang_nama_barang", form.value.barangentry_nama)
    formData.append("preOrderBarang_nama_akun", form.value.preOrderBarang_nama_akun)
    formData.append("preOrderBarang_no_telepon", form.value.preOrderBarang_no_telepon)
    formData.append(
      "preOrderBarang_target_selesai",
      toDatetimeString(form.value.preOrderBarang_target_selesai)
    )
    formData.append(
      "preOrderBarang_total_pembayaran",
      Number(form.value.preOrderBarang_total_pembayaran)
    )
    formData.append(
      "preOrderBarang_uang_muka",
      Number(form.value.preOrderBarang_uang_muka)
    )
    formData.append(
      "preOrderBarang_sisa_pembayaran",
      Number(form.value.preOrderBarang_sisa_pembayaran)
    )
    formData.append(
      "preOrderBarang_deskripsi_barang",
      form.value.preOrderBarang_deskripsi_barang
    )
    formData.append(
      "preOrderBarang_catatan",
      form.value.preOrderBarang_catatan
    )
    formData.append(
      "preOrderBarang_barang_entry_id",
      String(form.value.preOrderBarang_barang_entry_id)
    )
    console.log('xzc', selectedImage);
    

    if (selectedImage.value) {
      formData.append("preOrderBarang_path_gambar", selectedImage.value)
    }

    await $api.post(
      `${url.value}/api/pre-order-barang`,
      formData,
      {
        headers: {
          "Content-Type": "multipart/form-data"
        },
        onUploadProgress: (e) => {
          uploadProgress.value =
            Math.round((e.loaded / e.total) * 100)
        }
      }
    )
    await Swal.fire("Berhasil", "Data berhasil disimpan!", "success")
    emit("saved")
    closeModal()
  } catch (err) {
    Swal.fire("Gagal", "Gagal menyimpan data!", "error")
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
