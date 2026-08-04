import { ref, computed, onMounted } from "vue";
import Swal from "sweetalert2";
import { useRuntimeConfig, useNuxtApp } from "#imports";
import * as XLSX from "xlsx";
import { saveAs } from "file-saver";

/**
 * Single composable powering the Benang page (/pewarnaAlam) with three tabs:
 * Barang Masuk, Stok Benang, and Benang Keluar.
 */
export function useBenang() {
    const config = useRuntimeConfig();
    const url = ref(config.public.apiBase);
    const { $api } = useNuxtApp();

    // super admin (role id "1") — controls Aksi column on Barang Masuk
    const isSuperAdmin = computed(() => {
        if (typeof window === "undefined") return false;
        const roleId = localStorage.getItem("role") || sessionStorage.getItem("role");
        return roleId === "1";
    });

    const activeTab = ref("masuk"); // masuk | stok | keluar

    // ============================================================
    // Shared confirm popup (success / error) with optional retry
    // ============================================================
    const isConfirmOpen = ref(false);
    const confirmType = ref("success");
    const confirmTitle = ref("");
    const confirmSubtitle = ref("");
    const confirmButtonText = ref("Tutup");
    let confirmAction = null;

    const showConfirm = (type, title, subtitle, buttonText = "Tutup", action = null) => {
        confirmType.value = type;
        confirmTitle.value = title;
        confirmSubtitle.value = subtitle;
        confirmButtonText.value = buttonText;
        confirmAction = action;
        isConfirmOpen.value = true;
    };

    const closeConfirm = () => {
        isConfirmOpen.value = false;
        confirmAction = null;
    };

    const onConfirmButton = () => {
        const act = confirmAction;
        isConfirmOpen.value = false;
        confirmAction = null;
        if (typeof act === "function") act();
    };

    // ============================================================
    // Dropdown sources
    // ============================================================
    const jenisBenangList = ref([]); // { id, jenisbenang_nama }
    const pewarnaList = ref([]);     // { pewarna_id, pewarna_nama, pewarna_kode }
    const sumberWarnaList = ref([]); // ["Putih", ...]

    const loadDropdowns = async () => {
        // Settle each request independently so one failing endpoint can't
        // blank the other dropdowns (e.g. Jenis Benang staying empty just
        // because /api/pewarna or /api/benang-masuk/sumber-warna errored).
        const [jb, pw, sw] = await Promise.allSettled([
            $api.get(`${url.value}/api/jenisbenang`),
            $api.get(`${url.value}/api/pewarna`),
            $api.get(`${url.value}/api/benang-masuk/sumber-warna`),
        ]);

        if (jb.status === "fulfilled") jenisBenangList.value = jb.value.data.data || [];
        else console.error("Gagal memuat jenis benang:", jb.reason);

        if (pw.status === "fulfilled") pewarnaList.value = pw.value.data.data || [];
        else console.error("Gagal memuat pewarna:", pw.reason);

        if (sw.status === "fulfilled") sumberWarnaList.value = sw.value.data.data || [];
        else console.error("Gagal memuat sumber warna:", sw.reason);
    };

    // ============================================================
    // TAB 1 — Barang Masuk
    // ============================================================
    const masukData = ref([]);
    const masukSearch = ref("");
    const masukLoading = ref(false);
    const masukPage = ref(1);
    const masukPerPage = ref(10);

    const masukItemsPerPage = computed(() => {
        return masukPerPage.value === "all"
            ? masukData.value.length || 1
            : Number(masukPerPage.value);
    });

    const masukTotalPage = computed(() =>
        Math.ceil(masukData.value.length / masukItemsPerPage.value)
    );

    const masukPaginated = computed(() => {
        if (masukPerPage.value === "all") {
            return masukData.value;
        }

        const start = (masukPage.value - 1) * masukItemsPerPage.value;

        return masukData.value.slice(
            start,
            start + masukItemsPerPage.value
        );
    });

    const masukPaginatedPages = computed(() => {
        const total = masukTotalPage.value;
        const current = masukPage.value;

        if (total <= 7) {
            return Array.from({ length: total }, (_, i) => i + 1);
        }

        const pages = [];

        pages.push(1);

        if (current > 3) {
            pages.push("...");
        }

        const start = Math.max(2, current - 1);
        const end = Math.min(total - 1, current + 1);

        for (let i = start; i <= end; i++) {
            pages.push(i);
        }

        if (current < total - 2) {
            pages.push("...");
        }

        pages.push(total);

        return pages;
    });

    const getMasuk = async () => {
        masukLoading.value = true;
        try {
            const res = await $api.get(`${url.value}/api/benang-masuk`, {
                params: { search: masukSearch.value },
            });
            masukData.value = res.data.data || [];
        } catch (e) {
            console.error(e);
        } finally {
            masukLoading.value = false;
        }
    };

    // ---- Tambah Pewarna Alami ----
    const isPaOpen = ref(false);
    const paForm = ref({});
    const paErrors = ref({});
    const paSubmitting = ref(false);

    const openPa = () => {
        paForm.value = { warna: "", jenis_id: "", jumlah: "", sumber_warna: "" };
        paErrors.value = {};
        isPaOpen.value = true;
    };
    const closePa = () => { isPaOpen.value = false; };

    const validatePa = () => {
        const e = {};
        if (!String(paForm.value.warna).trim()) e.warna = "Data wajib diisi";
        if (!paForm.value.jenis_id) e.jenis_id = "Data wajib diisi";
        if (!paForm.value.jumlah || Number(paForm.value.jumlah) < 1) e.jumlah = "Data wajib diisi";
        if (!String(paForm.value.sumber_warna).trim()) e.sumber_warna = "Data wajib diisi";
        paErrors.value = e;
        return Object.keys(e).length === 0;
    };

    const submitPa = async () => {
        if (!validatePa()) return;
        paSubmitting.value = true;
        try {
            await $api.post(`${url.value}/api/benang-masuk/pewarna-alam`, {
                benang_masuk_warna: paForm.value.warna.trim(),
                benang_masuk_jenis_id: Number(paForm.value.jenis_id),
                benang_masuk_jumlah: Number(paForm.value.jumlah),
                benang_masuk_sumber_warna: paForm.value.sumber_warna,
            });
            closePa();
            showConfirm("success", "Data berhasil ditambahkan!", "Data pewarna alami berhasil ditambahkan ke sistem.");
            await Promise.all([getMasuk(), loadDropdowns()]);
        } catch (e) {
            closePa();
            showConfirm("error", "Data gagal ditambahkan!", "Data pewarna alam baru gagal disimpan ke sistem. Silakan coba kembali.");
        } finally {
            paSubmitting.value = false;
        }
    };

    // ---- Tambah Textile ----
    const isTxOpen = ref(false);
    const txForm = ref({});
    const txErrors = ref({});
    const txSubmitting = ref(false);

    const openTx = () => {
        txForm.value = { warna: "", jenis_id: "", jumlah: "" };
        txErrors.value = {};
        isTxOpen.value = true;
    };
    const closeTx = () => { isTxOpen.value = false; };

    const validateTx = () => {
        const e = {};
        if (!String(txForm.value.warna).trim()) e.warna = "Data wajib diisi";
        if (!txForm.value.jenis_id) e.jenis_id = "Data wajib diisi";
        if (!txForm.value.jumlah || Number(txForm.value.jumlah) < 1) e.jumlah = "Data wajib diisi";
        txErrors.value = e;
        return Object.keys(e).length === 0;
    };

    const submitTx = async () => {
        if (!validateTx()) return;
        txSubmitting.value = true;
        try {
            await $api.post(`${url.value}/api/benang-masuk/textile`, {
                benang_masuk_warna: txForm.value.warna.trim(),
                benang_masuk_jenis_id: Number(txForm.value.jenis_id),
                benang_masuk_jumlah: Number(txForm.value.jumlah),
            });
            closeTx();
            showConfirm("success", "Data berhasil ditambahkan!", "Data textile berhasil ditambahkan ke sistem.");
            await Promise.all([getMasuk(), loadDropdowns()]);
        } catch (e) {
            closeTx();
            showConfirm("error", "Data gagal ditambahkan!", "Data textile baru gagal disimpan ke sistem. Silakan coba kembali.");
        } finally {
            txSubmitting.value = false;
        }
    };

    // ---- Edit (superadmin) ----
    const isMasukEditOpen = ref(false);
    const masukEditForm = ref({});
    const masukEditErrors = ref({});
    const masukEditSubmitting = ref(false);

    const openMasukEdit = (row) => {
        masukEditForm.value = {
            id: row.benang_masuk_id,
            tipe: row.benang_masuk_tipe,
            warna: row.benang_masuk_warna,
            jenis_id: row.benang_masuk_jenis_id,
            jumlah: row.benang_masuk_jumlah,
            sumber_warna: row.benang_masuk_sumber_warna || "",
        };
        masukEditErrors.value = {};
        isMasukEditOpen.value = true;
    };
    const closeMasukEdit = () => { isMasukEditOpen.value = false; };

    const submitMasukEdit = async () => {
        const e = {};
        if (!String(masukEditForm.value.warna).trim()) e.warna = "Data wajib diisi";
        if (!masukEditForm.value.jenis_id) e.jenis_id = "Data wajib diisi";
        if (!masukEditForm.value.jumlah || Number(masukEditForm.value.jumlah) < 1) e.jumlah = "Data wajib diisi";
        masukEditErrors.value = e;
        if (Object.keys(e).length) return;

        masukEditSubmitting.value = true;
        try {
            await $api.put(`${url.value}/api/benang-masuk/${masukEditForm.value.id}`, {
                benang_masuk_warna: masukEditForm.value.warna.trim(),
                benang_masuk_jenis_id: Number(masukEditForm.value.jenis_id),
                benang_masuk_jumlah: Number(masukEditForm.value.jumlah),
                benang_masuk_sumber_warna: masukEditForm.value.sumber_warna || null,
            });
            closeMasukEdit();
            showConfirm("success", "Data berhasil diubah!", "Data benang masuk berhasil diperbarui.");
            await Promise.all([getMasuk(), loadDropdowns()]);
        } catch (err) {
            closeMasukEdit();
            showConfirm("error", "Data gagal diubah!", "Data benang masuk gagal diperbarui. Silakan coba kembali.");
        } finally {
            masukEditSubmitting.value = false;
        }
    };

    // ---- Delete masuk (superadmin) ----
    const isMasukDeleteOpen = ref(false);
    const pendingMasukDelete = ref(null);

    const askMasukDelete = (row) => {
        pendingMasukDelete.value = row;
        isMasukDeleteOpen.value = true;
    };
    const cancelMasukDelete = () => {
        isMasukDeleteOpen.value = false;
        pendingMasukDelete.value = null;
    };
    const confirmMasukDelete = async () => {
        const row = pendingMasukDelete.value;
        isMasukDeleteOpen.value = false;
        pendingMasukDelete.value = null;
        try {
            await $api.delete(`${url.value}/api/benang-masuk/${row.benang_masuk_id}`);
            showConfirm("success", "Data berhasil dihapus!", "Data benang masuk berhasil dihapus.");
            await Promise.all([getMasuk(), loadDropdowns()]);
        } catch (e) {
            showConfirm("error", "Data Gagal Dihapus!", "Data benang gagal dihapus dari sistem. Silakan coba kembali.", "Coba lagi", () => confirmMasukDeleteRetry(row));
        }
    };
    const confirmMasukDeleteRetry = async (row) => {
        try {
            await $api.delete(`${url.value}/api/benang-masuk/${row.benang_masuk_id}`);
            showConfirm("success", "Data berhasil dihapus!", "Data benang masuk berhasil dihapus.");
            await getMasuk();
        } catch (e) {
            showConfirm("error", "Data Gagal Dihapus!", "Data benang gagal dihapus dari sistem. Silakan coba kembali.", "Coba lagi", () => confirmMasukDeleteRetry(row));
        }
    };

    // ============================================================
    // TAB 2 — Stok Benang
    // ============================================================
    const stokData = ref([]);
    const stokLoading = ref(false);
    const stokFilter = ref("ALL"); // ALL | PEWARNA_ALAM | TEXTILE

    const stokPage = ref(1);
    const stokPerPage = ref(10);

    const stokItemsPerPage = computed(() => {
        return stokPerPage.value === "all"
            ? stokData.value.length || 1
            : Number(stokPerPage.value);
    });

    const stokTotalPage = computed(() =>
        Math.ceil(stokData.value.length / stokItemsPerPage.value)
    );

    const stokPaginated = computed(() => {
        if (stokPerPage.value === "all") {
            return stokData.value;
        }

        const start = (stokPage.value - 1) * stokItemsPerPage.value;

        return stokData.value.slice(
            start,
            start + stokItemsPerPage.value
        );
    });

    const stokPaginatedPages = computed(() => {
        const total = stokTotalPage.value;
        const current = stokPage.value;

        if (total <= 7) {
            return Array.from({ length: total }, (_, i) => i + 1);
        }

        const pages = [1];

        if (current > 3) pages.push("...");

        const start = Math.max(2, current - 1);
        const end = Math.min(total - 1, current + 1);

        for (let i = start; i <= end; i++) {
            pages.push(i);
        }

        if (current < total - 2) pages.push("...");

        pages.push(total);

        return pages;
    });

    const getStok = async () => {
        stokLoading.value = true;
        try {
            const res = await $api.get(`${url.value}/api/benang-masuk/stok`, {
                params: { tipe: stokFilter.value },
            });
            stokData.value = res.data.data || [];
        } catch (e) {
            console.error(e);
        } finally {
            stokLoading.value = false;
        }
    };

    const setStokFilter = async (val) => {
        stokFilter.value = val;
        await getStok();
    };

    // ============================================================
    // TAB 3 — Benang Keluar
    // ============================================================
    const keluarData = ref([]);
    const keluarSearch = ref("");
    const keluarLoading = ref(false);
    const today = new Date().toISOString().slice(0, 10);
    const startDate = ref(today);
    const endDate = ref(today);

    const keluarPage = ref(1);
    const keluarPerPage = ref(10);

    const keluarItemsPerPage = computed(() => {
        return keluarPerPage.value === "all"
            ? keluarData.value.length || 1
            : Number(keluarPerPage.value);
    });

    const keluarTotalPage = computed(() =>
        Math.ceil(keluarData.value.length / keluarItemsPerPage.value)
    );

    const keluarPaginated = computed(() => {

        if (keluarPerPage.value === "all") {
            return keluarData.value;
        }

        const start = (keluarPage.value - 1) * keluarItemsPerPage.value;

        return keluarData.value.slice(
            start,
            start + keluarItemsPerPage.value
        );
    });

    const keluarPaginatedPages = computed(() => {
        const total = keluarTotalPage.value;
        const current = keluarPage.value;

        if (total <= 7) {
            return Array.from({ length: total }, (_, i) => i + 1);
        }

        const pages = [1];

        if (current > 3)
            pages.push("...");
        const start = Math.max(2, current - 1);
        const end = Math.min(total - 1, current + 1);
        for (let i = start; i <= end; i++)
            pages.push(i);
        if (current < total - 2)
            pages.push("...");
        pages.push(total);
        return pages;
    });

    const getKeluar = async () => {
        keluarLoading.value = true;
        try {
            const res = await $api.get(`${url.value}/api/benang-keluar`, {
                params: { search: keluarSearch.value },
            });
            keluarData.value = res.data.data || [];
        } catch (e) {
            console.error(e);
        } finally {
            keluarLoading.value = false;
        }
    };

    // ---- Tambah Benang Keluar ----
    const isKeluarTambahOpen = ref(false);
    const keluarForm = ref({ nama_penenun: "" });
    const keluarItems = ref([]);
    const keluarErrors = ref({});
    const keluarSubmitting = ref(false);

    const emptyKeluarItem = () => ({
        _key: Date.now() + Math.random(),
        warna: "",
        tipe: "",
        jenis_id: "",
        jumlah: "",
        errWarna: "",
        errTipe: "",
        errJenis: "",
        errJumlah: "",
    });

    const openKeluarTambah = () => {
        keluarForm.value = { nama_penenun: "" };
        // default 3 rows
        keluarItems.value = [emptyKeluarItem(), emptyKeluarItem(), emptyKeluarItem()];
        keluarErrors.value = {};
        isKeluarTambahOpen.value = true;
    };
    const closeKeluarTambah = () => { isKeluarTambahOpen.value = false; };

    const addKeluarRow = () => { keluarItems.value.push(emptyKeluarItem()); };
    const removeKeluarRow = (idx) => {
        if (keluarItems.value.length <= 1) return; // last row: delete disabled
        keluarItems.value.splice(idx, 1);
    };

    const validateKeluar = () => {
        const errs = {};
        if (!String(keluarForm.value.nama_penenun).trim()) errs.nama_penenun = "Data wajib diisi";
        keluarItems.value.forEach((it) => {
            it.errWarna = String(it.warna).trim() ? "" : "Data wajib diisi";
            it.errTipe = it.tipe ? "" : "Data wajib diisi";
            it.errJenis = it.jenis_id ? "" : "Data wajib diisi";
            it.errJumlah = (it.jumlah && Number(it.jumlah) >= 1) ? "" : "Data wajib diisi";
        });
        keluarErrors.value = errs;
        const rowError = keluarItems.value.some((it) => it.errWarna || it.errTipe || it.errJenis || it.errJumlah);
        return Object.keys(errs).length === 0 && !rowError;
    };

    const submitKeluar = async () => {
        if (!validateKeluar()) return;
        keluarSubmitting.value = true;
        // snapshot raw form values so "Coba Lagi" can re-open with the same data
        payloadSnapshot = {
            nama_penenun: keluarForm.value.nama_penenun.trim(),
            items: keluarItems.value.map((it) => ({
                warna: it.warna, tipe: it.tipe, jenis_id: it.jenis_id, jumlah: it.jumlah,
            })),
        };
        try {
            const payload = {
                benang_keluar_nama_penenun: keluarForm.value.nama_penenun.trim(),
                items: keluarItems.value.map((it) => ({
                    warna: it.warna,
                    tipe: it.tipe,
                    jenis_id: Number(it.jenis_id),
                    jumlah: Number(it.jumlah),
                })),
            };
            await $api.post(`${url.value}/api/benang-keluar`, payload);
            closeKeluarTambah();
            showConfirm("success", "Data berhasil ditambahkan!", "Data benang keluar berhasil ditambahkan ke sistem.");
            await getKeluar();
        } catch (e) {
            closeKeluarTambah();
            showConfirm(
                "error",
                "Data gagal ditambahkan!",
                "Data benang keluar gagal disimpan ke sistem. Silakan coba kembali.",
                "Coba Lagi",
                () => { openKeluarTambahPrefilled(payloadSnapshot); }
            );
        } finally {
            keluarSubmitting.value = false;
        }
    };

    // keep a snapshot so "Coba Lagi" can re-open the form with the same data
    let payloadSnapshot = null;
    const openKeluarTambahPrefilled = () => {
        openKeluarTambah();
        if (payloadSnapshot) {
            keluarForm.value.nama_penenun = payloadSnapshot.nama_penenun;
            keluarItems.value = payloadSnapshot.items.map((it) => ({ ...emptyKeluarItem(), ...it }));
        }
    };

    // ---- Selesai ----
    const isSelesaiOpen = ref(false);
    const selesaiRecord = ref(null);
    const selesaiForm = ref({ catatan: "", foto: null, fotoCompressed: null });
    const selesaiErrors = ref({});
    const fotoPreview = ref(null);
    const selesaiSubmitting = ref(false);

    const openSelesai = (row) => {
        selesaiRecord.value = row;
        selesaiForm.value = { catatan: "", foto: null, fotoCompressed: null };
        selesaiErrors.value = {};
        fotoPreview.value = null;
        isSelesaiOpen.value = true;
    };
    const closeSelesai = () => {
        isSelesaiOpen.value = false;
        selesaiRecord.value = null;
    };

    const handleFotoUpload = async (event) => {
        const file = event.target.files[0];
        if (!file) return;
        selesaiForm.value.foto = file;
        fotoPreview.value = URL.createObjectURL(file);
        selesaiForm.value.fotoCompressed = await compressImage(file);
    };

    function compressImage(file) {
        return new Promise((resolve) => {
            const img = new Image();
            img.src = URL.createObjectURL(file);
            img.onload = () => {
                const canvas = document.createElement("canvas");
                const maxWidth = 1000;
                const scale = Math.min(1, maxWidth / img.width);
                canvas.width = img.width * scale;
                canvas.height = img.height * scale;
                const ctx = canvas.getContext("2d");
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                canvas.toBlob(
                    (blob) => resolve(new File([blob], `benang-${Date.now()}.jpg`, { type: "image/jpeg" })),
                    "image/jpeg",
                    0.75
                );
            };
        });
    }

    const submitSelesai = async () => {
        const e = {};
        if (!String(selesaiForm.value.catatan).trim()) e.catatan = "Data wajib diisi";
        if (!selesaiForm.value.foto) e.foto = "Data wajib diisi";
        selesaiErrors.value = e;
        if (Object.keys(e).length) return;

        selesaiSubmitting.value = true;
        try {
            const fd = new FormData();
            fd.append("benang_keluar_catatan", selesaiForm.value.catatan.trim());
            fd.append("benang_keluar_foto_hasil", selesaiForm.value.fotoCompressed || selesaiForm.value.foto);
            await $api.post(
                `${url.value}/api/benang-keluar/${selesaiRecord.value.benang_keluar_id}/selesai`,
                fd,
                { headers: { "Content-Type": "multipart/form-data" } }
            );
            closeSelesai();
            showConfirm("success", "Data berhasil diselesaikan!", "Data benang keluar berhasil diselesaikan.");
            await getKeluar();
        } catch (err) {
            closeSelesai();
            showConfirm("error", "Data gagal diselesaikan!", "Data benang keluar gagal disimpan ke sistem. Silakan coba kembali.");
        } finally {
            selesaiSubmitting.value = false;
        }
    };

    // ---- View (detail penyelesaian) ----
    const isKeluarViewOpen = ref(false);
    const keluarViewRecord = ref(null);
    const openKeluarView = (row) => {
        keluarViewRecord.value = row;
        isKeluarViewOpen.value = true;
    };
    const closeKeluarView = () => {
        isKeluarViewOpen.value = false;
        keluarViewRecord.value = null;
    };

    // ---- Delete keluar ----
    const isKeluarDeleteOpen = ref(false);
    const pendingKeluarDelete = ref(null);

    const askKeluarDelete = (row) => {
        pendingKeluarDelete.value = row;
        isKeluarDeleteOpen.value = true;
    };
    const cancelKeluarDelete = () => {
        isKeluarDeleteOpen.value = false;
        pendingKeluarDelete.value = null;
    };
    const doKeluarDelete = async (row) => {
        try {
            await $api.delete(`${url.value}/api/benang-keluar/${row.benang_keluar_id}`);
            showConfirm("success", "Data berhasil dihapus!", "Data benang keluar berhasil dihapus.");
            await getKeluar();
        } catch (e) {
            showConfirm("error", "Data Gagal Dihapus!", "Data benang gagal dihapus dari sistem. Silakan coba kembali.", "Coba lagi", () => doKeluarDelete(row));
        }
    };
    const confirmKeluarDelete = async () => {
        const row = pendingKeluarDelete.value;
        isKeluarDeleteOpen.value = false;
        pendingKeluarDelete.value = null;
        await doKeluarDelete(row);
    };

    // ---- Export ----
    const exportExcel = async () => {
        try {
            const res = await $api.get(`${url.value}/api/benang-keluar/export`, {
                params: { start_date: startDate.value, end_date: endDate.value },
            });
            const rows = res.data.data || [];
            if (!rows.length) {
                Swal.fire("Info", "Data tidak tersedia", "info");
                return;
            }
            const excelData = rows.map((r, i) => ({
                No: i + 1,
                Petugas: r.petugas || "-",
                "Tanggal Keluar": r.tanggal_keluar ? new Date(r.tanggal_keluar).toLocaleDateString("id-ID") : "-",
                "Tanggal Selesai": r.tanggal_selesai ? new Date(r.tanggal_selesai).toLocaleDateString("id-ID") : "-",
                "Nama Penenun": r.nama_penenun || "-",
                Warna: r.warna || "-",
                Tipe: r.tipe || "-",
                Jenis: r.jenis || "-",
                Jumlah: r.jumlah,
                Author: r.author || "-",
                Status: r.status,
            }));
            const ws = XLSX.utils.json_to_sheet(excelData);
            ws["!cols"] = [
                { wch: 5 }, { wch: 16 }, { wch: 16 }, { wch: 16 }, { wch: 18 },
                { wch: 20 }, { wch: 18 }, { wch: 18 }, { wch: 10 }, { wch: 16 }, { wch: 10 },
            ];
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Benang Keluar");
            const buffer = XLSX.write(wb, { bookType: "xlsx", type: "array" });
            saveAs(
                new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" }),
                `Benang_Keluar_${startDate.value}_${endDate.value}.xlsx`
            );
        } catch (e) {
            console.error(e);
            Swal.fire("Error", "Gagal export Excel", "error");
        }
    };

    // ============================================================
    // Tab switching + init
    // ============================================================
    const setTab = async (tab) => {
        activeTab.value = tab;
        if (tab === "masuk") await getMasuk();
        else if (tab === "stok") await getStok();
        else if (tab === "keluar") await getKeluar();
    };

    onMounted(async () => {
        await loadDropdowns();
        await getMasuk();
    });

    // ============================================================
    // Helpers
    // ============================================================
    const formatDate = (val) => {
        if (!val) return "-";
        return new Date(val).toLocaleDateString("id-ID", { day: "2-digit", month: "long", year: "numeric" });
    };
    const formatDateTime = (val) => {
        if (!val) return "-";
        return new Date(val).toLocaleString("id-ID", {
            day: "2-digit", month: "long", year: "numeric", hour: "2-digit", minute: "2-digit",
        });
    };

    return {
        // shared
        isSuperAdmin, activeTab, setTab,
        jenisBenangList, pewarnaList, sumberWarnaList,
        isConfirmOpen, confirmType, confirmTitle, confirmSubtitle, confirmButtonText,
        closeConfirm, onConfirmButton,
        formatDate, formatDateTime,

        // masuk
        masukData, masukSearch, masukLoading, getMasuk,
        isPaOpen, paForm, paErrors, paSubmitting, openPa, closePa, submitPa,
        isTxOpen, txForm, txErrors, txSubmitting, openTx, closeTx, submitTx,
        isMasukEditOpen, masukEditForm, masukEditErrors, masukEditSubmitting, openMasukEdit, closeMasukEdit, submitMasukEdit,
        isMasukDeleteOpen, pendingMasukDelete, askMasukDelete, cancelMasukDelete, confirmMasukDelete,

        // stok
        stokData, stokLoading, stokFilter, getStok, setStokFilter,

        // keluar
        keluarData, keluarSearch, keluarLoading, getKeluar, startDate, endDate, exportExcel,
        isKeluarTambahOpen, keluarForm, keluarItems, keluarErrors, keluarSubmitting,
        openKeluarTambah, closeKeluarTambah, addKeluarRow, removeKeluarRow, submitKeluar,
        isSelesaiOpen, selesaiRecord, selesaiForm, selesaiErrors, fotoPreview, selesaiSubmitting,
        openSelesai, closeSelesai, handleFotoUpload, submitSelesai,
        isKeluarViewOpen, keluarViewRecord, openKeluarView, closeKeluarView,
        isKeluarDeleteOpen, pendingKeluarDelete, askKeluarDelete, cancelKeluarDelete, confirmKeluarDelete,
        masukPage, masukPerPage, masukTotalPage, masukPaginated, masukPaginatedPages, stokPage, stokPerPage, stokPaginated, stokTotalPage,
        stokPaginatedPages, keluarPage, keluarPerPage, keluarPaginated, keluarTotalPage, keluarPaginatedPages,
    };
}
