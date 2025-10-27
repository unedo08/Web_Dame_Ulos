<template>
  <div v-if="show" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-2xl shadow-lg">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold">Detail Transaksi</h2>
        <button @click="$emit('close')" class="text-gray-500 hover:text-red-500 text-xl">
          &times;
        </button>
      </div>

      <div v-if="loading" class="text-center py-6">Memuat data...</div>
      <div v-else>
        <table class="datatable w-full text-sm">
          <thead>
            <tr class="bg-gray-100">
              <th class="p-2">#</th>
              <th class="p-2">Kode Barang</th>
              <th class="p-2">Nama Barang</th>
              <th class="p-2">Jumlah</th>
              <th class="p-2">Harga</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in detailBarang" :key="item.transaksidetail_id" class="text-center">
              <td class="p-2">{{ index + 1 }}</td>
              <td class="p-2">{{ item.kode_barang }}</td>
              <td class="p-2">{{ item.nama_barang }}</td>
              <td class="p-2">
                {{ item.transaksidetail_jumlah_barang }} pcs
              </td>
              <td class="p-2">
                Rp {{ formatNumber(item.transaksidetail_harga_barang) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<style scoped>
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

.bg-gray-800 {
  background-color: rgba(0, 0, 0, 0.5);
}
</style>

<script setup>
import { ref, watchEffect } from "vue";
import axios from "axios";
import { useRuntimeConfig } from "#imports";

const props = defineProps({
  show: Boolean,
  id: [Number, String],
});

const emit = defineEmits(["close"]);

const detailBarang = ref([]);
const loading = ref(false);
const url = useRuntimeConfig().public.apiBase;

const fetchDetail = async () => {
  if (!props.id) return;

  loading.value = true;
  detailBarang.value = [];

  try {
    console.log('asdsa', props.id);

    const transaksi = await axios.get(`${url}/api/transaksi/${props.id}`);
    const detailTransaksi = transaksi.data.data.details;

    const detailPromises = detailTransaksi.map((detail) =>
      axios.get(`${url}/api/transaksi-detail/${detail.transaksidetail_id}`)
    );
    const detailResults = await Promise.all(detailPromises);

    const entryBarangPromises = detailResults.map((res) =>
      axios.get(
        `${url}/api/entrybarang/${res.data.data.transaksidetail_barang_id}`
      )
    );
    const entryBarangResults = await Promise.all(entryBarangPromises);

    const codeBarangPromises = entryBarangResults.map((res) =>
      axios.get(`${url}/api/codebarang/${res.data.data.barangentry_code_id}`)
    );
    const codeBarangResults = await Promise.all(codeBarangPromises);

    detailBarang.value = detailResults.map((res, i) => {
      return {
        ...res.data.data,
        kode_barang: codeBarangResults[i].data.code_nama,
        nama_barang: entryBarangResults[i].data.data.barangentry_nama
      };
    });
  } catch (error) {
    console.error("Gagal mengambil detail transaksi:", error);
  } finally {
    loading.value = false;
  }
};

const formatNumber = (value) => {
  if (!value) return "0";
  return Number(value).toLocaleString("id-ID");
};

watchEffect(() => {
  if (props.show && props.id) {
    fetchDetail();
  }
});
</script>
