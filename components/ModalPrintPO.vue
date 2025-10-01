<template>
  <div
    class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center print:bg-white print:relative"
  >
    <div
      class="bg-white p-6 rounded-lg w-[600px] print:rounded-none print:w-full print:shadow-none print-area"
      ref="printSection"
    >

      <div class="text-center mb-4">
        <img src="../assets/image/DameUlosPO.png" alt="Logo" class="w-24 h-auto mx-auto mb-2" />
        <h2 class="text-xl font-bold">Dame Ulos Tarutung</h2>
      </div>

      <h2 class="text-xl font-bold text-center mb-2">Struk Transaksi</h2>
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
          <strong>Metode Pembayaran:</strong> {{ data.transaksi_cara_bayar }}
        </p>
        <p>
          <strong>Jumlah Barang:</strong> {{ data.transaksi_jumlah_barang }}
        </p>
        <p>
          <strong>Total:</strong> {{ formatRupiah(data.transaksi_total_harga) }}
        </p>
        <p><strong>Waktu:</strong> {{ formatTanggal(data.created_at) }}</p>
      </div>

      <div class="my-4">
        <h3 class="font-semibold mb-2">Detail Barang:</h3>
        <table class="w-full text-sm border-collapse">
          <thead>
            <tr class="border-b">
              <th class="text-left py-1">Nama</th>
              <th class="text-right py-1">Qty</th>
              <th class="text-right py-1">Harga</th>
              <th class="text-right py-1">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(item, idx) in items"
              :key="idx"
              class="border-b last:border-b-0"
            >
            <!-- <pre>{{ item }}</pre> -->
              <td class="py-1">{{ item.barangentry_nama }}</td>
              <td class="py-1 text-right">{{ item.transaksidetail_jumlah_barang }}</td>
              <td class="py-1 text-right">
                {{ formatRupiah(item.transaksidetail_harga_barang) }}
              </td>
              <td class="py-1 text-right">
                {{ formatRupiah(item.transaksidetail_jumlah_barang * item.transaksidetail_harga_barang) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Total Akhir -->
      <div class="text-right text-base font-semibold mt-4">
        Total Bayar: {{ formatRupiah(data.transaksi_total_harga) }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from "vue";

const props = defineProps({
  data: Object,
  items: Array,
});

function formatRupiah(number) {
  return (
    number?.toLocaleString("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }) || "Rp0"
  );
}

function formatTanggal(dateStr) {
  if (!dateStr) return "-";
  const date = new Date(dateStr);
  return date.toLocaleString("id-ID", {
    day: "2-digit",
    month: "long",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

onMounted(() => {
  // Tunggu render modal baru buka print
  setTimeout(() => {
    window.print();
  }, 300);
});

definePageMeta({
    layout: false
})
</script>

<style scoped>
@media print {
  body > * {
    display: none !important;
  }

  .fixed.inset-0 {
    display: none !important;
  }

  .print-area {
    display: block !important;
    position: static !important;
    width: 100% !important;
    background: white !important;
    padding: 0 !important;
    margin: 0 !important;
    box-shadow: none !important;
  }
}


</style>
