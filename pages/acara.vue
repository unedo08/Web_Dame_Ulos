<template>
  <div>
    <div class="judul text-xl font-semibold mb-4">Acara</div>

    <div class="flex items-center justify-between pt-2">
      <div>
        <button
          class="btn-add bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[140px] h-[38px]"
          @click="openModal"
        >
          + Tambah Acara
        </button>
      </div>
    </div>

    <!-- Product Table -->
    <div>
      <table class="datatable">
        <thead>
          <tr>
            <th>Nama Acara</th>
            <th>Jumlah Barang</th>
            <th>Modal Barang</th>
            <th>Harga Net</th>
            <th>Harga Price Tag</th>
            <th>Keterangan</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="acara in listAcara" :key="acara.acara_id">
            <td>{{ acara.acara_nama }}</td>
            <td>{{ acara.acara_jumlahbarang }}</td>
            <td>{{ formatRupiah(acara.acara_modalbarang) }}</td>
            <td>{{ formatRupiah(acara.acara_harganetbarang) }}</td>
            <td>{{ formatRupiah(acara.acara_hargapricetagbarang) }}</td>
            <td>{{ acara.acara_keterangan }}</td>
            <td>{{ acara.acara_status }}</td>
            <td class="space-x-2">
              <button
                class="text-blue-500 hover:text-blue-700"
                @click="editItem(acara)"
                title="Edit"
              >
                <PencilIcon class="w-5 h-5" />
              </button>

              <button
                class="text-green-500 hover:text-green-700"
                @click="markAsDone(acara)"
                title="Selesai"
              >
                <CheckCircleIcon class="w-5 h-5" />
              </button>

              <button
                class="text-yellow-500 hover:text-yellow-700"
                @click="exportItem(acara.acara_id)"
                title="Export"
              >
                <ArrowDownTrayIcon class="w-5 h-5" />
              </button>

              <button
                class="text-red-500 hover:text-red-700"
                @click="deleteProduct(acara.acara_id, acara.acara_nama)"
                title="Delete"
              >
                <TrashIcon class="w-5 h-5" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Dialog -->
    <div
      v-if="isModalOpen"
      class="fixed inset-0 flex justify-center items-center bg-gray-800 bg-opacity-50"
    >
      <div class="bg-white p-6 rounded-lg shadow-lg max-w-lg w-full">
        <h3 class="text-xl font-semibold mb-4">Tambah Acara</h3>
        <form @submit.prevent="submitAcara">
          <div class="mb-4">
            <label
              for="acara_nama"
              class="block text-sm font-medium text-gray-700"
              >Nama Acara <span class="required">*</span></label
            >
            <input
              v-model="newProduct.acara_nama"
              type="text"
              id="acara_nama"
              class="mt-1 block w-full border-[1px] pl-3 border-gray rounded-md shadow-sm w-[382px] h-[41px]"
              placeholder="Masukkan Nama Acara"
              required
            />
            <p v-if="errors.acara_nama" class="text-red-500 text-sm mt-1">
              {{ errors.acara_nama }}
            </p>
          </div>
          <div class="mb-4">
            <label
              for="acara_keterangan"
              class="block text-sm font-medium text-gray-700"
              >Keterangan <span class="required">*</span></label
            >
            <textarea
              v-model="newProduct.acara_keterangan"
              id="acara_keterangan"
              rows="3"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
              placeholder="Masukkan keterangan acara"
              required
            ></textarea>
            <p v-if="errors.acara_keterangan" class="text-red-500 text-sm mt-1">
              {{ errors.acara_keterangan }}
            </p>
          </div>

          <div class="flex justify-end">
            <button
              type="button"
              @click="closeModal"
              class="mr-4 px-4 py-2 bg-gray-400 text-gray-800 rounded-md hover:bg-gray-600"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
            >
              Tambah
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Print -->
    <div
      v-if="isModalPrintOpen"
      class="fixed inset-0 flex justify-center items-center bg-gray-800 bg-opacity-50"
    >
      <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
        <h3 class="text-lg font-semibold mb-4">Print Barcode</h3>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700"
            >Jumlah Kode</label
          >
          <input
            type="number"
            v-model="printJumlah"
            min="1"
            class="mt-1 block w-full border border-gray-300 rounded-md p-2"
            placeholder="Masukkan jumlah kode"
          />
        </div>
        <div class="flex justify-end">
          <button class="mr-4 text-gray-500" @click="closePrintModal">
            Cancel
          </button>
          <button
            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
            @click="handlePrint"
          >
            Print
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Edit Acara -->
    <div
      v-if="isEditModalOpen"
      class="fixed inset-0 flex justify-center items-center bg-gray-800 bg-opacity-50 z-50"
    >
      <div class="bg-white p-6 rounded-lg shadow-lg max-w-2xl w-full">
        <h3 class="text-lg font-semibold mb-4">
          Edit Acara - {{ editForm.acara_nama }}
        </h3>

        <!-- Input Scan Barcode -->
        <div class="flex space-x-2 items-end mb-4">
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700"
              >Scan Barcode / Kode Barang</label
            >
            <input
              v-model="barcodeInput"
              @keyup.enter="addToTempBarang"
              type="text"
              class="mt-1 block w-full border border-gray-300 rounded-md p-2"
              placeholder="Scan atau ketik kode barang"
            />
          </div>
          <button
            @click="addToTempBarang"
            class="px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
          >
            Tambah
          </button>
        </div>

        <!-- Tabel Barang Sementara -->
        <table class="datatable w-full mb-4">
          <thead>
            <tr>
              <th>Kode Barang</th>
              <th>Status</th>
              <th class="hide-col">Acara ID</th>
              <th class="hide-col">Barang Entry ID</th>
              <th class="hide-col">Harga Modal</th>
              <th class="hide-col">Harga Net</th>
              <th class="hide-col">Harga Price tag</th>
              <th class="hide-col">Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(barang, index) in tempBarangList" :key="index">
              <td>{{ barang.code }}</td>
              <td></td>
              <td class="hide-col">{{ barang.acara_id }}</td>
              <td class="hide-col">{{ barang.barangentry_id }}</td>
              <td class="hide-col">{{ barang.acara_modalbarang }}</td>
              <td class="hide-col">{{ barang.acara_harganetbarang }}</td>
              <td class="hide-col">{{ barang.acara_hargapricetagbarang }}</td>
              <td class="hide-col">{{ barang.acara_status }}</td>
              <td>
                <button
                  @click="removeFromTempBarang(barang.acaradet_id)"
                  class="text-red-500 hover:text-red-700"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div class="flex justify-end">
          <button
            @click="closeEditModal"
            class="mr-2 px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
          >
            Batal
          </button>
          <button
            @click="submitEdit"
            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
          >
            Submit
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, reactive } from "vue";
import {
  PencilIcon,
  CheckCircleIcon,
  ArrowDownTrayIcon,
  TrashIcon,
} from "@heroicons/vue/24/solid";
import * as XLSX from "xlsx";
import { useRuntimeConfig } from "#imports";
import axios from "axios";
import Swal from "sweetalert2";

