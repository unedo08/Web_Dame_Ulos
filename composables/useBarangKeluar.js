import { ref, computed, onMounted } from "vue";
import Swal from "sweetalert2";
import { useRuntimeConfig, useNuxtApp } from "#imports";
import * as XLSX from "xlsx";
import { saveAs } from "file-saver";

export function useBarangKeluar() {
    const config = useRuntimeConfig();
    const url = ref(config.public.apiBase);
    const { $api } = useNuxtApp();

    const tableData = ref([]);
    const search = ref("");
    const isLoading = ref(false);

    // ---- Tambah modal ----
    const isTambahOpen = ref(false);
    const tambahForm = ref({ nama_outsource: "" });
    const tambahItems = ref([]);
    const tambahErrors = ref({});
    const isSubmitting = ref(false);

    // ---- Konfirmasi Pengambilan modal ----
    const isEditOpen = ref(false);
    const editRecord = ref(null);
    const editChecked = ref({});   // { detail_id: boolean }
    const editQty = ref({});       // { detail_id: number }
    const editError = ref("");
    const isSavingEdit = ref(false);

    // ---- View modal ----
    const isViewOpen = ref(false);
    const viewRecord = ref(null);

    // ---- Delete confirm ----
    const isDeleteConfirmOpen = ref(false);
    const pendingDeleteRecord = ref(null);

    // ---- Confirm popup (success/fail) ----
    const isConfirmOpen = ref(false);
    const confirmType = ref("success"); // "success" | "error"
    const confirmTitle = ref("");
    const confirmSubtitle = ref("");

    // ========================
    // Data fetching
    // ========================
    const getData = async () => {
        isLoading.value = true;
        try {
            const res = await $api.get(`${url.value}/api/barang-keluar`, {
                params: { search: search.value },
            });
            tableData.value = res.data.data || [];
        } catch (e) {
            console.error(e);
        } finally {
            isLoading.value = false;
        }
    };

    // Lookup code → barangentry info
    const lookupCode = async (kodeBarang) => {
        try {
            const res = await $api.get(
                `${url.value}/api/entrybarang/getDataByCode/${kodeBarang}`
            );
            if (res.data.code === 200) return res.data.data;
            return null;
        } catch {
            return null;
        }
    };

    onMounted(getData);

    // ========================
    // Computed
    // ========================
    const filteredData = computed(() => {
        if (!search.value) return tableData.value;
        const kw = search.value.toLowerCase();
        return tableData.value.filter((r) =>
            r.barang_keluar_nama_outsource?.toLowerCase().includes(kw)
        );
    });

    // ========================
    // Tambah Modal
    // ========================
    const openTambah = () => {
        tambahForm.value = { nama_outsource: "" };
        tambahItems.value = [];
        tambahErrors.value = {};
        isTambahOpen.value = true;
    };

    const closeTambah = () => {
        isTambahOpen.value = false;
    };

    const addItemRow = () => {
        tambahItems.value.push({
            _key: Date.now(),
            kode_barang: "",
            nama_ulos: "",
            code_id: null,
            jumlah: "",
            error: "",
            errorJumlah: "",
            loading: false,
        });
    };

    const removeItemRow = (idx) => {
        tambahItems.value.splice(idx, 1);
    };

    const validateCodeInput = async (idx) => {
        const item = tambahItems.value[idx];
        const kode = item.kode_barang.trim();
        if (!kode) {
            item.nama_ulos = "";
            item.code_id = null;
            item.error = "";
            return;
        }
        item.loading = true;
        item.error = "";
        const data = await lookupCode(kode);
        item.loading = false;
        if (data) {
            item.code_id = data.barangentry_code_id;
            item.nama_ulos = data.barangentry_nama || "";
            item.error = "";
        } else {
            item.code_id = null;
            item.nama_ulos = "";
            item.error = "Kode barang tidak tersedia";
        }
    };

    const validateTambah = () => {
        const errs = {};
        if (!tambahForm.value.nama_outsource.trim()) {
            errs.nama_outsource = "Data wajib diisi";
        }
        if (tambahItems.value.length === 0) {
            errs.items = "Tambahkan minimal 1 item ulos";
        }
        tambahItems.value.forEach((item) => {
            if (!item.kode_barang.trim()) {
                item.error = "Data wajib diisi";
            }
            if (!item.jumlah || Number(item.jumlah) < 1) {
                item.errorJumlah = "Data wajib diisi";
            } else {
                item.errorJumlah = "";
            }
        });
        tambahErrors.value = errs;
        const hasItemError = tambahItems.value.some((i) => i.error || i.errorJumlah);
        return Object.keys(errs).length === 0 && !hasItemError;
    };

    const submitTambah = async () => {
        if (!validateTambah()) return;
        isSubmitting.value = true;
        try {
            const payload = {
                barang_keluar_nama_outsource: tambahForm.value.nama_outsource.trim(),
                items: tambahItems.value.map((item) => ({
                    code_id: item.code_id,
                    kode_barang: item.kode_barang.trim(),
                    nama_ulos: item.nama_ulos,
                    jumlah: Number(item.jumlah),
                })),
            };
            await $api.post(`${url.value}/api/barang-keluar`, payload);
            closeTambah();
            showConfirm(
                "success",
                "Data berhasil ditambahkan!",
                "Data pengeluaran baru berhasil ditambahkan ke sistem."
            );
            await getData();
        } catch (e) {
            closeTambah();
            showConfirm(
                "error",
                "Data gagal ditambahkan!",
                "Data pengeluaran baru gagal disimpan ke sistem.\nSilakan coba kembali."
            );
        } finally {
            isSubmitting.value = false;
        }
    };

    // ========================
    // Edit (Konfirmasi Pengambilan) Modal
    // ========================
    const openEdit = (record) => {
        editRecord.value = record;
        editChecked.value = {};
        editQty.value = {};
        editError.value = "";
        record.details.forEach((d) => {
            editChecked.value[d.barang_keluar_detail_id] = false;
            editQty.value[d.barang_keluar_detail_id] = d.barang_keluar_detail_jumlah;
        });
        isEditOpen.value = true;
    };

    const closeEdit = () => {
        isEditOpen.value = false;
        editRecord.value = null;
    };

    const submitEdit = async () => {
        const selectedItems = Object.entries(editChecked.value)
            .filter(([, checked]) => checked)
            .map(([id]) => ({
                detail_id: Number(id),
                jumlah: Number(editQty.value[id]),
            }));

        if (selectedItems.length === 0) {
            editError.value = "Data wajib diisi";
            return;
        }

        editError.value = "";
        isSavingEdit.value = true;
        try {
            await $api.post(
                `${url.value}/api/barang-keluar/${editRecord.value.barang_keluar_id}/selesai`,
                { items: selectedItems }
            );
            closeEdit();
            showConfirm(
                "success",
                "Pengambilan Berhasil Dikonfirmasi!",
                "Data pengambilan barang telah berhasil disimpan"
            );
            await getData();
        } catch (e) {
            closeEdit();
            showConfirm(
                "error",
                "Data gagal disimpan!",
                "Transaksi gagal diselesaikan. Silakan coba kembali."
            );
        } finally {
            isSavingEdit.value = false;
        }
    };

    // ========================
    // Delete
    // ========================
    const deleteRecord = async (record) => {
        isDeleteConfirmOpen.value = true;
        pendingDeleteRecord.value = record;
    };

    const confirmDelete = async () => {
        const record = pendingDeleteRecord.value;
        isDeleteConfirmOpen.value = false;
        pendingDeleteRecord.value = null;
        try {
            await $api.delete(`${url.value}/api/barang-keluar/${record.barang_keluar_id}`);
            showConfirm("success", "Data berhasil dihapus!", "Data barang keluar berhasil dihapus dari sistem.");
            await getData();
        } catch (e) {
            showConfirm("error", "Data gagal dihapus!", "Gagal menghapus data. Silakan coba kembali.");
        }
    };

    const cancelDelete = () => {
        isDeleteConfirmOpen.value = false;
        pendingDeleteRecord.value = null;
    };

    // ========================
    // View Modal
    // ========================
    const openView = (record) => {
        viewRecord.value = record;
        isViewOpen.value = true;
    };

    const closeView = () => {
        isViewOpen.value = false;
        viewRecord.value = null;
    };

    // ========================
    // Confirm Popup
    // ========================
    const showConfirm = (type, title, subtitle) => {
        confirmType.value = type;
        confirmTitle.value = title;
        confirmSubtitle.value = subtitle;
        isConfirmOpen.value = true;
    };

    const closeConfirm = () => {
        isConfirmOpen.value = false;
    };

    // ========================
    // Export
    // ========================
    const exportExcel = async () => {
        try {
            const res = await $api.get(`${url.value}/api/barang-keluar/export`);
            const rows = res.data.data || [];
            if (!rows.length) {
                Swal.fire("Info", "Data tidak tersedia", "info");
                return;
            }

            const excelData = rows.map((r, i) => ({
                No: i + 1,
                "Tanggal Keluar": r.tanggal_keluar
                    ? new Date(r.tanggal_keluar).toLocaleDateString("id-ID")
                    : "-",
                Petugas: r.petugas || "-",
                "Nama Outsource": r.barang_keluar_nama_outsource || "-",
                "Kode Barang": r.barang_keluar_detail_kode_barang || "-",
                "Nama Ulos": r.barang_keluar_detail_nama_ulos || "-",
                "Jumlah Barang": r.barang_keluar_detail_jumlah,
                Status: r.barang_keluar_status,
                "Tanggal Masuk": r.tanggal_masuk
                    ? new Date(r.tanggal_masuk).toLocaleDateString("id-ID")
                    : "-",
                Author: r.author || "-",
            }));

            const ws = XLSX.utils.json_to_sheet(excelData);
            ws["!cols"] = [
                { wch: 5 }, { wch: 16 }, { wch: 18 }, { wch: 22 },
                { wch: 14 }, { wch: 22 }, { wch: 14 }, { wch: 10 },
                { wch: 16 }, { wch: 18 },
            ];

            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Barang Keluar");
            const buffer = XLSX.write(wb, { bookType: "xlsx", type: "array" });
            saveAs(
                new Blob([buffer], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                }),
                `Barang_Keluar_${new Date().toISOString().slice(0, 10)}.xlsx`
            );
        } catch (e) {
            console.error(e);
            Swal.fire("Error", "Gagal export Excel", "error");
        }
    };

    // ========================
    // Helpers
    // ========================
    const formatDate = (val) => {
        if (!val) return "-";
        return new Date(val).toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "long",
            year: "numeric",
        });
    };

    const formatDateTime = (val) => {
        if (!val) return "-";
        return new Date(val).toLocaleString("id-ID", {
            day: "2-digit",
            month: "long",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        });
    };

    return {
        // state
        tableData,
        search,
        isLoading,
        filteredData,
        // tambah
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
        // edit
        isEditOpen,
        editRecord,
        editChecked,
        editQty,
        editError,
        isSavingEdit,
        openEdit,
        closeEdit,
        submitEdit,
        // view
        isViewOpen,
        viewRecord,
        openView,
        closeView,
        // delete
        isDeleteConfirmOpen,
        pendingDeleteRecord,
        deleteRecord,
        confirmDelete,
        cancelDelete,
        // confirm
        isConfirmOpen,
        confirmType,
        confirmTitle,
        confirmSubtitle,
        closeConfirm,
        // export
        exportExcel,
        // helpers
        formatDate,
        formatDateTime,
        getData,
    };
}
