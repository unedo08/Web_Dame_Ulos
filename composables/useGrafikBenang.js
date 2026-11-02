import { ref, onMounted } from "vue";
import { useRuntimeConfig, useNuxtApp } from "#imports";

/**
 * Grafik Benang page (/grafik-benang).
 * Reads /api/grafik-benang/pertahun and /api/grafik-benang/perbulan, which
 * aggregate the existing Benang Masuk (produksi) and Benang Keluar data.
 */
export function useGrafikBenang() {
    const config = useRuntimeConfig();
    const url = ref(config.public.apiBase);
    const { $api } = useNuxtApp();

    const now = new Date();
    const currentYear = now.getFullYear();

    // Filters
    const tipe = ref("PEWARNA_ALAM");
    const tahun = ref(currentYear);
    // <input type="month"> value, e.g. "2026-10"
    const bulanInput = ref(`${currentYear}-${String(now.getMonth() + 1).padStart(2, "0")}`);

    // Tipe dropdown = pewarna_m (Pewarna Alam / Textile)
    const pewarnaList = ref([]);

    const pertahun = ref(null);
    const perbulan = ref(null);
    const isLoadingTahun = ref(false);
    const isLoadingBulan = ref(false);

    const loadPewarna = async () => {
        try {
            const res = await $api.get(`${url.value}/api/pewarna`);
            pewarnaList.value = res.data.data || [];
        } catch (err) {
            console.error("Gagal memuat tipe benang:", err);
        }
    };

    const fetchPertahun = async () => {
        isLoadingTahun.value = true;
        try {
            const res = await $api.get(`${url.value}/api/grafik-benang/pertahun`, {
                params: { tipe: tipe.value, tahun: tahun.value },
            });
            pertahun.value = res.data.data;
        } catch (err) {
            console.error("Gagal memuat grafik pertahun:", err);
            pertahun.value = null;
        } finally {
            isLoadingTahun.value = false;
        }
    };

    const fetchPerbulan = async () => {
        const [y, m] = (bulanInput.value || "").split("-");
        if (!y || !m) return;

        isLoadingBulan.value = true;
        try {
            const res = await $api.get(`${url.value}/api/grafik-benang/perbulan`, {
                params: { tipe: tipe.value, tahun: Number(y), bulan: Number(m) },
            });
            perbulan.value = res.data.data;
        } catch (err) {
            console.error("Gagal memuat grafik perbulan:", err);
            perbulan.value = null;
        } finally {
            isLoadingBulan.value = false;
        }
    };

    const fetchAll = () => Promise.all([fetchPertahun(), fetchPerbulan()]);

    onMounted(() => {
        loadPewarna();
        fetchAll();
    });

    return {
        currentYear,
        tipe,
        tahun,
        bulanInput,
        pewarnaList,
        pertahun,
        perbulan,
        isLoadingTahun,
        isLoadingBulan,
        fetchPertahun,
        fetchPerbulan,
        fetchAll,
    };
}
