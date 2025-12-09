<template>
  <div v-if="show" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 z-50">
    <div class="bg-white border border-gray-300 shadow-lg rounded-[10px] p-6 w-[500px] max-h-[80vh] overflow-auto">
      <h2 class="text-xl font-bold mb-4">{{ modalTitle }}</h2>

      <template v-if="type === 'desc'">
        <div class="mb-4">
          <label>Scan / Input Kode Barang:</label>
          <input ref="barcodeDesc" v-model="barcode" @input="onBarcodeInput" class="w-full border rounded px-3 py-2"
            autofocus />
        </div>
      </template>

      <template v-if="type === 'size'">
        <div class="mb-4">
          <label for="">Scan / Input Kode Barang:</label>
          <input ref="barcodeSize" v-model="barcode" @input="onBarcodeInputSize" class="w-full border rounded px-3 py-2"
            autofocus />
        </div>
      </template>

      <template v-else-if="type === 'priceTag'">
        <div class="mb-4">
          <textarea ref="priceTagInput" v-model="barcodeInput" @input="handleInputPriceTag"
            class="w-full border rounded px-3 py-2" autofocus rows="4"
            placeholder="Scan or enter barcodes, separated by commas"></textarea>
        </div>

        <div ref="printContent" class="hidden print:block p-8 text-sm leading-relaxed">
          <div v-for="item in priceTagData" :key="item.data.barangentry_id" style="
              page-break-after: always;
              border: 1px solid #999;
              padding: 32px 40px;
              font-family: 'Times New Roman', serif;
              color: #541B1A;            
            ">

            <h1 style="
              font-size: 20px;
              font-weight: 700;
              text-transform: uppercase;
              margin-bottom: 14px;
            ">
              {{ item.data.barangentry_nama }}
            </h1>

            <div style="display: flex; gap: 60px;">
              <div style="flex: 1.2;">
                <p style="margin: 0; font-weight: 700;">Horas!</p>
                <p style="margin: 4px 0; text-align: justify;">
                  Mauliate atas dukungan dan pelestarian budaya Batak.
                  Dengan membeli dan memiliki salah satu karya terbaik dari
                  <strong>Dame Ulos</strong>, kamu telah ikut
                  <em style="font-weight: 600;">Menjaga Kehidupan dan Tradisi Batak.</em>
                </p>

                <p style="margin-top: 16px;">Salam Hangat,</p>
                <p style="font-style: italic; font-weight: 600;">Artisan Dame Ulos</p>

                <table style="
                  margin-top: 16px;
                  border-collapse: collapse;
                  font-size: 13px;
                  line-height: 1.4;
                ">
                  <!-- <pre>{{ item.data }}</pre> -->
                  <tbody>
                    <tr>
                      <td style="padding: 3px 8px 3px 0;">Tahun Pembuatan</td>
                      <td>: {{ new Date(item.data.created_at).getFullYear() }}</td>
                    </tr>
                    <tr>
                      <td style="padding: 3px 8px 3px 0;">Ukuran Tenun</td>
                      <td>
                        :
                        {{ item.data.barangentry_ukuran_ulos ?? "-" }} x
                        {{ item.data.barangentry_ukuran_mandar ?? "-" }} cm
                      </td>
                    </tr>
                    <tr>
                      <td style="padding: 3px 8px 3px 0;">Warna</td>
                      <td>: {{ item.data.barangentry_warna }}</td>
                    </tr>
                    <tr>
                      <td style="padding: 3px 8px 3px 0;">Maker</td>
                      <td>: Dame Ulos Collective</td>
                    </tr>
                    <tr>
                      <td style="padding: 3px 8px 3px 0;">a. Penenun</td>
                      <td>: {{ item.data.barangentry_nama_penenun }}</td>
                    </tr>
                    <tr>
                      <td style="padding: 3px 8px 3px 0;">b. Dyer</td>
                      <td>: {{ item.data.barangentry_dryer }}</td>
                    </tr>
                    <tr v-if="item.data.barangentry_pemintal">
                      <td style="padding: 3px 8px 3px 0;">c. Panirat</td>
                      <td>: {{ item.data.barangentry_pemintal }}</td>
                    </tr>
                  </tbody>
                </table>

                <p style="
                    margin-top: 20px;
                    font-size: 26px;
                    font-weight: 800;
                    letter-spacing: 1px;
                  ">
                  Rp {{ Number(item.data.barangentry_harga_net).toLocaleString("id-ID") }}
                </p>
              </div>

              <!-- RIGHT COLUMN -->
              <div style="flex: 1; font-size: 13px; line-height: 1.5;">
                <p style="
                  font-weight: 700;
                  text-align: center;
                  margin-bottom: 12px;
                  text-transform: uppercase;
                ">
                  BAGAIMANA CARA PERAWATAN <br />
                  KAIN TENUN YANG BENAR ?
                </p>

                <ol style="padding-left: 18px; margin-top: 0;">
                  <li>Ulos tidak bisa dicuci/direndam dengan detergen</li>
                  <li>
                    Setelah dipakai jangan dilipat, cukup digantung dan dianginkan
                    (keringat dapat menimbulkan jamur)
                  </li>
                  <li>
                    Apabila tidak digunakan dalam kurun waktu yang lama, sebaiknya kain
                    dijemur di luar ruangan selama satu jam untuk menghindari jamur
                  </li>
                  <li>Hindari menyimpan di tempat yang lembab dan di dalam plastik</li>
                  <li>
                    Khusus kain dengan pewarna tekstil dapat di <em>dryclean</em>
                  </li>
                </ol>

                <p style="
                    text-align: right;
                    margin-top: 80px;
                    font-style: italic;
                    font-size: 15px;
                  ">
                  Selamat Pakai
                </p>
              </div>
            </div>
          </div>
        </div>

      </template>

      <div class="mt-6 flex justify-end space-x-3">
        <button v-if="type === 'priceTag'" @click="printPriceTag" class="bg-green-600 text-white px-4 py-2 rounded">
          Print
        </button>
        <button @click="closeModal" class="bg-gray-500 text-white px-4 py-2 rounded">
          Close
        </button>
        <button v-if="barcode && size.ukuran_mandar && size.ukuran_ulos" @click="submitSize"
          class="bg-blue-500 text-white px-4 py-2 rounded">
          Simpan Ukuran
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, nextTick, onMounted } from "vue";
import axios from "axios";
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";

