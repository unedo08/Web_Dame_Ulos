<template>
    <div>
        <title>Keuangan</title>

        <div class="page-header">
            <h1 class="page-title">
                Keuangan
            </h1>
        </div>

        <div class="page-table-toolbar keuangan-toolbar">
            <div class="keuangan-search-box">
                <input v-model="search" type="text" placeholder="Cari pengeluaran..." class="keuangan-search-input" />
                <MagnifyingGlassIcon class="keuangan-search-icon" />
            </div>
            <div class="header-action">
                <div class="keuangan-date-filters">
                    <input type="date" v-model="startDate" class="keuangan-date-input" />
                    <input type="date" v-model="endDate" class="keuangan-date-input" />
                </div>
                <button class="keuangan-btn-export" @click="exportExcel">
                    Export Excel
                </button>
                <button class="keuangan-btn-add" @click="openModal()">
                    + Tambah
                </button>
            </div>
        </div>

        <div class="table-wrapper" v-sticky-action-shadow>
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Pengeluaran</th>
                        <th>Jenis Pengeluaran</th>
                        <th>Divisi</th>
                        <th>Jumlah Pengeluaran</th>
                        <th>Sumber Dana</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="(item, index) in filteredFinanceData" :key="item.pengeluaran_id">
                        <td>{{ index + 1 }}</td>
                        <td>
                            {{ formatDate(item.pengeluaran_tanggal) }}
                        </td>
                        <td>{{ item.pengeluaran_nama }}</td>
                        <td>{{ item.jenis_pengeluaran_nama }}</td>
                        <td>{{ item.divisi_nama }}</td>
                        <td>
                            {{ formatRupiah(item.pengeluaran_jumlah) }}
                        </td>
                        <td>{{ item.sumber_dana_nama }}</td>
                        <td>
                            <div class="custom-table-aksi action-wrapper">
                                <button class="action-btn" title="Edit" @click="openModal(item)">
                                    <PencilIcon class="keuangan-icon" />
                                </button>
                                <button class="action-btn delete" title="Hapus" @click="deleteData(item)">
                                    <TrashIcon class="keuangan-icon delete" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="financeData.length === 0">
                        <td colspan="8" class="empty-table">
                            <EmptyState
                                :search-query="search"
                                title="Data keuangan tidak ditemukan"
                                message="Belum ada data keuangan."
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="isModalOpen" class="modal-overlay" @click.self="closeModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>
                        {{ form.pengeluaran_id ? "Edit" : "Tambah" }} Pengeluaran
                    </h2>
                    <button class="form-modal-close-size form-modal-close-inline" aria-label="Tutup form" @click="closeModal">
                        ✕
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tanggal Transaksi <span class="pm-required">*</span></label>
                        <div class="relative">
                            <input type="date" v-model="form.tanggal"
                                :class="{ 'date-placeholder-visible': !form.tanggal }" />
                            <span v-if="!form.tanggal"
                                class="pointer-events-none absolute inset-y-0 left-[10px] flex items-center text-gray-400"
                                aria-hidden="true">
                                Pilih tanggal
                            </span>
                        </div>
                        <small v-if="errors.tanggal">
                            Data wajib diisi
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Nama Pengeluaran <span class="pm-required">*</span></label>
                        <input type="text" v-model="form.nama_pengeluaran" placeholder="Masukkan nama pengeluaran" />
                        <small v-if="errors.nama_pengeluaran">
                            Data wajib diisi
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Jenis Pengeluaran <span class="pm-required">*</span></label>
                        <select v-model="form.jenis_pengeluaran">
                            <option value="">
                                Pilih Jenis Pengeluaran
                            </option>
                            <option v-for="item in jenisPengeluaran" :key="item.jenis_pengeluaran_id" :value="item.jenis_pengeluaran_id">
                                {{ item.jenis_pengeluaran_nama }}
                            </option>
                        </select>
                        <small v-if="errors.jenis_pengeluaran">
                            Data wajib diisi
                        </small>
                    </div>
                    <div class="form-group">
                        <label>Divisi <span class="pm-required">*</span></label>
                        <select v-model="form.divisi">
                            <option value="">
                                Pilih Divisi
                            </option>
                            <option v-for="item in divisiList" :key="item.divisi_id" :value="item.divisi_id">
                                {{ item.divisi_nama }}
                            </option>
                        </select>

                        <small v-if="errors.divisi">
                            Data wajib diisi
                        </small>
                    </div>
                    <div class="form-group">
                        <label>
                            Jumlah Pengeluaran
                            <span class="pm-required">*</span>
                        </label>
                        <div class="keuangan-rupiah-input">
                            <span class="keuangan-rupiah-prefix">
                                Rp
                            </span>
                            <input type="text" :value="formatInputRupiah(form.jumlah_pengeluaran)"
                                @input="handleJumlahInput" placeholder="Masukkan jumlah pengeluaran"
                                class="keuangan-rupiah-field" />
                        </div>
                        <small v-if="errors.jumlah_pengeluaran">
                            Data wajib diisi
                        </small>
                    </div>
                    <div class="form-group">
                        <label>Sumber Dana <span class="pm-required">*</span></label>
                        <select v-model="form.sumber_dana">
                            <option value="">
                                Pilih Sumber Dana
                            </option>
                            <option v-for="item in sumberDanaList" :key="item.sumber_dana_id" :value="item.sumber_dana_id">
                                {{ item.sumber_dana_nama }}
                            </option>
                        </select>
                        <small v-if="errors.sumber_dana">
                            Data wajib diisi
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Jenis Pembayaran <span class="pm-required">*</span></label>

                        <select v-model="form.metode_pembayaran">
                            <option value="">
                                Pilih Jenis Pembayaran
                            </option>

                            <option v-for="item in metodePembayaran" :key="item.id" :value="item.id">
                                {{ item.carabayar_nama }}
                            </option>
                        </select>

                        <small v-if="errors.metode_pembayaran">
                            Data wajib diisi
                        </small>
                    </div>
                </div>
                <div class="modal-footer form-add-actions">
                    <button class="btn-cancel" @click="closeModal">
                        Batal
                    </button>
                    <button class="btn-save" @click="saveData">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import "@/assets/css/keuangan.css";
import { MagnifyingGlassIcon, PencilIcon, TrashIcon } from "@heroicons/vue/24/outline";
import { useKeuangan } from "@/composables/useKeuangan";

const {
    financeData,
    isModalOpen,
    form,
    errors,
    jenisPengeluaran,
    divisiList,
    sumberDanaList,
    metodePembayaran,
    openModal,
    closeModal,
    saveData,
    deleteData,
    formatDate,
    formatRupiah,
    formatInputRupiah,
    handleJumlahInput,
    search,
    filteredFinanceData,
    startDate,
    endDate,
    exportExcel,
} = useKeuangan();
</script>