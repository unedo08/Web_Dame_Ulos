<template>
  <div>
    <!-- Barcode text at the top -->
    <div class="text-xl font-semibold mb-4">
      Barcode
    </div>

    <div class="flex items-center justify-between pt-2">
      <!-- Search Box with fixed width -->
      <div class="flex-1">
        <!-- Untuk API nya nanti -->
        <!-- <input class="search-box p-2 border rounded-md" v-model="searchQuery" type="text"
          placeholder="Search products..." @input="searchProducts" /> -->

        <!-- Search untuk ss an -->
        <input class="search-box p-2 border rounded-md" v-model="searchQuery" type="text"
          placeholder="Search products..." />
      </div>

      <!-- Add Button that triggers Modal -->
      <div>
        <button class="btn-add bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[104px] h-[34px]"
          @click="openModal">
          + Tambah
        </button>
      </div>
    </div>

    <!-- Product Table -->
    <div>
      <table class="datatable">
        <thead>
          <tr>
            <th>#</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Jumlah</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="barang in listBarang" :key="barang.kode_barang">
            <td>{{ barang.no }}</td>
            <td>{{ barang.kode_barang }}</td>
            <td>{{ barang.nama_barang }}</td>
            <td>{{ barang.jumlah_barang }}</td>
            <td class="space-x-2">
              <button class="px-2 py-1 bg-green-500 text-white  hover:bg-green-600 rounded-[15px]"
                @click="openModelPrint(barang)">
                Print
              </button>
              <button class="px-2 py-1 bg-red-500 text-white hover:bg-red-600 rounded-[15px]"
                @click="deleteProduct(barang.no, barang.nama_barang)">
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Dialog -->
    <div v-if="isModalOpen" class="fixed inset-0 flex justify-center items-center bg-gray-800 bg-opacity-50">
      <div class="bg-white p-6 rounded-lg shadow-lg max-w-lg w-full">
        <h3 class="text-xl font-semibold mb-4">Tambah Barang</h3>
        <form @submit.prevent="submitProduct">
          <div class="mb-4">
            <label for="kode_barang" class="block text-sm font-medium text-gray-700">Jenis Barang</label>
            <input v-model="newProduct.kode_barang" type="text" id="kode_barang"
              class="mt-1 block w-full border-[1px] border-gray rounded-md shadow-sm w-[382px] h-[41px]"
              placeholder=" Masukkan nama barang" required />
          </div>

          <div class="mb-4">
            <div class="flex items-center space-x-4 mt-2">
              <div class="flex items-center">
                <input v-model="newProduct.jenis_barang" type="radio" id="tunggal" value="Tunggal" class="mr-2" />
                <label for="tunggal" class="text-sm text-gray-700">Tunggal</label>
              </div>
              <div class="flex items-center">
                <input v-model="newProduct.jenis_barang" type="radio" id="majemuk" value="Majemuk" class="mr-2" />
                <label for="majemuk" class="text-sm text-gray-700">Majemuk</label>
              </div>
            </div>
          </div>

          <div class="flex justify-end">
            <button type="button" @click="closeModal" class="mr-4 text-gray-500">Cancel</button>
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Save</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Print -->
    <div v-if="isModalPrintOpen" class="fixed inset-0 flex justify-center items-center bg-gray-800 bg-opacity-50">
      <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
        <h3 class="text-lg font-semibold mb-4">Print Barcode</h3>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Jumlah Kode</label>
          <input type="number" v-model="printJumlah" min="1"
            class="mt-1 block w-full border border-gray-300 rounded-md p-2" placeholder="Masukkan jumlah kode" />
        </div>
        <div class="flex justify-end">
          <button class="mr-4 text-gray-500" @click="closePrintModal">Cancel</button>
          <button class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600" @click="handlePrint">
            Print
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

