import { ref, computed, onMounted, nextTick, watch } from "vue";
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";
import * as XLSX from "xlsx";
import { useRouter } from "vue-router";

export function useEntry() {
    const { $api } = useNuxtApp();
    const router = useRouter();

    const modalOpen = ref(false);
    const modalType = ref("desc");
    const showModalAdd = ref(false);
    const showModalAddSize = ref(false);
    const selectedBarang = ref({});
    const url = ref("");
    const showModalEditStock = ref(false);
    const editJumlah = ref(1);

    const showEditModal = ref(false);
    const showEditDesc = ref(false);
    const showEditSize = ref(false);
    const showEditPO = ref(false);
    const selectedId = ref(null);
    const showSendModal = ref(false);

    const showRiwayatModal = ref(false);
    const riwayatLoading = ref(false);
    const riwayatData = ref([]);

    const barangDatabase = ref([]);
    const listBarang = ref([]);
    const priceTagData = ref([]);

    const showModalSearch = ref(false);
    const searchCode = ref("");
    const filteredBarang = ref([]);
    const isSearchActive = ref(false);
    const currentPage = ref(1);
    const itemsPerPage = ref(10);
    const printContent = ref(null);
    const activeTab = ref("wait");
    const searchQuery = ref("");
    const barangMap = ref({});
    const isSubmitting = ref(false);
    const isSubmittingSize = ref(false);
    const pengirimanStatus = ref("");
    const inputKodeDesc = ref(null);
    const inputKodeSize = ref(null);
    const selectedPreOrderId = ref(null);

    const {
        activeDropdown,
        closeActionDropdown,
        setActionDropdownMenu,
        toggleActionDropdown,
    } = useActionDropdown();

    onMounted(async () => {
        const config = useRuntimeConfig();
        url.value = config.public.apiBase;

        try {
            const response = await $api.get(`${url.value}/api/codebarang`);
            barangDatabase.value = response.data;
            await getListBarangTemp();
        } catch (error) {
            console.error("Gagal mengambil data barang: ", error);
        }
    });

    function formatTanggal(tanggal) {
        const date = new Date(tanggal);

        return new Intl.DateTimeFormat("id-ID", {
            day: "2-digit",
            month: "long",
            year: "numeric",
        }).format(date);
    }

    async function openSendModal(id) {
        selectedId.value = id;

        const result = await Swal.fire({
            title: "Konfirmasi Pengiriman",
            text: "Pilih metode pengiriman",
            icon: "warning",
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: "DIKIRIM",
            denyButtonText: "DIJEMPUT",
            confirmButtonColor: "#3B82F6",
            denyButtonColor: "#FBBF24",
            cancelButtonColor: "#9CA3AF",
        });

        if (result.isConfirmed) {
            pengirimanStatus.value = "Dikirim";
            showSendModal.value = true;
        }

        if (result.isDenied) {
            pengirimanStatus.value = "Dijemput";

            const pengiriman_id = await processOrderDijemput(
                selectedId.value
            );

            if (!pengiriman_id) return;

            await updatePengiriman(
                pengiriman_id,
                "-",
                "Dijemput"
            );

            Swal.fire({
                icon: "success",
                title: "Penjemputan Berhasil",
                text: "Barang siap dijemput oleh pembeli",
                timer: 2000,
                showConfirmButton: false,
            });
        }
    }

    async function processOrderDijemput(barangentry_id) {
        try {
            const res = await $api.get(
                `${url.value}/api/pre-order-barang/preOrderEntry/${barangentry_id}`
            );

            const po = res.data.data;
            let transaksi_id = po.preOrderBarang_transaksi_id;

            if (!transaksi_id) {
                const payloadTransaksi = {
                    transaksi_nama_customer:
                        po.preOrderBarang_nama_akun || "-",
                    transaksi_nomor_telepon:
                        po.preOrderBarang_no_telepon || "",
                    transaksi_jumlah_barang: 1,
                    transaksi_total_harga: parseInt(
                        po.preOrderBarang_total_pembayaran || 0
                    ),
                    transaksi_cara_bayar:
                        po.preOrderBarang_cara_bayar || "Cash",
                    transaksi_tipe: "Pre Order",
                    transaksi_status: "Pre Order",
                    transaksi_catatan: "",
                };

                const { data } = await $api.post(
                    `${url.value}/api/transaksi`,
                    payloadTransaksi
                );

                transaksi_id = data.data.transaksi_id;

                const barang = await $api.get(
                    `${url.value}/api/entrybarang/${barangentry_id}`
                );

                const code = await $api.get(
                    `${url.value}/api/codebarang/${barang.data.data.barangentry_code_id}`
                );

                const { data: barangResponse } = await $api.get(
                    `${url.value}/api/entrybarang/getDataByCode/${code.data.code_nama}`
                );

                const barangData = barangResponse.data;

                const detailPayload = {
                    transaksidetail_transaksi_id: transaksi_id,
                    transaksidetail_barang_id: barangData.barangentry_id,
                    transaksidetail_jumlah_barang: 1,
                    transaksidetail_harga_barang: Number(
                        barangData.barangentry_harga_net
                    ),
                    transaksidetail_status_penjualan: 0,
                };

                await $api.post(
                    `${url.value}/api/transaksi-detail`,
                    detailPayload
                );
            }

            const pengirimanPayload = {
                pengirimanBarang_transaksi_id: transaksi_id,
                pengirimanBarang_nama_penerima:
                    po.preOrderBarang_nama_akun || "-",
                pengirimanBarang_akun_penerima:
                    po.preOrderBarang_nama_akun || "-",
                pengirimanBarang_no_telepon:
                    po.preOrderBarang_no_telepon || "",
                pengirimanBarang_harga_kirim_barang: 0,
                pengirimanBarang_jenis_pengiriman_barang: "Dijemput",
                pengirimanBarang_alamat_pengiriman_barang: "-",
                pengirimanBarang_catatan: "",
                pengirimanBarang_status: "Dijemput",
            };

            const resPengiriman = await $api.post(
                `${url.value}/api/pengiriman-barang`,
                pengirimanPayload
            );

            const pengiriman_id =
                resPengiriman.data.data.pengiriman.pengirimanBarang_id;

            await $api.post(
                `${url.value}/api/transaksi-detail/updateStatusPenjualan/${transaksi_id}`,
                {}
            );

            await getListBarangTemp();

            return pengiriman_id;

        } catch (err) {
            Swal.fire({
                title: "Gagal!",
                text: "Silahkan lengkapi preorder",
                icon: "error",
                timer: 3000,
            });
        }
    }

    async function handleSend(data) {
        const pengiriman_id = await sendOrder(selectedId.value, data);

        if (!pengiriman_id) return;

        await updatePengiriman(
            pengiriman_id,
            data.nama_akun,
            pengirimanStatus.value
        );

        showSendModal.value = false;
    }

    async function updatePengiriman(pengiriman_id, namaAkun, status) {
        await $api.put(
            `${url.value}/api/pengiriman-barang/${pengiriman_id}`,
            {
                pengirimanBarang_nama_penerima: namaAkun,
                pengirimanBarang_status: status,
            }
        );

        Swal.fire({
            icon: "success",
            title: "Berhasil",
            text: `Barang akan ${status.toUpperCase()}`,
            timer: 2000,
            showConfirmButton: false,
        });

        await getListBarangTemp();
    }

    const openModalEditBarang = (id) => {
        selectedId.value = id;
        showEditModal.value = true;
    };

    const openModalEditPO = (barangEntryId, preOrderId) => {
        selectedId.value = barangEntryId;
        selectedPreOrderId.value = preOrderId;
        showEditPO.value = true;
    };

    const openModalDesc = (id) => {
        selectedId.value = id;
        showEditDesc.value = true;
    };

    const openModalSize = (id) => {
        selectedId.value = id;
        showEditSize.value = true;
    };

    const loadData = async () => {
        try {
            await getListBarangTemp();
        } catch (err) {
            console.error("Gagal me-reload data:", err);
        }
    };

    const formatRupiah2 = (value) => {
        if (!value && value !== 0) return "";

        const number = parseInt(
            value.toString().replace(/\D/g, ""),
            10
        );

        return number.toLocaleString("id-ID");
    };

    function formatUkuran(ulos, mandar) {
        const u = ulos ? `${ulos} cm` : "-";
        const m = mandar ? `${mandar} cm` : "-";

        return `${u} x ${m}`;
    }

    const updatePriceTag = (val) => {
        const numericValue =
            parseInt(val.replace(/\D/g, ""), 10) || 0;

        selectedBarang.value.barangentry_price_tag = numericValue;
    };

    const updateModal = (val) => {
        const numericValue =
            parseInt(val.replace(/\D/g, ""), 10) || 0;

        selectedBarang.value.barangentry_modal = numericValue;
    };

    const updateHargaNet = (val) => {
        const numericValue =
            parseInt(val.replace(/\D/g, ""), 10) || 0;

        selectedBarang.value.barangentry_harga_net = numericValue;
    };

    const fetchCodeBarang = async (data) => {
        try {
            const ids = [
                ...new Set(data.map((item) => item.barangentry_id))
            ];

            const requests = ids.map((id) =>
                $api.get(`${url.value}/api/entrybarang/${id}`)
            );

            const responses = await Promise.all(requests);

            const codeBarangPromises = responses.map((res) =>
                $api.get(
                    `${url.value}/api/codebarang/${res.data.data.barangentry_code_id}`
                )
            );

            const codeBarangResults = await Promise.all(
                codeBarangPromises
            );

            codeBarangResults.forEach((res, i) => {
                barangMap.value[ids[i]] = res.data.code_nama;
            });

        } catch (error) {
            console.error("Data gagal diambil", error);
        }
    };

    async function getListBarangTemp() {
        try {
            let endpoint = "";

            if (activeTab.value === "wait") {
                endpoint = "/api/entrybarang/getDataWaitForEntry";
            } else if (activeTab.value === "ready") {
                endpoint = "/api/entrybarang/getDataReady";
            } else {
                endpoint = "/api/entrybarang/getDataPO";
            }

            const response = await $api.get(
                `${url.value}${endpoint}`
            );

            listBarang.value = response.data.data.map((item) => ({
                ...item,
                code_nama:
                    item.code_nama || item.code?.code_nama || "",
                nama_akun_po:
                    item.pre_order_barang?.preOrderBarang_nama_akun || "-"
            }));

        } catch (error) {
            console.error("Gagal Memuat Data Barang: ", error);
        }
    }

    async function getListBarangPreOrder() {
        try {
            const response = await $api.get(
                `${url.value}/api/entrybarang/getDataPO`
            );

            listBarang.value = (response.data.data || []).map((item) => ({
                ...item,
                code_nama:
                    item.code_nama || item.code?.code_nama || "",
                nama_akun_po:
                    item.pre_order_barang?.preOrderBarang_nama_akun || "-"
            }));

            await fetchCodeBarang(listBarang.value);

        } catch (error) {
            console.error("Gagal Memuat Data Pre Order: ", error);
        }
    }

    async function openRiwayat(id) {
        showRiwayatModal.value = true;
        riwayatLoading.value = true;
        riwayatData.value = [];

        try {
            const res = await $api.get(
                `${url.value}/api/entrybarang/${id}/riwayat`
            );

            riwayatData.value = res.data.data;

        } catch (e) {
            console.error("Gagal memuat riwayat:", e);

        } finally {
            riwayatLoading.value = false;
        }
    }

    function formatTanggalWIB(d) {
        if (!d) return "-";

        const date = new Date(d);

        const tgl = date.toLocaleDateString("id-ID", {
            day: "numeric",
            month: "long",
            year: "numeric",
            timeZone: "Asia/Jakarta",
        });

        const jam = date.toLocaleTimeString("id-ID", {
            hour: "2-digit",
            minute: "2-digit",
            hour12: false,
            timeZone: "Asia/Jakarta",
        }).replace(":", ".");

        return `${tgl}, ${jam} WIB`;
    }

    async function exportReadyStock() {
        try {
            Swal.fire({
                title: "Mengexport data...",
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });

            const res = await $api.get(
                `${url.value}/api/entrybarang/export-ready`
            );

            const rows = res.data.data;

            const exportRows = rows.map((row) => ({
                "Kode Barang": row.code_nama ?? "",
                "Jenis Barang": row.jenisbarang_nama ?? "",
                "Nama Ulos": row.barangentry_nama ?? "",
                Warna: row.barangentry_warna ?? "",
                "Nama Penenun": row.barangentry_nama_penenun ?? "",
                "Nama Panirat": row.barangentry_nama_panirat ?? "",
                Dryer: row.barangentry_dryer ?? "",
                Modal: Number(row.barangentry_modal ?? 0),
                "Price Tag": Number(row.barangentry_price_tag ?? 0),
                "Harga Net": Number(row.barangentry_harga_net ?? 0),
                "Ukuran Mandar": row.barangentry_ukuran_mandar ?? "",
                "Ukuran Ulos": row.barangentry_ukuran_ulos ?? "",
                Jumlah: Number(row.barangentry_jumlah_barang ?? 0),
                Acara: row.acara_nama ?? "-",
                Tanggal: row.created_at
                    ? row.created_at.substring(0, 10)
                    : "",
            }));

            const worksheet = XLSX.utils.json_to_sheet(exportRows);
            const workbook = XLSX.utils.book_new();

            XLSX.utils.book_append_sheet(
                workbook,
                worksheet,
                "Ready Stock"
            );

            XLSX.writeFile(
                workbook,
                `ReadyStock_${new Date().toISOString().split("T")[0]}.xlsx`
            );

            Swal.close();
            Swal.fire(
                "Berhasil",
                "Export Excel selesai.",
                "success"
            );

        } catch (err) {
            console.error(err);
            Swal.close();

            Swal.fire(
                "Gagal",
                "Terjadi kesalahan saat export.",
                "error"
            );
        }
    }

    let barcodeTimeoutDesc = null;

    async function handleBarcodeDesc() {
        clearTimeout(barcodeTimeoutDesc);

        barcodeTimeoutDesc = setTimeout(async () => {
            if (!selectedBarang.value.code_nama) return;

            try {
                const response = await $api.get(
                    `${url.value}/api/codebarang/getDataByCode/${selectedBarang.value.code_nama}`
                );

                const data = response.data.data;

                if (!data) {
                    Swal.fire({
                        icon: "error",
                        title: "Kode Tidak Ditemukan",
                        text: "Pastikan kode barang valid",
                        timer: 2000,
                        showConfirmButton: false,
                    });

                    return;
                }

                selectedBarang.value.code_id = data.code_id;

            } catch (err) {
                console.error("Gagal mengambil data kode:", err);

                Swal.fire({
                    icon: "error",
                    title: "Gagal",
                    text: "Terjadi kesalahan saat memeriksa kode.",
                    timer: 2000,
                    showConfirmButton: false,
                });
            }
        }, 600);
    }

    let barcodeTimeoutSize = null;

    async function handleBarcodeSize() {
        clearTimeout(barcodeTimeoutSize);

        barcodeTimeoutSize = setTimeout(async () => {
            if (!selectedBarang.value.kode_barang) return;

            try {
                const response = await $api.get(
                    `${url.value}/api/codebarang/getDataByCode/${selectedBarang.value.kode_barang}`
                );

                const data = response.data.data;

                if (!data) {
                    Swal.fire({
                        icon: "error",
                        title: "Kode Tidak Ditemukan",
                        text: "Pastikan kode barang valid",
                        timer: 2000,
                        showConfirmButton: false,
                    });

                    return;
                }

                selectedBarang.value.code_id = data.code_id;

            } catch (err) {
                console.error("Gagal mengambil data kode:", err);

                Swal.fire({
                    icon: "error",
                    title: "Gagal",
                    text: "Terjadi kesalahan saat memeriksa kode.",
                    timer: 2000,
                    showConfirmButton: false,
                });
            }
        }, 600);
    }

    function handleSendClick(barang) {
        if (barang.status_barang === "PACKAGING") {
            router.push(`/packaging-page`);
        } else {
            openSendModal(barang.barangentry_id);
        }
    }

        async function getListBarangPreOrder() {
        try {
            const response = await $api.get(
                `${url.value}/api/entrybarang/getDataPO`
            );

            listBarang.value = (response.data.data || []).map((item) => ({
                ...item,
                code_nama:
                    item.code_nama || item.code?.code_nama || "",
                nama_akun_po:
                    item.pre_order_barang?.preOrderBarang_nama_akun || "-"
            }));

            await fetchCodeBarang(listBarang.value);

        } catch (error) {
            console.error("Gagal Memuat Data Pre Order: ", error);
        }
    }

    const searchFilterData = computed(() => {
        const sorted = [...listBarang.value].sort((a, b) => {
            return new Date(b.created_at) - new Date(a.created_at);
        });

        if (!searchQuery.value) return sorted;

        const q = searchQuery.value.toLowerCase();

        return sorted.filter((barang) => {
            const kodeNama =
                barang.code_nama ||
                barang.code?.code_nama ||
                "";

            const namaAkunPO =
                barang.pre_order_barang?.preOrderBarang_nama_akun || "";

            return (
                barang.barangentry_nama?.toLowerCase().includes(q) ||
                barang.barangentry_warna?.toLowerCase().includes(q) ||
                kodeNama.toLowerCase().includes(q) ||
                barang.barangentry_nama_penenun?.toLowerCase().includes(q) ||
                barang.barangentry_nama_panirat?.toLowerCase().includes(q) ||
                barang.barangentry_dryer?.toLowerCase().includes(q) ||
                String(barang.barangentry_modal || "").includes(q) ||
                String(barang.barangentry_price_tag || "").includes(q) ||
                String(barang.barangentry_harga_net || "").includes(q) ||
                namaAkunPO.toLowerCase().includes(q)
            );
        });
    });

    const pagination = computed(() => {
        if (itemsPerPage.value === "all") {
            return searchFilterData.value;
        }

        const perPage = Number(itemsPerPage.value);
        const start = (currentPage.value - 1) * perPage;

        return searchFilterData.value.slice(
            start,
            start + perPage
        );
    });

    const startItem = computed(() => {
        if (searchFilterData.value.length === 0) return 0;

        if (itemsPerPage.value === "all") {
            return 1;
        }

        return (
            (currentPage.value - 1) *
            Number(itemsPerPage.value) +
            1
        );
    });

    const endItem = computed(() => {
        if (searchFilterData.value.length === 0) return 0;

        if (itemsPerPage.value === "all") {
            return searchFilterData.value.length;
        }

        return Math.min(
            currentPage.value * Number(itemsPerPage.value),
            searchFilterData.value.length
        );
    });

    const totalPages = computed(() => {
        if (itemsPerPage.value === "all") return 1;

        return Math.max(
            1,
            Math.ceil(
                searchFilterData.value.length /
                Number(itemsPerPage.value)
            )
        );
    });

    function openModal(type) {
        modalType.value = type;
        modalOpen.value = true;
    }

    function tambahBarang(barang) {
        selectedBarang.value = {
            ...barang,
            barangentry_jumlah_barang:
                barang.barangentry_jumlah_barang ?? 1
        };

        showModalAdd.value = true;
    }

    function formatRupiah(value) {
        const number = parseInt(value);
        return "Rp. " + number.toLocaleString("id-ID");
    }

    async function sendOrder(barangentry_id, formData) {
        try {
            const res = await $api.get(
                `${url.value}/api/pre-order-barang/preOrderEntry/${barangentry_id}`
            );

            const dataPreOrder = res.data.data;
            let transaksi_po_lama = null;
            let status_transaksi_po_lama = "";

            if (!dataPreOrder.preOrderBarang_transaksi_id) {
                const payloadTransaksi = {
                    transaksi_nama_customer: formData.nama_akun,
                    transaksi_nomor_telepon: "",
                    transaksi_jumlah_barang: 1,
                    transaksi_total_harga: parseInt(
                        formData.total_pembayaran
                    ),
                    transaksi_cara_bayar: formData.cara_bayar,
                    transaksi_tipe: "Pre Order",
                    transaksi_status: "Pre Order",
                    transaksi_catatan: "",
                };

                const { data } = await $api.post(
                    `${url.value}/api/transaksi`,
                    payloadTransaksi
                );

                transaksi_po_lama = data.data.transaksi_id;

                const barang = await $api.get(
                    `${url.value}/api/entrybarang/${barangentry_id}`
                );

                const code = await $api.get(
                    `${url.value}/api/codebarang/${barang.data.data.barangentry_code_id}`
                );

                const { data: barangResponse } = await $api.get(
                    `${url.value}/api/entrybarang/getDataByCode/${code.data.code_nama}`
                );

                const barangData = barangResponse.data;

                const detailPayload = {
                    transaksidetail_transaksi_id: transaksi_po_lama,
                    transaksidetail_barang_id: barangData.barangentry_id,
                    transaksidetail_jumlah_barang: 1,
                    transaksidetail_harga_barang: Number(
                        barangData.barangentry_harga_net
                    ),
                    transaksidetail_status_penjualan: 0
                };

                await $api.post(
                    `${url.value}/api/transaksi-detail`,
                    detailPayload
                );

                status_transaksi_po_lama = "po lama";
            }

            const pengirimanPayload = {
                pengirimanBarang_transaksi_id:
                    status_transaksi_po_lama === "po lama"
                        ? transaksi_po_lama
                        : dataPreOrder.preOrderBarang_transaksi_id,
                pengirimanBarang_nama_penerima: formData.nama_akun,
                pengirimanBarang_akun_penerima: formData.nama_akun,
                pengirimanBarang_no_telepon: formData.no_telepon,
                pengirimanBarang_harga_kirim_barang: parseInt(
                    formData.harga_kirim
                ),
                pengirimanBarang_jenis_pengiriman_barang:
                    formData.jenis_pengiriman,
                pengirimanBarang_alamat_pengiriman_barang: formData.alamat,
                pengirimanBarang_catatan: "",
                pengirimanBarang_status: "Proses",
            };

            const resPengiriman = await $api.post(
                `${url.value}/api/pengiriman-barang`,
                pengirimanPayload
            );

            const dataPengiriman = resPengiriman.data.data;

            const po_id =
                status_transaksi_po_lama === "po lama"
                    ? transaksi_po_lama
                    : dataPreOrder.preOrderBarang_transaksi_id;

            await $api.post(
                `${url.value}/api/transaksi-detail/updateStatusPenjualan/${po_id}`,
                {}
            );

            await getListBarangTemp();
            showSendModal.value = false;

            return dataPengiriman.pengiriman.pengirimanBarang_id;

        } catch (err) {
            Swal.fire({
                title: "Gagal!",
                text: "Silahkan lengkapi preorder",
                icon: "error",
                confirmButtonText: "OK",
                timer: 3000,
                timerProgressBar: true,
            });
        }
    }

    async function submitBarang() {
        const requiredFields = [
            "code_id",
            "barangentry_nama",
            "barangentry_warna",
            "barangentry_nama_penenun",
            "barangentry_nama_panirat",
            "barangentry_dryer",
            "barangentry_modal",
            "barangentry_price_tag",
            "barangentry_harga_net",
            "barangentry_jumlah_barang"
        ];

        const missingFields = requiredFields.filter(
            (field) =>
                !selectedBarang.value[field] &&
                selectedBarang.value[field] !== 0
        );

        if (missingFields.length > 0) {
            Swal.fire({
                title: "Gagal!",
                text: "Silakan lengkapi semua field wajib.",
                icon: "error",
                confirmButtonText: "OK",
                timer: 3000,
                timerProgressBar: true,
            });

            return;
        }

        isSubmitting.value = true;

        try {
            const payload = {
                barangentry_code_id: String(selectedBarang.value.code_id),
                barangentry_nama: selectedBarang.value.barangentry_nama,
                barangentry_warna: selectedBarang.value.barangentry_warna,
                barangentry_nama_penenun:
                    selectedBarang.value.barangentry_nama_penenun,
                barangentry_nama_panirat:
                    selectedBarang.value.barangentry_nama_panirat,
                barangentry_dryer: selectedBarang.value.barangentry_dryer,
                barangentry_modal: selectedBarang.value.barangentry_modal,
                barangentry_price_tag: Number(
                    selectedBarang.value.barangentry_price_tag
                ),
                barangentry_harga_net: Number(
                    selectedBarang.value.barangentry_harga_net
                ),
                barangentry_jumlah_barang:
                    selectedBarang.value.barangentry_jumlah_barang,
            };

            await $api.post(
                `${url.value}/api/entrybarang/storeDescription`,
                payload
            );

            await getListBarangTemp();

            selectedBarang.value = {};
            showModalAdd.value = false;
            modalOpen.value = false;

            Swal.fire({
                title: "Sukses!",
                text: "Berhasil Menambahkan Deskripsi",
                icon: "success",
                confirmButtonText: "OK",
                timer: 3000,
                timerProgressBar: true,
            });

        } catch (error) {
            console.error("Gagal meyimpan barang:", error);

        } finally {
            isSubmitting.value = false;
        }
    }

    async function openTambahBarang() {
        selectedBarang.value = {
            barangentry_jumlah_barang: 1
        };

        showModalAdd.value = true;

        await nextTick();

        if (inputKodeDesc.value) {
            inputKodeDesc.value.focus();
        }
    }

    async function openAddSize() {
        selectedBarang.value = {};
        showModalAddSize.value = true;

        await nextTick();

        if (inputKodeSize.value) {
            inputKodeSize.value.focus();
        }
    }

    function cancelTambahBarang() {
        selectedBarang.value = {};
        showModalAdd.value = false;
    }

    function handleSizeSubmitted(barang) {
        selectedBarang.value = { ...barang };
        showModalAddSize.value = true;
    }

    async function submitSizeBarang() {
        const requiredFields = [
            "code_id",
            "ukuran_ulos"
        ];

        const missingFields = requiredFields.filter(
            (field) =>
                !selectedBarang.value[field] &&
                selectedBarang.value[field] !== 0
        );

        if (missingFields.length > 0) {
            Swal.fire({
                title: "Gagal!",
                text: "Silakan lengkapi semua field wajib.",
                icon: "error",
                confirmButtonText: "OK",
                timer: 3000,
                timerProgressBar: true,
            });

            return;
        }

        isSubmittingSize.value = true;

        try {
            const payload = {
                barangentry_code_id: String(selectedBarang.value.code_id),
                barangentry_ukuran_mandar:
                    selectedBarang.value.ukuran_mandar
                        ? String(selectedBarang.value.ukuran_mandar)
                        : "",
                barangentry_ukuran_ulos:
                    selectedBarang.value.ukuran_ulos
                        ? String(selectedBarang.value.ukuran_ulos)
                        : "",
            };

            await $api.post(
                `${url.value}/api/entrybarang/storeSize`,
                payload
            );

            const index = listBarang.value.findIndex(
                (item) =>
                    item.barangentry_code_id ===
                    selectedBarang.value.code_id
            );

            if (index !== -1) {
                listBarang.value[index].barangentry_ukuran_mandar =
                    payload.barangentry_ukuran_mandar;

                listBarang.value[index].barangentry_ukuran_ulos =
                    payload.barangentry_ukuran_ulos;

            } else {
                listBarang.value.push({
                    no: listBarang.value.length + 1,
                    ...selectedBarang.value,
                    barangentry_ukuran_ulos:
                        payload.barangentry_ukuran_ulos,
                    barangentry_ukuran_mandar:
                        payload.barangentry_ukuran_mandar,
                });
            }

            await getListBarangTemp();

            selectedBarang.value = {};
            showModalAddSize.value = false;
            modalOpen.value = false;

            Swal.fire({
                title: "Sukses!",
                text: "Berhasil Menambahkan Size",
                icon: "success",
                confirmButtonText: "OK",
                timer: 3000,
                timerProgressBar: true,
            });

        } catch (error) {
            console.error("Gagal menyimpan size:", error);

        } finally {
            isSubmittingSize.value = false;
        }
    }

    function cancelSizeBarang() {
        selectedBarang.value = {};
        showModalAddSize.value = false;
    }

    function openSearchModal() {
        showModalSearch.value = true;
        searchCode.value = "";
    }

    function closeSearchModal() {
        showModalSearch.value = false;
        searchCode.value = "";
    }

    async function handleSearch() {
        const keyword = searchCode.value.trim();
        if (!keyword) return;

        try {
            const response = await $api.get(
                `${url.value}/api/entrybarang/getDataByCode/${keyword}`
            );

            const code = response.data.data.barangentry_code_id;

            if (!code) {
                Swal.fire({
                    icon: "warning",
                    title: "Kode tidak ditemukan",
                    text: "Kode tidak ditemukan dari server.",
                });

                return;
            }

            filteredBarang.value = listBarang.value.filter((item) =>
                String(item.barangentry_code_id).includes(String(code))
            );

            isSearchActive.value = true;
            showModalSearch.value = false;

            if (filteredBarang.value.length === 0) {
                Swal.fire({
                    icon: "info",
                    title: "Barang tidak ditemukan",
                    text: "Barang tidak ditemukan di daftar.",
                });
            }

        } catch (error) {
            console.error("Gagal mencari data kode:", error);

            Swal.fire({
                icon: "error",
                title: "Terjadi kesalahan",
                text: "Terjadi kesalahan saat mencari kode.",
            });
        }
    }

        const openModalTambahStock = (barang) => {
        selectedBarang.value = barang;

        editJumlah.value =
            barang.barangentry_jumlah_barang ?? barang.jumlah ?? 0;

        showModalEditStock.value = true;
    };

    const editStockSubmit = async () => {
        const barangId = selectedBarang.value?.barangentry_id;

        if (!barangId) return;

        try {
            const responseEdit = await $api.post(
                `${url.value}/api/entrybarang/${barangId}/updateStok`,
                {
                    jumlah_barang: editJumlah.value
                }
            );

            if (responseEdit.data.code == 200) {
                showModalEditStock.value = false;

                Swal.fire({
                    title: "Berhasil!",
                    text: "Stock Berhasil di update.",
                    icon: "success",
                    timer: 1500,
                    showConfirmButton: false,
                });
            }

            await getListBarangTemp();

        } catch (error) {
            console.error("error", error);

            Swal.fire({
                title: "Gagal!",
                text: "Terjadi kesalahan saat update stock.",
                icon: "error",
                timer: 1500,
            });
        }
    };

    const deleteBarang = async (id) => {
        const result = await Swal.fire({
            title: "Konfirmasi Hapus",
            text: `Anda yakin ingin menghapus barang ini?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, Hapus!",
            cancelButtonText: "Batal",
            reverseButtons: true,
        });

        if (result.isConfirmed) {
            try {
                const response = await $api.post(
                    `${url.value}/api/entrybarang/${id}/deleteBarangEntry`,
                    {
                        status: "DELETED"
                    }
                );

                if (response.data.code === 200) {
                    Swal.fire({
                        title: "Berhasil!",
                        text: `Barang telah dihapus.`,
                        icon: "success",
                        timer: 1500,
                        showConfirmButton: false,
                    });
                }

                await getListBarangTemp();

            } catch (error) {
                console.error("error:", error);

                Swal.fire({
                    title: "Gagal",
                    text: "Terjadi kesalahan saat menghapus barang ini.",
                    icon: "error",
                });
            }
        }
    };

    const paginatedPages = computed(() => {
        if (itemsPerPage.value === "all") return [1];

        const total = totalPages.value;
        const current = currentPage.value;
        const pages = [];

        if (total <= 5) {
            for (let i = 1; i <= total; i++) {
                pages.push(i);
            }
        } else {
            if (current <= 3) {
                pages.push(1, 2, 3, "...", total);
            } else if (current >= total - 2) {
                pages.push(
                    1,
                    "...",
                    total - 2,
                    total - 1,
                    total
                );
            } else {
                pages.push(
                    1,
                    "...",
                    current - 1,
                    current,
                    current + 1,
                    "...",
                    total
                );
            }
        }

        return pages;
    });

    async function printPreOrder(id) {
        const response = await $api.get(
            `${url.value}/api/pre-order-barang/preOrderEntry/${id}`
        );

        const data = response.data.data;

        const resEntry = await $api.get(
            `${url.value}/api/entrybarang/${id}`
        );

        const barangEntry = resEntry.data.data;

        const code = await $api.get(
            `${url.value}/api/codebarang/${barangEntry.barangentry_code_id}`
        );

        const code_nama = code.data.code_nama;

        console.log("dsdd", data);

        printData(data, barangEntry, code_nama);
    }

    function printData(data, barangEntry, code_nama) {
        const printWindow = window.open("", "_blank");

        if (!printWindow) {
            alert("Pop-up blocker menghalangi membuka tab baru.");
            return;
        }

        const formatTanggal = (dateStr) => {
            if (!dateStr) return "-";

            const date = new Date(dateStr);

            return date.toLocaleDateString("id-ID", {
                day: "2-digit",
                month: "long",
                year: "numeric",
            });
        };

        const formatRupiah = (value) => {
            return new Intl.NumberFormat("id-ID", {
                style: "currency",
                currency: "IDR",
                minimumFractionDigits: 0,
            }).format(value || 0);
        };

        const htmlContent = `
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8" />
            <title>Pre Order Invoice</title>

            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 40px;
                    background: #fff;
                    color: #000;
                }

                .print-area {
                    max-width: 800px;
                    margin: auto;
                    box-sizing: border-box;
                }

                .header {
                    text-align: center;
                    margin-bottom: 20px;
                }

                .logo {
                    width: 100%;
                    max-width: 800px;
                }

                .info {
                    display: flex;
                    justify-content: space-between;
                    margin-top: 20px;
                    font-size: 14px;
                    gap: 40px;
                }

                .info-col {
                    flex: 1;
                }

                .info-row {
                    display: flex;
                    margin-bottom: 6px;
                }

                .info-label {
                    width: 150px;
                    font-weight: bold;
                }

                .info-separator {
                    margin-right: 6px;
                }

                .info-value {
                    flex: 1;
                    font-weight: normal;
                }

                h3 {
                    margin-top: 30px;
                    font-size: 15px;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 14px;
                    margin-top: 12px;
                    border: 1px solid #eee;
                    border-radius: 8px;
                    overflow: hidden;
                }

                thead {
                    background-color: #f9f9f9;
                    font-weight: bold;
                }

                th,
                td {
                    padding: 8px;
                    text-align: left;
                }

                tbody tr:not(:last-child) {
                    border-bottom: 1px dashed #ccc;
                }

                .total-row td {
                    font-weight: bold;
                    text-align: right;
                    padding-top: 12px;
                    border-top: 1px solid #ddd;
                }

                .signatures {
                    display: flex;
                    justify-content: flex-end;
                    gap: 40px;
                    margin: 40px 0;
                }

                .sign-box {
                    border: 1px solid #000;
                    width: 120px;
                    height: 80px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 12px;
                }

                .footer {
                    text-align: center;
                    font-size: 12px;
                    color: #555;
                    margin-top: 40px;
                }

                @media print {
                    body {
                        margin: 0;
                        padding: 0;
                    }
                }
            </style>
        </head>

        <body>
            <div class="print-area">
                <div class="header">
                    <img
                        src="/image/DameUlosPO.png"
                        alt="Logo Dame Ulos"
                        class="logo"
                    />
                </div>

                <div class="info">
                    <div class="info-col">
                        <div class="info-row">
                            <div class="info-label">Kode PO</div>
                            <div class="info-separator">:</div>
                            <div class="info-value">
                                ${code_nama || "-"}
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Nama Ulos</div>
                            <div class="info-separator">:</div>
                            <div class="info-value">
                                ${barangEntry.barangentry_nama || "-"}
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Deskripsi</div>
                            <div class="info-separator">:</div>
                            <div class="info-value">
                                ${data.preOrderBarang_deskripsi_barang || "-"}
                            </div>
                        </div>
                    </div>

                    <div class="info-col">
                        <div class="info-row">
                            <div class="info-label">Tanggal Dimulai</div>
                            <div class="info-separator">:</div>
                            <div class="info-value">
                                ${formatTanggal(data.updated_at)}
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Tanggal Selesai</div>
                            <div class="info-separator">:</div>
                            <div class="info-value">
                                ${formatTanggal(data.preOrderBarang_target_selesai)}
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Author</div>
                            <div class="info-separator">:</div>
                            <div class="info-value">
                                ${data.preOrderBarang_nama_akun || "-"}
                            </div>
                        </div>
                    </div>
                </div>

                <h3>Rincian Pembayaran</h3>

                <table>
                    <thead>
                        <tr>
                            <th>Total Pembayaran</th>
                            <th>Down Payment (DP)</th>
                            <th>Pelunasan</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>
                                ${formatRupiah(data.preOrderBarang_total_pembayaran)}
                            </td>
                            <td>
                                ${formatRupiah(data.preOrderBarang_uang_muka)}
                            </td>
                            <td>
                                ${formatRupiah(data.preOrderBarang_sisa_pembayaran)}
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="signatures">
                    <div class="sign-box">Tanda Masuk</div>
                    <div class="sign-box">Tanda Pengambilan</div>
                </div>

                <div class="footer">
                    <p>
                        Transaksi ini diproses berdasarkan Purchase Order yang berlaku.
                    </p>
                    <p>
                        Terima kasih telah mempercayakan kebutuhan Anda kepada kami.
                    </p>
                </div>
            </div>

            <script>
                window.onload = function () {
                    window.print();
                };
            <\/script>
        </body>
        </html>
        `;

        printWindow.document.write(htmlContent);
        printWindow.document.close();
    }

    watch(editJumlah, (val) => {
        if (val < 1 || !val) {
            editJumlah.value = 1;
        }
    });

    watch(activeTab, () => {
        getListBarangTemp();
    });

    watch([searchQuery, itemsPerPage], () => {
        currentPage.value = 1;
    });

    return {
        router,
        modalOpen,
        modalType,
        showModalAdd,
        showModalAddSize,
        selectedBarang,
        url,
        showModalEditStock,
        editJumlah,
        showEditModal,
        showEditDesc,
        showEditSize,
        showEditPO,
        selectedId,
        showSendModal,
        showRiwayatModal,
        riwayatLoading,
        riwayatData,
        barangDatabase,
        listBarang,
        priceTagData,
        showModalSearch,
        searchCode,
        filteredBarang,
        isSearchActive,
        currentPage,
        itemsPerPage,
        printContent,
        activeTab,
        searchQuery,
        barangMap,
        isSubmitting,
        isSubmittingSize,
        pengirimanStatus,
        inputKodeDesc,
        inputKodeSize,
        selectedPreOrderId,
        activeDropdown,
        closeActionDropdown,
        setActionDropdownMenu,
        toggleActionDropdown,
        formatTanggal,
        openSendModal,
        processOrderDijemput,
        handleSend,
        updatePengiriman,
        openModalEditBarang,
        openModalEditPO,
        openModalDesc,
        openModalSize,
        loadData,
        formatRupiah2,
        formatUkuran,
        updatePriceTag,
        updateModal,
        updateHargaNet,
        fetchCodeBarang,
        getListBarangTemp,
        openRiwayat,
        formatTanggalWIB,
        exportReadyStock,
        handleBarcodeDesc,
        handleBarcodeSize,
        handleSendClick,
        getListBarangPreOrder,
        searchFilterData,
        pagination,
        startItem,
        endItem,
        totalPages,
        openModal,
        tambahBarang,
        formatRupiah,
        sendOrder,
        submitBarang,
        openTambahBarang,
        openAddSize,
        cancelTambahBarang,
        handleSizeSubmitted,
        submitSizeBarang,
        cancelSizeBarang,
        openSearchModal,
        closeSearchModal,
        handleSearch,
        openModalTambahStock,
        editStockSubmit,
        deleteBarang,
        paginatedPages,
        printPreOrder,
        printData,
    };
}