<template>
  <div class="pm-page">
    <title>Setting - Metode Pembayaran</title>
    <h1 class="pm-title">Metode Pembayaran</h1>

    <div class="pm-toolbar">
      <div class="pm-search-wrap">
        <MagnifyingGlassIcon class="pm-search-icon" />
        <input v-model="search" type="text" placeholder="Cara metode pembayaran" class="pm-search-input" />
      </div>
      <button class="pm-btn-tambah" @click="openModal()">Tambah</button>
    </div>

    <div class="pm-table-wrap">
      <table class="pm-table">
        <thead>
          <tr>
            <th class="pm-th pm-th-no">#</th>
            <th class="pm-th">Nama Metode Pembayaran</th>
            <th class="pm-th">Terakhir Diperbaharui</th>
            <th class="pm-th pm-th-status">Status</th>
            <th class="pm-th pm-th-aksi">Aksi</th>
          </tr>
        </thead>
        <tbody v-if="paginatedData.length > 0">
          <tr v-for="(item, index) in paginatedData" :key="item.id" class="pm-tr">
            <td class="pm-td pm-td-no">{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
            <td class="pm-td">{{ item.carabayar_nama }}</td>
            <td class="pm-td">{{ formatDate(getDisplayDate(item)) }}</td>
            <td class="pm-td pm-td-status">
              <span :class="['pm-chip', statusChipClass(item.carabayar_status)]">
                {{ statusLabel(item.carabayar_status) }}
              </span>
            </td>
            <td class="pm-td pm-td-aksi">
              <button @click="openModal(item)" class="pm-btn-icon" title="Edit">
                <PencilSquareIcon class="pm-icon" />
              </button>
              <button @click="confirmDelete(item)" class="pm-btn-icon pm-btn-delete" title="Hapus">
                <TrashIcon class="pm-icon delete" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="filteredData.length === 0" class="pm-empty">
        <!-- <img src="/icons/empty-state.svg" alt="empty" class="pm-empty-icon" onerror="this.style.display='none'" /> -->
        <p class="pm-empty-text">Data Tidak Ditemukan</p>
      </div>
    </div>

    <div class="pm-pagination">
      <div class="pm-pagination-left">
        <span>
          Menampilkan {{ startItem }} sampai {{ endItem }} dari {{ totalItems }}
        </span>

        <span class="pm-pagination-sep">|</span>

        <span>Tampilkan</span>

        <select v-model="itemsPerPage" class="pm-per-page" @change="currentPage = 1">
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>

        <span>data</span>
      </div>

      <div class="pm-pagination-controls">
        <button class="pm-page-btn" :disabled="currentPage === 1" @click="currentPage = 1">
          &#xAB;
        </button>

        <button class="pm-page-btn" :disabled="currentPage === 1" @click="currentPage--">
          &#x3C;
        </button>

        <template v-for="(page, index) in paginatedPages" :key="index">
          <button v-if="page !== '...'" class="pm-page-btn" :class="{ 'pm-page-active': currentPage === page }"
            @click="currentPage = page">
            {{ page }}
          </button>

          <span v-else class="pm-page-btn pm-page-ellipsis">
            ...
          </span>
        </template>

        <button class="pm-page-btn" :disabled="currentPage === totalPages" @click="currentPage++">
          &#x3E;
        </button>

        <button class="pm-page-btn" :disabled="currentPage === totalPages" @click="currentPage = totalPages">
          &#xBB;
        </button>
      </div>
    </div>

    <!-- Modal Tambah / Edit -->
    <div v-if="isModalOpen" class="pm-modal-overlay" @click.self="closeModal">
      <div class="pm-modal">
        <div class="pm-modal-header">
          <h2 class="pm-modal-title">
            {{ isEditMode ? "Edit Metode Pembayaran" : "Tambah Metode Pembayaran" }}
          </h2>
          <button class="pm-modal-close" @click="closeModal">&#x2715;</button>
        </div>

        <div class="pm-modal-body">
          <label class="pm-label">
            Nama Metode Pembayaran <span class="pm-required">*</span>
          </label>
          <input v-model="form.carabayar_nama" type="text" placeholder="Masukkan nama metode pembayaran"
            class="pm-input" />

          <label class="pm-label pm-label-status">Status</label>
          <div class="pm-toggle-box">
            <div class="pm-toggle-info">
              <span class="pm-toggle-title">Status metode pembayaran</span>
              <span class="pm-toggle-desc">
                {{ form.carabayar_status == 1 ? "Akan dapat digunakan dalam transaksi" : "Tidak dapat digunakan dalam transaksi" }}
              </span>
            </div>
            <label class="pm-switch">
              <input type="checkbox" :checked="form.carabayar_status == 1"
                @change="form.carabayar_status = $event.target.checked ? 1 : 0" />
              <span class="pm-slider"></span>
            </label>
          </div>
        </div>

        <div class="pm-modal-footer">
          <button class="pm-btn-batal" @click="closeModal">Batal</button>
          <button class="pm-btn-simpan" :disabled="!form.carabayar_nama.trim() || isLoading" @click="handleSave">
            {{ isEditMode ? "Simpan" : "Tambah" }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import "@/assets/css/payment-method.css";
import { onMounted } from "vue";
import { MagnifyingGlassIcon, PencilSquareIcon, TrashIcon } from "@heroicons/vue/24/outline";
import { usePaymentMethod } from "@/composables/usePaymentMethod";

const {
  search,
  currentPage,
  itemsPerPage,
  isModalOpen,
  isEditMode,
  isLoading,
  form,
  filteredData,
  paginatedData,
  totalItems,
  totalPages,
  paginatedPages,
  fetchData,
  openModal,
  closeModal,
  saveData,
  deleteItem,
  formatDate,
  statusChipClass,
  statusLabel,
  getDisplayDate,
  startItem,
  endItem,
} = usePaymentMethod();

async function handleSave() {
  const { default: Swal } = await import("sweetalert2");
  const isEdit = isEditMode.value;
  const success = await saveData();
  if (success) {
    Swal.fire({
      title: isEdit ? "Metode Pembayaran Berhasil Diupdate" : "Metode Pembayaran Berhasil Ditambahkan",
      icon: "success",
      timer: 1500,
      showConfirmButton: true,
    });
  } else {
    Swal.fire({
      title: "Gagal",
      text: isEdit ? "Gagal mengupdate metode pembayaran." : "Gagal menambahkan metode pembayaran.",
      icon: "error",
      confirmButtonText: "OK",
    });
  }
}

async function confirmDelete(item) {
  const { default: Swal } = await import("sweetalert2");
  const result = await Swal.fire({
    title: "Hapus Metode Pembayaran?",
    text: `"${item.carabayar_nama}" akan dihapus.`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#aaa",
    confirmButtonText: "Ya, hapus",
    cancelButtonText: "Batal",
  });
  if (result.isConfirmed) {
    await deleteItem(item);
    Swal.fire({ title: "Metode Pembayaran Berhasil Dihapus", icon: "success", timer: 1500, showConfirmButton: true });
  }
}

onMounted(fetchData);
</script>
