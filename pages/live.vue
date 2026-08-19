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
          <input v-model="searchQuery" type="text" class="search-box mb-4 rounded-md" placeholder="Cari data live..." />
        </div>
        <div>
          <button class="btn-add bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[104px] h-[25px]"
            @click="openModalAddOrder">
            + Live
          </button>
        </div>
      </div>
      <div class="live-table-wrapper">
        <table class="datatable rounded-md overflow-hidden">
          <thead class="bg-blue-100">
            <tr>
              <!-- <th>No</th> -->
              <th class="px-4 py-2 text-left">Nama Akun</th>
              <th class="px-4 py-2 text-left">Tanggal</th>
              <th class="px-4 py-2 text-left">Kode Barang</th>
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
                  {{ formatTanggal(pengiriman.created_at) }}
                </td>

                <td class="px-4 py-2">
                  {{ pengiriman.code_nama }}
                </td>

                <td class="px-4 py-2">
                  {{ pengiriman.barangentry_nama }}
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
      </div>
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
      <input v-model="searchQuery" type="text" class="search-box mb-4 rounded-md" placeholder="Cari data live..." />
      <div class="live-table-wrapper">
        <table class="datatable rounded-md overflow-hidden">
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
      </div>
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
          <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
            Platform <span class="required">*</span>
          </label>
          <select v-model="form.platform"
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-cyan-500">
            <option disabled value="">Pilih Jenis Platform</option>
            <option v-for="platform in jenisPlatform" :key="platform.id" :value="platform.platform_nama">
              {{ platform.platform_nama }}
            </option>
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
          <label class="judul-label block text-sm font-medium text-gray-700 mb-1">
            Platform <span class="required">*</span>
          </label>
          <select v-model="form.platform"
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-cyan-500">
            <option disabled value="">Pilih Jenis Platform</option>
            <option v-for="platform in jenisPlatform" :key="platform.id" :value="platform.platform_nama">
              {{ platform.platform_nama }}
            </option>
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
import { reactive, ref, onMounted, computed, watch, nextTick } from "vue";
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";
import ModalLiveTransaksi from "../components/ModalLiveTransaksi.vue";

const config = useRuntimeConfig();
const { $api } = useNuxtApp();
const url = ref(config.public.apiBase);
const pengirimanData = ref({});
const transactionData = ref([]);
const searchQuery = ref("");
const activeTab = ref("order");
const currentPage = ref(1);
const itemsPerPage = ref(10);
const isModalOpenAddOrder = ref(false);
const isModalOpenEditOrder = ref(false);
const isSubmitting = ref(false);
const isSubmittingEdit = ref(false);
const jenisPlatform = ref([]);
const selected = ref({ namaAkun: "", barang: [] });
const errors = reactive({});
const isModalOpen = ref(false);

const form = ref({
  id: null,
  barang: "",
  namaAkun: "",
  platform: "",
  hargaTotal: "",
});

const barangInput = ref(null);

onMounted(() => {
  fetchDataPengiriman();
});

const fetchDataPengiriman = async () => {
  try {
    if (activeTab.value === "order") {
      const res = await $api.get(`${url.value}/api/live-barang/data-live-grouped`);
      const resJenisPlatform = await $api.get(`${url.value}/api/platform`);
      const rawData = res.data.data || {}

      const filtered = {}

      for (const akun in rawData) {
        const items = rawData[akun].filter(item => Number(item.is_check) === 0)

        if (items.length > 0) {
          filtered[akun] = items
        }
      }

      pengirimanData.value = filtered;
      jenisPlatform.value = resJenisPlatform.data.data;
    } else {
      const res = await $api.get(`${url.value}/api/live-barang/getAmountLive`);
      transactionData.value = res.data.data || [];
    }
  } catch (error) {
    console.error("Gagal fetch data:", error);
  }
};

const flatData = computed(() => {
  const result = []

  for (const akun in pengirimanData.value) {
    pengirimanData.value[akun].forEach(item => {
      result.push({
        ...item,
        akun
      })
    })
  }

  return result
})

const sortedFlatData = computed(() => {
  return [...filteredFlatData.value].sort((a, b) => {
    return new Date(b.created_at) - new Date(a.created_at)
  })
})

