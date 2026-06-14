import { ref, computed, onMounted } from "vue";
import Swal from "sweetalert2";
import { useRuntimeConfig, useNuxtApp } from "#imports";
import * as XLSX from "xlsx";
import { saveAs } from "file-saver";

export function useKeuangan() {

    const config = useRuntimeConfig();
    const url = ref(config.public.apiBase);
    const { $api } = useNuxtApp();
    const financeData = ref([]);
    const search = ref("");
    const today = new Date().toISOString().slice(0, 10);
    const startDate = ref(today);
    const endDate = ref(today);
    const isModalOpen = ref(false);
    const jenisPengeluaran = ref([]);
    const divisiList = ref([]);
    const sumberDanaList = ref([]);
    const metodePembayaran = ref([]);

    const form = ref({
        pengeluaran_id: null,
        tanggal: "",
        nama_pengeluaran: "",
        jenis_pengeluaran: "",
        divisi: "",
        jumlah_pengeluaran: "",
        sumber_dana: "",
        metode_pembayaran: "",
    });
    const errors = ref({});

    const getData = async () => {
        try {
            const response = await $api.get(
                `${url.value}/api/pengeluaran`
            );
            financeData.value = response.data.data || [];
        } catch (error) {
            console.error(error);
            Swal.fire({
                title: "Error",
                text: "Gagal mengambil data pengeluaran",
                icon: "error",
            });
        }
    };

    async function getJenisPengeluaran() {
        const res = await $api.get(`${url.value}/api/jenis-pengeluaran`);
        jenisPengeluaran.value = res.data.data;
    }

    async function getDivisiList() {
        const res = await $api.get(`${url.value}/api/divisi`);
        divisiList.value = res.data.data;
    }

    async function getSumberDanaList() {
        const res = await $api.get(`${url.value}/api/sumber-dana`);
        sumberDanaList.value = res.data.data;
    }

    async function getMetodePembayaran() {
        const res = await $api.get(`${url.value}/api/carabayar`);
        metodePembayaran.value = res.data.data;
    }

    onMounted(() => {
        getData();
        getJenisPengeluaran();
        getDivisiList();
        getSumberDanaList();
        getMetodePembayaran();
    });

    const filteredFinanceData = computed(() => {

        const keyword = search.value.toLowerCase();

        return financeData.value.filter((item) => {

            return (
                item.jenis_pengeluaran_nama?.toLowerCase().includes(keyword) ||
                item.divisi_nama?.toLowerCase().includes(keyword) ||
                item.sumber_dana_nama?.toLowerCase().includes(keyword) ||
                item.cara_bayar_nama?.toLowerCase().includes(keyword)
            );

        });

    });

    const resetForm = () => {

        form.value = {
            pengeluaran_id: null,
            tanggal: "",
            nama_pengeluaran: "",
            jenis_pengeluaran: "",
            divisi: "",
            jumlah_pengeluaran: "",
            sumber_dana: "",
            metode_pembayaran: "",
        };

        errors.value = {};
    };

    const openModal = (data = null) => {
        if (data) {
            form.value = {
                pengeluaran_id: data.pengeluaran_id,
                tanggal: data.pengeluaran_tanggal,
                nama_pengeluaran: data.pengeluaran_nama,
                jenis_pengeluaran: data.pengeluaran_jenis_id,
                divisi: data.pengeluaran_divisi_id,
                jumlah_pengeluaran: data.pengeluaran_jumlah,
                sumber_dana: data.pengeluaran_sumber_dana_id,
                metode_pembayaran: data.pengeluaran_cara_bayar_id,
            };
        } else {
            resetForm();
        }
        isModalOpen.value = true;
    };

    const closeModal = () => {
        isModalOpen.value = false;
    };

    const validateForm = () => {
        errors.value = {};
        const requiredFields = [
            "tanggal",
            "nama_pengeluaran",
            "jenis_pengeluaran",
            "divisi",
            "jumlah_pengeluaran",
            "sumber_dana",
            "metode_pembayaran",
        ];
        requiredFields.forEach((field) => {
            if (!form.value[field]) {
                errors.value[field] = true;
            }
        });
        return Object.keys(errors.value).length === 0;
    };

    const saveData = async () => {
        if (!validateForm()) return;
        try {
            const payload = {
                pengeluaran_tanggal: form.value.tanggal,
                pengeluaran_nama: form.value.nama_pengeluaran,
                pengeluaran_jenis_id: form.value.jenis_pengeluaran,
                pengeluaran_divisi_id: form.value.divisi,
                pengeluaran_jumlah: parseInt(
                    form.value.jumlah_pengeluaran
                ),
                pengeluaran_sumber_dana_id: form.value.sumber_dana,
                pengeluaran_cara_bayar_id:
                    form.value.metode_pembayaran,
            };

            if (form.value.pengeluaran_id) {

                await $api.put(
                    `${url.value}/api/pengeluaran/${form.value.pengeluaran_id}`,
                    payload
                );

            } else {

                await $api.post(
                    `${url.value}/api/pengeluaran`,
                    payload
                );
            }
            closeModal();
            Swal.fire({
                icon: "success",
                title: "Berhasil",
                text: "Data berhasil disimpan",
            });
            getData();
        } catch (error) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Gagal menyimpan data",
            });
        }
    };

    const deleteData = async (item) => {

        const result = await Swal.fire({
            html: `
                <div class="keuangan-delete-confirmation">

                    <div class="keuangan-delete-icon-warning">
                        !
                    </div>

                    <div class="keuangan-delete-title">
                        Konfirmasi hapus data pengeluaran
                    </div>

                    <div class="keuangan-delete-subtitle">
                        Apakah Anda yakin ingin menghapus data pengeluaran ini?
                    </div>

                </div>
            `,

            showCancelButton: true,

            confirmButtonText: "Ya, dihapus",
            cancelButtonText: "Tidak",

            reverseButtons: true,

            customClass: {
                popup: "keuangan-swal-popup",
                confirmButton: "keuangan-swal-confirm-btn",
                cancelButton: "keuangan-swal-cancel-btn",
            },

            buttonsStyling: false,
        });

        if (!result.isConfirmed) return;

        try {

            await $api.delete(
                `${url.value}/api/pengeluaran/${item.pengeluaran_id}`
            );

            await Swal.fire({
                html: `
                    <div class="keuangan-delete-success-wrapper">

                        <div class="keuangan-delete-success-icon">
                            ✓
                        </div>

                        <div class="keuangan-delete-success-title">
                            Data berhasil dihapus
                        </div>

                        <div class="keuangan-delete-success-subtitle">
                            Data pengeluaran berhasil dihapus.
                        </div>

                    </div>
                `,

                confirmButtonText: "Tutup",

                customClass: {
                    popup: "keuangan-swal-popup",
                    confirmButton: "keuangan-swal-confirm-btn",
                },

                buttonsStyling: false,
            });
            getData();
        } catch (error) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Gagal menghapus data",
            });
        }
    };

    const formatDate = (date) => {
        if (!date) return "-";
        return new Date(date).toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "long",
            year: "numeric",
        });
    };

    const formatRupiah = (value) => {
        if (!value) return "Rp 0";
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        }).format(value);
    };

    const formatInputRupiah = (value) => {
        if (!value) return "";
        return new Intl.NumberFormat("id-ID").format(value);
    };

    const handleJumlahInput = (e) => {
        const raw = e.target.value.replace(/\D/g, "");
        form.value.jumlah_pengeluaran = raw;
    };

    const exportExcel = async () => {
        try {
            const res = await $api.get(`${url.value}/api/pengeluaran`, {
                params: {
                    start_date: startDate.value,
                    end_date: endDate.value,
                },
            });

            if (!res.data.data || !res.data.data.length) {
                Swal.fire("Info", "Data tidak tersedia", "info");
                return;
            }

            const rows = res.data.data;

            const excelData = rows.map((r, i) => ({
                No: i + 1,
                Tanggal: r.pengeluaran_tanggal,
                Pengeluaran: r.pengeluaran_nama,
                "Jenis Pengeluaran": r.jenis_pengeluaran_nama || "-",
                Divisi: r.divisi_nama || "-",
                "Jumlah Pengeluaran": Number(r.pengeluaran_jumlah),
                "Sumber Dana": r.sumber_dana_nama || "-",
                "Metode Pembayaran": r.cara_bayar_nama || "-",
            }));

            const ws = XLSX.utils.json_to_sheet(excelData);

            const rupiahFormat = '"Rp" #,##0';
            const range = XLSX.utils.decode_range(ws["!ref"]);

            for (let R = range.s.r + 1; R <= range.e.r; ++R) {
                const cell = XLSX.utils.encode_cell({ r: R, c: 5 });
                if (ws[cell]) {
                    ws[cell].t = "n";
                    ws[cell].z = rupiahFormat;
                }
            }

            const totalRow = range.e.r + 1;
            ws[`A${totalRow + 1}`] = { v: "TOTAL", t: "s" };
            ws[`F${totalRow + 1}`] = {
                f: `SUM(F2:F${totalRow})`,
                t: "n",
                z: rupiahFormat,
            };

            ws["!freeze"] = { xSplit: 0, ySplit: 1 };

            ws["!cols"] = [
                { wch: 5 },
                { wch: 15 },
                { wch: 25 },
                { wch: 20 },
                { wch: 15 },
                { wch: 20 },
                { wch: 18 },
                { wch: 20 },
            ];

            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Report Pengeluaran");

            const buffer = XLSX.write(wb, {
                bookType: "xlsx",
                type: "array",
                cellStyles: true,
            });

            saveAs(
                new Blob([buffer], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                }),
                `Report_Pengeluaran_${startDate.value}_${endDate.value}.xlsx`
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

    return {
        financeData,
        search,
        isModalOpen,
        form,
        errors,
        jenisPengeluaran,
        divisiList,
        sumberDanaList,
        metodePembayaran,
        filteredFinanceData,
        startDate,
        endDate,
        exportExcel,
        openModal,
        closeModal,
        saveData,
        deleteData,
        formatDate,
        formatRupiah,
        formatInputRupiah,
        handleJumlahInput,
    };
}