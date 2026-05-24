import { ref } from "vue";
import Swal from "sweetalert2";

export function useKeuangan() {

    const financeData = ref([]);
    const search = ref("");

    const isModalOpen = ref(false);

    const jenisPengeluaran = ref([
        "Ekspedisi",
        "Operasional",
        "Marketing",
    ]);

    const divisiList = ref([
        "Marketing",
        "Gudang",
        "Finance",
    ]);

    const sumberDanaList = ref([
        "Internal",
        "Eksternal",
    ]);

    const metodePembayaran = ref([
        "Cash",
        "Transfer",
        "QRIS",
    ]);

    const form = ref({
        id: null,
        tanggal: "",
        nama_pengeluaran: "",
        jenis_pengeluaran: "",
        divisi: "",
        jumlah_pengeluaran: "",
        sumber_dana: "",
        metode_pembayaran: "",
    });

    const filteredFinanceData = computed(() => {

        const keyword = search.value.toLowerCase();

        return financeData.value.filter((item) => {

            return (
                item.nama_pengeluaran?.toLowerCase().includes(keyword) ||
                item.jenis_pengeluaran?.toLowerCase().includes(keyword) ||
                item.divisi?.toLowerCase().includes(keyword) ||
                item.sumber_dana?.toLowerCase().includes(keyword)
            );

        });

    });

    const errors = ref({});

    const resetForm = () => {
        form.value = {
            id: null,
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
            form.value = { ...data };
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

    const saveData = () => {

        if (!validateForm()) return;

        if (form.value.id) {

            const index = financeData.value.findIndex(
                x => x.id === form.value.id
            );

            financeData.value[index] = {
                ...form.value,
            };

        } else {

            financeData.value.push({
                ...form.value,
                id: Date.now(),
            });

        }

        closeModal();

        Swal.fire({
            icon: "success",
            title: "Berhasil",
            text: "Data berhasil disimpan",
        });
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

        financeData.value = financeData.value.filter(
            x => x.id !== item.id
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

    return {
        financeData,
        isModalOpen,
        form,
        errors,
        search,
        filteredFinanceData,
        jenisPengeluaran,
        divisiList,
        sumberDanaList,
        metodePembayaran,
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