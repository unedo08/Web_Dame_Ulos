<template>
  <div>
    <title>Menu Customer</title>
    <div class="judul text-xl font-semibold mb-4">Menu Customer</div>
    <div class="flex items-center justify-between pt-2">
      <input class="search-box p-2 border rounded-md w-[385px] text-sm" v-model="searchQuery" type="text"
        placeholder="Search customer..." />

      <!-- <button
        class="btn-add bg-blue-500 text-white rounded-md hover:bg-blue-600 w-[104px] h-[25px]"
        @click="openModal"
      >
        + Tambah
      </button> -->
    </div>

    <table class="datatable w-full rounded-md overflow-hidden mt-4">
      <thead class="bg-blue-100">
        <tr>
          <th class="px-4 py-2 text-left">No.</th>
          <th class="px-4 py-2 text-left">Akun</th>
          <th class="px-4 py-2 text-left">Nama</th>
          <th class="px-4 py-2 text-left">Alamat</th>
          <th class="px-4 py-2 text-left">No HP</th>
          <th class="px-4 py-2 text-left">Platform</th>
          <th class="px-4 py-2 text-left">Aksi</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="(cust, index) in pagination" :key="cust.customer_id"
          :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
          <td class="px-4 py-2">{{ index + 1 }}</td>
          <td class="px-4 py-2">{{ cust.customer_akun }}</td>
          <td class="px-4 py-2">{{ cust.customer_nama }}</td>
          <td class="px-4 py-2">{{ cust.customer_alamat }}</td>
          <td class="px-4 py-2">{{ cust.customer_notelepon }}</td>
          <td class="px-4 py-2">{{ cust.transaksi_tipe }}</td>

          <td class="px-4 py-2">
            <button class="px-2 py-1 bg-red-500 text-white hover:bg-red-600 rounded-md text-xs"
              @click="deleteCustomer(cust.customer_id, cust.customer_nama)">
              Delete
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
        <button class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400" :disabled="currentPage === 1"
          @click="currentPage--">
          Sebelumnya
        </button>

        <button v-for="(page, index) in paginatedPages" :key="index"
          @click="typeof page === 'number' && (currentPage = page)" :class="[
            'px-3 py-1 rounded',
            currentPage === page ? 'bg-blue-500 text-white' : 'bg-gray-200',
            page === '...' ? 'cursor-default' : 'cursor-pointer'
          ]" :disabled="page === '...'">
          {{ page }}
        </button>

        <button class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400" :disabled="currentPage === totalPages"
          @click="currentPage++">
          Selanjutnya
        </button>
      </div>
    </div>

    <div v-if="isModalOpen" class="fixed inset-0 flex justify-center items-center bg-gray-800 bg-opacity-50 z-50">
      <div class="bg-white p-6 rounded-lg max-w-lg w-full">
        <h3 class="text-xl font-semibold mb-4">Tambah Customer</h3>

        <form @submit.prevent="submitCustomer">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">
              Akun <span class="text-red-500">*</span>
            </label>
            <input v-model="newCustomer.customer_akun" type="text"
              class="mt-1 block w-full border border-gray-300 rounded-md p-2"
              placeholder="Isikan username / akun pelanggan" />
            <p v-if="errors.customer_akun" class="text-red-500 text-xs">{{ errors.customer_akun }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">
              Nama Customer <span class="text-red-500">*</span>
            </label>
            <input v-model="newCustomer.customer_nama" type="text"
              class="mt-1 block w-full border border-gray-300 rounded-md p-2" placeholder="Nama customer" />
            <p v-if="errors.customer_nama" class="text-red-500 text-xs">{{ errors.customer_nama }}</p>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">
              Alamat <span class="text-red-500">*</span>
            </label>
            <input v-model="newCustomer.customer_alamat" type="text"
              class="mt-1 block w-full border border-gray-300 rounded-md p-2" placeholder="Alamat customer" />
            <p v-if="errors.customer_alamat" class="text-red-500 text-xs">{{ errors.customer_alamat }}</p>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">
              Nomor HP <span class="text-red-500">*</span>
            </label>
            <input v-model="newCustomer.customer_notelpon" type="text"
              class="mt-1 block w-full border border-gray-300 rounded-md p-2" placeholder="Nomor telepon" />
            <p v-if="errors.customer_notelpon" class="text-red-500 text-xs">
              {{ errors.customer_notelpon }}
            </p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Platform</label>
            <input v-model="newCustomer.customer_platform" type="text"
              class="mt-1 block w-full border border-gray-300 rounded-md p-2"
              placeholder="Contoh: Instagram, Tiktok, Shopee" />
          </div>
          <div class="flex justify-end">
            <button type="button" @click="closeModal"
              class="mr-4 px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
              Cancel
            </button>

            <button type="submit"
              class="px-4 py-2 bg-cyan-600 text-white rounded-md hover:bg-cyan-700 disabled:bg-gray-400">
              Save
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import Swal from "sweetalert2";
import { useRuntimeConfig } from "#imports";
const { $api } = useNuxtApp();
const searchQuery = ref("");
const customer = ref([]);
const url = ref("");

const isModalOpen = ref(false);
const itemsPerPage = ref(10);
const currentPage = ref(1);
const newCustomer = ref({
  customer_akun: "",
  customer_nama: "",
  customer_alamat: "",
  customer_notelepon: "",
  transaksi_tipe: "-"
});

const errors = ref({});

onMounted(async () => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;
  fetchData();
});

