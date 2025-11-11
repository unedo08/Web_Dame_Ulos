<template>
    <div v-if="show" class="fixed inset-0 flex items-center justify-center bg-black/40 z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-[700px] border border-gray-300">

            <div ref="printContent">
                <div class="space-y-2 mb-6 text-sm">
                    <div class="flex">
                        <span class="font-semibold w-40">Nama Penerima</span> :
                        <span class="ml-2">{{ data.nama }}</span>
                    </div>
                    <div class="flex">
                        <span class="font-semibold w-40">Nomor Telepon/Hp</span> :
                        <span class="ml-2">{{ data.telp }}</span>
                    </div>
                    <div class="flex">
                        <span class="font-semibold w-40">Alamat</span> :
                        <span class="ml-2">{{ data.alamat }}</span>
                    </div>
                </div>

                <hr class="border-gray-300 mb-4" />

                <div class="flex justify-start">
                    <img src="/image/DameUlosPengiriman.png" class="h-32" alt="DAME ULOS" />
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button @click="triggerPrint" class="px-4 py-2 mx-4 bg-green-600 text-white rounded hover:bg-green-700">
                    Print
                </button>
                <button @click="$emit('close')" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";

const props = defineProps({
    show: Boolean,
    data: Object
});

const printContent = ref(null);

function triggerPrint() {
    const content = printContent.value;

    if (!content) return;

    const printWindow = window.open("", "", "width=800,height=600");

    printWindow.document.write(`
    <html>
      <head>
        <title>Packaging Data</title>
        <style>
          body {
            font-family: Arial, sans-serif;
            padding: 20px;
            font-size: 14px;
          }
          .title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 10px;
          }
          .section {
            margin-bottom: 14px;
          }
        </style>
      </head>
      <body>
        ${content.innerHTML}
      </body>
    </html>
  `);

    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
}
</script>
