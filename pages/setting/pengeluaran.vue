<template>
    <div class="jp-page">

        <title>Setting - Jenis Pengeluaran</title>

        <h1 class="jp-title">
           Jenis Pengeluaran
        </h1>

        <div class="jp-toolbar">
            <div class="jp-search-wrap">
                <MagnifyingGlassIcon class="jp-search-icon" />
                <input v-model="search" type="text" placeholder="Cari pengeluaran" class="jp-search-input" />
            </div>
            <button class="jp-btn-tambah" @click="openModal()">
                Tambah
            </button>
        </div>

        <div class="jp-table-wrap">
            <table class="jp-table">
                <thead>
                    <tr>
                        <th class="jp-th jp-th-no">No</th>
                        <th class="jp-th">Nama Pengeluaran</th>
                        <th class="jp-th">Terakhir Diperbaharui</th>
                        <th class="jp-th jp-th-status">Status</th>
                        <th class="jp-th jp-th-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody v-if="paginatedData.length > 0">
                    <tr v-for="(item, index) in paginatedData" :key="item.jenis_pengeluaran_id" class="jp-tr">
                        <td class="jp-td jp-td-no">
                            {{
                                (currentPage - 1) * itemsPerPage
                                + index
                                + 1
                            }}
                        </td>
                        <td class="jp-td">
                            {{ item.jenis_pengeluaran_nama }}
                        </td>
                        <td class="jp-td">
                            {{ formatDate(getDisplayDate(item)) }}
                        </td>
                        <td class="jp-td jp-td-status">
                            <span :class="[
                                'jp-chip',
                                statusChipClass(item.jenis_pengeluaran_status)
                            ]">
                                {{
                                    statusLabel(item.jenis_pengeluaran_status)
                                }}
                            </span>
                        </td>
                        <td class="jp-td jp-td-aksi">
                            <button @click="openModal(item)" class="jp-btn-icon">
                                <PencilSquareIcon class="jp-icon" />
                            </button>

                            <button @click="confirmDelete(item)" class="jp-btn-icon jp-btn-delete">
                                <TrashIcon class="jp-icon delete" />
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="filteredData.length === 0" class="jp-empty">
                <p class="jp-empty-text">
                    Data Tidak Ditemukan
                </p>
            </div>
        </div>

        <div v-if="filteredData.length > 0" class="jp-pagination">
            <div class="jp-pagination-left">
                <span>
                    Menampilkan
                    {{ paginatedData.length }}
                    dari
                    {{ totalItems }}
                    data
                </span>

                <select v-model="itemsPerPage" class="jp-per-page">
                    <option :value="5">5</option>
                    <option :value="10">10</option>
                    <option :value="25">25</option>
                    <option :value="50">50</option>
                </select>
            </div>

            <div class="jp-pagination-controls">

                <button class="jp-page-btn" :disabled="currentPage === 1" @click="currentPage--">
                    Prev
                </button>

                <button v-for="page in paginatedPages" :key="page" class="jp-page-btn" :class="{
                    'jp-page-active': currentPage === page,
                    'jp-page-ellipsis': page === '...'
                }" :disabled="page === '...'" @click="typeof page === 'number' && (currentPage = page)">
                    {{ page }}
                </button>

                <button class="jp-page-btn" :disabled="currentPage === totalPages" @click="currentPage++">
                    Next
                </button>
            </div>
        </div>

        <div v-if="isModalOpen" class="jp-modal-overlay" @click.self="closeModal">
            <div class="jp-modal">
                <div class="jp-modal-header">
                    <h2 class="jp-modal-title">
                        {{
                            form.jenis_pengeluaran_id
                                ? "Edit Pengeluaran"
                                : "Tambah Jenis Pengeluaran"
                        }}

                    </h2>
                    <button class="jp-modal-close" @click="closeModal">
                        ✕
                    </button>
                </div>

                <div class="jp-modal-body">
                    <label class="jp-label">
                        Nama Pengeluaran
                        <span class="jp-required">
                            *
                        </span>
                    </label>

                    <input v-model="form.jenis_pengeluaran_nama" type="text" placeholder="Masukkan nama pengeluaran"
                        class="jp-input" />
                    <label class="jp-label jp-label-status">
                        Status
                    </label>
                    <div class="jp-toggle-box">
                        <div class="jp-toggle-info">
                            <span class="jp-toggle-title">
                                Status pengeluaran
                            </span>
                            <span class="jp-toggle-desc">
                                {{
                                    form.jenis_pengeluaran_status == 1
                                        ? "Akan dapat digunakan dalam transaksi"
                                        : "Tidak dapat digunakan dalam transaksi"
                                }}
                            </span>
                        </div>
                        <label class="jp-switch">
                            <input type="checkbox" :checked="form.jenis_pengeluaran_status == 1"
                                @change="form.jenis_pengeluaran_status = $event.target.checked ? 1 : 0" />
                            <span class="jp-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="jp-modal-footer">
                    <button class="jp-btn-batal" @click="closeModal">
                        Batal
                    </button>

                    <button class="jp-btn-simpan" :disabled="!form.jenis_pengeluaran_nama.trim() || isLoading"
                        @click="handleSave">

                        {{
                            isLoading
                                ? "Loading..."
                                : form.jenis_pengeluaran_id
                                    ? "Simpan"
                                    : "Tambah"
                        }}

                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import "@/assets/css/pengeluaran-setting.css";

import Swal from "sweetalert2";

import {
    MagnifyingGlassIcon,
    PencilSquareIcon,
    TrashIcon,
} from "@heroicons/vue/24/outline";

import { useJenisPengeluaran } from "@/composables/useJenisPengeluaran";

const {
    search,
    currentPage,
    itemsPerPage,
    isModalOpen,
    isLoading,
    form,
    filteredData,
    paginatedData,
    totalItems,
    totalPages,
    paginatedPages,
    openModal,
    closeModal,
    saveData,
    deleteItem,
    fetchData,
    formatDate,
    statusChipClass,
    statusLabel,
    getDisplayDate,
} = useJenisPengeluaran();

await fetchData();

async function handleSave() {

    const success = await saveData();

    if (!success) return;

    Swal.fire({
        icon: "success",

        title: form.value.jenis_pengeluaran_id
            ? "Jenis Pengeluaran berhasil diupdate"
            : "Jenis Pengeluaran berhasil ditambahkan",

        timer: 1500,

        showConfirmButton: false,
    });
}

async function confirmDelete(item) {
  const { default: Swal } = await import("sweetalert2");
  const result = await Swal.fire({
    title: "Hapus Jenis Pengeluaran?",
    text: `"${item.jenis_pengeluaran_nama}" akan dihapus.`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#aaa",
    confirmButtonText: "Ya, hapus",
    cancelButtonText: "Batal",
  });
  if (result.isConfirmed) {
    await deleteItem(item);
    Swal.fire({ title: "Jenis Pengeluaran Berhasil Dihapus", icon: "success", timer: 1500, showConfirmButton: true });
  }
}
</script>