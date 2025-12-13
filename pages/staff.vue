<template>
    <div>
        <title>Staff</title>
        <div class="judul text-xl font-semibold mb-4">Manajemen Staff</div>
        <div class="flex items-center justify-between pt-2">
            <input class="search-box p-2 border rounded-md w-[385px] text-sm" v-model="searchQuery" type="text"
                placeholder="Search staff..." />
        </div>

        <table class="datatable w-full rounded-md overflow-hidden mt-4">
            <thead class="bg-blue-100">
                <tr>
                    <th class="px-4 py-2 text-left">No.</th>
                    <th class="px-4 py-2 text-left">Nama</th>
                    <th class="px-4 py-2 text-left">Email</th>
                    <th class="px-4 py-2 text-left">Role</th>
                    <th class="px-4 py-2 text-left">Aksi</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="(user, index) in pagination" :key="user.id"
                    :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
                    <td class="px-4 py-2">{{ index + 1 }}</td>
                    <td class="px-4 py-2">{{ user.name }}</td>
                    <td class="px-4 py-2">{{ user.email }}</td>
                    <td class="px-4 py-2">{{ user.role?.name }}</td>

                    <td class="px-4 py-2 flex gap-2">
                        <button class="px-2 py-1 bg-yellow-500 text-white rounded text-xs hover:bg-yellow-600"
                            @click="openPasswordModal(user)">
                            Update Password
                        </button>

                        <button class="px-2 py-1 bg-red-500 text-white rounded text-xs hover:bg-red-600"
                            @click="deleteUser(user.id, user.name)">
                            Delete
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="flex justify-between items-center mt-4 text-xs">
            <div class="flex items-center space-x-2">
                <label>Tampilkan:</label>
                <select v-model="itemsPerPage" class="border px-2 py-1 rounded text-xs">
                    <option :value="5">5</option>
                    <option :value="10">10</option>
                    <option :value="20">20</option>
                    <option value="all">All</option>
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <button class="px-3 py-1 bg-gray-300 rounded" :disabled="currentPage === 1" @click="currentPage--">
                    Sebelumnya
                </button>

                <button v-for="page in paginatedPages" :key="page"
                    @click="typeof page === 'number' && (currentPage = page)" :disabled="page === '...'" :class="[
                        'px-3 py-1 rounded',
                        currentPage === page
                            ? 'bg-blue-500 text-white'
                            : 'bg-gray-200',
                        page === '...' && 'cursor-default'
                    ]">
                    {{ page }}
                </button>

                <button class="px-3 py-1 bg-gray-300 rounded" :disabled="currentPage === totalPages"
                    @click="currentPage++">
                    Selanjutnya
                </button>
            </div>
        </div>

        <div v-if="isPasswordModal" class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
            <div class="bg-white p-6 rounded-lg w-[420px]">
                <h3 class="text-lg font-semibold mb-4">
                    Update Password – {{ selectedUser?.name }}
                </h3>

                <div class="space-y-3">
                    <input v-model="currentPassword" type="password" placeholder="Password Saat Ini"
                        class="w-full border rounded p-2" />

                    <input v-model="newPassword" type="password" placeholder="Password Baru"
                        class="w-full border rounded p-2" />
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button class="px-4 py-2 bg-gray-300 rounded" @click="closePasswordModal">
                        Batal
                    </button>

                    <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" @click="updatePassword">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import Swal from "sweetalert2";
import { useRuntimeConfig } from "#imports";

const url = ref("");
const searchQuery = ref("");
const users = ref([]);
const itemsPerPage = ref(10);
const currentPage = ref(1);
const isPasswordModal = ref(false);
const selectedUser = ref(null);
const currentPassword = ref("");
const newPassword = ref("");

onMounted(() => {
    url.value = useRuntimeConfig().public.apiBase;
    fetchUsers();
});

const fetchUsers = async () => {
    try {
        const token = sessionStorage.getItem("auth_token");
        const res = await axios.get(`${url.value}/api/getUser`, {
            headers: {
                Authorization: `Bearer ${token}`,
            },
        });
        users.value = res.data.data || res.data;
    } catch (err) {
        console.error(err);
    }
};

const filteredUsers = computed(() => {
    const q = searchQuery.value.toLowerCase();
    return users.value.filter(
        (u) =>
            u.name?.toLowerCase().includes(q) ||
            u.email?.toLowerCase().includes(q) ||
            u.role?.name?.toLowerCase().includes(q)
    );
});

/* PAGINATION */
const pagination = computed(() => {
    if (itemsPerPage.value === "all") return filteredUsers.value;
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredUsers.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => {
    if (itemsPerPage.value === "all") return 1;
    return Math.ceil(filteredUsers.value.length / itemsPerPage.value);
});

const paginatedPages = computed(() => {
    const total = totalPages.value;
    const current = currentPage.value;
    if (total <= 5) return Array.from({ length: total }, (_, i) => i + 1);
    if (current <= 3) return [1, 2, 3, "...", total];
    if (current >= total - 2) return [1, "...", total - 2, total - 1, total];
    return [1, "...", current - 1, current, current + 1, "...", total];
});

/* PASSWORD */
const openPasswordModal = (user) => {
    selectedUser.value = user;
    currentPassword.value = "";
    newPassword.value = "";
    isPasswordModal.value = true;
};

const closePasswordModal = () => {
    isPasswordModal.value = false;
};

const updatePassword = async () => {
    if (!currentPassword.value || !newPassword.value) {
        Swal.fire("Gagal", "Semua field wajib diisi", "warning");
        return;
    }

    try {
        const token = sessionStorage.getItem("auth_token");
        console.log('sadsa');

        await axios.post(
            `${url.value}/api/user/update-password`,
            {
                current_password: currentPassword.value,
                new_password: newPassword.value,
            },
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            }
        );

        Swal.fire("Berhasil", "Password berhasil diperbarui", "success");
        closePasswordModal();
    } catch (err) {
        Swal.fire(
            "Gagal",
            err.response?.data?.message || "Update password gagal",
            "error"
        );
    }
};

/* DELETE */
const deleteUser = async (id, name) => {
    const confirm = await Swal.fire({
        title: "Hapus Staff?",
        text: `Yakin ingin menghapus ${name}?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, Hapus",
        cancelButtonText: "Batal",
    });

    if (!confirm.isConfirmed) return;

    try {
        const token = sessionStorage.getItem("auth_token");
        await axios.delete(`${url.value}/api/users/${id}`, {
            headers: {
                Authorization: `Bearer ${token}`,
            },
        });

        users.value = users.value.filter((u) => u.id !== id);
        Swal.fire("Berhasil", "Staff berhasil dihapus", "success");
    } catch (err) {
        Swal.fire("Gagal", "Tidak dapat menghapus user", "error");
    }
};
</script>

<style scoped>
* {
    font-family: "Nunito", sans-serif;
}

.search-box {
    border: 1px solid #ccc;
    height: 28px;
    font-size: 12px;
}

.datatable th,
.datatable td {
    font-size: 12px;
}
</style>
