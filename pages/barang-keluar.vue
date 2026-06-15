<template>
    <div>
        <title>Barang Keluar</title>

        <div class="bk-page-header">
            <h1 class="bk-page-title">Barang Keluar</h1>
        </div>

        <!-- Toolbar -->
        <div class="bk-toolbar">
            <div class="bk-search-box">
                <MagnifyingGlassIcon class="bk-search-icon" />
                <input v-model="search" type="text" placeholder="Cari nama outsource..." class="bk-search-input"
                    @input="getData" />
            </div>
            <div class="bk-actions">
                <input type="date" v-model="startDate" class="bk-date-input" @change="getData" />

                <input type="date" v-model="endDate" class="bk-date-input" @change="getData" />

                <button class="bk-btn-export" @click="exportExcel">
                    Export Excel
                </button>

                <button class="bk-btn-add" @click="openTambah">
                    + Tambah
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="bk-table-wrapper">
            <table class="bk-table">
                <thead>
                    <tr>
                        <th>Tanggal Keluar</th>
                        <th>Petugas</th>
                        <th>Nama Outsource</th>
                        <th>Nama Ulos</th>
                        <th>Jumlah Barang</th>
                        <th>Tanggal Masuk</th>
                        <th>Author</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="isLoading">
                        <td colspan="8" style="text-align:center;padding:32px;color:#9ca3af">Memuat data...</td>
                    </tr>
                    <tr v-else-if="filteredData.length === 0" class="bk-empty-row">
                        <td colspan="8">Data tidak ditemukan</td>
                    </tr>
                    <tr v-else v-for="row in filteredData" :key="row.barang_keluar_id">
                        <td>{{ formatDate(row.created_at) }}</td>
                        <td>{{ row.creator?.name || "-" }}</td>
                        <td>{{ row.barang_keluar_nama_outsource }}</td>
                        <td>
                            <ul class="bk-items-list">
                                <li v-for="d in row.details" :key="d.barang_keluar_detail_id">
                                    {{ d.barang_keluar_detail_nama_ulos || "-" }}
                                </li>
                            </ul>
                        </td>
                        <td>
                            <ul class="bk-items-list">
                                <li v-for="d in row.details" :key="d.barang_keluar_detail_id">
                                    {{ d.barang_keluar_detail_jumlah }} pcs
                                </li>
                            </ul>
                        </td>
                        <td>{{ formatDate(row.barang_keluar_tanggal_masuk) }}</td>
                        <td>{{ row.completer?.name || "-" }}</td>
                        <td>
                            <div class="bk-action-group">
                                <!-- Edit: only for PENDING -->
                                <button v-if="row.barang_keluar_status === 'PENDING'" class="bk-btn-icon"
                                    title="Konfirmasi Pengambilan" @click="openEdit(row)">
                                    <PencilIcon class="bk-icon" />
                                </button>
                                <!-- View: only for SELESAI -->
                                <button v-if="row.barang_keluar_status === 'SELESAI'" class="bk-btn-icon"
                                    title="Lihat Detail" @click="openView(row)">
                                    <EyeIcon class="bk-icon bk-icon-view" />
                                </button>
                                <!-- Delete -->
                                <button class="bk-btn-icon" title="Hapus" @click="deleteRecord(row)">
                                    <TrashIcon class="bk-icon bk-icon-delete" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ===== Modal: Tambah Barang Keluar ===== -->
        <div v-if="isTambahOpen" class="bk-modal-overlay" @click.self="closeTambah">
            <div class="bk-modal bk-modal-lg">
                <div class="bk-modal-header">
                    <h2>Tambah Barang Keluar</h2>
                    <button class="bk-modal-close" @click="closeTambah">✕</button>
                </div>

                <div class="bk-modal-body">
                    <!-- Nama Outsource -->
                    <div class="bk-form-group">
                        <label>Nama Outsource <span class="bk-required">*</span></label>
                        <input v-model="tambahForm.nama_outsource" type="text" placeholder="Masukkan nama outsource" />
                        <small v-if="tambahErrors.nama_outsource">{{ tambahErrors.nama_outsource }}</small>
                    </div>

                    <!-- Item Ulos -->
                    <div class="bk-items-section">
                        <div class="bk-items-label">
                            Item Ulos <span class="bk-required">*</span>
                        </div>

                        <div v-if="tambahItems.length === 0" class="bk-empty-items">
                            Belum ada item ulos. Tambahkan diatas.
                        </div>
                        <small v-if="tambahErrors.items && tambahItems.length === 0"
                            style="color:#dc2626;font-size:11px">
                            {{ tambahErrors.items }}
                        </small>

                        <div v-for="(item, idx) in tambahItems" :key="item._key">
                            <div class="bk-item-row">
                                <!-- Kode Barang -->
                                <div class="bk-item-input-wrap">
                                    <input v-model="item.kode_barang" type="text" placeholder="Kode barang"
                                        :class="{ 'is-invalid': item.error }" @blur="validateCodeInput(idx)" />
                                    <small v-if="item.error">{{ item.error }}</small>
                                </div>

                                <!-- Jumlah -->
                                <div class="bk-item-input-wrap">
                                    <input v-model="item.jumlah" type="number" min="1" placeholder="Jumlah"
                                        :class="{ 'is-invalid': item.errorJumlah }" />
                                    <small v-if="item.errorJumlah">{{ item.errorJumlah }}</small>
                                </div>

                                <button v-if="tambahItems.length > 1" class="bk-btn-remove-item"
                                    @click="removeItemRow(idx)">
                                    <TrashIcon class="bk-icon bk-icon-delete" />
                                </button>
                            </div>

                            <!-- Nama Ulos resolved -->
                            <div v-if="item.nama_ulos"
                                style="font-size:12px;color:#059669;margin:-6px 0 8px;padding-left:2px">
                                ✓ {{ item.nama_ulos }}
                            </div>
                        </div>

                        <button class="bk-btn-add-item" @click="addItemRow">+ Add</button>
                    </div>
                </div>

                <div class="bk-modal-footer">
                    <button class="bk-btn-cancel" @click="closeTambah">Batal</button>
                    <button class="bk-btn-save" :disabled="isSubmitting" @click="submitTambah">
                        {{ isSubmitting ? "Menyimpan..." : "Simpan" }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ===== Modal: Konfirmasi Pengambilan Barang ===== -->
        <div v-if="isEditOpen" class="bk-modal-overlay" @click.self="closeEdit">
            <div class="bk-modal">
                <div class="bk-modal-header">
                    <h2>Konfirmasi Pengambilan Barang</h2>
                    <button class="bk-modal-close" @click="closeEdit">✕</button>
                </div>

                <div class="bk-modal-body">
                    <p style="font-size:13px;margin:0 0 12px;color:#374151">
                        Outsource: <strong>{{ editRecord?.barang_keluar_nama_outsource }}</strong>
                    </p>

                    <div v-for="d in editRecord?.details" :key="d.barang_keluar_detail_id" class="bk-check-row">
                        <input type="checkbox" :id="`chk-${d.barang_keluar_detail_id}`"
                            v-model="editChecked[d.barang_keluar_detail_id]" />
                        <label :for="`chk-${d.barang_keluar_detail_id}`" class="bk-check-label">
                            {{ d.barang_keluar_detail_nama_ulos || "-" }}
                            <small>{{ d.barang_keluar_detail_kode_barang }}</small>
                        </label>
                        <input type="number" min="1" :max="d.barang_keluar_detail_jumlah"
                            v-model.number="editQty[d.barang_keluar_detail_id]"
                            :disabled="!editChecked[d.barang_keluar_detail_id]" />
                    </div>

                    <p v-if="editError" class="bk-alert-required">{{ editError }}</p>
                </div>

                <div class="bk-modal-footer">
                    <button class="bk-btn-cancel" @click="closeEdit">Batal</button>
                    <button class="bk-btn-save" :disabled="isSavingEdit" @click="submitEdit">
                        {{ isSavingEdit ? "Menyimpan..." : "Selesai" }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ===== Modal: Detail Pengambilan Barang (View) ===== -->
        <div v-if="isViewOpen" class="bk-modal-overlay" @click.self="closeView">
            <div class="bk-modal">
                <div class="bk-modal-header">
                    <h2>Detail Pengambilan Barang</h2>
                    <button class="bk-modal-close" @click="closeView">✕</button>
                </div>

                <div class="bk-modal-body">
                    <div class="bk-view-info">
                        <p><strong>Outsource:</strong> {{ viewRecord?.barang_keluar_nama_outsource }}</p>
                        <p><strong>Tanggal Keluar:</strong> {{ formatDate(viewRecord?.created_at) }}</p>
                        <p><strong>Tanggal Masuk:</strong> {{ formatDate(viewRecord?.barang_keluar_tanggal_masuk) }}</p>
                        <p><strong>Author:</strong> {{ viewRecord?.completer?.name || "-" }}</p>
                    </div>

                    <div v-for="d in viewRecord?.details" :key="d.barang_keluar_detail_id" class="bk-view-item">
                        <div>
                            <div class="bk-view-item-name">{{ d.barang_keluar_detail_nama_ulos || "-" }}</div>
                            <div class="bk-view-item-meta">{{ d.barang_keluar_detail_kode_barang }}</div>
                        </div>
                        <div><strong>{{ d.barang_keluar_detail_jumlah }} pcs</strong></div>
                    </div>
                </div>

                <div class="bk-modal-footer">
                    <button class="bk-btn-save" @click="closeView">Tutup</button>
                </div>
            </div>
        </div>

        <!-- ===== Delete Confirmation Modal ===== -->
        <div v-if="isDeleteConfirmOpen" class="bk-modal-overlay">
            <div class="bk-confirm-card">
                <div class="bk-confirm-body">
                    <div class="bk-confirm-icon-wrap">
                        <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="bk-confirm-svg">
                            <circle cx="40" cy="40" r="34" stroke="#f87171" stroke-width="4" />
                            <line x1="40" y1="26" x2="40" y2="44" stroke="#f87171" stroke-width="4.5"
                                stroke-linecap="round" />
                            <circle cx="40" cy="53" r="2.5" fill="#f87171" />
                        </svg>
                    </div>
                    <div class="bk-confirm-title">Hapus Data Barang Keluar?</div>
                    <div class="bk-confirm-subtitle">
                        Data <strong>{{ pendingDeleteRecord?.barang_keluar_nama_outsource }}</strong> akan dihapus
                        secara permanen. Tindakan ini tidak dapat dibatalkan.
                    </div>
                    <div style="display:flex;gap:12px;justify-content:center">
                        <button class="bk-confirm-cancel-btn" @click="cancelDelete">Batal</button>
                        <button class="bk-confirm-delete-btn" @click="confirmDelete">Ya, Hapus</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== Confirm Popup (success / error) ===== -->
        <div v-if="isConfirmOpen" class="bk-modal-overlay">
            <div class="bk-confirm-card">
                <div class="bk-confirm-body">
                    <!-- Success icon -->
                    <div v-if="confirmType === 'success'" class="bk-confirm-icon-wrap">
                        <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="bk-confirm-svg">
                            <circle cx="40" cy="40" r="29" stroke="#4ade80" stroke-width="4" stroke-linecap="round" />
                            <polyline points="24,42 35,53 56,30" stroke="#4ade80" stroke-width="4.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <!-- Error icon -->
                    <div v-else class="bk-confirm-icon-wrap">
                        <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="bk-confirm-svg">
                            <circle cx="40" cy="40" r="29" stroke="#f87171" stroke-width="4" />
                            <line x1="27" y1="27" x2="53" y2="53" stroke="#f87171" stroke-width="4.5"
                                stroke-linecap="round" />
                            <line x1="53" y1="27" x2="27" y2="53" stroke="#f87171" stroke-width="4.5"
                                stroke-linecap="round" />
                        </svg>
                    </div>

                    <div class="bk-confirm-title">{{ confirmTitle }}</div>
                    <div class="bk-confirm-subtitle">{{ confirmSubtitle }}</div>
                    <button class="bk-confirm-close-btn" @click="closeConfirm">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import "@/assets/css/barang-keluar.css";
import { MagnifyingGlassIcon, PencilIcon, EyeIcon, TrashIcon } from "@heroicons/vue/24/outline";
import { useBarangKeluar } from "@/composables/useBarangKeluar";

const {
    tableData,
    search,
    isLoading,
    filteredData,
    isTambahOpen,
    tambahForm,
    tambahItems,
    tambahErrors,
    isSubmitting,
    openTambah,
    closeTambah,
    addItemRow,
    removeItemRow,
    validateCodeInput,
    submitTambah,
    isEditOpen,
    editRecord,
    editChecked,
    editQty,
    editError,
    isSavingEdit,
    openEdit,
    closeEdit,
    submitEdit,
    isViewOpen,
    viewRecord,
    openView,
    closeView,
    isDeleteConfirmOpen,
    pendingDeleteRecord,
    deleteRecord,
    confirmDelete,
    cancelDelete,
    isConfirmOpen,
    confirmType,
    confirmTitle,
    confirmSubtitle,
    closeConfirm,
    exportExcel,
    formatDate,
    formatDateTime,
    getData,
    startDate,
    endDate,
} = useBarangKeluar();
</script>
