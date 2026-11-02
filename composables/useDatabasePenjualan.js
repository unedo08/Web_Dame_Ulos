import { ref, onMounted, computed, watch } from "vue";
import Swal from "sweetalert2";
import * as XLSX from "xlsx";
import { saveAs } from "file-saver";

export function useDatabasePenjualan() {
    // =====================================================
    // DATE FILTER
    // =====================================================

    const today = new Date().toISOString().slice(0, 10);

    const startDate = ref(today);
    const endDate = ref(today);

    // =====================================================
    // API CONFIGURATION
    // =====================================================

    const config = useRuntimeConfig();
    const url = ref(config.public.apiBase);
    const { $api } = useNuxtApp();

    const transaksi = ref([]);

    // =====================================================
    // SEARCH & PAGINATION
    // =====================================================

    const searchQuery = ref("");
    const currentPage = ref(1);
    const itemsPerPage = ref(10);

    // =====================================================
    // DETAIL MODAL
    // =====================================================

    const showDetailModal = ref(false);
    const trx_id = ref(null);

    // =====================================================
    // DROPDOWN ACTION
    // =====================================================

    const {
        activeDropdown,
        closeActionDropdown,
        setActionDropdownMenu,
        toggleActionDropdown,
    } = useActionDropdown();

    // =====================================================
    // FETCH TRANSAKSI
    // =====================================================

    const fetchTransaksi = async () => {
        try {
            const res = await $api.get(
                `${url.value}/api/transaksi/grouped`
            );

            transaksi.value = res.data.data;
        } catch (e) {
            Swal.fire(
                "Gagal",
                "Tidak dapat memuat data transaksi",
                "error"
            );
        }
    };

    // =====================================================
    // FORMAT DATE KEY
    // =====================================================

    const formatDateKey = (dateStr) => {
        const d = new Date(dateStr);

        const dd = String(d.getDate()).padStart(2, "0");
        const mm = String(d.getMonth() + 1).padStart(2, "0");
        const yyyy = d.getFullYear();

        return `${dd}-${mm}-${yyyy}`;
    };

    // =====================================================
    // SORT TRANSAKSI
    // =====================================================

    const sortedTransaksi = computed(() => {
        return [...transaksi.value].sort(
            (a, b) => new Date(b.created_at) - new Date(a.created_at)
        );
    });

    // =====================================================
    // GROUP TRANSAKSI
    // =====================================================

    const groupedTransaksi = computed(() => {
        const groups = {};

        sortedTransaksi.value.forEach((trx) => {
            const dateKey = formatDateKey(trx.created_at);

            const key = `${dateKey}__${trx.customer_nama}__${trx.cara_bayar}`;

            if (!groups[key]) {
                groups[key] = {
                    created_at: trx.created_at,
                    customer_nama: trx.customer_nama,
                    transaksi_tipe: trx.transaksi_tipe,
                    transaksi_acara: trx.transaksi_acara,
                    cara_bayar: trx.cara_bayar,
                    catatan: trx.catatan,
                    transaksi_id: trx.transaksi_id,
                    items: [],
                };
            }

            trx.items.forEach((item) => {
                const harga = Number(item.barang_price_tag || 0);
                const jumlah = 1;

                groups[key].items.push({
                    ...item,
                    harga,
                    jumlah,
                    subtotal: harga * jumlah,
                });
            });
        });

        return Object.values(groups);
    });

    // =====================================================
    // FILTER TRANSAKSI
    // =====================================================

    const filteredTransaksi = computed(() => {
        if (!searchQuery.value) {
            return groupedTransaksi.value;
        }

        const q = searchQuery.value.toLowerCase();

        return groupedTransaksi.value.filter((trx) =>
            trx.customer_nama.toLowerCase().includes(q) ||
            trx.items.some((i) =>
                (i.code_nama || "").toLowerCase().includes(q)
            )
        );
    });

    // =====================================================
    // PAGINATION
    // =====================================================

    const paginatedTransaksi = computed(() => {
        const start =
            (currentPage.value - 1) * Number(itemsPerPage.value);

        return filteredTransaksi.value.slice(
            start,
            start + Number(itemsPerPage.value)
        );
    });

    const totalTransaksi = computed(() => {
        return filteredTransaksi.value.length;
    });

    const startItem = computed(() => {
        if (totalTransaksi.value === 0) return 0;

        return (
            (currentPage.value - 1) * Number(itemsPerPage.value) + 1
        );
    });

    const endItem = computed(() => {
        if (totalTransaksi.value === 0) return 0;

        return Math.min(
            currentPage.value * Number(itemsPerPage.value),
            totalTransaksi.value
        );
    });

    const totalPages = computed(() => {
        return Math.ceil(
            totalTransaksi.value / Number(itemsPerPage.value)
        );
    });

    const paginatedPages = computed(() => {
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
                pages.push(1, "...", total - 2, total - 1, total);
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

    // =====================================================
    // WATCHERS
    // =====================================================

    watch(searchQuery, () => {
        currentPage.value = 1;
    });

    watch(itemsPerPage, () => {
        currentPage.value = 1;
    });

    // =====================================================
    // FORMAT CURRENCY & DATE
    // =====================================================

    const formatCurrency = (v) =>
        new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        }).format(v);

    const formatDate = (d) =>
        new Date(d).toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "short",
            year: "numeric",
        });

    // =====================================================
    // DETAIL
    // =====================================================

    const openViewDetail = (id) => {
        trx_id.value = id;
        showDetailModal.value = true;
    };

    // =====================================================
    // DELETE TRANSAKSI
    // =====================================================

    const deleteTransaksi = async (id) => {
        Swal.fire({
            title: "Apakah Anda yakin?",
            text: "Data transaksi yang dihapus tidak dapat dikembalikan!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Ya, hapus!",
            cancelButtonText: "Batal",
            reverseButtons: true,
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const response = await $api.delete(
                        `${url.value}/api/transaksi-detail/${id}`
                    );

                    if (response.status === 200) {
                        transaksi.value = transaksi.value.filter(
                            (item) => item.transaksi_id !== id
                        );

                        Swal.fire({
                            title: "Terhapus!",
                            text: "Data transaksi berhasil dihapus.",
                            icon: "success",
                            timer: 2000,
                            showConfirmButton: false,
                        });
                    }
                } catch (error) {
                    console.error("Gagal menghapus transaksi:", error);

                    Swal.fire({
                        title: "Gagal!",
                        text: "Terjadi kesalahan saat menghapus data.",
                        icon: "error",
                        confirmButtonText: "OK",
                    });
                }
            }
        });
    };

    // =====================================================
    // EXPORT EXCEL
    // =====================================================

    const exportExcel = async () => {
        try {
            const res = await $api.get(
                `${url.value}/api/transaksi/report`,
                {
                    params: {
                        start_date: startDate.value,
                        end_date: endDate.value,
                    },
                }
            );

            if (!res.data.success || !res.data.data.length) {
                Swal.fire("Info", "Data tidak tersedia", "info");
                return;
            }

            const rows = res.data.data;

            const excelData = rows.map((r, i) => ({
                No: i + 1,
                "Tanggal Transaksi": r.created_at,
                "Nama Customer": r.customer_nama,
                "Jenis Transaksi": r.transaksi_tipe,
                Platform: r.transaksi_platform || "-",
                "Kode Barang": r.code_nama,
                "Nama Barang": r.barangentry_nama,
                "Harga Modal": Number(r.barangentry_modal),
                "Price Tag": Number(r.barangentry_price_tag),
                "Harga Net": Number(r.barangentry_harga_net),
                Jumlah: Number(r.transaksidetail_jumlah_barang),
                "Harga Jual": Number(r.transaksidetail_harga_barang),
                Subtotal: Number(r.subtotal),
                Pembayaran: r.carabayar_nama,
                Catatan: r.transaksi_catatan || "-",
            }));

            const ws = XLSX.utils.json_to_sheet(excelData);

            const headerCells = Object.keys(excelData[0]).length;

            for (let C = 0; C < headerCells; C++) {
                const cell = XLSX.utils.encode_cell({
                    r: 0,
                    c: C,
                });

                ws[cell].s = {
                    font: {
                        bold: true,
                    },
                };
            }

            const rupiahFormat = '"Rp" #,##0';
            const range = XLSX.utils.decode_range(ws["!ref"]);

            const COL_RUPIAH = [6, 7, 8, 10, 11];

            for (let R = range.s.r + 1; R <= range.e.r; ++R) {
                COL_RUPIAH.forEach((C) => {
                    const cell = XLSX.utils.encode_cell({
                        r: R,
                        c: C,
                    });

                    if (ws[cell]) {
                        ws[cell].t = "n";
                        ws[cell].z = rupiahFormat;
                    }
                });
            }

            const totalRow = range.e.r + 1;

            ws[`A${totalRow + 1}`] = {
                v: "TOTAL",
                t: "s",
            };

            ws[`L${totalRow + 1}`] = {
                f: `SUM(L2:L${totalRow})`,
                t: "n",
                z: rupiahFormat,
            };

            ws["!freeze"] = {
                xSplit: 0,
                ySplit: 1,
            };

            ws["!cols"] = [
                { wch: 5 },
                { wch: 18 },
                { wch: 20 },
                { wch: 15 },
                { wch: 10 },
                { wch: 15 },
                { wch: 15 },
                { wch: 16 },
                { wch: 16 },
                { wch: 16 },
                { wch: 10 },
                { wch: 16 },
                { wch: 16 },
                { wch: 18 },
                { wch: 25 },
            ];

            const wb = XLSX.utils.book_new();

            XLSX.utils.book_append_sheet(
                wb,
                ws,
                "Report Penjualan"
            );

            const buffer = XLSX.write(wb, {
                bookType: "xlsx",
                type: "array",
                cellStyles: true,
            });

            saveAs(
                new Blob([buffer], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                }),
                `Report_Penjualan_${startDate.value}_${endDate.value}.xlsx`
            );

            Swal.fire({
                icon: "success",
                title: "Berhasil",
                text: "File Excel berhasil diunduh",
                timer: 1500,
                showConfirmButton: false,
            });
        } catch (err) {
            console.error(err);
            Swal.fire("Error", "Gagal export Excel", "error");
        }
    };

    // =====================================================
    // STATUS
    // =====================================================

    const statusChipClass = (status) => {
        return status === 1
            ? "bg-green-100 text-green-700 border border-green-300 rounded"
            : "bg-yellow-100 text-yellow-700 border border-yellow-300 rounded";
    };

    // =====================================================
    // PRINT TRANSAKSI
    // =====================================================

    async function handlePrint(transaksi_id) {
        const { data: responsePrint } = await $api.get(
            `${url.value}/api/transaksi/${transaksi_id}`
        );

        const transaksiData = responsePrint.data;

        const detailWithNames = await Promise.all(
            transaksiData.details.map(async (detail) => {
                const barangRes = await $api.get(
                    `${url.value}/api/entrybarang/${detail.transaksidetail_barang_id}`
                );

                const kodeBarang = await $api.get(
                    `${url.value}/api/codebarang/` +
                    barangRes.data.data.barangentry_code_id
                );

                return {
                    ...detail,
                    barangentry_nama:
                        barangRes.data.data.barangentry_nama ||
                        "Tidak Diketahui",
                    barangentry_code: kodeBarang.data.code_nama,
                };
            })
        );

        printToNewTab(transaksiData, detailWithNames);
    }

    // =====================================================
    // PRINT INVOICE
    // =====================================================

    function printToNewTab(data, items) {
        // Pindahkan isi function printToNewTab dari databasePenjualan.vue
        // yang lama ke bagian ini TANPA mengubah HTML invoice dan CSS print.
    }

    // =====================================================
    // LIFECYCLE
    // =====================================================

    onMounted(() => {
        fetchTransaksi();
    });

    // =====================================================
    // RETURN
    // =====================================================

    return {
        startDate,
        endDate,
        searchQuery,
        showDetailModal,
        trx_id,
        currentPage,
        itemsPerPage,

        activeDropdown,
        closeActionDropdown,
        setActionDropdownMenu,
        toggleActionDropdown,

        filteredTransaksi,
        paginatedTransaksi,
        totalTransaksi,
        totalPages,
        startItem,
        endItem,
        paginatedPages,

        formatCurrency,
        formatDate,
        statusChipClass,

        openViewDetail,
        deleteTransaksi,
        exportExcel,
        handlePrint,
    };
}