const priceTagData = ref([]);
const priceTagInput = ref(null);
const url = ref("");
const barcode = ref("");
const barcodeInput = ref("");
const barcodeList = ref([]);
const barang = ref(null);
const printContent = ref(null);
let scanTimeout;
let scanTimeoutSize;

onMounted(() => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;
});
const props = defineProps({
  show: Boolean,
  type: String,
  barangDatabase: Object,
});

const barcodeDesc = ref(null);
watch(
  () => props.show,
  (val) => {
    if (val && props.type === "desc") {
      setTimeout(() => {
        barcodeDesc.value?.focus();
      }, 300);
    } else {
      barcode.value = "";
      barang.value = null;
      size.value = { mandar: "", ulos: "" };
    }
  }
);

const barcodeSize = ref(null);
watch(
  () => props.show,
  (val) => {
    if (val && props.type === "size") {
      setTimeout(() => {
        barcodeSize.value?.focus();
      }, 300);
    } else {
      barcode.value = "";
      barang.value = null;
      size.value = { mandar: "", ulos: "" };
    }
  }
);

const emit = defineEmits(["close", "scanned", "sizeSubmitted"]);
const size = ref({ mandar: "", ulos: "" });

const modalTitle = computed(() => {
  switch (props.type) {
    case "desc":
      return "Scan Barang";
    case "size":
      return "Input Ukuran";
    case "priceTag":
      return "Cetak Price Tag";
    default:
      return "Modal";
  }
});

