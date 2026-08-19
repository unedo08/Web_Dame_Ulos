<template>
  <div v-if="show" class="packaging-modal-overlay">
    <div class="packaging-modal">
      <div class="packaging-modal-header">
        <h2>Form Packaging</h2>

        <button @click="$emit('close')" class="packaging-modal-close">
          ✕
        </button>
      </div>

      <div class="packaging-modal-body">
        <div class="packaging-modal-table-wrapper">
          <table class="packaging-modal-table">
            <thead>
              <tr>
                <th>Pilih</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Harga</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in barang" :key="index">
                <td><input type="checkbox" v-model="item.is_check" /></td>
                <td>{{ item.kode }}</td>
                <td>{{ item.nama }}</td>
                <td>{{ item.jumlah }}</td>
                <td class="price">{{ formatCurrency(item.harga) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="packaging-modal-footer">
        <button @click="closeModal" class="packaging-btn-cancel">
          Batal
        </button>
        <button @click="submitForm" class="packaging-btn-save">
          Simpan
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import Swal from "sweetalert2";
import { useRuntimeConfig } from "#imports";

const config = useRuntimeConfig();
const url = config.public.apiBase;
const { $api } = useNuxtApp();
const props = defineProps({
  show: Boolean,
  barang: Array,
  pengiriman: Object
});

const emit = defineEmits(["close", "save", "openPreview"]);

const submitForm = async () => {
  const barangTerpilih = props.barang.filter(item => item.is_check);
  if (barangTerpilih.length === 0) {
    Swal.fire("Gagal!", "Pilih minimal satu barang.", "warning");
    return;
  }

  try {
    for (const item of barangTerpilih) {
      await $api.post(`${url}/api/packaging`, {
        packaging_transactiondetail_id: item.trx_detail_id,
        packaging_nama_akun: props.pengiriman.pengirimanBarang_nama_penerima,
        packaging_alamat: props.pengiriman.pengirimanBarang_alamat_pengiriman_barang
      });
    }

    emit("save");

    emit("openPreview", {
      nama: props.pengiriman.pengirimanBarang_nama_penerima,
      telp: props.pengiriman.pengirimanBarang_no_telepon,
      alamat: props.pengiriman.pengirimanBarang_alamat_pengiriman_barang
    });

    emit("close");

  } catch (error) {
    console.error(error);
    Swal.fire("Error!", "Gagal menyimpan data", "error");
  }
};

function closeModal() {
  emit("close");
}

function formatCurrency(value) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(value);
}
</script>

<style scoped>
/* =========================================================
   MODAL OVERLAY
   ========================================================= */

.packaging-modal-overlay {
  position: fixed;
  inset: 0;

  z-index: 50;

  display: flex;
  align-items: center;
  justify-content: center;

  padding: 20px;
  box-sizing: border-box;

  background: rgba(0, 0, 0, 0.4);
}


/* =========================================================
   MODAL
   ========================================================= */

.packaging-modal {
  width: 800px;
  max-width: 100%;

  max-height: 90vh;

  background: white;

  border: 1px solid #d1d5db;
  border-radius: 10px;

  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);

  display: flex;
  flex-direction: column;

  overflow: hidden;
}


/* =========================================================
   HEADER
   ========================================================= */

.packaging-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;

  padding: 16px 24px;

  flex-shrink: 0;
}

.packaging-modal-header h2 {
  margin: 0;

  font-size: 18px;
  font-weight: 700;
}

.packaging-modal-close {
  border: none;
  background: transparent;

  color: #6b7280;

  font-size: 18px;

  cursor: pointer;

  padding: 4px 6px;
}

.packaging-modal-close:hover {
  color: #374151;
}


/* =========================================================
   BODY
   ========================================================= */

.packaging-modal-body {
  padding: 0 24px 20px;

  overflow-y: auto;

  min-height: 0;
}


/* =========================================================
   TABLE WRAPPER
   ========================================================= */

