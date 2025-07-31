<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
    <div
      class="bg-white border border-gray-300 shadow-lg rounded-[10px] p-6 w-[500px] max-h-[80vh] overflow-auto"
    >
      <h2 class="text-xl font-bold mb-4">{{ modalTitle }}</h2>

      <!-- Modal DESC -->
      <template v-if="type === 'desc'">
        <div class="mb-4">
          <label>Scan / Input Kode Barang:</label>
          <input
            ref="barcodeDesc"
            v-model="barcode"
            @input="onBarcodeInput"
            class="w-full border rounded px-3 py-2"
            autofocus
          />
        </div>
      </template>

      <!-- Modal SIZE -->
      <template v-if="type === 'size'">
        <div class="mb-4">
          <label for="">Scan / Input Kode Barang:</label>
          <input
            ref="barcodeSize"
            v-model="barcode"
            @input="onBarcodeInputSize"
            class="w-full border rounded px-3 py-2"
            autofocus
          />
        </div>
      </template>

      <!-- Modal PRICE TAG -->
      <template v-else-if="type === 'priceTag'">
        <div class="mb-4">
          <textarea
            v-model="barcodeInput"
            @keyup.enter="handleScanPriceTag"
            class="w-full border rounded px-3 py-2"
            autofocus
            rows="4"
            placeholder="Scan or enter barcodes, separated by commas"
          ></textarea>
        </div>

        <div
          ref="printContent"
          class="hidden print:block p-8 text-sm leading-relaxed"
        >
          <div
            v-for="item in priceTagData"
            :key="item.barangentry_id"
            style="page-break-after: always"
          >
            <div style="display: flex; gap: 10px">
              <div style="flex: 1">
                <h1>{{ item.data.barangentry_nama }}</h1>
                <p class="text-xl font-semibold mb-4">Horas!</p>
                <p>Mauliate atas dukungan dan pelestarian budaya Batak.</p>
                <p>
                  Dengan membeli dan memiliki salah satu karya terbaik dari
                  <strong>Dame Ulos</strong>,
                </p>
                <p>
                  kamu telah ikut
                  <strong>Menjaga Kehidupan dan Tradisi Batak</strong>.
                </p>

                <p class="mt-4">Salam Hangat,</p>
                <p><em>Artisan Dame Ulos</em></p>

                <table
                  style="
                    margin-top: 10px;
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 0.9rem;
                  "
                >
                  <tr>
                    <td
                      style="
                        padding: 2px 6px;
                        font-weight: bold;
                        font-size: 12px;
                      "
                    >
                      Tahun Pembuatan
                    </td>
                    <td style="padding: 2px 6px; font-size: 12px">
                      {{ new Date(item.data.created_at).getFullYear() }}
                    </td>
                  </tr>
                  <tr>
                    <td
                      style="
                        padding: 2px 6px;
                        font-weight: bold;
                        font-size: 12px;
                      "
                    >
                      Ukuran Tenun
                    </td>
                    <td style="padding: 2px 6px; font-size: 12px">
                      {{ item.data.barangentry_ukuran_ulos ?? "-" }} x
                      {{ item.data.barangentry_ukuran_mandar ?? "-" }}
                    </td>
                  </tr>
                  <tr>
                    <td
                      style="
                        padding: 2px 6px;
                        font-weight: bold;
                        font-size: 12px;
                      "
                    >
                      Warna
                    </td>
                    <td style="padding: 2px 6px; font-size: 12px">
                      {{ item.data.barangentry_warna }}
                    </td>
                  </tr>
                  <tr>
                    <td
                      style="
                        padding: 2px 6px;
                        font-weight: bold;
                        font-size: 12px;
                      "
                    >
                      Maker
                    </td>
                    <td style="padding: 2px 6px; font-size: 12px">
                      Dame Ulos Collective
                    </td>
                  </tr>
                  <tr>
                    <td
                      style="
                        padding: 2px 6px;
                        padding-left: 1.5rem;
                        font-weight: bold;
                        font-size: 12px;
                      "
                    >
                      a. Penenun:
                    </td>
                    <td style="padding: 2px 6px; font-size: 12px">
                      {{ item.data.barangentry_nama_penenun }}
                    </td>
                  </tr>
                  <tr>
                    <td
                      style="
                        padding: 2px 6px;
                        padding-left: 1.5rem;
                        font-weight: bold;
                        font-size: 12px;
                      "
                    >
                      b. Panirat:
                    </td>
                    <td style="padding: 2px 6px; font-size: 12px">
                      {{ item.data.barangentry_nama_panirat }}
                    </td>
                  </tr>
                  <tr>
                    <td
                      style="
                        padding: 2px 6px;
                        padding-left: 1.5rem;
                        font-weight: bold;
                        font-size: 12px;
                      "
                    >
                      c. Dyer:
                    </td>
                    <td style="padding: 2px 6px; font-size: 12px">
                      {{ item.data.barangentry_dryer }}
                    </td>
                  </tr>
                </table>
              </div>

              <div style="flex: 1">
                <p
                  class="font-bold mb-2"
                  style="text-align: center; font-weight: 600"
                >
                  BAGAIMANA CARA PERAWATAN KAIN TENUN YANG BENAR?
                </p>
                <ol class="list-decimal list-inside">
                  <li>Ulos tidak bisa dicuci/direndam dengan detergen</li>
                  <li>
                    Setelah dipakai jangan dilipat, cukup digantung dan
                    dianginkan
                  </li>
                  <li>Jika tidak digunakan lama, jemur kain selama 1 jam</li>
                  <li>Hindari tempat lembab dan penyimpanan dalam plastik</li>
                  <li>Khusus kain pewarna tekstil bisa di dry clean</li>
                </ol>
                <p class="mt-4">Selamat Pakai</p>
              </div>
            </div>
          </div>
        </div>
      </template>

      <div class="mt-6 flex justify-end space-x-3">
        <button
          v-if="type === 'priceTag'"
          @click="printPriceTag"
          class="bg-green-600 text-white px-4 py-2 rounded"
        >
          Print
        </button>
        <button
          @click="$emit('close')"
          class="bg-gray-500 text-white px-4 py-2 rounded"
        >
          Close
        </button>
        <button
          v-if="barcode && size.ukuran_mandar && size.ukuran_ulos"
          @click="submitSize"
          class="bg-blue-500 text-white px-4 py-2 rounded"
        >
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

async function handleScanPriceTag() {
  const scanned = barcodeInput.value
    .split(",")
    .map((code) => code.trim())
    .filter((code) => code);

  if (scanned.length) {
    barcodeList.value = [...new Set(scanned)];
  }

  barcodeInput.value = "";
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
        if (res.data) results.push(res.data);
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
</script>
