<template>
  <div>
    <title>Transaksi Live</title>
    <div class="judul text-xl font-semibold mb-4">Transaksi Live</div>
    <div class="flex space-x-6">
      <button @click="activeTab = 'order'" class="pb-1 text-sm relative"
        :class="activeTab === 'order' ? 'text-red-900 font-semibold' : 'text-gray-500'">
        Order
        <span v-if="activeTab === 'order'" class="absolute left-0 right-0 -bottom-0.5 h-[2px] bg-red-900 mx-auto"
          style="width: 90%"></span>
      </button>

      <button @click="activeTab = 'transaction'" class="pb-1 text-sm relative"
        :class="activeTab === 'transaction' ? 'text-red-900 font-semibold' : 'text-gray-500'">
        Transaction
        <span v-if="activeTab === 'transaction'" class="absolute left-0 right-0 -bottom-0.5 h-[2px] bg-red-900 mx-auto"
          style="width: 90%"></span>
      </button>
    </div>
    <div class="mx-auto" v-show="activeTab === 'order'">
      <br />
      <div class="flex items-center justify-between pt-2">
        <div class="flex-1">
          <input v-model="searchQuery" type="text" class="search-box p-2 rounded-md" placeholder="Cari data live..." />
        </div>
        <div>
          <button class="btn-add bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[104px] h-[25px]"
            @click="openModalAddOrder">
            + Live
          </button>
        </div>
      </div>
      <table class="datatable w-full rounded-md overflow-hidden">
        <thead class="bg-blue-100">
          <tr>
            <!-- <th>No</th> -->
            <th class="px-4 py-2 text-left">Nama Akun</th>
            <th class="px-4 py-2 text-left">Nama Barang</th>
            <th class="px-4 py-2 text-left">Nama Platform</th>
            <th class="px-4 py-2 text-left">Harga Terjual</th>
            <th class="px-4 py-2 text-left">Total Harga Terjual (Akun)</th>
            <th class="px-4 py-2 text-left">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <template v-for="(items, akun) in paginatedGroupedData" :key="akun">
            <!-- :key="pengiriman.pengirimanBarang_id" -->
            <tr v-for="(pengiriman, index) in items" :class="[
              index % 2 === 0 ? 'bg-white' : 'bg-gray-50',
              index === items.length - 1 ? 'border-b-2 border-gray-200' : '',
            ]">
              <!-- <td>{{ index + 1 }}</td> -->
              <td class="px-4 py-2" v-if="index === 0" :rowspan="items.length">
                {{ akun }}
              </td>

              <td class="px-4 py-2">
                {{ barangMap[pengiriman.live_order_barang_id] || "" }}
              </td>
              <td class="px-4 py-2">
                {{ capitalizeFirst(pengiriman.live_order_platform) }}
              </td>
              <td class="px-4 py-2">
                {{ formatCurrency(pengiriman.live_order_harga_terjual) }}
              </td>

              <td class="px-4 py-2" v-if="index === 0" :rowspan="items.length">
                {{ formatCurrency(totalHargaPerAkun[akun]) }}
              </td>
              <td class="px-4 py-2">
                <div class="flex space-x-2">
                  <button
                    class="flex items-center gap-1 px-2 py-1 bg-green-500 text-white hover:bg-green-600 rounded-[10px] text-s"
                    @click="editOrderLive(pengiriman.live_order_id)">
                    Edit
                  </button>
                  <button
                    class="flex items-center gap-1 px-2 py-1 bg-red-500 text-white hover:bg-red-600 rounded-[10px] text-s"
                    @click="deleteOrder(pengiriman.live_order_id)">
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </template>
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

    <div class="mx-auto" v-show="activeTab === 'transaction'">
      <br />
      <input v-model="searchQuery" type="text" class="search-box mb-4" placeholder="Cari data live..." />
      <table class="datatable">
        <thead>
          <tr>
            <th>No</th>
            <th>Nama Akun</th>
            <th>Jumlah</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(pengiriman, index) in pagination" :key="pengiriman.pengirimanBarang_id">
            <td>{{ index + 1 }}</td>
            <td>{{ pengiriman.live_order_nama_akun }}</td>
            <td>{{ pengiriman.jumlah }}</td>
            <td>
              <button class="bg-green-500 hover:bg-green-800 text-white px-3 py-1 rounded-md"
                @click="openModalEditTransaksi(pengiriman.live_order_nama_akun)">
                Edit
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
  </div>

  <!-- Modal Add Order -->
  <div v-if="isModalOpenAddOrder" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
      <h3 class="text-lg font-semibold mb-4">Tambah Transaksi Live</h3>
      <form @submit.prevent="submitLiveOrder">
        <div class="mb-4">
          <label for="barang" class="block text-sm font-medium text-gray-700 mb-1">
            Barang<span class="text-red-500">*</span>
          </label>
          <input ref="barangInput" type="text" id="barang" v-model="form.barang" placeholder="Masukkan nama barang"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400"
            required/>
          <p v-if="errors.barang" class="text-red-500 text-sm mt-1">
            {{ errors.barang }}
          </p>
        </div>
        <div class="mb-4">
          <label for="namaAkun" class="block text-sm font-medium text-gray-700 mb-1">
            Nama Akun<span class="text-red-500">*</span>
          </label>
          <input type="text" id="namaAkun" v-model="form.namaAkun" placeholder="Masukkan nama akun"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400"
            required />
          <p v-if="errors.namaAkun" class="text-red-500 text-sm mt-1">
            {{ errors.namaAkun }}
          </p>
        </div>
        <div class="mb-4">
          <label for="platform" class="block text-sm font-medium text-gray-700 mb-1">
            Platform<span class="text-red-500">*</span>
          </label>
          <select id="platform" v-model="form.platform"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400"
            required>
            <option value="" disabled>Pilih Platform</option>
            <option value="tiktok">TikTok</option>
            <option value="instagram">Instagram</option>
            <option value="facebook">Facebook</option>
            <option value="whatsapp">WhatsApp</option>
          </select>
          <p v-if="errors.platform" class="text-red-500 text-sm mt-1">
            {{ errors.platform }}
          </p>
        </div>

        <div class="mb-4">
          <label for="hargaTotal" class="block text-sm font-medium text-gray-700 mb-1">
            Harga Total<span class="text-red-500">*</span>
          </label>
          <div class="flex">
            <span
              class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
              Rp
            </span>
            <input type="text" id="hargaTotal" :value="formattedHarga" @input="updateHarga($event.target.value)"
              placeholder="Masukkan harga"
              class="w-full border border-gray-300 rounded-r-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400"
              required />
            <p v-if="errors.hargaTotal" class="text-red-500 text-sm mt-1">
              {{ errors.hargaTotal }}
            </p>
          </div>
        </div>

        <div class="flex justify-end space-x-2">
          <button type="button" @click="closeModalAddOrder"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
            Batal
          </button>
          <button type="submit" :disabled="isSubmitting"
            class="px-4 py-2 bg-[#1C9DBD] text-white rounded-md hover:bg-[#17a2b8]">
            {{ isSubmitting ? "Menyimpan..." : "Tambah" }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Edit Order -->
  <div v-if="isModalOpenEditOrder"
    class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
      <h3 class="text-lg font-semibold mb-4">Edit Transaksi Live</h3>
      <form @submit.prevent="submitLiveEditOrder">
        <div class="mb-4">
          <label for="barang" class="block text-sm font-medium text-gray-700 mb-1">
            Barang<span class="text-red-500">*</span>
          </label>
          <input type="text" id="barang" v-model="form.barang" placeholder="Masukkan nama barang"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400"
            required />
          <p v-if="errors.barang" class="text-red-500 text-sm mt-1">
            {{ errors.barang }}
          </p>
        </div>
        <div class="mb-4">
          <label for="namaAkun" class="block text-sm font-medium text-gray-700 mb-1">
            Nama Akun<span class="text-red-500">*</span>
          </label>
          <input type="text" id="namaAkun" v-model="form.namaAkun" placeholder="Masukkan nama akun"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400"
            required />
          <p v-if="errors.namaAkun" class="text-red-500 text-sm mt-1">
            {{ errors.namaAkun }}
          </p>
        </div>
        <div class="mb-4">
          <label for="platform" class="block text-sm font-medium text-gray-700 mb-1">
            Platform<span class="text-red-500">*</span>
          </label>
          <select id="platform" v-model="form.platform"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400"
            required>
            <option value="" disabled>Pilih Platform</option>
            <option value="tiktok">TikTok</option>
            <option value="instagram">Instagram</option>
            <option value="facebook">Facebook</option>
            <option value="whatsapp">WhatsApp</option>
            <option value="shopee">Shopee</option>
          </select>
          <p v-if="errors.platform" class="text-red-500 text-sm mt-1">
            {{ errors.platform }}
          </p>
        </div>

        <div class="mb-4">
          <label for="hargaTotal" class="block text-sm font-medium text-gray-700 mb-1">
            Harga Total<span class="text-red-500">*</span>
          </label>
          <div class="flex">
            <span
              class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
              Rp
            </span>
            <input type="text" id="hargaTotal" :value="formattedHarga" @input="updateHarga($event.target.value)"
              placeholder="Masukkan harga"
              class="w-full border border-gray-300 rounded-r-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400"
              required />
            <p v-if="errors.hargaTotal" class="text-red-500 text-sm mt-1">
              {{ errors.hargaTotal }}
            </p>
          </div>
        </div>

        <div class="flex justify-end space-x-2">
          <button type="button" @click="closeModalEditOrder"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
            Batal
          </button>
          <button type="submit" :disabled="isSubmittingEdit"
            class="px-4 py-2 bg-[#1C9DBD] text-white rounded-md hover:bg-[#17a2b8]">
            {{ isSubmittingEdit ? "Menyimpan..." : "Simpan" }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <ModalLiveTransaksi v-model:show="isModalOpen" :namaAkun="selected.namaAkun" :barang="selected.barang"
    @save="handleSave" @close="isModalOpen = false" @removeItem="handleRemoveItem" />
</template>

<script setup>
import { reactive, ref, onMounted, computed, nextTick } from "vue";
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";
import ModalLiveTransaksi from "../components/ModalLiveTransaksi.vue";

const config = useRuntimeConfig();
const url = ref(config.public.apiBase);
const { $api } = useNuxtApp();
const pengirimanData = ref([]);
const searchQuery = ref("");
const activeTab = ref("order");
const isModalOpenAddOrder = ref(false);
const isModalOpenEditOrder = ref(false);
const isSubmitting = ref(false);
const isSubmittingEdit = ref(false);
const currentPage = ref(1);
const itemsPerPage = ref(10);
const barangMap = ref({});
const selected = ref({ namaAkun: "", barang: [] });
const errors = reactive({});

const isModalOpen = ref(false);
const handleSave = (form) => {
  isModalOpen.value = false;
};

const handleRemoveItem = (index) => {
  selected.value.barang.splice(index, 1);
};

const form = ref({
  id: null,
  barang: "",
  namaAkun: "",
  platform: "",
  hargaTotal: "",
});

const barangInput = ref(null);

onMounted(() => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;
  fetchDataPengiriman();
});

const openModalEditTransaksi = async (namaAkun) => {
  try {
    const { data } = await $api.get(
      `${url.value}/api/live-barang/data-live/` + namaAkun);

    const barangBelumPackaging = data.data.filter(item => item.is_check === 0);
    if (barangBelumPackaging.length === 0) {
      Swal.fire({
        icon: "info",
        title: "Semua Barang Sudah di Packaging",
        text: "Tidak ada barang tersisa untuk diproses.",
        confirmButtonColor: "#3085d6",
      });
      return;
    }

    if (data.data && data.data.length > 0) {
      selected.value = {
        namaAkun: data.data[0].live_order_nama_akun,
        barang: data.data
          .filter(item => item.is_check === 0)
          .map((item) => ({
            kode: item.code_nama,
            nama: item.barangentry_nama,
            jumlah: item.live_order_jumlah_barang,
            harga: parseFloat(item.live_order_harga_terjual),
            live_order_id: item.live_order_id,
            is_check: true,
          })),
      };
      isModalOpen.value = true;
    }
  } catch (error) {
    console.error("Gagal ambil data:", error);
  }
};

const capitalizeFirst = (str) => {
  if (!str) return "";
  return str.charAt(0).toUpperCase() + str.slice(1);
};

const fetchBarangNames = async (data) => {
  try {
  
    const ids = [...new Set(data.map((item) => item.live_order_barang_id))];
    const requests = ids.map((id) =>
      $api.get(`${url.value}/api/entrybarang/${id}`)
    );
    const responses = await Promise.all(requests);
    responses.forEach((res, i) => {
      barangMap.value[ids[i]] = res.data.data.barangentry_nama;
    });
  } catch (error) {
    console.error("Gagal fetch nama barang:", error);
  }
};

const fetchDataPengiriman = async () => {
  try {
  
    let endpoint = "";
    if (activeTab.value === "order") {
      endpoint = "/api/live-barang";
    } else {
      endpoint = "/api/live-barang/getAmountLive";
    }
    const res = await $api.get(`${url.value}${endpoint}`);
    // const res = await $api.get(`http://192.168.18.52:8080${endpoint}`);
    pengirimanData.value = res.data.data;
    if (activeTab.value === "order") {
      await fetchBarangNames(pengirimanData.value);
    }
  } catch (error) {
    console.error("Gagal fetch data pengiriman:", error);
  }
};

const groupedData = computed(() => {
  const groups = {};
  pengirimanData.value.forEach((item) => {
    if (!groups[item.live_order_nama_akun]) {
      groups[item.live_order_nama_akun] = [];
    }
    groups[item.live_order_nama_akun].push(item);
  });
  return groups;
});

const filteredGroupedData = computed(() => {
  if (!searchQuery.value) return groupedData.value;

  let result = {};

  for (let akun in groupedData.value) {
    const filteredItems = groupedData.value[akun].filter((item) => {
      const namaBarang = barangMap[item.live_order_barang_id] || "";
      const platform = capitalizeFirst(item.live_order_platform);
      return (
        akun.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        namaBarang.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        platform.toLowerCase().includes(searchQuery.value.toLowerCase())
      );
    });

    if (filteredItems.length > 0) {
      result[akun] = filteredItems;
    }
  }

  return result;
});

const paginatedGroupedData = computed(() => {
  const groups = Object.entries(filteredGroupedData.value);

  if (itemsPerPage.value === "all") {
    return Object.fromEntries(groups);
  }

  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;

  return Object.fromEntries(groups.slice(start, end));
});

const totalGroups = computed(() => Object.keys(filteredGroupedData.value).length);

const totalHargaPerAkun = computed(() => {
  const totalMap = {};
  pengirimanData.value.forEach((item) => {
    if (!totalMap[item.live_order_nama_akun]) {
      totalMap[item.live_order_nama_akun] = 0;
    }
    totalMap[item.live_order_nama_akun] += Number(
      item.live_order_harga_terjual
    );
  });
  return totalMap;
});

function validate() {
  errors.barang = !form.barang ? "Kode barang wajib diisi" : "";
  errors.namaAkun = !form.value.namaAkun ? "Nama akun wajib diisi" : "";
  errors.platform = !form.value.platform ? "Platform selesai wajib diisi" : "";
  errors.hargaTotal = !form.value.hargaTotal ? "Harga Total wajib diisi" : "";

  return Object.values(errors).every((err) => !err);
}

const submitLiveOrder = async () => {

  isSubmitting.value = true;
  try {
  
    const namaBarang = await $api.get(
      `${url.value}/api/entrybarang/getDataByCode/` + form.value.barang);
    await $api.post(`${url.value}/api/live-barang/store-live`, {
      live_order_barang_id: namaBarang.data.data.barangentry_id,
      live_order_nama_akun: form.value.namaAkun,
      live_order_platform: form.value.platform,
      live_order_harga_terjual: form.value.hargaTotal,
    });

    form.value = {
      barang: "",
      namaAkun: "",
      platform: "",
      hargaTotal: "",
    };
    closeModalAddOrder();
    await fetchDataPengiriman();
    Swal.fire({
      title: "Berhasil!",
      text: "Order Berhasil ditambahkan.",
      icon: "success",
      timer: 1500,
      showConfirmButton: false,
    });
  } catch (error) {
    console.error("Gagal menyimpan data:", error);
    Swal.fire({
      title: "Gagal!",
      text: "Terjadi kesalahan saat menambahkan order.",
      icon: "error",
    });
  } finally {
    isSubmitting.value = false;
  }
};

const editOrderLive = async (id) => {
  try {
  
    const res = await $api.get(`${url.value}/api/live-barang/show-live/${id}`);
    const data = res.data.data;
    const resBarang = await $api.get(
      `${url.value}/api/entrybarang/` + data.live_order_barang_id);
    const code = resBarang.data.data.barangentry_code_id;

    const codeNama = await $api.get(`${url.value}/api/codebarang/` + code);

    form.value.id = data.live_order_id;
    form.value.barang = codeNama.data.code_nama;
    form.value.namaAkun = data.live_order_nama_akun;
    form.value.platform = data.live_order_platform.toLowerCase();
    form.value.hargaTotal = Number(data.live_order_harga_terjual);

    isModalOpenEditOrder.value = true;
  } catch (error) {
    console.error("Gagal mengambil data:", error);
  }
};

const submitLiveEditOrder = async () => {

  isSubmittingEdit.value = true;
  try {
    const namaBarang = await $api.get(
      `${url.value}/api/entrybarang/getDataByCode/` + form.value.barang);
    await $api.put(
      `${url.value}/api/live-barang/update-live/${form.value.id}`,
      {
        live_order_barang_id: namaBarang.data.data.barangentry_id,
        live_order_nama_akun: form.value.namaAkun,
        live_order_platform: form.value.platform,
        live_order_harga_terjual: form.value.hargaTotal,
      });
    isModalOpenEditOrder.value = false;
    await fetchDataPengiriman();
    Swal.fire({
      title: "Berhasil!",
      text: "Order Berhasil diedit.",
      icon: "success",
      timer: 1500,
      showConfirmButton: false,
    });
  } catch (error) {
    console.error("Gagal update data:", error);
    Swal.fire({
      title: "Gagal!",
      text: "Terjadi kesalahan saat menambahkan produk.",
      icon: "error",
    });
  } finally {
    isSubmittingEdit.value = false;
  }
};

const closeModalEditOrder = () => {
  isModalOpenEditOrder.value = false;
  resetForm();
};

const resetForm = () => {
  form.value = {
    barang: "",
    namaAkun: "",
    platform: "",
    hargaTotal: "",
  };
};

const listpengirimanData = computed(() => {
  const sorted = [...pengirimanData.value].sort((a, b) => {
    return new Date(b.created_at) - new Date(a.created_at);
  });
  if (!searchQuery.value) return sorted;

  const q = searchQuery.value.toLowerCase();
  return sorted.filter((pengiriman) => {
    return (
      pengiriman.live_order_nama_akun?.toLowerCase().includes(q) ||
      pengiriman.live_order_platform?.toLowerCase().includes(q)
    );
  });
});

const pagination = computed(() => {
  if (itemsPerPage.value === "all") {
    return listpengirimanData.value;
  }

  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return listpengirimanData.value.slice(start, end);
});

const totalPages = computed(() => {
  if (itemsPerPage.value === "all") return 1;

  if (activeTab.value === "order") {
    return Math.ceil(totalGroups.value / itemsPerPage.value);
  }

  return Math.ceil(listpengirimanData.value.length / itemsPerPage.value);
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(value);
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString("id-ID", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

const openModalAddOrder = () => {
  isModalOpenAddOrder.value = true;
};

const closeModalAddOrder = () => {
  isModalOpenAddOrder.value = false;
  resetForm();
};

const formattedHarga = computed(() => {
  if (!form.value.hargaTotal) return "";
  return form.value.hargaTotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
});

const updateHarga = (val) => {
  const number = val.replace(/\./g, "");
  form.value.hargaTotal = number;
};

const deleteOrder = async (id) => {
  const result = await Swal.fire({
    title: "Konfirmasi Hapus",
    text: `Anda yakin ingin menghapus order ini?`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, Hapus!",
    cancelButtonText: "Batal",
    reverseButtons: true,
  });

  if (result.isConfirmed) {
    try {
    
      await $api.delete(`${url.value}/api/live-barang/delete-live/${id}`);

      await fetchDataPengiriman();
      await Swal.fire({
        title: "Berhasil!",
        text: `Order ini telah dihapus.`,
        icon: "success",
        timer: 1500,
        showConfirmButton: false,
      });
    } catch (error) {
      console.error("Error saat menghapus order ini:", error);
      Swal.fire({
        title: "Gagal",
        text: "Terjadi kesalahan saat menghapus order ini.",
        icon: "error",
      });
    }
  }
};

const paginatedPages = computed(() => {
  const total = totalPages.value;
  const current = currentPage.value;
  const pages = [];

  if (total <= 5) {
    for (let i = 1; i <= total; i++) {
      pages.push(i);
    }
  } else {
    if (current <= 3) {
      pages.push(1, 2, 3, "...", total);
    } else if (current >= total - 2) {
      pages.push(1, "...", total - 2, total - 1, total);
    } else {
      pages.push(1, "...", current - 1, current, current + 1, "...", total);
    }
  }

  return pages;
});

watch(isModalOpenAddOrder, async (val) => {
  if (val) {
    await nextTick();
    barangInput.value.focus();
  }
});

watch(searchQuery, () => {
  currentPage.value = 1;
});

watch(currentPage, (val) => {
  if (val < 1) currentPage.value = 1;
  if (val > totalPages.value) currentPage.value = totalPages.value;
});

watch(activeTab, () => {
  fetchDataPengiriman();
});
</script>

<style scoped>
* {
  font-family: "Nunito", sans-serif;
}

.search-box {
  border: 1px solid #ccc;
  padding: 10px;
  width: 385px;
  height: 25px;
  font-size: 12px;
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

.search-box::placeholder {
  color: #888;
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

.bg-gray-800 {
  background-color: rgba(0, 0, 0, 0.5);
}

.bg-white.rounded-lg.shadow-lg {
  border: 1px solid #E4E6FC;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

input,
select,
textarea {
  background-color: #FDFDFF !important;
  border: 1px solid #E4E6FC !important;
  /* border-radius: 8px !important; */
  font-size: 14px;
  color: #000000;
  transition: all 0.2s ease-in-out;
}

input::placeholder,
textarea::placeholder {
  color: #808080;
  opacity: 0.8;
}

input:focus,
select:focus,
textarea:focus {
  border-color: #1C9DBD !important;
  outline: none;
  background-color: #FFFFFF;
  box-shadow: 0 0 0 2px rgba(28, 157, 189, 0.15);
}

label {
  color: #000000;
  font-weight: 500;
  font-size: 14px;
}

.inline-flex.items-center {
  background-color: #FDFDFF !important;
  border: 1px solid #E4E6FC !important;
  color: #404040;
}

button {
  font-weight: 500;
  border-radius: 8px;
  font-size: 14px;
}

button.bg-gray-200 {
  background-color: #F3F4F6;
  color: #404040;
  border: 1px solid #E4E6FC;
}

button.bg-gray-200:hover {
  background-color: #E8E9F0;
}

button.bg-\[\#1C9DBD\] {
  background: linear-gradient(180deg, #1C9DBD 0%, #0A84FF 100%) !important;
  border: none;
}

button.bg-\[\#1C9DBD\]:hover {
  background: linear-gradient(180deg, #0A84FF 0%, #0077E6 100%) !important;
}

h3 {
  color: #000000;
  font-weight: 600;
  font-size: 16px;
}

.bg-gray-800.bg-opacity-50 {
  background-color: rgba(0, 0, 0, 0.4);
}

.text-red-500 {
  color: #FF5757;
}
</style>
