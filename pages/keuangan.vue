<template>
    <div>
        <title>Keuangan</title>

        <div class="page-header">
            <h1 class="page-title">
                Keuangan
            </h1>
        </div>

        <div class="keuangan-toolbar">
            <div class="keuangan-search-box">
                <input v-model="search" type="text" placeholder="Cari pengeluaran..." class="keuangan-search-input" />
                <MagnifyingGlassIcon class="keuangan-search-icon" />
            </div>
            <div class="header-action">
                <button class="keuangan-btn-export">
                    Export
                </button>
                <button class="keuangan-btn-add" @click="openModal()">
                    + Tambah
                </button>
            </div>
        </div>

        <div class="table-wrapper">
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
                            <div class="action-wrapper">
                                <button class="action-btn" @click="openModal(item)">
                                    <PencilSquareIcon class="keuangan-icon" />
                                </button>
                                <button class="action-btn delete" @click="deleteData(item)">
                                    <TrashIcon class="keuangan-icon" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="financeData.length === 0">
                        <td colspan="8" class="empty-table">
                            Data Tidak Ditemukan
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="isModalOpen" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>
                        {{ form.pengeluaran_id ? "Edit" : "Tambah" }} Pengeluaran
                    </h2>
                    <button @click="closeModal">
                        ✕
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tanggal Transaksi <span class="pm-required">*</span></label>
                        <input type="date" v-model="form.tanggal" />
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
                <div class="modal-footer">
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
import { MagnifyingGlassIcon, PencilSquareIcon, TrashIcon } from "@heroicons/vue/24/outline";
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
} = useKeuangan();
</script>