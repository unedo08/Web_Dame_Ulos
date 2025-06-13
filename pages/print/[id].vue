<template>
  <div class="print-area">
    <div class="text-center mb-4">
      <img
        src="/image/DameUlosLogo2.png"
        alt="Logo"
        class="w-24 h-auto mx-auto mb-2"
      />
      <h2 class="font-bold text-base">Dame Ulos Tarutung</h2>
    </div>

    <h2 class="font-bold text-center mb-2">Struk Transaksi</h2>
    <p class="text-center text-sm mb-4">Terima kasih telah berbelanja!</p>

    <div class="mb-4 text-sm">
      <p>
        <strong>Nama Customer:</strong>
        {{ data.transaksi_nama_customer || "-" }}
      </p>
      <p>
        <strong>No Telepon:</strong> {{ data.transaksi_nomor_telepon || "-" }}
      </p>
      <p>
        <strong>Metode Pembayaran:</strong>
        {{ data.transaksi_cara_bayar || "-" }}
      </p>
      <p><strong>Jumlah Barang:</strong> {{ jumlahBarang }}</p>
      <p>
        <strong>Total:</strong>
        {{ formatRupiah(data.transaksi_total_harga) }}
      </p>
      <p><strong>Waktu:</strong> {{ formatTanggal(data.created_at) }}</p>
    </div>

    <div class="my-4">
      <h3 class="font-semibold mb-2">Detail Barang:</h3>
      <table class="table">
        <thead>
          <tr>
            <th>Nama</th>
            <th class="text-left">Qty</th>
            <th class="text-left">Harga</th>
            <th class="text-left">Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.transaksidetail_id">
            <td>{{ item.barangentry_nama }}</td>
            <td class="text-left">{{ item.transaksidetail_jumlah_barang }}</td>
            <td class="text-left">
              {{ formatRupiah(item.transaksidetail_harga_barang) }}
            </td>
            <td class="text-left">
              {{
                formatRupiah(
                  item.transaksidetail_harga_barang *
                    item.transaksidetail_jumlah_barang
                )
              }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div
      style="
        display: flex;
        justify-content: space-between;
        font-weight: 700;
        font-size: 1.1rem;
        margin-top: 20px;
      "
    >
      <div>Jumlah Barang: {{ jumlahBarang }}</div>
      <div>Subtotal: {{ formatRupiah(data.transaksi_total_harga) }}</div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRoute } from "vue-router";
import axios from "axios";
import { useRuntimeConfig } from "#imports";

const route = useRoute();
const id = route.params.id;

const data = ref({});
const items = ref([]);
const url = ref("");

onMounted(async () => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;
  const res = await axios.get(`${url.value}/api/transaksi/${id}`);
  data.value = res.data.data;
  items.value = await Promise.all(
    data.value.details.map(async (detail) => {
      const barangRes = await axios.get(
        `${url.value}/api/entrybarang/${detail.transaksidetail_barang_id}`
      );
      return {
        ...detail,
        barangentry_nama:
          barangRes.data.data.barangentry_nama || "Tidak Diketahui",
      };
    })
  );

  if (data.value && items.value.length) {
    setTimeout(() => {
      window.print();
    }, 500);
  }
});

const jumlahBarang = computed(() =>
  items.value.reduce((acc, item) => acc + item.transaksidetail_jumlah_barang, 0)
);

const formatRupiah = (angka) =>
  (Number(angka) || 0).toLocaleString("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  });

const formatTanggal = (dateStr) => {
  if (!dateStr) return "-";
  const date = new Date(dateStr);
  return date.toLocaleString("id-ID", {
    day: "2-digit",
    month: "long",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

definePageMeta({
  layout: false,
});
</script>

<style>
body {
  font-family: Arial, sans-serif;
  margin: 0;
  padding: 20px;
  background: #fff;
  color: #000;
}

.print-area {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
}

.text-center {
  text-align: center;
}

.table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.table th,
.table td {
  padding: 8px 6px;
  border-bottom: 1px solid #ccc;
}

.table th {
  font-weight: 600;
  border-bottom: 2px solid #444;
}

.table tbody tr:last-child td {
  border-bottom: 2px solid #000;
}

@media print {
  body {
    margin: 0;
    padding: 0;
  }

  .print-area {
    width: 100%;
    max-width: none;
    margin: 0;
    padding: 0;
    box-shadow: none;
  }
}
</style>
