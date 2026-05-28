import { ref, computed, watch } from "vue";
import Swal from "sweetalert2";
import { useNuxtApp, useRuntimeConfig } from "#imports";

export function useDivisi() {

    const { $api } = useNuxtApp();
    const config = useRuntimeConfig();
    const baseUrl = config.public.apiBase;

    const divisiData = ref([]);
    const search = ref("");
    const currentPage = ref(1);
    const itemsPerPage = ref(10);

    const isModalOpen = ref(false);
    const isEditMode = ref(false);
    const isLoading = ref(false);

    const form = ref({
        divisi_id: null,
        divisi_nama: "",
        divisi_status: 1,
    });

    async function fetchData() {

        try {

            const { data } = await $api.get(`${baseUrl}/api/divisi/all`);

            divisiData.value = data.data ?? [];

        } catch (err) {

            console.error("Gagal fetch data divisi:", err);

            Swal.fire({
                icon: "error",
                title: "Gagal",
                text: "Gagal mengambil data divisi",
            });
        }
    }

    const filteredData = computed(() => {

        const q = search.value.toLowerCase().trim();

        if (!q) return divisiData.value;

        return divisiData.value.filter((item) =>
            item.divisi_nama?.toLowerCase().includes(q)
        );
    });

    const totalItems = computed(() => filteredData.value.length);

    const totalPages = computed(() =>
        Math.max(1, Math.ceil(totalItems.value / itemsPerPage.value))
    );

    const paginatedData = computed(() => {

        const start = (currentPage.value - 1) * itemsPerPage.value;

        return filteredData.value.slice(start, start + itemsPerPage.value);
    });

    const paginatedPages = computed(() => {

        const total = totalPages.value;
        const current = currentPage.value;

        if (total <= 5) return Array.from({ length: total }, (_, i) => i + 1);

        if (current <= 3) return [1, 2, 3, "...", total];

        if (current >= total - 2)
            return [1, "...", total - 2, total - 1, total];

        return [1, "...", current - 1, current, current + 1, "...", total];
    });

    function openModal(data = null) {

        if (data) {

            isEditMode.value = true;

            form.value = {
                divisi_id: data.divisi_id,
                divisi_nama: data.divisi_nama,
                divisi_status: data.divisi_status,
            };

        } else {

            isEditMode.value = false;

            form.value = {
                divisi_id: null,
                divisi_nama: "",
                divisi_status: 1,
            };
        }

        isModalOpen.value = true;
    }

    function closeModal() {
        isModalOpen.value = false;
    }

    async function saveData() {

        if (!form.value.divisi_nama.trim()) return false;

        isLoading.value = true;

        try {

            const payload = {
                divisi_nama: form.value.divisi_nama,
                divisi_status: form.value.divisi_status,
            };

            if (isEditMode.value) {

                await $api.put(
                    `${baseUrl}/api/divisi/${form.value.divisi_id}`,
                    payload
                );

            } else {

                await $api.post(
                    `${baseUrl}/api/divisi`,
                    payload
                );
            }

            closeModal();

            currentPage.value = 1;

            await fetchData();

            return true;

        } catch (err) {

            console.error("Gagal simpan data divisi:", err);

            Swal.fire({
                icon: "error",
                title: "Gagal",
                text: isEditMode.value
                    ? "Gagal mengubah data divisi"
                    : "Gagal menambahkan data divisi",
            });

            return false;

        } finally {

            isLoading.value = false;
        }
    }

    async function deleteItem(item) {

        try {

            await $api.delete(
                `${baseUrl}/api/divisi/${item.divisi_id}`
            );

            await fetchData();

            if (currentPage.value > totalPages.value) {
                currentPage.value = totalPages.value;
            }

        } catch (err) {

            console.error("Gagal hapus data divisi:", err);

            Swal.fire({
                icon: "error",
                title: "Gagal",
                text: "Gagal menghapus data divisi",
            });
        }
    }

    function formatDate(dateStr) {

        if (!dateStr) return "-";

        const d = new Date(dateStr);

        const tanggal = d.toLocaleDateString("id-ID", {
            day: "numeric",
            month: "long",
            year: "numeric",
        });

        const jam = d.toLocaleTimeString("id-ID", {
            hour: "2-digit",
            minute: "2-digit",
            hour12: false,
        }).replace(":", ".");

        return `${tanggal}, ${jam} WIB`;
    }

    function statusChipClass(status) {
        return status == 1 ? "chip-aktif" : "chip-nonaktif";
    }

    function statusLabel(status) {
        return status == 1 ? "Aktif" : "Tidak Aktif";
    }

    function getDisplayDate(item) {
        return item.updated_at && item.updated_at !== item.created_at
            ? item.updated_at
            : item.created_at;
    }

    watch(search, () => {
        currentPage.value = 1;
    });

    return {
        divisiData,
        search,
        currentPage,
        itemsPerPage,
        isModalOpen,
        isEditMode,
        isLoading,
        form,
        filteredData,
        paginatedData,
        totalItems,
        totalPages,
        paginatedPages,
        fetchData,
        openModal,
        closeModal,
        saveData,
        deleteItem,
        formatDate,
        statusChipClass,
        statusLabel,
        getDisplayDate,
    };
}