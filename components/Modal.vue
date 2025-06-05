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
            @keyup.enter="handleScan"
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
            @keyup.enter="handleScanSize"
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

          <div class="mt-4">
            <button
              @click="printPriceTag"
              class="bg-green-600 text-white px-4 py-2 rounded"
            >
              Print
            </button>
          </div>
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
            <div style="display: flex; gap: 40px">
              <div style="flex: 1">
                <h1>{{item.barangentry_nama}}</h1>
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
                    margin-top: 20px;
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 0.9rem;
                  "
                >
                  <tr>
                    <td style="padding: 4px 8px; font-weight: bold">
                      Tahun Pembuatan
                    </td>
                    <td style="padding: 4px 8px">
                      {{ new Date(item.created_at).getFullYear() }}
                    </td>
                  </tr>
                  <tr>
                    <td style="padding: 4px 8px; font-weight: bold">
                      Ukuran Tenun
                    </td>
                    <td style="padding: 4px 8px">
                      {{ item.barangentry_ukuran_ulos ?? "-" }} x
                      {{ item.barangentry_ukuran_mandar ?? "-" }}
                    </td>
                  </tr>
                  <tr>
                    <td style="padding: 4px 8px; font-weight: bold">Warna</td>
                    <td style="padding: 4px 8px">
                      {{ item.barangentry_warna }}
                    </td>
                  </tr>
                  <tr>
                    <td style="padding: 4px 8px; font-weight: bold">Maker</td>
                    <td style="padding: 4px 8px">Dame Ulos Collective</td>
                  </tr>
                  <tr>
                    <td style="padding: 4px 8px; padding-left: 1.5rem">
                      <strong>a. Penenun:</strong>
                    </td>
                    <td style="padding: 4px 8px">
                      {{ item.barangentry_nama_penenun }}
                    </td>
                  </tr>
                  <tr>
                    <td style="padding: 4px 8px; padding-left: 1.5rem">
                      <strong>b. Dyer:</strong>
                    </td>
                    <td style="padding: 4px 8px">
                      {{ item.barangentry_dryer }}
                    </td>
                  </tr>
                </table>
              </div>

              <div style="flex: 1">
                <p class="font-semibold mb-2">
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
import { useRuntimeConfig } from '#imports'

const priceTagData = ref([]);
const url = ref('')

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
    if (val) {
      nextTick(() => {
        barcodeDesc.value?.focus();
      });
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
    if (val) {
      nextTick(() => {
        barcodeSize.value?.focus();
      });
    } else {
      barcode.value = "";
      barang.value = null;
      size.value = { mandar: "", ulos: "" };
    }
  }
);

const emit = defineEmits(["close", "scanned", "sizeSubmitted"]);

const barcode = ref("");
const barcodeInput = ref("");
const barcodeList = ref([]);
const barang = ref(null);
const printContent = ref(null);
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
  } else {
    alert("Barang tidak ditemukan.");
    barang.value = null;
    barcode.value = null;
    console.error("Gagal Memeriksa Kode Barang:", err)
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
    alert("Barang tidak ditemukan.");
    barang.value = null;
    barcode.value = null;
    console.error("Gagal Memeriksa Kode Barang:", err)
  }
}

async function handleScanPriceTag() {
  const scanned = barcodeInput.value
    .split(",")
    .map((code) => code.trim())
    .filter((code) => code);

  if (scanned.length) {
    barcodeList.value = [...new Set(scanned)];
    // emit("scanned", barcodeList.value);
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
    const results= [];
    for (const code of barcodeList.value) {
      try {
        const res = await axios.get(`${url.value}/api/entrybarang/getDataByCode/${code}`);
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
            body { font-family: sans-serif; padding: 20px; line-height: 1.6; }
            ol { padding-left: 1rem; }
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

watch(
  () => props.show,
  (val) => {
    if (!val) {
      barcode.value = "";
      barang.value = null;
      size.value = { mandar: "", ulos: "" };
    }
  }
);
</script>

<!-- <style>
body {
    font-family: sans-serif;
    padding: 20px;
    line-height: 1.6;
}

.print-layout {
    display: flex;
    gap: 40px;
}

.print-column {
    flex: 1;
}

ol {
    padding-left: 1rem;
}
</style> -->
