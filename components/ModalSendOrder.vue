<template>
    <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60">
        <div class="bg-white rounded-lg shadow-xl max-w-[40%] w-full overflow-y-auto p-6 relative animate-fadeIn">
            <h2 class="text-xl font-semibold mb-6 border-b pb-3">Kirim Order</h2>

            <div class="grid grid-cols-2 gap-4">

                <!-- NAMA AKUN -->
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">Nama Penerima <span style="color:red">*</span></label>
                    <input v-model="form.nama_akun" type="text"
                        class="border border-gray-300 bg-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 outline-none"
                        placeholder="Nama Penerima" />
                </div>

                <!-- TELEPON -->
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">No Telepon/WA <span style="color:red">*</span></label>
                    <input v-model="form.no_telepon" type="text"
                        class="border border-gray-300 bg-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 outline-none"
                        placeholder="No Telepon/WA" />
                </div>

                <!-- CARA BAYAR -->
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">Metode Pembayaran <span style="color:red">*</span></label>
                    <select v-model="form.cara_bayar"
                        class="border border-gray-300 bg-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 outline-none">
                        <option disabled value="">-- Pilih Cara Bayar --</option>
                        <option v-for="opt in caraBayarOptions" :key="opt">{{ opt }}</option>
                    </select>
                </div>

                <!-- TOTAL PEMBAYARAN
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">Total Pembayaran <span style="color:red">*</span></label>
                    <input :value="formatCurrency(form.total_pembayaran)"
                        @input="e => form.total_pembayaran = unformat(e.target.value)" inputmode="numeric"
                        class="border border-gray-300 bg-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 outline-none" />
                </div> -->

                <!-- HARGA KIRIM -->
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">Biaya Pengiriman <span style="color:red">*</span></label>
                    <input :value="formatCurrency(form.harga_kirim)"
                        @input="e => form.harga_kirim = unformat(e.target.value)" inputmode="numeric"
                        class="border border-gray-300 bg-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 outline-none"
                        placeholder="Biaya Pengiriman" />
                </div>

                <!-- ALAMAT FULL WIDTH -->
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">Alamat <span style="color:red">*</span></label>
                    <textarea v-model="form.alamat" rows="2"
                        class="border border-gray-300 bg-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 outline-none"
                        placeholder="Alamat"></textarea>
                </div>

                <!-- JENIS PENGIRIMAN -->

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">Pengiriman <span style="color:red">*</span></label>
                    <input v-model="form.jenis_pengiriman" type="text"
                        class="border border-gray-300 bg-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 outline-none"
                        placeholder="Pengiriman" />
                </div>
                <!-- <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">Pengiriman <span style="color:red">*</span></label>
                    <select v-model="form.jenis_pengiriman"
                        class="border border-gray-300 bg-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 outline-none">
                        <option disabled value="">-- Pilih Jenis Pengiriman --</option>
                        <option v-for="opt in jenisPengirimanOptions" :key="opt">{{ opt }}</option>
                    </select>
                </div> -->


            </div>

            <!-- BUTTON -->
            <div class="flex justify-end gap-3 mt-8">
                <button @click="close" class="px-5 py-2 rounded-lg bg-gray-200 hover:bg-gray-300">Batal</button>
                <button @click="submit"
                    class="px-6 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Kirim</button>
            </div>
        </div>
    </div>
</template>


<script setup>
import { ref } from "vue";

const props = defineProps({ visible: Boolean });
const emit = defineEmits(["close", "submitted"]);

const caraBayarOptions = ["Dana", "OVO", "QRIS", "Transfer Bank", "Tunai"];
const jenisPengirimanOptions = ["JNE", "J&T", "Grab", "Gojek", "Lainnya"];

const form = ref({
    cara_bayar: "",
    total_pembayaran: "",
    harga_kirim: "",
    jenis_pengiriman: "",
    alamat: "",
    nama_akun: "",
    no_telepon: "",
});

function close() {
    emit("close");
}
function submit() {
    emit("submitted", form.value);
    resetForm();
    close();
}

function resetForm() {
    form.value = {
        cara_bayar: "",
        total_pembayaran: "",
        harga_kirim: "",
        jenis_pengiriman: "",
        alamat: "",
        nama_akun: "",
        no_telepon: "",
    };
}

function formatCurrency(val) {
    if (!val) return "";
    return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function unformat(val) {
    return val.replace(/\D/g, "") || "";
}
</script>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-8px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fadeIn {
    animation: fadeIn 0.25s ease-out;
}
</style>
