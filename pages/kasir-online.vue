<template>
  <div class="max-w-6xl mx-auto p-8 bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-semibold mb-6">Tambah Online Transaksi</h2>

    <form
      class="grid grid-cols-1 md:grid-cols-2 gap-6"
    >
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Kode Barang *</label
          >
          <input
            ref="kodeBarangInput"
            v-model="form.code_barang"
            @keyup.enter="handleKodeBarangEnter"
            type="text"
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 autofocus"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Platform *</label
          >
          <input
            ref="platformInput"
            v-model="form.platform"
            :disabled="!form.code_barang"
            type="text"
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Nama Penerima *</label
          >
          <input
            v-model="form.namaPenerima"
            :disabled="!form.code_barang"
            type="text"
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Pengiriman *</label
          >
          <input
            v-model="form.pengiriman"
            :disabled="!form.code_barang"
            type="text"
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Metode Pembayaran *</label
          >
          <select
            v-model="form.metodePembayaran"
            :disabled="!form.code_barang"
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
          >
            <option disabled value="">Pilih metode pembayaran</option>
            <option>Transfer Bank</option>
            <option>COD</option>
            <option>QRIS</option>
            <option>Virtual Account</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Alamat *</label
          >
          <textarea
            v-model="form.alamat"
            :disabled="!form.code_barang"
            rows="3"
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
          ></textarea>
        </div>
      </div>

      <!-- Kolom Kanan -->
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Nama Akun *</label
          >
          <input
            v-model="form.namaAkun"
            :disabled="!form.code_barang"
            type="text"
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Harga Terjual *</label
          >
          <div class="relative">
            <span class="absolute left-3 top-2.5 text-gray-500">Rp</span>
            <input
              v-model="form.hargaTerjual"
              :disabled="!form.code_barang"
              type="number"
              class="w-full border border-gray-300 rounded-md px-10 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Nama Telepon *</label
          >
          <input
            v-model="form.nomor_telepon"
            :disabled="!form.code_barang"
            type="text"
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Biaya Pengiriman *</label
          >
          <div class="relative">
            <span class="absolute left-3 top-2.5 text-gray-500">Rp</span>
            <input
              v-model="form.biayaPengiriman"
              :disabled="!form.code_barang"
              type="number"
              class="w-full border border-gray-300 rounded-md px-10 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Catatan *</label
          >
          <textarea
            v-model="form.catatan"
            :disabled="!form.code_barang"
            rows="3"
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:bg-gray-100"
          ></textarea>
        </div>
      </div>

      <!-- Tombol -->
      <div class="md:col-span-2 flex justify-end gap-4 mt-8">
        <button
          type="button"
          class="bg-gray-300 text-gray-800 px-6 py-2 rounded-md hover:bg-gray-400 transition"
        >
          Batal
        </button>
        <button
          type="button"
          @click="submitForm"
          :disabled="!form.code_barang"
          class="bg-cyan-600 text-white px-6 py-2 rounded-md hover:bg-cyan-700 transition disabled:bg-gray-400"
        >
          Tambah
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from "vue";
import axios from "axios";
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";

const kodeBarangInput = ref(null);
const url = ref("");

onMounted(() => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;
  kodeBarangInput.value?.focus();
});

const form = reactive({
  code_barang: "",
  platform: "",
  namaPenerima: "",
  pengiriman: "",
  metodePembayaran: "",
  alamat: "",
  namaAkun: "",
  hargaTerjual: "",
  nomor_telepon: "",
  biayaPengiriman: "",
  catatan: "",
});

const platformInput = ref(null);

function handleKodeBarangEnter() {
  platformInput.value?.focus();
}

async function submitForm() {
  if (!form.code_barang) {
    return;
  }
  const payloadTransaksi = {
    transaksi_nama_customer: form.namaPenerima,
    transaksi_nomor_telepon: form.nomor_telepon,
    transaksi_jumlah_barang: 1,
    transaksi_total_harga: form.hargaTerjual,
    transaksi_cara_bayar: form.metodePembayaran,
    transaksi_tipe: "Online",
    transaksi_status: "Pending",
    transaksi_catatan: form.catatan,
  };

  try {
    const responseData = await axios.get(
      `${url.value}/api/entrybarang/getDataKasir/`+form.code_barang
    );
    const dataEntry = responseData.data.data[0];

    const transaksiData = await axios.post(
      `${url.value}/api/transaksi`,
      payloadTransaksi
    );
    const transaksi_id = transaksiData.data.data.transaksi_id;
    try{
      const payloadTransaksiDetail = {
        transaksidetail_transaksi_id: transaksi_id,
        transaksidetail_barang_id: dataEntry.barangentry_id,
        transaksidetail_jumlah_barang: 1,
        transaksidetail_harga_barang: dataEntry.barangentry_harga_net,
      };
      
      await axios.post(
        `${url.value}/api/transaksi-detail`,
        payloadTransaksiDetail
      );
  
      const pengirimanPayload = {
        pengirimanBarang_transaksi_id: transaksi_id,
        pengirimanBarang_nama_penerima: form.namaPenerima,
        pengirimanBarang_akun_penerima: form.namaAkun,
        pengirimanBarang_no_telepon: form.nomor_telepon,
        pengirimanBarang_harga_kirim_barang: form.biayaPengiriman,
        pengirimanBarang_jenis_pengiriman_barang: form.pengiriman,
        pengirimanBarang_alamat_pengiriman_barang: form.alamat,
        pengirimanBarang_catatan: form.catatan,
        pengirimanBarang_status: "Proses",
      };
  
      await axios.post(`${url.value}/api/pengiriman-barang`, pengirimanPayload);

      Swal.fire({
        title: "Sukses!",
        text: "Berhasil Melakukan Online Transaksi",
        icon: "success",
        confirmButtonText: "OK",
      });
    }catch(err){
      console.error('Error melakukan transaksi', err)
    }

    form.namaPenerima = "";
    form.nomor_telepon = "";
    form.platform = "";
    form.pengiriman = "";
    form.metodePembayaran = "";
    form.alamat = "";
    form.namaAkun = "";
    form.hargaTerjual = "";
    form.namaAkun = "";
    form.biayaPengiriman = "";
    form.catatan = "";
  } catch (err) {
    console.error("Error", err);
  }
}
</script>
