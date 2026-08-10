<template>
    <div>
        <title>Menu Benang</title>

        <div class="bk-page-header">
            <h1 class="bk-page-title">Benang</h1>
        </div>

        <!-- Tabs -->
        <div class="bn-tabs">
            <button class="bn-tab" :class="{ 'is-active': activeTab === 'masuk' }" @click="setTab('masuk')">Barang
                Masuk</button>
            <button class="bn-tab" :class="{ 'is-active': activeTab === 'stok' }" @click="setTab('stok')">Stok
                Benang</button>
            <button class="bn-tab" :class="{ 'is-active': activeTab === 'keluar' }" @click="setTab('keluar')">Benang
                Keluar</button>
        </div>

        <!-- ============================ TAB: BARANG MASUK ============================ -->
        <section v-show="activeTab === 'masuk'">
            <div class="bk-toolbar">
                <div class="bk-search-box">
                    <MagnifyingGlassIcon class="bk-search-icon" />
                    <input v-model="masukSearch" type="text" placeholder="Cari warna..." class="bk-search-input" @input="
                        masukPage = 1;
                    getMasuk();
                    " />
                </div>
                <div class="bk-actions">
                    <button class="bn-btn-pa" @click="openPa">Pewarna Alam</button>
                    <button class="bn-btn-tx" @click="openTx">Textile</button>
                </div>
            </div>

            <div class="bk-table-wrapper">
                <table class="bk-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Tipe Benang</th>
                            <th>Jenis Benang</th>
                            <th>Warna</th>
                            <th>Jumlah Benang</th>
                            <th>Author</th>
                            <th v-if="isSuperAdmin">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="masukLoading">
                            <td :colspan="isSuperAdmin ? 7 : 6" style="text-align:center;padding:32px;color:#9ca3af">
                                Memuat data...</td>
                        </tr>
                        <tr v-else-if="masukPaginated.length === 0" class="bk-empty-row">
                            <td :colspan="isSuperAdmin ? 7 : 6">Data tidak ditemukan</td>
                        </tr>
                        <tr v-else v-for="row in masukPaginated" :key="row.benang_masuk_id">
                            <td>{{ formatDate(row.tanggal) }}</td>
                            <td>
                                <span class="bn-chip"
                                    :class="row.benang_masuk_tipe === 'PEWARNA_ALAM' ? 'bn-chip-pa' : 'bn-chip-tx'">
                                    {{ row.tipe_label }}
                                </span>
                            </td>
                            <td>{{ row.jenis_nama || "-" }}</td>
                            <td>{{ row.benang_masuk_warna }}</td>
                            <td>{{ row.benang_masuk_jumlah }}</td>
                            <td>{{ row.author || "-" }}</td>
                            <td v-if="isSuperAdmin">
                                <div class="bk-action-group">
                                    <button class="bk-btn-icon" title="Edit" @click="openMasukEdit(row)">
                                        <PencilIcon class="bk-icon bk-icon-edit" />
                                    </button>
                                    <button class="bk-btn-icon" title="Hapus" @click="askMasukDelete(row)">
                                        <TrashIcon class="bk-icon bk-icon-delete" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="bk-pagination" v-if="masukData.length">
                <div class="bk-pagination-left">
                    <label>Tampilkan:</label>
                    <select v-model="masukPerPage" @change="masukPage = 1">
                        <option :value="5">5</option>
                        <option :value="10">10</option>
                        <option :value="20">20</option>
                        <option :value="50">50</option>
                        <option value="all">All</option>
                    </select>
                </div>
                <div class="bk-pagination-right">
                    <button class="bk-page-btn" :disabled="masukPage === 1" @click="masukPage--">
                        Sebelumnya
                    </button>
                    <button v-for="(page, index) in masukPaginatedPages" :key="index"
                        @click="typeof page === 'number' && (masukPage = page)" :disabled="page === '...'" :class="[
                            'bk-page-number',
                            page === masukPage ? 'active' : '',
                            page === '...' ? 'ellipsis' : ''
                        ]">
                        {{ page }}
                    </button>
                    <button class="bk-page-btn" :disabled="masukPage === masukTotalPage" @click="masukPage++">
                        Selanjutnya
                    </button>
                </div>
            </div>
        </section>

        <!-- ============================ TAB: STOK BENANG ============================ -->
        <section v-show="activeTab === 'stok'">
            <div class="bk-toolbar">
                <div class="bn-filter-group">
                    <button class="bn-filter-btn" :class="{ 'is-active': stokFilter === 'ALL' }"
                        @click="setStokFilter('ALL')">All</button>
                    <button class="bn-filter-btn" :class="{ 'is-active': stokFilter === 'TEXTILE' }"
                        @click="setStokFilter('TEXTILE')">Textile</button>
                    <button class="bn-filter-btn" :class="{ 'is-active': stokFilter === 'PEWARNA_ALAM' }"
                        @click="setStokFilter('PEWARNA_ALAM')">Pewarna Alam</button>
                </div>
            </div>

            <div class="bk-table-wrapper">
                <table class="bk-table">
                    <thead>
                        <tr>
                            <th>Tipe</th>
                            <th>Jenis Benang</th>
                            <th>Warna</th>
                            <th>Jumlah Benang</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="stokLoading">
                            <td colspan="4" style="text-align:center;padding:32px;color:#9ca3af">Memuat data...</td>
                        </tr>
                        <tr v-else-if="stokPaginated.length === 0" class="bk-empty-row">
                            <td colspan="4">Data tidak ditemukan</td>
                        </tr>
                        <tr v-else v-for="(row, i) in stokPaginated" :key="i">
                            <td>
                                <span class="bn-chip"
                                    :class="row.benang_masuk_tipe === 'PEWARNA_ALAM' ? 'bn-chip-pa' : 'bn-chip-tx'">
                                    {{ row.tipe_label }}
                                </span>
                            </td>
                            <td>{{ row.jenis_nama || "-" }}</td>
                            <td>{{ row.benang_masuk_warna }}</td>
                            <td>{{ row.total_jumlah }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="bk-pagination" v-if="stokData.length">

                <div class="bk-pagination-left">
                    <label>Tampilkan:</label>

                    <select v-model="stokPerPage" @change="stokPage = 1">

                        <option :value="5">5</option>
                        <option :value="10">10</option>
                        <option :value="20">20</option>
                        <option :value="50">50</option>
                        <option value="all">All</option>

                    </select>
                </div>
                <div class="bk-pagination-right">
                    <button class="bk-page-btn" :disabled="stokPage === 1" @click="stokPage--">
                        Sebelumnya
                    </button>
                    <button v-for="(page, index) in stokPaginatedPages" :key="index"
                        @click="typeof page === 'number' && (stokPage = page)" :disabled="page === '...'" :class="[
                            'bk-page-number',
                            stokPage === page ? 'active' : '',
                            page === '...' ? 'ellipsis' : ''
                        ]">
                        {{ page }}
                    </button>
                    <button class="bk-page-btn" :disabled="stokPage === stokTotalPage" @click="stokPage++">
                        Selanjutnya
                    </button>
                </div>
            </div>
        </section>

        <!-- ============================ TAB: BENANG KELUAR ============================ -->
        <section v-show="activeTab === 'keluar'">
            <div class="bk-toolbar">
                <div class="bk-search-box">
                    <MagnifyingGlassIcon class="bk-search-icon" />
                    <input v-model="keluarSearch" type="text" placeholder="Cari nama penenun..." class="bk-search-input"
                        @input="getKeluar" />
                </div>
                <div class="bk-actions">
                    <input type="date" v-model="startDate" class="bk-date-input" />
                    <input type="date" v-model="endDate" class="bk-date-input" />
                    <button class="bk-btn-export" @click="exportExcel">Export Excel</button>
                    <button class="bk-btn-add" @click="openKeluarTambah">+ Benang Keluar</button>
                </div>
            </div>

            <div class="bk-table-wrapper">
                <table class="bk-table">
                    <thead>
                        <tr>
                            <th>Petugas Benang</th>
                            <th>Tanggal Keluar</th>
                            <th>Tanggal Selesai</th>
                            <th>Nama Penenun</th>
                            <th>Warna</th>
                            <th>Tipe</th>
                            <th>Jenis</th>
                            <th>Jumlah</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="keluarLoading">
                            <td colspan="11" style="text-align:center;padding:32px;color:#9ca3af">Memuat data...</td>
                        </tr>
                        <tr v-else-if="keluarPaginated.length === 0" class="bk-empty-row">
                            <td colspan="11">Data tidak ditemukan</td>
                        </tr>
                        <tr v-else v-for="row in keluarPaginated" :key="row.benang_keluar_id">
                            <td>{{ row.petugas || "-" }}</td>
                            <td>{{ formatDate(row.tanggal_keluar) }}</td>
                            <td>{{ row.tanggal_selesai ? formatDate(row.tanggal_selesai) : "-" }}</td>
                            <td>{{ row.nama_penenun }}</td>
                            <td>{{ row.warna || "-" }}</td>
                            <td>{{ row.tipe || "-" }}</td>
                            <td>{{ row.jenis || "-" }}</td>
                            <td>{{ row.jumlah }}</td>
                            <td>{{ row.author || "-" }}</td>
                            <td>
                                <span :class="row.status === 'DONE' ? 'bn-badge-done' : 'bn-badge-proses'">
                                    {{ row.status === 'DONE' ? 'Done' : 'Proses' }}
                                </span>
                            </td>
                            <td>
                                <div class="bk-action-group">
                                    <button v-if="row.status !== 'DONE'" class="bk-btn-save"
                                        style="padding:6px 12px;font-size:12px" @click="openSelesai(row)">
                                        Selesai
                                    </button>
                                    <button v-else class="bk-btn-icon" title="Lihat Detail"
                                        @click="openKeluarView(row)">
                                        <EyeIcon class="bk-icon bk-icon-view" />
                                    </button>
                                    <button class="bk-btn-icon" title="Hapus" @click="askKeluarDelete(row)">
                                        <TrashIcon class="bk-icon bk-icon-delete" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="bk-pagination" v-if="keluarData.length">
                <div class="bk-pagination-left">
                    <label>Tampilkan:</label>
                    <select v-model="keluarPerPage" @change="keluarPage = 1">
                        <option :value="5">5</option>
                        <option :value="10">10</option>
                        <option :value="20">20</option>
                        <option :value="50">50</option>
                        <option value="all">All</option>
                    </select>
                </div>
                <div class="bk-pagination-right">
                    <button class="bk-page-btn" :disabled="keluarPage === 1" @click="keluarPage--">
                        Sebelumnya
                    </button>
                    <button v-for="(page, index) in keluarPaginatedPages" :key="index"
                        @click="typeof page === 'number' && (keluarPage = page)" :disabled="page === '...'" :class="[
                            'bk-page-number',
                            keluarPage === page ? 'active' : '',
                            page === '...' ? 'ellipsis' : ''
                        ]">
                        {{ page }}
                    </button>
                    <button class="bk-page-btn" :disabled="keluarPage === keluarTotalPage" @click="keluarPage++">
                        Selanjutnya
                    </button>
                </div>
            </div>
        </section>

        <!-- ===== Modal: Tambah Pewarna Alami ===== -->
        <div v-if="isPaOpen" class="bk-modal-overlay" @click.self="closePa">
            <div class="bk-modal">
                <div class="bk-modal-header">
                    <h2>Tambah Pewarna Alami</h2>
                    <button class="bk-modal-close" @click="closePa">✕</button>
                </div>
                <div class="bk-modal-body">
                    <div class="bk-form-group">
                        <label>Warna Barang <span class="bk-required">*</span></label>
                        <input v-model="paForm.warna" type="text" placeholder="Masukkan warna barang"
                            :class="{ 'is-invalid': paErrors.warna }" />
                        <small v-if="paErrors.warna">{{ paErrors.warna }}</small>
                    </div>
                    <div class="bk-form-group">
                        <label>Jenis Benang <span class="bk-required">*</span></label>
                        <select v-model="paForm.jenis_id">
                            <option value="" disabled>Pilih jenis benang</option>
                            <option v-for="j in jenisBenangList" :key="j.id" :value="j.id">{{ j.jenisbenang_nama }}
                            </option>
                        </select>
                        <small v-if="paErrors.jenis_id">{{ paErrors.jenis_id }}</small>
                    </div>
                    <div class="bk-form-group">
                        <label>Jumlah Benang <span class="bk-required">*</span></label>
                        <input v-model="paForm.jumlah" type="number" min="1" placeholder="Masukkan jumlah benang"
                            :class="{ 'is-invalid': paErrors.jumlah }" />
                        <small v-if="paErrors.jumlah">{{ paErrors.jumlah }}</small>
                    </div>
                    <div class="bk-form-group">
                        <label>Sumber Warna <span class="bk-required">*</span></label>
                        <select v-model="paForm.sumber_warna">
                            <option value="" disabled>Pilih sumber warna</option>
                            <option v-for="(w, i) in sumberWarnaList" :key="i" :value="w">{{ w }}</option>
                        </select>
                        <small v-if="paErrors.sumber_warna">{{ paErrors.sumber_warna }}</small>
                    </div>
                </div>
                <div class="bk-modal-footer">
                    <button class="bk-btn-cancel" @click="closePa">Batal</button>
                    <button class="bk-btn-save" :disabled="paSubmitting" @click="submitPa">
                        {{ paSubmitting ? "Menyimpan..." : "Tambah" }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ===== Modal: Tambah Textile ===== -->
        <div v-if="isTxOpen" class="bk-modal-overlay" @click.self="closeTx">
            <div class="bk-modal">
                <div class="bk-modal-header">
                    <h2>Tambah Textile</h2>
                    <button class="bk-modal-close" @click="closeTx">✕</button>
                </div>
                <div class="bk-modal-body">
                    <div class="bk-form-group">
                        <label>Warna Benang <span class="bk-required">*</span></label>
                        <input v-model="txForm.warna" type="text" placeholder="Masukkan warna benang"
                            :class="{ 'is-invalid': txErrors.warna }" />
                        <small v-if="txErrors.warna">{{ txErrors.warna }}</small>
                    </div>
                    <div class="bk-form-group">
                        <label>Jenis Benang <span class="bk-required">*</span></label>
                        <select v-model="txForm.jenis_id">
                            <option value="" disabled>Pilih jenis benang</option>
                            <option v-for="j in jenisBenangList" :key="j.id" :value="j.id">{{ j.jenisbenang_nama }}
                            </option>
                        </select>
                        <small v-if="txErrors.jenis_id">{{ txErrors.jenis_id }}</small>
                    </div>
                    <div class="bk-form-group">
                        <label>Jumlah Benang <span class="bk-required">*</span></label>
                        <input v-model="txForm.jumlah" type="number" min="1" placeholder="Masukkan jumlah benang"
                            :class="{ 'is-invalid': txErrors.jumlah }" />
                        <small v-if="txErrors.jumlah">{{ txErrors.jumlah }}</small>
                    </div>
                </div>
                <div class="bk-modal-footer">
                    <button class="bk-btn-cancel" @click="closeTx">Batal</button>
                    <button class="bk-btn-save" :disabled="txSubmitting" @click="submitTx">
                        {{ txSubmitting ? "Menyimpan..." : "Tambah" }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ===== Modal: Edit Benang Masuk (superadmin) ===== -->
        <div v-if="isMasukEditOpen" class="bk-modal-overlay" @click.self="closeMasukEdit">
            <div class="bk-modal">
                <div class="bk-modal-header">
                    <h2>Edit Benang Masuk</h2>
                    <button class="bk-modal-close" @click="closeMasukEdit">✕</button>
                </div>
                <div class="bk-modal-body">
                    <div class="bk-form-group">
                        <label>Warna <span class="bk-required">*</span></label>
                        <input v-model="masukEditForm.warna" type="text"
                            :class="{ 'is-invalid': masukEditErrors.warna }" />
                        <small v-if="masukEditErrors.warna">{{ masukEditErrors.warna }}</small>
                    </div>
                    <div class="bk-form-group">
                        <label>Jenis Benang <span class="bk-required">*</span></label>
                        <select v-model="masukEditForm.jenis_id">
                            <option value="" disabled>Pilih jenis benang</option>
                            <option v-for="j in jenisBenangList" :key="j.id" :value="j.id">{{ j.jenisbenang_nama }}
                            </option>
                        </select>
                        <small v-if="masukEditErrors.jenis_id">{{ masukEditErrors.jenis_id }}</small>
                    </div>
                    <div class="bk-form-group">
                        <label>Jumlah Benang <span class="bk-required">*</span></label>
                        <input v-model="masukEditForm.jumlah" type="number" min="1"
                            :class="{ 'is-invalid': masukEditErrors.jumlah }" />
                        <small v-if="masukEditErrors.jumlah">{{ masukEditErrors.jumlah }}</small>
                    </div>
                    <div class="bk-form-group" v-if="masukEditForm.tipe === 'PEWARNA_ALAM'">
                        <label>Sumber Warna</label>
                        <select v-model="masukEditForm.sumber_warna">
                            <option value="">-</option>
                            <option v-for="(w, i) in sumberWarnaList" :key="i" :value="w">{{ w }}</option>
                        </select>
                    </div>
                </div>
                <div class="bk-modal-footer">
                    <button class="bk-btn-cancel" @click="closeMasukEdit">Batal</button>
                    <button class="bk-btn-save" :disabled="masukEditSubmitting" @click="submitMasukEdit">
                        {{ masukEditSubmitting ? "Menyimpan..." : "Simpan" }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ===== Modal: Tambah Benang Keluar ===== -->
        <div v-if="isKeluarTambahOpen" class="bk-modal-overlay" @click.self="closeKeluarTambah">
            <div class="bk-modal bk-modal-lg">
                <div class="bk-modal-header">
                    <h2>Tambah Benang Keluar</h2>
                    <button class="bk-modal-close" @click="closeKeluarTambah">✕</button>
                </div>
                <div class="bk-modal-body">
                    <div class="bk-form-group">
                        <label>Nama Penenun <span class="bk-required">*</span></label>
                        <input v-model="keluarForm.nama_penenun" type="text" placeholder="Masukkan nama penenun"
                            :class="{ 'is-invalid': keluarErrors.nama_penenun }" />
                        <small v-if="keluarErrors.nama_penenun">{{ keluarErrors.nama_penenun }}</small>
                    </div>

                    <div class="bk-items-section">
                        <div class="bk-items-label">Jenis Benang <span class="bk-required">*</span></div>
                        <div class="bn-keluar-head">
                            <div>Warna</div>
                            <div>Tipe</div>
                            <div>Jenis</div>
                            <div>Jumlah</div>
                            <div></div>
                        </div>

                        <div v-for="(item, idx) in keluarItems" :key="item._key" class="bn-keluar-row">
                            <div class="bn-cell">
                                <select v-model="item.warna" :class="{ 'is-invalid': item.errWarna }">
                                    <option value="" disabled>Warna</option>
                                    <option v-for="(w, i) in warnaAlamList" :key="i" :value="w">{{ w }}</option>
                                </select>
                                <small v-if="item.errWarna">{{ item.errWarna }}</small>
                            </div>
                            <div class="bn-cell">
                                <select v-model="item.tipe" :class="{ 'is-invalid': item.errTipe }">
                                    <option value="" disabled>Tipe</option>
                                    <option v-for="p in pewarnaList" :key="p.pewarna_id" :value="p.pewarna_kode">
                                        {{ p.pewarna_nama }}
                                    </option>
                                </select>
                                <small v-if="item.errTipe">{{ item.errTipe }}</small>
                            </div>
                            <div class="bn-cell">
                                <select v-model="item.jenis_id" :class="{ 'is-invalid': item.errJenis }">
                                    <option value="" disabled>Jenis</option>
                                    <option v-for="j in jenisBenangList" :key="j.id" :value="j.id">{{ j.jenisbenang_nama
                                    }}</option>
                                </select>
                                <small v-if="item.errJenis">{{ item.errJenis }}</small>
                            </div>
                            <div class="bn-cell">
                                <input v-model="item.jumlah" type="number" min="1" placeholder="0"
                                    :class="{ 'is-invalid': item.errJumlah }" />
                                <small v-if="item.errJumlah">{{ item.errJumlah }}</small>
                            </div>
                            <button class="bk-btn-remove-item bn-btn-remove" :disabled="keluarItems.length <= 1"
                                @click="removeKeluarRow(idx)">
                                <TrashIcon class="bk-icon bk-icon-delete" />
                            </button>
                        </div>

                        <button class="bk-btn-add-item" @click="addKeluarRow">+ Add</button>
                    </div>
                </div>
                <div class="bk-modal-footer">
                    <button class="bk-btn-cancel" @click="closeKeluarTambah">Batal</button>
                    <button class="bk-btn-save" :disabled="keluarSubmitting" @click="submitKeluar">
                        {{ keluarSubmitting ? "Menyimpan..." : "Tambah" }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ===== Modal: Penyelesaian Benang Keluar (Selesai) ===== -->
        <div v-if="isSelesaiOpen" class="bk-modal-overlay" @click.self="closeSelesai">
            <div class="bk-modal">
                <div class="bk-modal-header">
                    <h2>Penyelesaian Benang Keluar</h2>
                    <button class="bk-modal-close" @click="closeSelesai">✕</button>
                </div>
                <div class="bk-modal-body">
                    <div class="bk-form-group">
                        <label>Catatan <span class="bk-required">*</span></label>
                        <textarea v-model="selesaiForm.catatan" class="bn-textarea" placeholder="Masukkan catatan"
                            :class="{ 'is-invalid': selesaiErrors.catatan }"></textarea>
                        <small v-if="selesaiErrors.catatan">{{ selesaiErrors.catatan }}</small>
                    </div>
                    <div class="bk-form-group">
                        <label>Foto Hasil <span class="bk-required">*</span></label>
                        <input class="bn-foto-input" type="file" accept="image/*" @change="handleFotoUpload" />
                        <small v-if="selesaiErrors.foto">{{ selesaiErrors.foto }}</small>
                        <img v-if="fotoPreview" :src="fotoPreview" class="bn-foto-preview" />
                    </div>
                </div>
                <div class="bk-modal-footer">
                    <button class="bk-btn-cancel" @click="closeSelesai">Batal</button>
                    <button class="bk-btn-save" :disabled="selesaiSubmitting" @click="submitSelesai">
                        {{ selesaiSubmitting ? "Menyimpan..." : "Tambah" }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ===== Modal: Detail Penyelesaian (View) ===== -->
        <div v-if="isKeluarViewOpen" class="bk-modal-overlay" @click.self="closeKeluarView">
            <div class="bk-modal">
                <div class="bk-modal-header">
                    <h2>Detail Penyelesaian</h2>
                    <button class="bk-modal-close" @click="closeKeluarView">✕</button>
                </div>
                <div class="bk-modal-body">
                    <div class="bk-view-info">
                        <p><strong>Nama Penenun:</strong> {{ keluarViewRecord?.nama_penenun }}</p>
                        <p><strong>Tanggal Selesai:</strong> {{ formatDate(keluarViewRecord?.tanggal_selesai) }}</p>
                        <p><strong>Author:</strong> {{ keluarViewRecord?.author || "-" }}</p>
                    </div>
                    <div class="bk-form-group">
                        <label>Catatan</label>
                        <p style="font-size:13px;color:#374151;margin:0">{{ keluarViewRecord?.catatan || "-" }}</p>
                    </div>
                    <div class="bk-form-group">
                        <label>Foto Hasil</label>
                        <img v-if="keluarViewRecord?.foto_hasil" :src="keluarViewRecord.foto_hasil"
                            class="bn-view-foto" />
                        <p v-else style="font-size:13px;color:#9ca3af;margin:0">Tidak ada foto</p>
                    </div>
                </div>
                <div class="bk-modal-footer">
                    <button class="bk-btn-save" @click="closeKeluarView">Tutup</button>
                </div>
            </div>
        </div>

        <!-- ===== Delete confirm: Benang Masuk ===== -->
        <div v-if="isMasukDeleteOpen" class="bk-modal-overlay">
            <div class="bk-confirm-card">
                <div class="bk-confirm-body">
                    <div class="bk-confirm-icon-wrap">
                        <svg viewBox="0 0 80 80" fill="none" class="bk-confirm-svg">
                            <circle cx="40" cy="40" r="34" stroke="#f87171" stroke-width="4" />
                            <line x1="40" y1="26" x2="40" y2="44" stroke="#f87171" stroke-width="4.5"
                                stroke-linecap="round" />
                            <circle cx="40" cy="53" r="2.5" fill="#f87171" />
                        </svg>
                    </div>
                    <div class="bk-confirm-title">Konfirmasi hapus data benang masuk</div>
                    <div class="bk-confirm-subtitle">Apakah Anda yakin ingin menghapus data benang ini?</div>
                    <div style="display:flex;gap:12px;justify-content:center">
                        <button class="bk-confirm-cancel-btn" @click="cancelMasukDelete">Batal</button>
                        <button class="bk-confirm-delete-btn" @click="confirmMasukDelete">Ya, dihapus</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== Delete confirm: Benang Keluar ===== -->
        <div v-if="isKeluarDeleteOpen" class="bk-modal-overlay">
            <div class="bk-confirm-card">
                <div class="bk-confirm-body">
                    <div class="bk-confirm-icon-wrap">
                        <svg viewBox="0 0 80 80" fill="none" class="bk-confirm-svg">
                            <circle cx="40" cy="40" r="34" stroke="#f87171" stroke-width="4" />
                            <line x1="40" y1="26" x2="40" y2="44" stroke="#f87171" stroke-width="4.5"
                                stroke-linecap="round" />
                            <circle cx="40" cy="53" r="2.5" fill="#f87171" />
                        </svg>
                    </div>
                    <div class="bk-confirm-title">Konfirmasi hapus data benang keluar</div>
                    <div class="bk-confirm-subtitle">Apakah Anda yakin ingin menghapus data benang ini?</div>
                    <div style="display:flex;gap:12px;justify-content:center">
                        <button class="bk-confirm-cancel-btn" @click="cancelKeluarDelete">Batal</button>
                        <button class="bk-confirm-delete-btn" @click="confirmKeluarDelete">Ya, dihapus</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== Shared confirm popup (success / error) ===== -->
        <div v-if="isConfirmOpen" class="bk-modal-overlay">
            <div class="bk-confirm-card">
                <div class="bk-confirm-body">
                    <div v-if="confirmType === 'success'" class="bk-confirm-icon-wrap">
                        <svg viewBox="0 0 80 80" fill="none" class="bk-confirm-svg">
                            <circle cx="40" cy="40" r="29" stroke="#4ade80" stroke-width="4" stroke-linecap="round" />
                            <polyline points="24,42 35,53 56,30" stroke="#4ade80" stroke-width="4.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div v-else class="bk-confirm-icon-wrap">
                        <svg viewBox="0 0 80 80" fill="none" class="bk-confirm-svg">
                            <circle cx="40" cy="40" r="29" stroke="#f87171" stroke-width="4" />
                            <line x1="27" y1="27" x2="53" y2="53" stroke="#f87171" stroke-width="4.5"
                                stroke-linecap="round" />
                            <line x1="53" y1="27" x2="27" y2="53" stroke="#f87171" stroke-width="4.5"
                                stroke-linecap="round" />
                        </svg>
                    </div>
                    <div class="bk-confirm-title">{{ confirmTitle }}</div>
                    <div class="bk-confirm-subtitle">{{ confirmSubtitle }}</div>
                    <button class="bk-confirm-close-btn" @click="onConfirmButton">{{ confirmButtonText }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import "@/assets/css/barang-keluar.css";
import "@/assets/css/benang.css";
import { MagnifyingGlassIcon, PencilIcon, EyeIcon, TrashIcon } from "@heroicons/vue/24/outline";
import { useBenang } from "@/composables/useBenang";

const {
    isSuperAdmin, activeTab, setTab,
    jenisBenangList, pewarnaList, sumberWarnaList, warnaAlamList,
    isConfirmOpen, confirmType, confirmTitle, confirmSubtitle, confirmButtonText, onConfirmButton,
    formatDate,
    masukData, masukSearch, masukLoading, getMasuk,
    isPaOpen, paForm, paErrors, paSubmitting, openPa, closePa, submitPa,
    isTxOpen, txForm, txErrors, txSubmitting, openTx, closeTx, submitTx,
    isMasukEditOpen, masukEditForm, masukEditErrors, masukEditSubmitting, openMasukEdit, closeMasukEdit, submitMasukEdit,
    isMasukDeleteOpen, pendingMasukDelete, askMasukDelete, cancelMasukDelete, confirmMasukDelete,
    stokData, stokLoading, stokFilter, setStokFilter,
    keluarData, keluarSearch, keluarLoading, getKeluar, startDate, endDate, exportExcel,
    isKeluarTambahOpen, keluarForm, keluarItems, keluarErrors, keluarSubmitting,
    openKeluarTambah, closeKeluarTambah, addKeluarRow, removeKeluarRow, submitKeluar,
    isSelesaiOpen, selesaiRecord, selesaiForm, selesaiErrors, fotoPreview, selesaiSubmitting,
    openSelesai, closeSelesai, handleFotoUpload, submitSelesai,
    isKeluarViewOpen, keluarViewRecord, openKeluarView, closeKeluarView,
    isKeluarDeleteOpen, pendingKeluarDelete, askKeluarDelete, cancelKeluarDelete, confirmKeluarDelete, masukPage,
    masukPerPage, masukTotalPage, masukPaginated, masukPaginatedPages, stokPage,stokPerPage, stokPaginated, stokTotalPage, 
    stokPaginatedPages, keluarPage, keluarPerPage, keluarPaginated, keluarTotalPage,keluarPaginatedPages,
} = useBenang();
</script>
