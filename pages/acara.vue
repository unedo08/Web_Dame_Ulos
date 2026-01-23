<template>
  <div>
    <title>Acara</title>
    <div class="judul text-xl font-semibold mb-2">Acara</div>

    <div class="flex items-center justify-end">
      <button class="btn-add bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[100px] h-[25px]"
        @click="openModal">
        + Tambah Acara
      </button>
    </div>

    <!-- Product Table -->
    <div>
      <table class="datatable w-full rounded-md overflow-hidde">
        <thead class="bg-blue-100">
          <tr>
            <th class="px-4 py-2 text-left">Nama Acara</th>
            <th class="px-4 py-2 text-left">Jumlah Barang</th>
            <th class="px-4 py-2 text-left">Modal Barang</th>
            <th class="px-4 py-2 text-left">Harga Net</th>
            <th class="px-4 py-2 text-left">Harga Price Tag</th>
            <th class="px-4 py-2 text-left">Keterangan</th>
            <th class="px-4 py-2 text-left">Status</th>
            <th class="px-4 py-2 text-left">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="acara in pagination" :key="acara.acara_id" class="odd:bg-white even:bg-gray-50 hover:bg-gray-100">
            <td class="px-4 py-2">{{ acara.acara_nama }}</td>
            <td class="px-4 py-2">{{ acara.acara_jumlahbarang }}</td>
            <td class="px-4 py-2">
              {{ formatRupiah(acara.acara_modalbarang) }}
            </td>
            <td class="px-4 py-2">
              {{ formatRupiah(acara.acara_harganetbarang) }}
            </td>
            <td class="px-4 py-2">
              {{ formatRupiah(acara.acara_hargapricetagbarang) }}
            </td>
            <td class="px-4 py-2">{{ acara.acara_keterangan }}</td>
            <td class="px-4 py-2">{{ acara.acara_status }}</td>
            <td class="space-x-2 px-4 py-2">
              <button class="text-blue-500 hover:text-blue-700" @click="editItem(acara)" title="Edit">
                <PlusIcon class="w-5 h-5" />
              </button>

              <button class="text-green-500 hover:text-green-700" @click="markAsDone(acara)" title="Selesai">
                <CheckCircleIcon class="w-5 h-5" />
              </button>

              <button class="text-yellow-500 hover:text-yellow-700" @click="exportItem(acara.acara_id)" title="Export">
                <ArrowDownTrayIcon class="w-5 h-5" />
              </button>

              <button class="text-red-500 hover:text-red-700" @click="deleteProduct(acara.acara_id, acara.acara_nama)"
                title="Delete">
                <TrashIcon class="w-5 h-5" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="flex justify-between items-center mt-8 mb-4 text-xs">
        <div class="flex items-center space-x-2">
          <label for="perPage">Tampilkan:</label>
          <select id="perPage" v-model="itemsPerPage" class="border px-2 py-1 rounded text-xs">
            <option :value="5">5</option>
            <option :value="10">10</option>
            <option :value="20">20</option>
            <option :value="50">50</option>
            <option value="all">All</option>
          </select>
        </div>

        <div class="flex items-center space-x-2">
          <button class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400 text-xs" :disabled="currentPage === 1"
            @click="currentPage--">
            Sebelumnya
          </button>

          <button v-for="(page, index) in paginatedPages" :key="index"
            @click="typeof page === 'number' && (currentPage = page)" :class="[
              'px-3 py-1 rounded text-xs',
              currentPage === page ? 'bg-blue-500 text-white' : 'bg-gray-200',
              page === '...' ? 'cursor-default' : 'cursor-pointer',
            ]" :disabled="page === '...'">
            {{ page }}
          </button>

          <button class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400 text-xs" :disabled="currentPage === totalPages"
            @click="currentPage++">
            Selanjutnya
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Dialog -->
    <div v-if="isModalOpen" class="fixed inset-0 flex justify-center items-center bg-gray-800 bg-opacity-50 z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg max-w-lg w-full">
        <h3 class="text-xl font-semibold mb-4">Tambah Acara</h3>
        <form @submit.prevent="submitAcara">
          <div class="mb-4">
            <label for="acara_nama" class="block text-sm font-medium text-gray-700">Nama Acara <span
                class="required">*</span></label>
            <input v-model="newProduct.acara_nama" type="text" id="acara_nama"
              class="mt-1 block w-full border border-gray-300 pl-3 border-gray bg-[#FDFDFF] rounded-md shadow-sm w-[382px] h-[41px]"
              placeholder="Masukkan Nama Acara" required />
            <p v-if="errors.acara_nama" class="text-red-500 text-sm mt-1">
              {{ errors.acara_nama }}
            </p>
          </div>
          <div class="mb-4">
            <label for="acara_keterangan" class="block text-sm font-medium text-gray-700">Keterangan <span
                class="required">*</span></label>
            <textarea v-model="newProduct.acara_keterangan" id="acara_keterangan" rows="3"
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 bg-[#FDFDFF]"
              placeholder="Masukkan keterangan acara" required></textarea>
            <p v-if="errors.acara_keterangan" class="text-red-500 text-sm mt-1">
              {{ errors.acara_keterangan }}
            </p>
          </div>

          <div class="flex justify-end">
            <button type="button" @click="closeModal"
              class="mr-4 px-4 py-2 bg-[#D8D8D8] text-gray-800 rounded-md hover:bg-[#D8D8D8]">
              Batal
            </button>
            <button type="submit" class="px-4 py-2 bg-[#1C9DBD] text-white rounded-md hover:bg-[#1C9DBD]">
              Tambah
            </button>
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
          <button class="mr-4 text-gray-500" @click="closePrintModal">
            Cancel
          </button>
          <button class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600" @click="handlePrint">
            Print
          </button>
        </div>
      </div>
    </div>

    <div v-if="isEditModalOpen" class="fixed inset-0 flex justify-center items-center bg-gray-800 bg-opacity-50 z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold mb-4">
          Edit Acara - {{ editForm.acara_nama }}
        </h3>
        <div class="flex gap-2 mb-3">
          <input v-model="barcodeInput" @keyup.enter="addToTemp" type="text" class="flex-1 border rounded-md px-3 py-2"
            placeholder="Scan / ketik barcode" />
          <button @click="addToTemp" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            Tambah
          </button>
        </div>

        <div class="mb-3">
          <div class="text-xs text-gray-600 mb-1">Barcode siap dikirim:</div>
          <div class="flex flex-wrap gap-2">
            <span v-for="(code, i) in scannedCodes" :key="i" class="bg-gray-200 px-2 py-1 rounded text-xs">
              {{ code }}
            </span>
          </div>
        </div>

        <button @click="addListBarangAcara" class="mb-4 px-4 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600">
          Tambah ke List
        </button>

        <table class="datatable w-full mb-4">
          <thead class="bg-blue-100">
            <tr>
              <th>Kode</th>
              <th>Modal</th>
              <th>Harga Net</th>
              <th>Price Tag</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in listBarangAcara" :key="item.barangentry_id">
              <td>{{ item.code_nama }}</td>
              <td>{{ formatRupiah(item.barangentry_modal) }}</td>
              <td>{{ formatRupiah(item.barangentry_harga_net) }}</td>
              <td>{{ formatRupiah(item.barangentry_price_tag) }}</td>
              <td>
                <button class="text-red-500" @click="removeItem(item.barangentry_id)">
                  Hapus
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div class="flex justify-end gap-2">
          <button @click="closeEditModal" class="px-4 py-2 bg-gray-300 rounded">
            Batal
          </button>
          <button @click="submitEdit" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
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
  PlusIcon
} from "@heroicons/vue/24/solid";
import * as XLSX from "xlsx";
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";
const { $api } = useNuxtApp();
const scannedCodes = ref([])
const listBarangAcara = ref([])