const fetchData = async () => {
  try {
    const res = await $api.get(`${url.value}/api/customer`);

    customer.value = res.data.data
      .map((item) => ({
        customer_id: item.customer_id,
        customer_akun: item.customer_akun,
        customer_nama: item.customer_nama,
        customer_alamat: item.customer_alamat,
        customer_notelepon: item.customer_notelepon,
        transaksi_tipe: item.transaksi_tipe,
        created_at: item.created_at
      }))
      .sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  } catch (error) {
    console.error("Error fetching customer:", error);
  }
};

const listCustomer = computed(() => {
  const q = searchQuery.value.toLowerCase();

  return customer.value.filter((cust) => {
    return (
      cust.customer_nama?.toLowerCase().includes(q) ||
      cust.customer_akun?.toLowerCase().includes(q) ||
      cust.customer_alamat?.toLowerCase().includes(q) ||
      cust.customer_notelepon?.toLowerCase().includes(q) ||
      cust.transaksi_tipe?.toLowerCase().includes(q)
    );
  });
});

const pagination = computed(() => {
  if (itemsPerPage.value === "all") return listCustomer.value;
  const start = (currentPage.value - 1) * itemsPerPage.value;
  return listCustomer.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => {
  if (itemsPerPage.value === "all") return 1;
  return Math.ceil(listCustomer.value.length / itemsPerPage.value);
});

const paginatedPages = computed(() => {
  const total = totalPages.value;
  const current = currentPage.value;

  if (total <= 5) return Array.from({ length: total }, (_, i) => i + 1);
  if (current <= 3) return [1, 2, 3, "...", total];
  if (current >= total - 2) return [1, "...", total - 2, total - 1, total];
  return [1, "...", current - 1, current, current + 1, "...", total];
});

const openModal = () => {
  isModalOpen.value = true;
  errors.value = {};
};

const closeModal = () => {
  isModalOpen.value = false;
  newCustomer.value = {
    customer_akun: "",
    customer_nama: "",
    customer_alamat: "",
    customer_notelpon: "",
    customer_platform: "-"
  };
  errors.value = {};
};

// VALIDATION
const validateCustomer = () => {
  errors.value = {};

  if (!newCustomer.value.customer_akun.trim())
    errors.value.customer_akun = "Akun wajib diisi";

  if (!newCustomer.value.customer_nama.trim())
    errors.value.customer_nama = "Nama wajib diisi";

  if (!newCustomer.value.customer_alamat.trim())
    errors.value.customer_alamat = "Alamat wajib diisi";

  if (!newCustomer.value.customer_notelpon.trim())
    errors.value.customer_notelpon = "Nomor telepon wajib diisi";

  return Object.keys(errors.value).length === 0;
};

const submitCustomer = async () => {
  if (!validateCustomer()) {
    Swal.fire("Gagal!", "Silakan lengkapi semua field wajib.", "error");
    return;
  }
  try {
    await $api.post(`${url.value}/api/customer/addCustomer`, newCustomer.value);

    Swal.fire("Berhasil!", "Customer berhasil ditambahkan.", "success");

    closeModal();
    fetchData();
  } catch (err) {
    Swal.fire("Gagal!", "Kesalahan saat menambah customer", "error");
  }
};

const deleteCustomer = async (id, nama) => {
  const confirm = await Swal.fire({
    title: "Hapus Customer?",
    text: `Anda yakin ingin menghapus '${nama}'?`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, Hapus!",
    cancelButtonText: "Batal"
  });

  if (!confirm.isConfirmed) return;

  try {
    await $api.delete(`${url.value}/api/customer/deleteCustomer/${id}`);

    customer.value = customer.value.filter((c) => c.customer_id !== id);

    Swal.fire("Berhasil!", "Customer berhasil dihapus.", "success");
  } catch (err) {
    Swal.fire("Gagal!", "Tidak dapat menghapus data.", "error");
  }
};
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
  text-align: left;
  font-size: 12px;
}

.datatable th {
  background-color: #f4f4f4;
}

.btn-add {
  font-size: 12px;
}
</style>