function handleScan() {
  const found = props.barangDatabase.find(
    (b) => b.code_nama === barcode.value.trim()
  );

  if (found) {
    barang.value = { ...found };
    emit("scanned", { ...found });
    emit("close");
  } else {
    Swal.fire({
      icon: "error",
      title: "Barang Tidak Ditemukan",
      text: "Kode barang tidak cocok dengan database.",
      timer: 2000,
      showConfirmButton: false,
    });
    barang.value = null;
    barcode.value = null;
    console.error("Gagal Memeriksa Kode Barang:");
  }
}

function handleScanSize() {
  const found = props.barangDatabase.find(
    (b) => b.code_nama === barcode.value.trim()
  );

  if (found) {
    barang.value = { ...found };
    emit("sizeSubmitted", { ...found });
  } else {
    Swal.fire({
      icon: "error",
      title: "Barang Tidak Ditemukan",
      text: "Kode barang tidak cocok dengan database.",
      timer: 2000,
      showConfirmButton: false,
    });
    barang.value = null;
    barcode.value = null;
    console.error("Gagal Memeriksa Kode Barang:");
  }
}

let priceTagTimeout;
function handleInputPriceTag() {
  clearTimeout(priceTagTimeout);
  priceTagTimeout = setTimeout(() => {
    if (barcodeInput.value.length > 0 && !barcodeInput.value.endsWith(",")) {
      barcodeInput.value = barcodeInput.value.replace(/[\r\n]+/g, ",");
    }
  }, 300);
}

async function handleScanPriceTag() {
  const scanned = barcodeInput.value
    .split(",")
    .map((code) => code.trim())
    .filter((code) => code);

  if (scanned.length) {
    barcodeList.value = [...new Set(scanned)];
  }

  // barcodeInput.value = "";
}

async function printPriceTag() {
  try {
    const scanned = barcodeInput.value
      .split(",")
      .map((code) => code.trim())
      .filter((code) => code !== "");

    if (scanned.length === 0) {
      return;
    }
    barcodeList.value = [...new Set(scanned)];
    const results = [];
    for (const code of barcodeList.value) {
      try {
        const res = await axios.get(
          `${url.value}/api/entrybarang/getDataByCode/${code}`
        );
        if (res.data?.data) results.push({ data: res.data.data });
      } catch (err) {
        console.error(`Gagal ambil data untuk ${code}`, err);
      }
    }

    priceTagData.value = results;
  } catch (error) {
    console.error("Gagal Mengambil data price tag:", error);
  }
  nextTick(() => {
    const content = printContent.value;
    if (!content) return;

    const printWindow = window.open("", "", "width=800,height=600");
    printWindow.document.write(`
      <html>
        <head>
          <title>Price Tag</title>
          <style>
            body {
              font-family: Nunito;
              padding: 20px;
              font-size: 12px;
              line-height: 1.2;
            }

            p, td, li, h2, h3 {
              margin: 0;
              line-height: 1.2;
            }

            table {
              font-size: 12px;
            }

            ol {
              padding-left: 1rem;
              line-height: 1.2;
            }

            h1 {
              font-size: 20px;
              margin-bottom: 4px;
            }

            .text-xl {
              font-size: 12px;
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
  });
}

function onBarcodeInput(e) {
  clearTimeout(scanTimeout);
  scanTimeout = setTimeout(() => {
    handleScan();
  }, 2000);
}
function onBarcodeInputSize(e) {
  clearTimeout(scanTimeoutSize);
  scanTimeoutSize = setTimeout(() => {
    handleScanSize();
  }, 2000);
}

const closeModal = () => {
  emit('close')
  barcodeInput.value = "";
}
watch(
  () => props.show,
  (val) => {
    if (val && props.type === "priceTag") {
      setTimeout(() => {
        priceTagInput.value?.focus();
      }, 300);
    }
  }
);

</script>

<style scoped>
.bg-gray-800 {
  background-color: rgba(0, 0, 0, 0.5);
}
</style>