const url = ref("");
const acara = ref([]);
const acaraCounter = ref(1);
const isEditModalOpen = ref(false);
const editForm = ref({});
const barcodeInput = ref("");
const tempBarangList = ref([]);
const listAcara = ref([]);
const errors = reactive({});
const currentPage = ref(1);
const itemsPerPage = ref(10);

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

const sortedListAcara = computed(() => {
  return [...listAcara.value].sort((a, b) => {
    return new Date(b.created_at) - new Date(a.created_at);
  });
});

const pagination = computed(() => {
  if (itemsPerPage.value === "all") {
    return sortedListAcara.value;
  }

  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return sortedListAcara.value.slice(start, end);
});

const totalPages = computed(() => {
  if (itemsPerPage.value === "all") return 1;
  return Math.ceil(listAcara.value.length / itemsPerPage.value);
});

function validate() {
  errors.acara_nama = !newProduct.value.acara_nama
    ? "Nama Acara wajib diisi"
    : "";
  errors.acara_keterangan = !newProduct.value.acara_keterangan
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
    const response = await $api.get(`${url.value}/api/acara`);
    listAcara.value = response.data.data;
  } catch (error) {
    console.error("Gagal memuat data acara:", error);
  }
}

const paginatedPages = computed(() => {
  if (itemsPerPage.value === "all") return [1];

  const total = totalPages.value;
  const current = currentPage.value;
  const pages = [];

  if (total <= 5) {
    for (let i = 1; i <= total; i++) pages.push(i);
  } else {
    if (current <= 3) pages.push(1, 2, 3, "...", total);
    else if (current >= total - 2) pages.push(1, "...", total - 2, total - 1, total);
    else pages.push(1, "...", current - 1, current, current + 1, "...", total);
  }

  return pages;
});