const paginatedFlatData = computed(() => {
  if (itemsPerPage.value === "all") return sortedFlatData.value

  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value

  return sortedFlatData.value.slice(start, end)
})

const filteredFlatData = computed(() => {
  if (!searchQuery.value) return flatData.value

  return flatData.value.filter(item => {
    return (
      item.akun?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      item.barangentry_nama?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      item.code_nama?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      item.live_order_platform?.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  })
})

const groupedSortedData = computed(() => {
  const grouped = {}

  sortedFlatData.value.forEach(item => {
    if (!grouped[item.akun]) {
      grouped[item.akun] = []
    }
    grouped[item.akun].push(item)
  })

  return grouped
})

const sortedGroupedEntries = computed(() => {
  return Object.entries(groupedSortedData.value).sort((a, b) => {
    const lastA = new Date(a[1][0]?.created_at || 0)
    const lastB = new Date(b[1][0]?.created_at || 0)
    return lastB - lastA
  })
})

const groupedData = computed(() => {
  const entries = Object.entries(pengirimanData.value)

  const sortedEntries = entries.map(([akun, items]) => {
    const sortedItems = [...items].sort((a, b) => {
      return new Date(b.created_at) - new Date(a.created_at)
    })

    return [akun, sortedItems]
  })

  sortedEntries.sort((a, b) => {
    const lastA = new Date(a[1][0]?.created_at || 0)
    const lastB = new Date(b[1][0]?.created_at || 0)
    return lastB - lastA
  })

  const result = {}
  sortedEntries.forEach(([akun, items]) => {
    result[akun] = items
  })

  return result
})

const filteredGroupedData = computed(() => {
  if (!searchQuery.value) return groupedData.value;
  const result = {};

  for (const akun in groupedData.value) {
    const filtered = groupedData.value[akun].filter(item => {
      return (
        akun.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        item.barangentry_nama?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        item.code_nama?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        item.live_order_platform?.toLowerCase().includes(searchQuery.value.toLowerCase())
      );
    });
    if (filtered.length) result[akun] = filtered;
  }
  return result;
});

const paginatedGroupedData = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value

  return Object.fromEntries(
    sortedGroupedEntries.value.slice(start, end)
  )
})

const totalGroups = computed(() =>
  Object.keys(filteredGroupedData.value).length
);

const totalHargaPerAkun = computed(() => {
  const result = {};

  for (const akun in groupedData.value) {
    let total = 0;

    groupedData.value[akun].forEach(item => {
      if (Number(item.is_check) === 0) {
        total += Number(item.live_order_harga_terjual);
      }
    });

    result[akun] = total;
  }

  return result;
});

const totalPages = computed(() => {
  if (itemsPerPage.value === "all") return 1
  return Math.ceil(sortedGroupedEntries.value.length / itemsPerPage.value)
})

