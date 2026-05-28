<template>
  <div class="pm-page">
    <title>Setting - Jenis Benang</title>
    <h1 class="pm-title">Jenis Benang</h1>

    <div class="pm-toolbar">
      <div class="pm-search-wrap">
        <MagnifyingGlassIcon class="pm-search-icon" />
        <input
          v-model="search"
          type="text"
          placeholder="Cari jenis benang"
          class="pm-search-input"
        />
      </div>
      <button class="pm-btn-tambah" @click="openModal()">Tambah</button>
    </div>

    <div class="pm-table-wrap">
      <table class="pm-table">
        <thead>
          <tr>
            <th class="pm-th pm-th-no">#</th>
            <th class="pm-th">Jenis Benang</th>
            <th class="pm-th">Terakhir Diperbaharui</th>
            <th class="pm-th pm-th-status">Status</th>
            <th class="pm-th pm-th-aksi">Aksi</th>
          </tr>
        </thead>
        <tbody v-if="paginatedData.length > 0">
          <tr v-for="(item, index) in paginatedData" :key="item.id" class="pm-tr">
            <td class="pm-td pm-td-no">{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
            <td class="pm-td">{{ item.jenisbenang_nama }}</td>
            <td class="pm-td">{{ formatDate(getDisplayDate(item)) }}</td>
            <td class="pm-td pm-td-status">
              <span :class="['pm-chip', statusChipClass(item.jenisbenang_status)]">
                {{ statusLabel(item.jenisbenang_status) }}
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
        <p class="pm-empty-text">Data Tidak Ditemukan</p>
      </div>
    </div>

    <div class="pm-pagination">
      <div class="pm-pagination-left">
        <span class="pm-pagination-info">
          Menampilkan {{ Math.min((currentPage - 1) * itemsPerPage + 1, totalItems) }}
          sampai {{ Math.min(currentPage * itemsPerPage, totalItems) }}
          dari {{ totalItems }}
        </span>
        <select v-model="itemsPerPage" class="pm-per-page" @change="currentPage = 1">
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
        </select>
      </div>
      <div class="pm-pagination-controls">
        <button class="pm-page-btn" :disabled="currentPage === 1" @click="currentPage = 1">&#171;</button>
        <button class="pm-page-btn" :disabled="currentPage === 1" @click="currentPage--">&#8249;</button>
        <button
          v-for="(page, i) in paginatedPages"
          :key="i"
          class="pm-page-btn"
          :class="{ 'pm-page-active': currentPage === page, 'pm-page-ellipsis': page === '...' }"
          :disabled="page === '...'"
          @click="typeof page === 'number' && (currentPage = page)"
        >
          {{ page }}
        </button>
        <button class="pm-page-btn" :disabled="currentPage === totalPages" @click="currentPage++">&#8250;</button>
        <button class="pm-page-btn" :disabled="currentPage === totalPages" @click="currentPage = totalPages">&#187;</button>
      </div>
    </div>

    <!-- Modal Tambah / Edit -->
    <div v-if="isModalOpen" class="pm-modal-overlay" @click.self="closeModal">
      <div class="pm-modal">
        <div class="pm-modal-header">
          <h2 class="pm-modal-title">
            {{ isEditMode ? "Edit Jenis Benang" : "Tambah Jenis Benang" }}
          </h2>
          <button class="pm-modal-close" @click="closeModal">&#x2715;</button>
        </div>

        <div class="pm-modal-body">
          <label class="pm-label">
            Jenis Benang <span class="pm-required">*</span>
          </label>
          <input
            v-model="form.jenisbenang_nama"
            type="text"
            placeholder="Masukkan jenis benang"
            class="pm-input"
          />

          <label class="pm-label pm-label-status">Status</label>
          <div class="pm-toggle-box">
            <div class="pm-toggle-info">
              <span class="pm-toggle-title">Status jenis benang</span>
              <span class="pm-toggle-desc">
                {{ form.jenisbenang_status == 1 ? "Akan dapat digunakan dalam transaksi" : "Tidak dapat digunakan dalam transaksi" }}
              </span>
            </div>
            <label class="pm-switch">
              <input
                type="checkbox"
                :checked="form.jenisbenang_status == 1"
                @change="form.jenisbenang_status = $event.target.checked ? 1 : 0"
              />
              <span class="pm-slider"></span>
            </label>
          </div>
        </div>

        <div class="pm-modal-footer">
          <button class="pm-btn-batal" @click="closeModal">Batal</button>
          <button
            class="pm-btn-simpan"
            :disabled="!form.jenisbenang_nama.trim() || isLoading"
            @click="handleSave"
          >
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
import { useJenisBenang } from "@/composables/useJenisBenang";

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
} = useJenisBenang();

async function handleSave() {
  const { default: Swal } = await import("sweetalert2");
  const isEdit = isEditMode.value;
  const success = await saveData();
  if (success) {
    Swal.fire({
      title: isEdit ? "Jenis Benang Berhasil Diupdate" : "Jenis Benang Berhasil Ditambahkan",
      icon: "success",
      timer: 1500,
      showConfirmButton: false,
    });
  } else {
    Swal.fire({
      title: "Gagal",
      text: isEdit ? "Gagal mengupdate jenis benang." : "Gagal menambahkan jenis benang.",
      icon: "error",
      confirmButtonText: "OK",
    });
  }
}

async function confirmDelete(item) {
  const { default: Swal } = await import("sweetalert2");
  const result = await Swal.fire({
    title: "Hapus Jenis Benang?",
    text: `"${item.jenisbenang_nama}" akan dihapus.`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#aaa",
    confirmButtonText: "Ya, hapus",
    cancelButtonText: "Batal",
  });
  if (result.isConfirmed) {
    await deleteItem(item);
    Swal.fire({ title: "Jenis Benang Berhasil Dihapus", icon: "success", timer: 1500, showConfirmButton: false });
  }
}

onMounted(fetchData);
</script>