const editItem = async (item) => {
  isEditModalOpen.value = true
  editForm.value = { ...item }
  scannedCodes.value = []

  try {
    const res = await $api.get(
      `${url.value}/api/acaradet/getListBarangAcara/${item.acara_id}`
    )
    listBarangAcara.value = res.data.data || []
  } catch {
    listBarangAcara.value = []
  }
}

const addToTemp = () => {
  const code = barcodeInput.value.trim()
  if (!code) return

  if (scannedCodes.value.includes(code)) {
    Swal.fire("Duplikat", "Kode sudah ada", "warning")
    return
  }

  scannedCodes.value.push(code)
  barcodeInput.value = ""
}

const addListBarangAcara = async () => {
  if (scannedCodes.value.length === 0) {
    Swal.fire("Kosong", "Tidak ada barcode", "warning")
    return
  }

  try {
    const res = await $api.post(
      `${url.value}/api/acaradet/addListBarangAcara`,
      { code_nama: scannedCodes.value }
    )
    const newItems = res.data.data || []
    const map = new Map()

    ;[...listBarangAcara.value, ...newItems].forEach(item => {
      map.set(item.barangentry_id, item)
    })

    listBarangAcara.value = Array.from(map.values())
    scannedCodes.value = []

  } catch (err) {
    Swal.fire("Gagal", "Gagal tambah ke list", "error")
  }
}

const removeItem = (id) => {
  listBarangAcara.value =
    listBarangAcara.value.filter(i => i.barangentry_id !== id)
}

const submitEdit = async () => {
  if (listBarangAcara.value.length === 0) {
    Swal.fire("Kosong", "Tidak ada barang", "warning")
    return
  }

  try {
    const ids = listBarangAcara.value.map(i => i.barangentry_id)
    await $api.post(
      `${url.value}/api/acaradet/addDetAcara`,
      {
        acaradet_acara_id: editForm.value.acara_id,
        acaradet_barangentry_id: ids
      }
    )

    const jumlahBarang = listBarangAcara.value.length
    const totalModal = listBarangAcara.value.reduce(
      (sum, i) => sum + Number(i.barangentry_modal || 0),
      0
    )
    const totalHargaNet = listBarangAcara.value.reduce(
      (sum, i) => sum + Number(i.barangentry_harga_net || 0),
      0
    )
    const totalPriceTag = listBarangAcara.value.reduce(
      (sum, i) => sum + Number(i.barangentry_price_tag || 0),
      0
    )

    await $api.put(
      `${url.value}/api/acara/updateAcara/${editForm.value.acara_id}`,
      {
        acara_jumlahbarang: jumlahBarang,
        acara_modalbarang: totalModal,
        acara_harganetbarang: totalHargaNet,
        acara_hargapricetagbarang: totalPriceTag,
        acara_keterangan: "Ready To store",
        acara_status: "Ready"
      }
    )

    Swal.fire("Berhasil", "Acara berhasil diupdate", "success")
    closeEditModal()
    getListAcara()

  } catch (err) {
    console.error(err)
    Swal.fire("Gagal", "Gagal menyimpan acara", "error")
  }
}

const closeEditModal = () => {
  isEditModalOpen.value = false
  barcodeInput.value = ""
  scannedCodes.value = []
  listBarangAcara.value = []
}


