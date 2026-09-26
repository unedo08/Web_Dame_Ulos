import { ref, computed, watch } from "vue";
import { useNuxtApp, useRuntimeConfig } from "#imports";

export function usePaymentMethod() {
  const { $api } = useNuxtApp();
  const config = useRuntimeConfig();
  const baseUrl = config.public.apiBase;

  const paymentMethods = ref([]);
  const search = ref("");
  const currentPage = ref(1);
  const itemsPerPage = ref(10);

  const isModalOpen = ref(false);
  const isEditMode = ref(false);
  const isLoading = ref(false);

  const form = ref({
    id: null,
    carabayar_nama: "",
    carabayar_status: 1,
  });

  async function fetchData() {
    try {
      const { data } = await $api.get(`${baseUrl}/api/carabayar/all`);
      paymentMethods.value = data.data ?? [];
    } catch (err) {
      console.error("Gagal fetch data cara bayar:", err);
    }
  }

  const filteredData = computed(() => {
    const q = search.value.toLowerCase().trim();
    if (!q) return paymentMethods.value;
    return paymentMethods.value.filter((item) =>
      item.carabayar_nama.toLowerCase().includes(q)
    );
  });

  const totalItems = computed(() => filteredData.value.length);

  const totalPages = computed(() =>
    Math.max(
      1,
      Math.ceil(totalItems.value / Number(itemsPerPage.value))
    )
  );

  const startItem = computed(() => {
    if (totalItems.value === 0) return 0;

    return (
      (currentPage.value - 1) *
      Number(itemsPerPage.value) +
      1
    );
  });

  const endItem = computed(() => {
    return Math.min(
      currentPage.value * Number(itemsPerPage.value),
      totalItems.value
    );
  });

  const paginatedData = computed(() => {
    const start =
      (currentPage.value - 1) *
      Number(itemsPerPage.value);

    return filteredData.value.slice(
      start,
      start + Number(itemsPerPage.value)
    );
  });

  const paginatedPages = computed(() => {
    const total = totalPages.value;
    const current = currentPage.value;

    if (total <= 5) {
      return Array.from(
        { length: total },
        (_, i) => i + 1
      );
    }

    if (current <= 3) {
      return [1, 2, 3, "...", total];
    }

    if (current >= total - 2) {
      return [
        1,
        "...",
        total - 2,
        total - 1,
        total,
      ];
    }

    return [
      1,
      "...",
      current - 1,
      current,
      current + 1,
      "...",
      total,
    ];
  });

  function openModal(data = null) {
    if (data) {
      isEditMode.value = true;
      form.value = {
        id: data.id,
        carabayar_nama: data.carabayar_nama,
        carabayar_status: data.carabayar_status,
      };
    } else {
      isEditMode.value = false;
      form.value = { id: null, carabayar_nama: "", carabayar_status: 1 };
    }
    isModalOpen.value = true;
  }

  function closeModal() {
    isModalOpen.value = false;
  }

  async function saveData() {
    if (!form.value.carabayar_nama.trim()) return false;
    isLoading.value = true;
    try {
      const payload = {
        carabayar_nama: form.value.carabayar_nama,
        carabayar_status: form.value.carabayar_status,
      };

      if (isEditMode.value) {
        await $api.put(`${baseUrl}/api/carabayar/${form.value.id}`, payload);
      } else {
        await $api.post(`${baseUrl}/api/carabayar`, payload);
      }

      closeModal();
      currentPage.value = 1;
      await fetchData();
      return true;
    } catch (err) {
      console.error("Gagal simpan data:", err);
      return false;
    } finally {
      isLoading.value = false;
    }
  }

  async function deleteItem(item) {
    try {
      await $api.delete(`${baseUrl}/api/carabayar/${item.id}`);
      await fetchData();
      if (currentPage.value > totalPages.value) {
        currentPage.value = totalPages.value;
      }
    } catch (err) {
      console.error("Gagal hapus data:", err);
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
    paymentMethods,
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
    startItem,
    endItem,
  };
}