const url = ref("");
const acara = ref([]);
const acaraCounter = ref(1);
const isEditModalOpen = ref(false);
const editForm = ref({});
const barcodeInput = ref("");
const tempBarangList = ref([]);
const listAcara = ref([]);
const errors = reactive({});

const newProduct = ref({
  acara_id: 0,
  acara_nama: "",
  acara_jumlahbarang: 0,
  acara_modalbarang: "",
  acara_harganetbarang: 0,
  acara_hargapricetagbarang: 0,
  acara_keterangan: "",
  acara_status: "Belum Selesai",
});

const isModalOpen = ref(false);
const isModalPrintOpen = ref(false);
const selectedProduct = ref(null);
const printJumlah = ref(1);

onMounted(async () => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;
  try {
    getListAcara();
  } catch (error) {
    console.error("Gagal mengambil data acara: ", error);
  }
  isModalPrintOpen.value = false;
});

function validate() {
  errors.acara_nama = !newProduct.acara_nama ? "Nama Acara wajib diisi" : "";
  errors.acara_keterangan = !newProduct.acara_keterangan
    ? "Keterangan wajib diisi"
    : "";

  return Object.values(errors).every((err) => !err);
}

const formatRupiah = (harga) => {
  if (!harga) return "";
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(harga);
};

async function getListAcara() {
  try {
    const response = await axios.get(`${url.value}/api/acara`);
    listAcara.value = response.data.data;
  } catch (error) {
    console.error("Gagal memuat data acara:", error);
  }
}

watch(
  acara,
  (val) => {
    localStorage.setItem("acaraList", JSON.stringify(val));
  },
  { deep: true }
);