const paginatedPages = computed(() => {

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

const filteredTransaction = computed(() => {

  if (!searchQuery.value) return transactionData.value;

  return transactionData.value.filter(item =>
    item.live_order_nama_akun?.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

const pagination = computed(() => {

  if (itemsPerPage.value === "all") return filteredTransaction.value;

  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;

  return filteredTransaction.value.slice(start, end);
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0
  }).format(Number(value));
};

const capitalizeFirst = (str) => {
  if (!str) return "";
  return str.charAt(0).toUpperCase() + str.slice(1);
};

const handleSave = () => {
  isModalOpen.value = false;
};

const handleRemoveItem = (index) => {
  selected.value.barang.splice(index, 1);
};

const openModalEditTransaksi = async (namaAkun) => {
  try {
    const { data } = await $api.get(
      `${url.value}/api/live-barang/data-live/akun/` + namaAkun
    );
    const barangBelumPackaging = data.data.filter(item => item.is_check === 0);

    if (barangBelumPackaging.length === 0) {
      Swal.fire({
        icon: "info",
        title: "Semua Barang Sudah di Packaging",
        text: "Tidak ada barang tersisa untuk diproses.",
      });
      return;
    }

    selected.value = {
      namaAkun: data.data[0].live_order_nama_akun,
      barang: barangBelumPackaging.map((item) => ({
        kode: item.code_nama,
        nama: item.barangentry_nama,
        jumlah: item.live_order_jumlah_barang,
        harga: parseFloat(item.live_order_harga_terjual),
        live_order_id: item.live_order_id,
        platform: item.live_order_platform,
        is_check: true,
      })),
    };
    isModalOpen.value = true;
  } catch (error) {
    console.error("Gagal ambil data:", error);
  }
};

const closeModalEditOrder = () => {
  isModalOpenEditOrder.value = false;
  resetForm();
};

const resetForm = () => {
  form.value = {
    id: null,
    barang: "",
    namaAkun: "",
    platform: "",
    hargaTotal: "",
  };
};

const openModalAddOrder = () => {
  isModalOpenAddOrder.value = true;
};

const closeModalAddOrder = () => {
  isModalOpenAddOrder.value = false;
  resetForm();
};

const submitLiveOrder = async () => {
  isSubmitting.value = true;
  try {
    const namaBarang = await $api.get(
      `${url.value}/api/entrybarang/getDataByCode/` + form.value.barang
    );

    await $api.post(`${url.value}/api/live-barang/store-live`, {
      live_order_barang_id: namaBarang.data.data.barangentry_id,
      live_order_nama_akun: form.value.namaAkun,
      live_order_platform: form.value.platform,
      live_order_harga_terjual: form.value.hargaTotal
    });

    closeModalAddOrder();
    fetchDataPengiriman();
    Swal.fire("Berhasil", "Order berhasil ditambahkan", "success");
  } catch (e) {
    Swal.fire("Error", "Gagal menambahkan order", "error");
  }
  isSubmitting.value = false;
};

const editOrderLive = async (id) => {
  try {
    const res = await $api.get(`${url.value}/api/live-barang/show-live/${id}`);
    const data = res.data.data;
    form.value.id = data.live_order_id;
    form.value.namaAkun = data.live_order_nama_akun;
    form.value.platform = data.live_order_platform;
    form.value.hargaTotal = data.live_order_harga_terjual;
    isModalOpenEditOrder.value = true;
  } catch (error) {
    console.error(error);
  }
};


function formatTanggal(dateStr) {
  if (!dateStr) return "-";
  const date = new Date(dateStr);
  return date.toLocaleString("id-ID", {
    day: "2-digit",
    month: "long",
    year: "numeric",
  });
}

const submitLiveEditOrder = async () => {
  isSubmittingEdit.value = true;
  try {
    await $api.put(`${url.value}/api/live-barang/update-live/${form.value.id}`, {
      live_order_nama_akun: form.value.namaAkun,
      live_order_platform: form.value.platform,
      live_order_harga_terjual: form.value.hargaTotal
    });
    isModalOpenEditOrder.value = false;
    fetchDataPengiriman();
    Swal.fire("Berhasil", "Order berhasil diupdate", "success");
  } catch (e) {
    Swal.fire("Error", "Gagal update", "error");
  }
  isSubmittingEdit.value = false;
};

const deleteOrder = async (id) => {
  const result = await Swal.fire({
    title: "Hapus Order?",
    icon: "warning",
    showCancelButton: true
  });

  if (!result.isConfirmed) return;
  await $api.delete(`${url.value}/api/live-barang/delete-live/${id}`);
  fetchDataPengiriman();
};

const formattedHarga = computed(() => {
  if (!form.value.hargaTotal) return "";
  return form.value.hargaTotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
});

const updateHarga = (val) => {
  const number = val.replace(/\./g, "");
  form.value.hargaTotal = number;
};

watch(searchQuery, () => currentPage.value = 1);

watch(activeTab, () => {
  currentPage.value = 1;
  fetchDataPengiriman();
});

watch(isModalOpenAddOrder, async (val) => {
  if (val) {
    await nextTick();
    barangInput.value?.focus();
  }
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
  height: 34px;
}

.live-table-wrapper {
  display: block;
  width: 100%;
  max-width: 100%;
  min-width: 0;
  overflow-x: auto;
  overflow-y: hidden;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: thin;
}

.datatable {
  width: 100%;
  min-width: 1000px;
  max-width: none;
  border-collapse: collapse;
  table-layout: auto;
  margin-top: 20px;
}

.datatable th,
.datatable td {
  padding: 10px;
  text-align: left;
  font-size: 12px;
  white-space: nowrap;
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

/* =========================================================
   TABLET
   ========================================================= */

@media (max-width: 1024px) {

  .live-table-wrapper {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    overflow-x: auto;
    overflow-y: hidden;
  }

  .datatable {
    width: 1000px !important;
    min-width: 1000px !important;
    max-width: none !important;
  }

  .search-box {
    width: 300px;
  }

  .btn-add {
    width: 104px !important;
    height: 30px !important;
    padding: 0 10px;
    font-size: 11px;
  }
}

/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 767px) {

  .judul {
    font-size: 18px !important;
  }

  .flex.space-x-6 {
    width: 100%;
    overflow-x: auto;
    flex-wrap: nowrap;
    gap: 18px;
    padding-bottom: 4px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
  }

  .flex.space-x-6::-webkit-scrollbar {
    display: none;
  }

  .flex.space-x-6 button {
    flex: 0 0 auto;
    white-space: nowrap;
  }

  .flex.items-center.justify-between.pt-2 {
    flex-direction: column;
    align-items: stretch;
    gap: 8px;
  }

  .search-box {
    width: 100% !important;
    max-width: none;
    height: 38px;
    box-sizing: border-box;
    margin-bottom: 4px !important;
  }

  .btn-add {
    width: auto !important;
    min-width: 68px;
    height: 30px !important;
    padding: 0 10px;
    font-size: 10px;
  }

  .flex.items-center.justify-between.pt-2 > div:last-child {
    display: flex;
    justify-content: flex-end;
  }

  .live-table-wrapper {
    display: block;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
  }

  .datatable {
    display: table;
    width: 1000px !important;
    min-width: 1000px !important;
    max-width: none !important;
    table-layout: auto;
  }

  .datatable th,
  .datatable td {
    padding: 9px 11px;
    font-size: 11px;
    white-space: nowrap;
  }

  .datatable td .flex {
    flex-wrap: nowrap;
    gap: 5px;
  }

  .datatable td button {
    flex: 0 0 auto;
    padding: 5px 9px;
    font-size: 10px;
    white-space: nowrap;
  }

  .flex.justify-between.items-center.mt-8.mb-4.text-xs {
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
    margin-top: 12px;
  }

  .flex.justify-between.items-center.mt-8.mb-4.text-xs
    > div:first-child {
    justify-content: flex-start;
  }

  .flex.justify-between.items-center.mt-8.mb-4.text-xs
    > div:last-child {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    gap: 4px;
  }

  .flex.justify-between.items-center.mt-8.mb-4.text-xs
    button {
    padding: 5px 9px;
    font-size: 10px;
  }

  .fixed.inset-0 {
    padding: 10px;
    box-sizing: border-box;
  }

  .fixed.inset-0 > .bg-white.rounded-lg.shadow-lg {
    width: 100% !important;
    max-width: none !important;
    max-height: calc(100vh - 20px);
    overflow-y: auto;
    box-sizing: border-box;
    padding: 16px !important;
  }

  .fixed.inset-0 h3 {
    font-size: 15px;
  }

  .fixed.inset-0 input,
  .fixed.inset-0 select {
    font-size: 12px;
    padding: 9px 10px;
  }

  .fixed.inset-0 .flex.justify-end button {
    padding: 8px 14px;
    font-size: 11px;
  }
}

/* =========================================================
   SMALL PHONE
   ======================================================== */
@media (max-width: 480px) {
  .judul {
    font-size: 17px !important;
  }

  .search-box {
    height: 36px;
    padding: 8px 10px;
    font-size: 11px;
  }

  .btn-add {
    min-width: 64px;
    height: 29px !important;
    padding: 0 9px;
    font-size: 10px;
  }

  .datatable {
    width: 1000px !important;
    min-width: 1000px !important;
    max-width: none !important;
  }

  .datatable th,
  .datatable td {
    padding: 8px 10px;
    font-size: 10px;
    white-space: nowrap;
  }

  .datatable td button {
    padding: 4px 8px;
    font-size: 10px;
  }
}
</style>
