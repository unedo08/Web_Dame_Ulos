import { ref } from "vue";
import Swal from "sweetalert2";
import { useRuntimeConfig, useNuxtApp } from "#imports";

export function useCheckProduk() {

    const config = useRuntimeConfig();
    const url = ref(config.public.apiBase);
    const { $api } = useNuxtApp();
    const codeBarang = ref("");
    const product = ref(null);

    const handleSearch = async () => {
        if (!codeBarang.value) return;

        try {
            const response = await $api.get(
                `${url.value}/api/entrybarang/getDataKasir/${codeBarang.value}`
            );

            if (!response.data.data || response.data.data.length === 0) {
                Swal.fire({
                    title: "Kode Tidak Ditemukan",
                    text: "Kode produk tidak terdaftar dalam sistem. Pastikan kode sudah benar atau hubungi admin.",
                    icon: "warning",
                });

                product.value = null;
                return;
            }

            const kasirData = response.data.data[0];

            const codeResponse = await $api.get(
                `${url.value}/api/codebarang/${kasirData.barangentry_code_id}`
            );

            const codeData = codeResponse.data.code_nama;

            product.value = {
                ...kasirData,
                code_barang: codeData,
            };
            codeBarang.value = "";
        } catch (error) {
            Swal.fire({
                title: "Kode Tidak Ditemukan",
                text: "Kode produk tidak terdaftar dalam sistem. Pastikan kode sudah benar atau hubungi admin.",
                icon: "warning",

                confirmButtonText: "Tutup",

                customClass: {
                    popup: "cp-swal-popup",
                    confirmButton: "cp-swal-confirm-btn",
                },

                buttonsStyling: false,
            });

            product.value = null;
        }
    };

    const formatRupiah = (harga) => {
        if (!harga) return "Rp 0";

        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        }).format(harga);
    };

    return {
        codeBarang,
        product,
        handleSearch,
        formatRupiah,
    };
}