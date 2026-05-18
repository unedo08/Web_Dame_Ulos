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
                    title: "Tidak Ditemukan",
                    text: "Kode barang tidak valid",
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
            console.error(error);

            Swal.fire({
                title: "Error",
                text: "Gagal mengambil data produk",
                icon: "error",
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