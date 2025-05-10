<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="bg-white border border-gray-300 shadow-lg rounded-[10px] p-6 w-[500px] max-h-[80vh] overflow-auto">
            <h2 class="text-xl font-bold mb-4">{{ modalTitle }}</h2>

            <!-- Modal DESC -->
            <template v-if="type === 'desc'">
                <div class="mb-4">
                    <label>Scan / Input Kode Barang:</label>
                    <input v-model="barcode" @keyup.enter="handleScan" class="w-full border rounded px-3 py-2"
                        autofocus />
                </div>
                <div v-if="barang" class="space-y-3">
                    <input v-model="barang.kode_barang" disabled class="w-full border px-3 py-2" />
                    <input v-model="barang.nama_barang" disabled class="w-full border px-3 py-2" />
                    <input v-model="barang.jumlah_barang" disabled class="w-full border px-3 py-2" />
                </div>
            </template>

            <!-- Modal SIZE -->
            <template v-else-if="type === 'size'">
                <div class="space-y-3">
                    <input v-model="size.mandar" placeholder="Ukuran Mandar" class="w-full border px-3 py-2" />
                    <input v-model="size.ulos" placeholder="Ukuran Ulos" class="w-full border px-3 py-2" />
                </div>
            </template>

            <!-- Modal PRICE TAG -->
            <template v-else-if="type === 'priceTag'">
                <div class="text-gray-600">Tombol atau konten cetak price tag bisa ditaruh di sini</div>
            </template>

            <div class="mt-6 flex justify-end space-x-3">
                <button @click="$emit('close')" class="bg-gray-500 text-white px-4 py-2 rounded">Close</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue'
// import axios from 'axios'
// use to databaase
// onMounted(async () =>{
//     try{
//         const response = await axios.get('api/ulos')
//         barangDatabase.value = response.data || []
//     }catch{
//         console.error('Error Data: ', error)
//     }
// })

const props = defineProps({
    show: Boolean,
    type: String,
    barangDatabase: Array
})
const emit = defineEmits(['close', 'scanned', 'sizeSubmitted'])

const barcode = ref('')
const barang = ref(null)
const size = ref({ mandar: '', ulos: '' })

const modalTitle = computed(() => {
    switch (props.type) {
        case 'desc': return 'Scan Barang'
        case 'size': return 'Input Ukuran'
        case 'priceTag': return 'Cetak Price Tag'
        default: return 'Modal'
    }
})

function handleScan() {
    const found = props.barangDatabase.find(b => b.kode_barang === barcode.value.trim())
    if (found) {
        barang.value = { ...found }
        emit('scanned', { ...found })
    } else {
        alert('Barang tidak ditemukan.')
        barang.value = null
    }
}

watch(() => props.show, (val) => {
    if (!val) {
        barcode.value = ''
        barang.value = null
        size.value = { mandar: '', ulos: '' }
    }
})
</script>