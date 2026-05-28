<template>
    <div class="dv-page">

        <title>Setting - Divisi</title>

        <h1 class="dv-title">
            Divisi
        </h1>

        <div class="dv-toolbar">

            <div class="dv-search-wrap">

                <MagnifyingGlassIcon class="dv-search-icon" />

                <input v-model="search" type="text" placeholder="Cari divisi" class="dv-search-input" />

            </div>

            <button class="dv-btn-tambah" @click="openModal()">
                Tambah
            </button>

        </div>

        <div class="dv-table-wrap">

            <table class="dv-table">

                <thead>

                    <tr>

                        <th class="dv-th dv-th-no">
                            No
                        </th>

                        <th class="dv-th">
                            Nama Divisi
                        </th>

                        <th class="dv-th">
                            Terakhir Diperbaharui
                        </th>

                        <th class="dv-th dv-th-status">
                            Status
                        </th>

                        <th class="dv-th dv-th-aksi">
                            Aksi
                        </th>

                    </tr>

                </thead>

                <tbody v-if="paginatedData.length > 0">

                    <tr v-for="(item, index) in paginatedData" :key="item.divisi_id" class="dv-tr">

                        <td class="dv-td dv-td-no">
                            {{
                                (currentPage - 1) * itemsPerPage
                                + index
                                + 1
                            }}
                        </td>

                        <td class="dv-td">
                            {{ item.divisi_nama }}
                        </td>

                        <td class="dv-td">
                            {{ formatDate(getDisplayDate(item)) }}
                        </td>

                        <td class="dv-td dv-td-status">

                            <span :class="[
                                'dv-chip',
                                statusChipClass(item.divisi_status)
                            ]">
                                {{ statusLabel(item.divisi_status) }}
                            </span>

                        </td>

                        <td class="dv-td dv-td-aksi">

                            <button @click="openModal(item)" class="dv-btn-icon">
                                <PencilSquareIcon class="dv-icon" />
                            </button>

                            <button @click="confirmDelete(item)" class="dv-btn-icon dv-btn-delete">
                                <TrashIcon class="dv-icon delete" />
                            </button>

                        </td>

                    </tr>

                </tbody>

            </table>

            <div v-if="filteredData.length === 0" class="dv-empty">
                <p class="dv-empty-text">
                    Data Tidak Ditemukan
                </p>
            </div>

        </div>

        <div v-if="filteredData.length > 0" class="dv-pagination">

            <div class="dv-pagination-left">

                <span>
                    Menampilkan
                    {{ paginatedData.length }}
                    dari
                    {{ totalItems }}
                    data
                </span>

                <select v-model="itemsPerPage" class="jp-per-page" @change="currentPage = 1">
                    <option :value="5">5</option>
                    <option :value="10">10</option>
                    <option :value="25">25</option>
                    <option :value="50">50</option>
                </select>

            </div>

            <div class="dv-pagination-controls">

                <button class="dv-page-btn" :disabled="currentPage === 1" @click="currentPage--">
                    Prev
                </button>

                <button v-for="page in paginatedPages" :key="page" class="dv-page-btn" :class="{
                    'dv-page-active': currentPage === page,
                    'dv-page-ellipsis': page === '...'
                }" :disabled="page === '...'" @click="typeof page === 'number' && (currentPage = page)">
                    {{ page }}
                </button>

                <button class="dv-page-btn" :disabled="currentPage === totalPages" @click="currentPage++">
                    Next
                </button>

            </div>

        </div>

        <!-- MODAL -->
        <div v-if="isModalOpen" class="dv-modal-overlay" @click.self="closeModal">

            <div class="dv-modal">

                <div class="dv-modal-header">

                    <h2 class="dv-modal-title">
                        {{
                            form.divisi_id
                                ? "Edit Divisi"
                                : "Tambah Divisi"
                        }}
                    </h2>

                    <button class="dv-modal-close" @click="closeModal">
                        ✕
                    </button>

                </div>

                <div class="dv-modal-body">

                    <label class="dv-label">

                        Nama Divisi

                        <span class="dv-required">
                            *
                        </span>

                    </label>

                    <input v-model="form.divisi_nama" type="text" placeholder="Masukkan nama divisi" class="dv-input" />

                    <label class="dv-label dv-label-status">
                        Status
                    </label>

                    <div class="dv-toggle-box">

                        <div class="dv-toggle-info">

                            <span class="dv-toggle-title">
                                Status divisi
                            </span>

                            <span class="dv-toggle-desc">

                                {{
                                    form.divisi_status == 1
                                        ? "Akan dapat digunakan dalam transaksi"
                                        : "Tidak dapat digunakan dalam transaksi"
                                }}

                            </span>

                        </div>

                        <label class="dv-switch">

                            <input type="checkbox" :checked="form.divisi_status == 1"
                                @change="form.divisi_status = $event.target.checked ? 1 : 0" />

                            <span class="dv-slider"></span>

                        </label>

                    </div>

                </div>

                <div class="dv-modal-footer">

                    <button class="dv-btn-batal" @click="closeModal">
                        Batal
                    </button>

                    <button class="dv-btn-simpan" :disabled="!form.divisi_nama.trim() || isLoading" @click="handleSave">

                        {{
                            isLoading
                                ? "Loading..."
                                : form.divisi_id
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
import "@/assets/css/divisi-setting.css";

import Swal from "sweetalert2";

import {
    MagnifyingGlassIcon,
    PencilSquareIcon,
    TrashIcon,
} from "@heroicons/vue/24/outline";

import { useDivisi } from "@/composables/useDivisi";

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
} = useDivisi();

await fetchData();

async function handleSave() {

    const success = await saveData();

    if (!success) return;

    Swal.fire({
        icon: "success",
        title: form.value.divisi_id
            ? "Divisi berhasil diupdate"
            : "Divisi berhasil ditambahkan",
        timer: 1500,
        showConfirmButton: false,
    });
}

async function confirmDelete(item) {
    const { default: Swal } = await import("sweetalert2");
    const result = await Swal.fire({
        title: "Hapus Data Divisi?",
        text: `"${item.divisi_nama}" akan dihapus.`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#aaa",
        confirmButtonText: "Ya, hapus",
        cancelButtonText: "Batal",
    });
    if (result.isConfirmed) {
        await deleteItem(item);
        Swal.fire({ title: "Data Divisi Berhasil Dihapus", icon: "success", timer: 1500, showConfirmButton: true });
    }
}
</script>