// State
const searchQuery = ref('')
const barang = ref([
  { no: 1, kode_barang: 'Product1', nama_barang: 'Category A', jumlah_barang: 10 },
  { no: 2, kode_barang: 'Product2', nama_barang: 'Category B', jumlah_barang: 25 },
  { no: 3, kode_barang: 'Product3', nama_barang: 'Category A', jumlah_barang: 15 },
  { no: 4, kode_barang: 'Product4', nama_barang: 'Category C', jumlah_barang: 40 },
])

const newProduct = ref({
  kode_barang: '',
  nama_barang: '',
  jumlah_barang: 0,
  jenis_barang: 'Tunggal'
})

const isModalOpen = ref(false)
const isModalPrintOpen = ref(false)
const selectedProduct = ref(null)
const printJumlah = ref(1)

onMounted(() => {
  // Pastikan modal print tidak terbuka setelah halaman dimuat
  isModalPrintOpen.value = false
})

// List with Search
const listBarang = computed(() => {
  if (!searchQuery.value) return barang.value
  return barang.value.filter(item =>
    item.kode_barang.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    item.nama_barang.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

// Modal Add
const openModal = () => {
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
}

const submitProduct = () => {
  const product = {
    no: barang.value.length + 1,
    kode_barang: newProduct.value.kode_barang,
    nama_barang: newProduct.value.nama_barang,
    jumlah_barang: newProduct.value.jumlah_barang,
  }
  barang.value.push(product)
  closeModal()
  newProduct.value = { kode_barang: '', nama_barang: '', jumlah_barang: 0, jenis_barang: 'Tunggal' }
}

// Modal Print
const openModelPrint = (product) => {
  console.log("Open print modal", product)
  selectedProduct.value = { ...product }
  printJumlah.value = product.jumlah_barang
  isModalPrintOpen.value = true
}

const closePrintModal = () => {
  console.log("close print modal")
  isModalPrintOpen.value = false
  selectedProduct.value = null
}

// Print Barcode
const handlePrint = () => {
  const baseCode = 'ULOSS00001'

  const win = window.open('', '', 'width=800,height=600')
  if (!win) return

  win.document.write(`
    <!DOCTYPE html>
    <html>
      <head>
        <title>Print Barcode</title>
        <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
        <style>
          @media print {
            @page {
              size: landscape;
            }
          }
          body {
            margin: 0;
            height: 100vh;
            position: relative;
          }
          .barcode-container {
            position: absolute;
            bottom: 20px;
            right: 20px;
            text-align: center;
            font-size: 10px;
          }
        </style>
      </head>
      <body>
        <div class="barcode-container">
          <div>${baseCode}</div>
          <svg id="barcode"></svg>
        </div>

        <script>
          window.onload = function() {
            JsBarcode("#barcode", "${baseCode}", {
              format: "CODE128",
              lineColor: "#000",
              width: 1.5,
              height: 50,
              displayValue: false
            })
            window.print()
          }
        <\/script>
      </body>
    </html>
  `)

  win.document.close()
  closePrintModal()
}

// Delete Product
const deleteProduct = (no, nama_barang) => {  
  if (confirm(`Anda yakin ingin menghapus "${nama_barang}" ini?`)) {
    barang.value = barang.value.filter(item => item.no !== no)
  }
}
</script>

<style scoped>
.search-box {
  border: 1px solid #ccc;
  padding: 10px;
  width: 385px;
  height: 34px;
}

.datatable {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

.datatable th,
.datatable td {
  padding: 10px;
  border: 1px solid #ddd;
  text-align: left;
}

.datatable th {
  background-color: #f4f4f4;
}

.btn-add {
  background-color: #3D8BFD;
  color: white;
  border-radius: 5px;
  cursor: pointer;
}

.btn-add:hover {
  background-color: #3D8BFD;
}

/* Modal styles */
.fixed {
  position: fixed;
}

.bg-gray-800 {
  background-color: rgba(0, 0, 0, 0.5);
}
</style>