watch(currentPage, (val) => {
  if (val < 1) currentPage.value = 1;
  if (val > totalPages.value) currentPage.value = totalPages.value;
});

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
      timer: 3000,
      timerProgressBar: true,
    });
    return;
  }

  try {
    await $api.post(`${url.value}/api/acara/addAcara`, {
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
  const result = await Swal.fire({
    title: "Konfirmasi Hapus",
    text: `Anda yakin ingin menghapus "${acara_nama}"?`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, Hapus!",
    cancelButtonText: "Batal",
    reverseButtons: true,
  });

  if (result.isConfirmed) {
    try {
      const response = await $api.delete(`${url.value}/api/acara/deleteAcara/${id}`);

      if (response.status === 200) {
        listAcara.value = listAcara.value.filter(
          (item) => item.acara_id !== id
        );
        await Swal.fire({
          title: "Berhasil!",
          text: `"${acara_nama}" telah dihapus.`,
          icon: "success",
          timer: 1500,
          showConfirmButton: false,
        });
      }
    } catch (error) {
      console.error("Error deleting product:", error);
      Swal.fire({
        title: "Gagal",
        text: "Terjadi kesalahan saat menghapus data.",
        icon: "error",
      });
    }
  }
};

// const editItem = async (item) => {
//   isEditModalOpen.value = true;
//   editForm.value = { ...item };
//   barcodeInput.value = "";
//   tempBarangList.value = [];
//   try {
//     const response = await $api.get(
//       `${url.value}/api/acaradet/getDataByAcara/${item.acara_id}`);

//     const data = response.data.data || [];
//     const detailedBarangList = await Promise.all(
//       data.map(async (barang) => {
//         try {
//           const detailResponse = await $api.get(
//             `${url.value}/api/entrybarang/${barang.acaradet_barangentry_id}`);
//           const detail = detailResponse.data.data;

//           let codeData = null;
//           try {
//             const codeId = parseInt(detail.barangentry_code_id, 10);
//             if (!isNaN(codeId)) {
//               const codeResponse = await $api.get(
//                 `${url.value}/api/codebarang/${codeId}`);
//               codeData = codeResponse.data;
//             } else {
//               console.warn(
//                 "Code ID bukan angka yang valid:",
//                 detail.barangentry_code_id
//               );
//             }
//           } catch (codeErr) {
//             console.error("Gagal ambil data codebarang:", codeErr);
//           }

//           return {
//             code: codeData.code_nama,
//             acara_id: item.acara_id,
//             acaradet_id: barang.acaradet_id,
//             barangentry_id: detail.barangentry_id,
//             acara_modalbarang: detail.barangentry_modal,
//             acara_harganetbarang: detail.barangentry_harga_net,
//             acara_hargapricetagbarang: detail.barangentry_price_tag,
//             acara_status: detail.barangentry_status,
//           };
//         } catch (err) {
//           console.error("Gagal ambil detail barangentry:", err);
//           return null;
//         }
//       })
//     );

//     // Filter out null (gagal ambil data)
//     tempBarangList.value = detailedBarangList.filter(Boolean);
//   } catch (error) {
//     console.error("Gagal mengambil data barang acara:", error);
//     tempBarangList.value = [];
//   }
// };

const addToTempBarang = () => {
  const code = barcodeInput.value.trim()
  if (!code) return

  if (scannedCodes.value.includes(code)) {
    Swal.fire("Duplikat", "Kode sudah ada di list", "warning")
    return
  }

  scannedCodes.value.push(code)
  barcodeInput.value = ""
}

const removeFromTempBarang = async (id) => {
  if (confirm(`Anda yakin ingin menghapus data ini?`)) {
    try {
      const response = await $api.delete(
        `${url.value}/api/acaradet/deleteDetAcara/` + id);
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

// const submitEdit = async () => {
//   try {
//     const jumlahBarang = tempBarangList.value.length;
//     const totalModal = tempBarangList.value.reduce(
//       (acc, curr) => acc + (Number(curr.acara_modalbarang) || 0),
//       0
//     );
//     const totalHargaNet = tempBarangList.value.reduce(
//       (acc, curr) => acc + (Number(curr.acara_harganetbarang) || 0),
//       0
//     );
//     const totalPriceTag = tempBarangList.value.reduce(
//       (acc, curr) => acc + (Number(curr.acara_hargapricetagbarang) || 0),
//       0
//     );

//     await $api.put(
//       `${url.value}/api/acara/updateAcara/${editForm.value.acara_id}`,
//       {
//         acara_nama: editForm.value.acara_nama,
//         acara_keterangan: editForm.value.acara_keterangan,
//         acara_jumlahbarang: jumlahBarang,
//         acara_modalbarang: totalModal,
//         acara_harganetbarang: totalHargaNet,
//         acara_hargapricetagbarang: totalPriceTag,
//         acara_keterangan: "Ready To store",
//         acara_status: "Ready",
//       });

//     await getListAcara();
//     closeEditModal();
//   } catch (error) {
//     console.error("Gagal mengupdate acara:", error);
//     Swal.fire("Gagal", "Gagal menyimpan perubahan.", "error");
//   }
// };

const exportItem = async (id) => {
  try {
    const response = await $api.get(`${url.value}/api/acara/export/${id}`);
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

    const mergeRange = {
      s: { r: 1, c: 0 },
      e: { r: detailData.length, c: 0 },
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
    Swal.fire("Gagal", "Gagal mengekspor data.", "error");
  }
};

const closePrintModal = () => {
  isModalPrintOpen.value = false;
  selectedProduct.value = null;
};

const handlePrint = () => {
  if (!selectedProduct.value) return;
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
  /* border: 1px solid #ddd; */
  text-align: left;
  font-size: 12px;
}

.datatable th {
  background-color: #f4f4f4;
}

.btn-add {
  background-color: #3d8bfd;
  color: white;
  border-radius: 5px;
  cursor: pointer;
  font-size: 12px;
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
