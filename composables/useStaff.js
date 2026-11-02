import { ref, computed, onMounted } from "vue";
import Swal from "sweetalert2";

export function useStaff() {
    const { $api } = useNuxtApp();

    // =====================================================
    // STATE
    // =====================================================

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
    const isEditModal = ref(false);

    const errors = ref({});

    // =====================================================
    // FORM TAMBAH STAFF
    // =====================================================

    const addForm = ref({
        name: "",
        email: "",
        password: "",
        role_id: "",
    });

    // =====================================================
    // FORM EDIT STAFF
    // =====================================================

    const editForm = ref({
        id: "",
        name: "",
        email: "",
        role_id: "",
    });

    // =====================================================
    // ON MOUNTED
    // =====================================================

    onMounted(() => {
        url.value = useRuntimeConfig().public.apiBase;
        fetchUsers();
    });

    // =====================================================
    // FETCH USERS
    // =====================================================

    const fetchUsers = async () => {
        try {
            const res = await $api.get(
                `${url.value}/api/getUser`
            );

            users.value = res.data.data || res.data;

        } catch (err) {
            console.error(err);
        }
    };

    // =====================================================
    // EDIT USER
    // =====================================================

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
                }
            );

            Swal.fire(
                "Berhasil",
                "User berhasil diperbarui",
                "success"
            );

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

    // =====================================================
    // SEARCH USER
    // =====================================================

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

    // =====================================================
    // PAGINATION
    // =====================================================

    const pagination = computed(() => {
        const start =
            (currentPage.value - 1) *
            Number(itemsPerPage.value);

        return filteredUsers.value.slice(
            start,
            start + Number(itemsPerPage.value)
        );
    });

    const totalPages = computed(() => {
        return Math.max(
            1,
            Math.ceil(
                filteredUsers.value.length /
                Number(itemsPerPage.value)
            )
        );
    });

    const startItem = computed(() => {
        if (filteredUsers.value.length === 0) {
            return 0;
        }

        return (
            (currentPage.value - 1) *
            Number(itemsPerPage.value)
        ) + 1;
    });

    const endItem = computed(() => {
        return Math.min(
            currentPage.value *
            Number(itemsPerPage.value),
            filteredUsers.value.length
        );
    });

    const paginatedPages = computed(() => {
        const total = totalPages.value;
        const current = currentPage.value;

        if (total <= 5) {
            return Array.from(
                { length: total },
                (_, i) => i + 1
            );
        }

        if (current <= 3) {
            return [1, 2, 3, "...", total];
        }

        if (current >= total - 2) {
            return [
                1,
                "...",
                total - 2,
                total - 1,
                total
            ];
        }

        return [
            1,
            "...",
            current - 1,
            current,
            current + 1,
            "...",
            total
        ];
    });

    // =====================================================
    // PASSWORD
    // =====================================================

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
        if (
            !currentPassword.value ||
            !newPassword.value
        ) {
            Swal.fire(
                "Gagal",
                "Semua field wajib diisi",
                "warning"
            );

            return;
        }

        try {
            await $api.post(
                `${url.value}/api/user/update-password`,
                {
                    current_password: currentPassword.value,
                    new_password: newPassword.value,
                }
            );

            Swal.fire(
                "Berhasil",
                "Password berhasil diperbarui",
                "success"
            );

            closePasswordModal();

        } catch (err) {
            Swal.fire(
                "Gagal",
                err.response?.data?.message ||
                "Update password gagal",
                "error"
            );
        }
    };

    // =====================================================
    // ROLES
    // =====================================================

    const roles = ref([
        { id: 1, name: "Super Admin" },
        { id: 2, name: "Admin" },
        { id: 3, name: "Marketing" },
        { id: 4, name: "Quality Control" },
        { id: 5, name: "Packaging" },
        { id: 6, name: "Pewarna Alam" },
        { id: 7, name: "Social Media" },
    ]);

    // =====================================================
    // TAMBAH STAFF
    // =====================================================

    const openAddModal = () => {
        addForm.value = {
            name: "",
            email: "",
            password: "",
            role_id: "",
        };

        resetErrors();

        isAddModal.value = true;
    };

    const closeAddModal = () => {
        isAddModal.value = false;
    };

    const submitAddUser = async () => {
        resetErrors();

        const {
            name,
            email,
            password,
            role_id
        } = addForm.value;

        // VALIDASI

        if (!name) {
            setError("name", "Nama wajib diisi");
        }

        if (!email) {
            setError("email", "Email wajib diisi");
        }

        if (!password) {
            setError("password", "Password wajib diisi");
        }

        if (!role_id) {
            setError("role_id", "Role wajib dipilih");
        }

        if (Object.keys(errors.value).length > 0) {
            Swal.fire(
                "Gagal",
                "Mohon lengkapi semua data",
                "warning"
            );

            return;
        }

        // SUBMIT API

        try {
            await $api.post(
                `${url.value}/api/register`,
                {
                    name,
                    email,
                    password,
                    password_confirmation: password,
                    role_id,
                }
            );

            Swal.fire(
                "Berhasil",
                "Staff berhasil ditambahkan",
                "success"
            );

            closeAddModal();

            fetchUsers();

        } catch (err) {
            Swal.fire(
                "Gagal",
                err.response?.data?.message ||
                "Gagal menambah user",
                "error"
            );
        }
    };

    // =====================================================
    // VALIDASI ERROR
    // =====================================================

    const resetErrors = () => {
        errors.value = {};
    };

    const setError = (field, message) => {
        errors.value[field] = message;
    };

    // =====================================================
    // DELETE USER
    // =====================================================

    const deleteUser = async (id, name) => {
        const confirm = await Swal.fire({
            title: "Hapus Staff?",
            text: `Yakin ingin menghapus ${name}?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, Hapus",
            cancelButtonText: "Batal",
            reverseButtons: true,
        });

        if (!confirm.isConfirmed) {
            return;
        }

        try {
            await $api.delete(
                `${url.value}/api/users/${id}`
            );

            users.value = users.value.filter(
                (u) => u.id !== id
            );

            Swal.fire(
                "Berhasil",
                "Staff berhasil dihapus",
                "success"
            );

        } catch (err) {
            Swal.fire(
                "Gagal",
                "Tidak dapat menghapus user",
                "error"
            );
        }
    };

    // =====================================================
    // RETURN
    // =====================================================

    return {
        // STATE
        searchQuery,
        users,
        itemsPerPage,
        currentPage,

        isPasswordModal,
        selectedUser,
        currentPassword,
        newPassword,

        isAddModal,
        isEditModal,
        errors,

        // FORM
        addForm,
        editForm,

        // FETCH
        fetchUsers,

        // EDIT
        openEditModal,
        closeEditModal,
        submitUpdateUser,

        // SEARCH
        filteredUsers,

        // PAGINATION
        pagination,
        totalPages,
        startItem,
        endItem,
        paginatedPages,

        // PASSWORD
        openPasswordModal,
        closePasswordModal,
        updatePassword,

        // ROLES
        roles,

        // ADD
        openAddModal,
        closeAddModal,
        submitAddUser,

        // VALIDATION
        resetErrors,
        setError,

        // DELETE
        deleteUser
    };
}