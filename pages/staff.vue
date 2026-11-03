<template>
    <div class="staff-page">
        <title>Setting - Staff</title>

        <!-- TITLE -->
        <h1 class="staff-title">Manajemen Staff</h1>

        <!-- TOOLBAR -->
        <div class="staff-toolbar">
            <div class="staff-search-wrap">
                <input v-model="searchQuery" type="text" placeholder="Cari nama atau email staff..."
                    class="staff-search-input" />
            </div>

            <button class="staff-btn-tambah" @click="openAddModal">
                + Tambah Staff
            </button>
        </div>

        <!-- TABLE -->
        <div class="staff-table-wrap">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th class="staff-th staff-th-no">No.</th>
                        <th class="staff-th">Nama</th>
                        <th class="staff-th">Email</th>
                        <th class="staff-th">Role</th>
                        <th class="staff-th staff-th-aksi">Aksi</th>
                    </tr>
                </thead>

                <tbody v-if="pagination.length > 0">
                    <tr v-for="(user, index) in pagination" :key="user.id" class="staff-tr">
                        <td class="staff-td staff-td-no">
                            {{ startItem + index }}
                        </td>

                        <td class="staff-td">
                            {{ user.name }}
                        </td>

                        <td class="staff-td">
                            {{ user.email }}
                        </td>

                        <td class="staff-td">
                            {{ user.role?.name }}
                        </td>

                        <td class="staff-td staff-td-aksi">
                            <div class="staff-action-buttons">
                                <button class="staff-btn-icon staff-btn-edit" title="Edit" @click="openEditModal(user)">
                                    Edit
                                </button>

                                <button class="staff-btn-icon staff-btn-delete" title="Hapus"
                                    @click="deleteUser(user.id, user.name)">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- EMPTY STATE -->
            <div v-if="filteredUsers.length === 0" class="staff-empty">
                <p class="staff-empty-text">
                    Data Staff Tidak Ditemukan
                </p>
            </div>
        </div>

        <!-- PAGINATION -->
        <div class="staff-pagination">
            <div class="staff-pagination-left">
                <span>
                    Menampilkan {{ startItem }} sampai {{ endItem }}
                    dari {{ filteredUsers.length }}
                </span>

                <span class="staff-pagination-sep">|</span>

                <span>Tampilkan</span>

                <select v-model="itemsPerPage" class="staff-per-page" @change="currentPage = 1">
                    <option :value="10">10</option>
                    <option :value="25">25</option>
                    <option :value="50">50</option>
                    <option :value="100">100</option>
                </select>

                <span>data</span>
            </div>

            <div class="staff-pagination-controls">
                <button class="staff-page-btn" :disabled="currentPage === 1" @click="currentPage = 1">
                    &#xAB;
                </button>

                <button class="staff-page-btn" :disabled="currentPage === 1" @click="currentPage--">
                    &#x3C;
                </button>

                <template v-for="(page, idx) in paginatedPages" :key="idx">
                    <button v-if="page !== '...'" class="staff-page-btn"
                        :class="{ 'staff-page-active': currentPage === page }" @click="currentPage = page">
                        {{ page }}
                    </button>

                    <span v-else class="staff-page-btn staff-page-ellipsis">
                        ...
                    </span>
                </template>

                <button class="staff-page-btn" :disabled="currentPage === totalPages" @click="currentPage++">
                    &#x3E;
                </button>

                <button class="staff-page-btn" :disabled="currentPage === totalPages" @click="currentPage = totalPages">
                    &#xBB;
                </button>
            </div>
        </div>

        <!-- MODAL EDIT -->
        <div v-if="isEditModal" class="staff-modal-overlay" @click.self="closeEditModal">
            <div class="staff-modal">
                <div class="staff-modal-header">
                    <h2 class="staff-modal-title">Edit User</h2>

                    <button class="staff-modal-close" aria-label="Tutup" @click="closeEditModal"></button>
                </div>

                <div class="staff-modal-body">
                    <div class="staff-form-group">
                        <label class="staff-label">Nama</label>

                        <input v-model="editForm.name" type="text" class="staff-input" />
                    </div>

                    <div class="staff-form-group">
                        <label class="staff-label">Email</label>

                        <input v-model="editForm.email" type="email" disabled
                            class="staff-input staff-input-disabled" />
                    </div>

                    <div class="staff-form-group">
                        <label class="staff-label">User Role</label>

                        <select v-model="editForm.role_id" class="staff-input">
                            <option v-for="r in roles" :key="r.id" :value="r.id">
                                {{ r.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="staff-modal-footer form-add-actions">
                    <button class="staff-btn-batal" @click="closeEditModal">
                        Batal
                    </button>

                    <button class="staff-btn-simpan" @click="submitUpdateUser">
                        Simpan
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL TAMBAH -->
        <div v-if="isAddModal" class="staff-modal-overlay" @click.self="closeAddModal">
            <div class="staff-modal">
                <div class="staff-modal-header">
                    <h2 class="staff-modal-title">Tambah Staff Baru</h2>

                    <button class="staff-modal-close" aria-label="Tutup" @click="closeAddModal"></button>
                </div>

                <div class="staff-modal-body">
                    <div class="staff-form-group">
                        <label class="staff-label">
                            Nama <span class="staff-required">*</span>
                        </label>

                        <input v-model="addForm.name" type="text" class="staff-input" placeholder="Masukkan nama" />

                        <p v-if="errors.name" class="staff-error">
                            {{ errors.name }}
                        </p>
                    </div>

                    <div class="staff-form-group">
                        <label class="staff-label">
                            Email <span class="staff-required">*</span>
                        </label>

                        <input v-model="addForm.email" type="email" class="staff-input" placeholder="Masukkan email" />

                        <p v-if="errors.email" class="staff-error">
                            {{ errors.email }}
                        </p>
                    </div>

                    <div class="staff-form-group">
                        <label class="staff-label">
                            Password <span class="staff-required">*</span>
                        </label>

                        <input v-model="addForm.password" type="password" class="staff-input"
                            placeholder="Masukkan password" />

                        <p v-if="errors.password" class="staff-error">
                            {{ errors.password }}
                        </p>
                    </div>

                    <div class="staff-form-group">
                        <label class="staff-label">
                            Role <span class="staff-required">*</span>
                        </label>

                        <select v-model="addForm.role_id" class="staff-input">
                            <option value="">Pilih Role</option>

                            <option v-for="r in roles" :key="r.id" :value="r.id">
                                {{ r.name }}
                            </option>
                        </select>

                        <p v-if="errors.role_id" class="staff-error">
                            {{ errors.role_id }}
                        </p>
                    </div>
                </div>

                <div class="staff-modal-footer form-add-actions">
                    <button class="staff-btn-batal" @click="closeAddModal">
                        Batal
                    </button>

                    <button class="staff-btn-simpan" @click="submitAddUser">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useStaff } from "~/composables/useStaff";

const {
    searchQuery,
    itemsPerPage,
    currentPage,
    isAddModal,
    errors,
    isEditModal,
    addForm,
    editForm,
    filteredUsers,
    pagination,
    totalPages,
    startItem,
    endItem,
    paginatedPages,
    roles,
    openEditModal,
    closeEditModal,
    submitUpdateUser,
    openAddModal,
    closeAddModal,
    submitAddUser,
    deleteUser
} = useStaff();
</script>

<style src="@/assets/css/staff.css"></style>