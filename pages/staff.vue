<template>
    <div>
        <title>Staff</title>
        <div class="judul text-xl font-semibold mb-4">Manajemen Staff</div>
        <div class="flex items-center justify-between pt-2">
            <input class="search-box mb-4 rounded-md" v-model="searchQuery" type="text"
                placeholder="Search staff..." />
            <button class="btn-add bg-blue-500 text-white rounded-md hover:bg-blue-600 w-[104px] h-[25px]"
                @click="openAddModal">
                + Tambah Staff
            </button>
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
                            @click="openEditModal(user)">
                            Edit
                        </button>

                        <button class="px-2 py-1 bg-red-500 text-white rounded text-xs hover:bg-red-600"
                            @click="deleteUser(user.id, user.name)">
                            Delete
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="flex justify-between items-center mt-8 mb-4 text-xs">
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

        <div v-if="isEditModal" class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
            <div class="bg-white p-6 rounded-lg w-[420px]">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Edit User</h3>
                    <button @click="closeEditModal">✕</button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Name</label>
                        <input v-model="editForm.name" type="text" class="w-full border rounded p-2 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input v-model="editForm.email" type="email" disabled
                            class="w-full border rounded p-2 text-sm bg-gray-100" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">User Role</label>
                        <select v-model="editForm.role_id" class="w-full border rounded p-2 text-sm">
                            <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button class="px-4 py-2 bg-gray-300 rounded" @click="closeEditModal">Close</button>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                        @click="submitUpdateUser">
                        Save
                    </button>
                </div>
            </div>
        </div>

        <div v-if="isAddModal" class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
            <div class="bg-white p-6 rounded-lg w-[420px]">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Tambah User Baru</h3>
                    <button @click="closeAddModal">✕</button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Nama <span class="text-red-500">*</span>
                        </label>
                        <input v-model="addForm.name" type="text" class="w-full border rounded p-2 text-sm"
                            placeholder="Masukkan nama" />
                        <p v-if="errors.name" class="text-red-500 text-xs mt-1">
                            {{ errors.name }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input v-model="addForm.email" type="email" class="w-full border rounded p-2 text-sm"
                            placeholder="Masukkan email" />
                        <p v-if="errors.email" class="text-red-500 text-xs mt-1">
                            {{ errors.email }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <input v-model="addForm.password" type="password" class="w-full border rounded p-2 text-sm"
                            placeholder="Masukkan password" />
                        <p v-if="errors.password" class="text-red-500 text-xs mt-1">
                            {{ errors.password }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Role <span class="text-red-500">*</span>
                        </label>
                        <select v-model="addForm.role_id" class="w-full border rounded p-2 text-sm">
                            <option value="">Pilih Role</option>
                            <option v-for="r in roles" :key="r.id" :value="r.id">
                                {{ r.name }}
                            </option>
                        </select>
                        <p v-if="errors.role_id" class="text-red-500 text-xs mt-1">
                            {{ errors.role_id }}
                        </p>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button class="px-4 py-2 bg-gray-300 rounded" @click="closeAddModal">
                        Batal
                    </button>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" @click="submitAddUser">
                        Tambah
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import Swal from "sweetalert2";
import { useRuntimeConfig } from "#imports";
const { $api } = useNuxtApp();

const url = ref("");
const searchQuery = ref("");
const users = ref([]);
const itemsPerPage = ref(10);
const currentPage = ref(1);
const isPasswordModal = ref(false);
const selectedUser = ref(null);
const currentPassword = ref("");
const newPassword = ref("");
const isAddModal = ref(false);
const errors = ref({});
const isEditModal = ref(false);

onMounted(() => {
    url.value = useRuntimeConfig().public.apiBase;
    fetchUsers();
});

const addForm = ref({
    name: "",
    email: "",
    password: "",
    role_id: "",
});

const editForm = ref({
  id: "",
  name: "",
  email: "",
  role_id: "",
});

const fetchUsers = async () => {
    try {
        const res = await $api.get(`${url.value}/api/getUser`);
        users.value = res.data.data || res.data;
    } catch (err) {
        console.error(err);
    }
};

const openEditModal = (user) => {
  editForm.value = {
    id: user.id,
    name: user.name,
    email: user.email,
    role_id: user.role?.id || "",
  };
  isEditModal.value = true;
};

const closeEditModal = () => {
  isEditModal.value = false;
};

const submitUpdateUser = async () => {
  try {
    await $api.put(
      `${url.value}/api/user/${editForm.value.id}`,
      {
        name: editForm.value.name,
        email: editForm.value.email,
        role_id: editForm.value.role_id
      });

    Swal.fire("Berhasil", "User berhasil diperbarui", "success");
    isEditModal.value = false;
    fetchUsers();
  } catch (err) {
    Swal.fire(
      "Gagal",
      err.response?.data?.message || "Update user gagal",
      "error"
    );
  }
};

const filteredUsers = computed(() => {
    const q = searchQuery.value.toLowerCase();

    return [...users.value]
        .filter(
            (u) =>
                u.name?.toLowerCase().includes(q) ||
                u.email?.toLowerCase().includes(q) ||
                u.role?.name?.toLowerCase().includes(q)
        )
        .sort((a, b) => {
            return new Date(b.created_at) - new Date(a.created_at);
        });
});

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
        await $api.post(
            `${url.value}/api/user/update-password`,
            {
                current_password: currentPassword.value,
                new_password: newPassword.value,
            });

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

const roles = ref([
    { id: 1, name: "Super Admin" },
    { id: 2, name: "Admin" },
    { id: 3, name: "Marketing" },
    { id: 4, name: "Quality Control" },
    { id: 5, name: "Packaging" },
    { id: 6, name: "Pewarna Alam" },
    { id: 7, name: "Social Media" },
]);

const openAddModal = () => {
    addForm.value = {
        name: "",
        email: "",
        password: "",
        role_id: "",
    };
    isAddModal.value = true;
};

const closeAddModal = () => {
    isAddModal.value = false;
};

const submitAddUser = async () => {
    resetErrors();

    const { name, email, password, role_id } = addForm.value;

    if (!name) setError("name", "Nama wajib diisi");
    if (!email) setError("email", "Email wajib diisi");
    if (!password) setError("password", "Password wajib diisi");
    if (!role_id) setError("role_id", "Role wajib dipilih");

    if (Object.keys(errors.value).length > 0) {
        Swal.fire("Gagal", "Mohon lengkapi semua data", "warning");
        return;
    }

    try {
        await $api.post(
            `${url.value}/api/register`,
            {
                name,
                email,
                password,
                password_confirmation: password,
                role_id,
            });

        Swal.fire("Berhasil", "Staff berhasil ditambahkan", "success");
        closeAddModal();
        fetchUsers();
    } catch (err) {
        Swal.fire(
            "Gagal",
            err.response?.data?.message || "Gagal menambah user",
            "error"
        );
    }
};

const resetErrors = () => {
    errors.value = {};
};

const setError = (field, message) => {
    errors.value[field] = message;
};

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
        
        await $api.delete(`${url.value}/api/users/${id}`);

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
  padding: 10px;
  width: 385px;
  height: 34px;
}

.datatable {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.datatable th,
.datatable td {
    padding: 10px;
    text-align: left;
    font-size: 12px;
}

.datatable th {
    background-color: #f4f4f4;
}

.btn-add {
    background-color: #3d8bfd;
    color: white;
    border-radius: 5px;
    cursor: pointer;
    font-size: 12px;
}

.btn-add:hover {
    background-color: #3d8bfd;
}
</style>