watch(acaraCounter, (val) => {
  localStorage.setItem("acaraIdCounter", val.toString());
});

const openModal = () => {
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

const submitAcara = async () => {
  if (!validate()) {
    Swal.fire({
      title: "Gagal!",
      text: "Silakan lengkapi semua field wajib.",
      icon: "error",
      confirmButtonText: "OK",
    });
    return;
  }

  try {
    const response = await axios.post(`${url.value}/api/acara/addAcara`, {
      acara_nama: newProduct.value.acara_nama,
      acara_keterangan: newProduct.value.acara_keterangan,
    });

    await getListAcara();
    newProduct.value = {
      acara_id: 0,
      acara_nama: "",
      acara_jumlahbarang: 0,
      acara_modalbarang: "",
      acara_harganetbarang: 0,
      acara_hargapricetagbarang: 0,
      acara_keterangan: "",
      acara_status: "Belum Selesai",
    };
    closeModal();
  } catch (error) {
    console.error("Gagal menambahkan data acara:", error);
  }
};

const deleteProduct = async (id, acara_nama) => {
  if (confirm(`Anda yakin ingin menghapus "${acara_nama}" ini?`)) {
    try {
      const response = await axios.delete(
        `${url.value}/api/acara/deleteAcara/` + id
      );
      if (response.status === 200) {
        acara.value = acara.value.filter((item) => item.acara_id !== id);
      }
      await getListAcara();
    } catch (error) {
      console.error("Gagal menghapus data acara: ", error);
    }
  }
};

const editItem = async (item) => {
  isEditModalOpen.value = true;
  editForm.value = { ...item };
  barcodeInput.value = "";
  tempBarangList.value = [];
  try {
    const response = await axios.get(
      `${url.value}/api/acaradet/getDataByAcara/${item.acara_id}`
    );

    const data = response.data.data || [];
    const detailedBarangList = await Promise.all(
      data.map(async (barang) => {
        try {
          const detailResponse = await axios.get(
            `${url.value}/api/entrybarang/${barang.acaradet_barangentry_id}`
          );
          const detail = detailResponse.data.data;

          let codeData = null;
          try {
            const codeId = parseInt(detail.barangentry_code_id, 10);
            if (!isNaN(codeId)) {
              const codeResponse = await axios.get(
                `${url.value}/api/codebarang/${codeId}`
              );
              codeData = codeResponse.data;
            } else {
              console.warn(
                "Code ID bukan angka yang valid:",
                detail.barangentry_code_id
              );
            }
          } catch (codeErr) {
            console.error("Gagal ambil data codebarang:", codeErr);
          }

          return {
            code: codeData.code_nama,
            acara_id: item.acara_id,
            acaradet_id: barang.acaradet_id,
            barangentry_id: detail.barangentry_id,
            acara_modalbarang: detail.barangentry_modal,
            acara_harganetbarang: detail.barangentry_harga_net,
            acara_hargapricetagbarang: detail.barangentry_price_tag,
            acara_status: detail.barangentry_status,
          };
        } catch (err) {
          console.error("Gagal ambil detail barangentry:", err);
          return null;
        }
      })
    );

    // Filter out null (gagal ambil data)
    tempBarangList.value = detailedBarangList.filter(Boolean);
  } catch (error) {
    console.error("Gagal mengambil data barang acara:", error);
    tempBarangList.value = [];
  }
};

// Tutup modal edit
const closeEditModal = () => {
  isEditModalOpen.value = false;
  barcodeInput.value = "";
  tempBarangList.value = [];
};

const addToTempBarang = async () => {
  const code = barcodeInput.value.trim();
  if (!code) return;

  try {
    const response = await axios.get(
      `${url.value}/api/entrybarang/getDataByCode/` + code
    );
    const barang = response.data.data;

    if (!barang || !barang.barangentry_id) {
      alert("Barang tidak ditemukan");
      return;
    }

    await axios.post(`${url.value}/api/acaradet/addDetAcara`, {
      acaradet_acara_id: editForm.value.acara_id,
      acaradet_barangentry_id: barang.barangentry_id,
    });

    if (!tempBarangList.value.includes(code)) {
      tempBarangList.value.push({
        code: code,
        acara_id: editForm.value.acara_id,
        barangentry_id: barang.barangentry_id,
        acara_modalbarang: barang.barangentry_modal,
        acara_harganetbarang: barang.barangentry_harga_net,
        acara_hargapricetagbarang: barang.barangentry_price_tag,
        acara_status: barang.barangentry_status,
      });
    }

    barcodeInput.value = "";
  } catch (error) {
    alert("Barcode tidak ditemukan");
    barcodeInput.value = "";
    console.error("Data tidak ditemukan: ", error);
  }
};

const removeFromTempBarang = async (id) => {
  if (confirm(`Anda yakin ingin menghapus data ini?`)) {
    try {
      const response = await axios.delete(
        `${url.value}/api/acaradet/deleteDetAcara/` + id
      );
      if (response.status === 200) {
        tempBarangList.value = tempBarangList.value.filter(
          (item) => item.acaradet_id !== id
        );
      }
    } catch (error) {
      console.error("Gagal menghapus data acara: ", error);
    }
  }
};

const submitEdit = async () => {
  try {
    const jumlahBarang = tempBarangList.value.length;
    const totalModal = tempBarangList.value.reduce(
      (acc, curr) => acc + (Number(curr.acara_modalbarang) || 0),
      0
    );
    const totalHargaNet = tempBarangList.value.reduce(
      (acc, curr) => acc + (Number(curr.acara_harganetbarang) || 0),
      0
    );
    const totalPriceTag = tempBarangList.value.reduce(
      (acc, curr) => acc + (Number(curr.acara_hargapricetagbarang) || 0),
      0
    );

    await axios.put(
      `${url.value}/api/acara/updateAcara/${editForm.value.acara_id}`,
      {
        acara_nama: editForm.value.acara_nama,
        acara_keterangan: editForm.value.acara_keterangan,
        acara_jumlahbarang: jumlahBarang,
        acara_modalbarang: totalModal,
        acara_harganetbarang: totalHargaNet,
        acara_hargapricetagbarang: totalPriceTag,
        acara_keterangan: "Ready To store",
        acara_status: "Ready",
      }
    );

    await getListAcara();
    closeEditModal();
  } catch (error) {
    console.error("Gagal mengupdate acara:", error);
    alert("Gagal menyimpan perubahan.");
  }
};

const exportItem = async (id) => {
  try {
    const response = await axios.get(`${url.value}/api/acara/export/${id}`);
    const acaraData = response.data.data.acara;
    const detailData = response.data.data.detail;

    const headers = [
      "Nama Acara",
      "Jumlah Barang",
      "Modal",
      "Harga Net",
      "Price Tag",
      "Keterangan",
      "Status",
      "ID Detail",
      "ID Barang Entry",
      "Created At",
      "Updated At",
    ];

    const rows = detailData.map((item) => [
      acaraData.acara_nama,
      acaraData.acara_jumlahbarang,
      acaraData.acara_modalbarang,
      acaraData.acara_harganetbarang,
      acaraData.acara_hargapricetagbarang,
      acaraData.acara_keterangan,
      acaraData.acara_status,
      item.acaradet_id,
      item.acaradet_barangentry_id,
      item.created_at,
      item.updated_at,
    ]);

    const data = [headers, ...rows];
    const worksheet = XLSX.utils.aoa_to_sheet(data);

    // 🔗 Merge Nama Acara (kolom A)
    const mergeRange = {
      s: { r: 1, c: 0 }, // start row 1 (0-indexed), col 0 (A)
      e: { r: detailData.length, c: 0 }, // end row = total rows, same col
    };
    worksheet["!merges"] = [mergeRange];

    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Export Acara");

    const filename = `Export_Acara_${acaraData.acara_nama.replace(
      /\s+/g,
      "_"
    )}.xlsx`;
    XLSX.writeFile(workbook, filename);
  } catch (error) {
    console.error("Gagal mengekspor data acara:", error);
    alert("Gagal mengekspor data.");
  }
};

const closePrintModal = () => {
  isModalPrintOpen.value = false;
  selectedProduct.value = null;
};

const handlePrint = () => {
  alert("Simulasi print acara: " + selectedProduct.value.nama_acara);
  closePrintModal();
};
</script>

<style scoped>
* {
  font-family: "Nunito", sans-serif;
}

.hide-col {
  display: none;
}

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
  background-color: #3d8bfd;
  color: white;
  border-radius: 5px;
  cursor: pointer;
}

.btn-add:hover {
  background-color: #3d8bfd;
}

/* Modal styles */
.fixed {
  position: fixed;
}

.bg-gray-800 {
  background-color: rgba(0, 0, 0, 0.5);
}
</style>
