<template>
    <div class="sd-page">

        <title>Setting - Sumber Dana</title>

        <h1 class="sd-title">
            Sumber Dana
        </h1>

        <div class="sd-toolbar">
            <div class="sd-search-wrap">
                <MagnifyingGlassIcon class="sd-search-icon" />
                <input v-model="search" type="text" placeholder="Cari sumber dana" class="sd-search-input" />
            </div>
            <button class="sd-btn-tambah" @click="openModal()">
                Tambah
            </button>
        </div>

        <div class="sd-table-wrap">
            <table class="sd-table">
                <thead>
                    <tr>
                        <th class="sd-th sd-th-no">No</th>
                        <th class="sd-th">Nama Sumber Dana</th>
                        <th class="sd-th">Terakhir Diperbaharui</th>
                        <th class="sd-th sd-th-status">Status</th>
                        <th class="sd-th sd-th-aksi">Aksi</th>
                    </tr>
                </thead>

                <tbody v-if="paginatedData.length > 0">
                    <tr v-for="(item, index) in paginatedData" :key="item.sumber_dana_id" class="sd-tr">
                        <td class="sd-td sd-td-no">
                            {{
                                (currentPage - 1) * itemsPerPage
                                + index
                                + 1
                            }}
                        </td>

                        <td class="sd-td">
                            {{ item.sumber_dana_nama }}
                        </td>

                        <td class="sd-td">
                            {{ formatDate(getDisplayDate(item)) }}
                        </td>

                        <td class="sd-td sd-td-status">
                            <span :class="[
                                'sd-chip',
                                statusChipClass(item.sumber_dana_status)
                            ]">
                                {{ statusLabel(item.sumber_dana_status) }}
                            </span>
                        </td>

                        <td class="sd-td sd-td-aksi">
                            <button @click="openModal(item)" class="sd-btn-icon">
                                <PencilSquareIcon class="sd-icon" />
                            </button>

                            <button @click="confirmDelete(item)" class="sd-btn-icon sd-btn-delete">
                                <TrashIcon class="sd-icon delete" />
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="filteredData.length === 0" class="sd-empty">
                <p class="sd-empty-text">
                    Data Tidak Ditemukan
                </p>
            </div>

        </div>

        <div v-if="filteredData.length > 0" class="sd-pagination">
            <div class="sd-pagination-left">
                <span>
                    Menampilkan
                    {{ paginatedData.length }}
                    dari
                    {{ totalItems }}
                    data
                </span>
                <select v-model="itemsPerPage" class="sd-per-page" @change="currentPage = 1">
                    <option :value="5">5</option>
                    <option :value="10">10</option>
                    <option :value="25">25</option>
                    <option :value="50">50</option>
                </select>
            </div>

            <div class="sd-pagination-controls">

                <button class="sd-page-btn" :disabled="currentPage === 1" @click="currentPage--">
                    Prev
                </button>

                <button v-for="page in paginatedPages" :key="page" class="sd-page-btn" :class="{
                    'sd-page-active': currentPage === page,
                    'sd-page-ellipsis': page === '...'
                }" :disabled="page === '...'" @click="typeof page === 'number' && (currentPage = page)">
                    {{ page }}
                </button>

                <button class="sd-page-btn" :disabled="currentPage === totalPages" @click="currentPage++">
                    Next
                </button>

            </div>

        </div>

        <!-- MODAL -->
        <div v-if="isModalOpen" class="sd-modal-overlay" @click.self="closeModal">

            <div class="sd-modal">

                <div class="sd-modal-header">

                    <h2 class="sd-modal-title">
                        {{
                            form.sumber_dana_id
                                ? "Edit Sumber Dana"
                                : "Tambah Sumber Dana"
                        }}
                    </h2>

                    <button class="sd-modal-close" @click="closeModal">
                        ✕
                    </button>

                </div>

                <div class="sd-modal-body">

                    <label class="sd-label">

                        Nama Sumber Dana

                        <span class="sd-required">
                            *
                        </span>

                    </label>

                    <input v-model="form.sumber_dana_nama" type="text" placeholder="Masukkan nama sumber dana"
                        class="sd-input" />

                    <label class="sd-label sd-label-status">
                        Status
                    </label>

                    <div class="sd-toggle-box">

                        <div class="sd-toggle-info">

                            <span class="sd-toggle-title">
                                Status sumber dana
                            </span>

                            <span class="sd-toggle-desc">

                                {{
                                    form.sumber_dana_status == 1
                                        ? "Akan dapat digunakan dalam transaksi"
                                        : "Tidak dapat digunakan dalam transaksi"
                                }}

                            </span>

                        </div>

                        <label class="sd-switch">

                            <input type="checkbox" :checked="form.sumber_dana_status == 1"
                                @change="form.sumber_dana_status = $event.target.checked ? 1 : 0" />

                            <span class="sd-slider"></span>

                        </label>

                    </div>

                </div>

                <div class="sd-modal-footer">

                    <button class="sd-btn-batal" @click="closeModal">
                        Batal
                    </button>

                    <button class="sd-btn-simpan" :disabled="!form.sumber_dana_nama.trim() || isLoading"
                        @click="handleSave">

                        {{
                            isLoading
                                ? "Loading..."
                                : form.sumber_dana_id
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
import "@/assets/css/sumber-dana-setting.css";

import Swal from "sweetalert2";

import {
    MagnifyingGlassIcon,
    PencilSquareIcon,
    TrashIcon,
} from "@heroicons/vue/24/outline";

import { useSumberDana } from "@/composables/useSumberDana";

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
} = useSumberDana();

await fetchData();

async function handleSave() {

    const success = await saveData();

    if (!success) return;

    Swal.fire({
        icon: "success",
        title: form.value.sumber_dana_id
            ? "Sumber Dana berhasil diupdate"
            : "Sumber Dana berhasil ditambahkan",
        timer: 1500,
        showConfirmButton: false,
    });
}

async function confirmDelete(item) {
    const { default: Swal } = await import("sweetalert2");
    const result = await Swal.fire({
        title: "Hapus Sumber Dana?",
        text: `"${item.sumber_dana_nama}" akan dihapus.`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#aaa",
        confirmButtonText: "Ya, hapus",
        cancelButtonText: "Batal",
    });
    if (result.isConfirmed) {
        await deleteItem(item);
        Swal.fire({ title: "Sumber Dana Berhasil Dihapus", icon: "success", timer: 1500, showConfirmButton: true });
    }
}
</script>