.packaging-modal-table-wrapper {
  width: 100%;
  max-width: 100%;
  min-width: 0;

  overflow-x: auto;
  overflow-y: hidden;

  -webkit-overflow-scrolling: touch;

  scrollbar-width: thin;
}


/* =========================================================
   TABLE
   ========================================================= */

.packaging-modal-table {
  width: 100%;
  min-width: 650px;

  border-collapse: collapse;

  font-size: 13px;
}

.packaging-modal-table th,
.packaging-modal-table td {
  padding: 9px 12px;

  border: 1px solid #e5e7eb;

  white-space: nowrap;

  text-align: center;
}

.packaging-modal-table th {
  background: #f3f4f6;

  font-weight: 600;
}

.packaging-modal-table td.price {
  text-align: right;
}


/* Checkbox */

.packaging-modal-table input[type="checkbox"] {
  width: 16px;
  height: 16px;

  cursor: pointer;
}


/* =========================================================
   FOOTER
   ========================================================= */

.packaging-modal-footer {
  display: flex;
  justify-content: flex-end;

  gap: 8px;

  padding: 14px 24px;

  border-top: 1px solid #e5e7eb;

  flex-shrink: 0;
}


/* =========================================================
   BUTTON
   ========================================================= */

.packaging-btn-cancel,
.packaging-btn-save {
  height: 34px;

  padding: 0 14px;

  border-radius: 6px;

  font-size: 12px;

  cursor: pointer;
}

.packaging-btn-cancel {
  background: white;

  border: 1px solid #d1d5db;

  color: #374151;
}

.packaging-btn-cancel:hover {
  background: #f9fafb;
}

.packaging-btn-save {
  background: #3b82f6;

  border: 1px solid #3b82f6;

  color: white;
}

.packaging-btn-save:hover {
  background: #2563eb;
}


/* =========================================================
   TABLET
   ========================================================= */

@media (max-width: 1024px) {

  .packaging-modal-overlay {
    padding: 16px;
  }

  .packaging-modal {
    width: 760px;
  }
}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 767px) {

  .packaging-modal-overlay {
    padding: 10px;
  }

  .packaging-modal {
    width: 100%;
    max-width: none;

    max-height: calc(100vh - 20px);

    border-radius: 9px;
  }


  /* Header */
  .packaging-modal-header {
    padding: 13px 15px;
  }

  .packaging-modal-header h2 {
    font-size: 15px;
  }


  /* Body */
  .packaging-modal-body {
    padding: 0 15px 15px;
  }


  /* Table tetap lebar */
  .packaging-modal-table {
    width: 650px !important;
    min-width: 650px !important;
    max-width: none !important;
  }

  .packaging-modal-table th,
  .packaging-modal-table td {
    padding: 8px 10px;

    font-size: 11px;

    white-space: nowrap;
  }


  /* Footer */
  .packaging-modal-footer {
    padding: 11px 15px;

    gap: 7px;
  }

  .packaging-btn-cancel,
  .packaging-btn-save {
    height: 32px;

    padding: 0 12px;

    font-size: 11px;
  }
}


/* =========================================================
   SMALL PHONE
   ========================================================= */

@media (max-width: 480px) {

  .packaging-modal-overlay {
    padding: 8px;
  }

  .packaging-modal {
    max-height: calc(100vh - 16px);
  }


  .packaging-modal-header {
    padding: 11px 13px;
  }

  .packaging-modal-header h2 {
    font-size: 14px;
  }


  .packaging-modal-body {
    padding: 0 12px 12px;
  }


  .packaging-modal-table {
    width: 650px !important;
    min-width: 650px !important;
  }

  .packaging-modal-table th,
  .packaging-modal-table td {
    padding: 7px 9px;

    font-size: 10px;
  }


  .packaging-btn-cancel,
  .packaging-btn-save {
    height: 30px;

    padding: 0 11px;

    font-size: 10px;
  }
}
</style>