import { ref, computed, watch } from "vue";

export function usePaymentMethod() {
  const currentPage = ref(1);
  const itemsPerPage = ref(10);

  const paymentMethods = ref([
    {
      id: 1,
      name: "Cash",
      status: 1,
      updatedAt: new Date(),
      deleted: false,
    },
  ]);

  const search = ref("");
  const isModalOpen = ref(false);

  const form = ref({
    id: null,
    name: "",
    status: 1,
  });

  const filteredData = computed(() => {
    return paymentMethods.value
      .filter((x) => !x.deleted)
      .filter((x) =>
        x.name.toLowerCase().includes(search.value.toLowerCase())
      );
  });

  const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return filteredData.value.slice(start, end);
  });

  const totalPages = computed(() => {
    return Math.ceil(filteredData.value.length / itemsPerPage.value);
  });

  const paginatedPages = computed(() => {
    const total = totalPages.value;
    const current = currentPage.value;
    const pages = [];

    if (total <= 5) {
      for (let i = 1; i <= total; i++) pages.push(i);
    } else {
      if (current <= 3) pages.push(1, 2, 3, "...", total);
      else if (current >= total - 2)
        pages.push(1, "...", total - 2, total - 1, total);
      else
        pages.push(1, "...", current - 1, current, current + 1, "...", total);
    }

    return pages;
  });

  const openModal = (data = null) => {
    form.value = data
      ? { ...data }
      : { id: null, name: "", status: 1 };

    isModalOpen.value = true;
  };

  const closeModal = () => {
    isModalOpen.value = false;
  };

  const saveData = () => {
    if (!form.value.name) return;

    if (form.value.id) {
      const index = paymentMethods.value.findIndex(
        (x) => x.id === form.value.id
      );

      paymentMethods.value[index] = {
        ...form.value,
        updatedAt: new Date(),
      };
    } else {
      paymentMethods.value.push({
        ...form.value,
        id: Date.now(),
        updatedAt: new Date(),
        deleted: false,
      });
    }

    closeModal();
  };

  const deleteItem = (item) => {
    item.deleted = true;
  };

  const formatDate = (date) => {
    const d = new Date(date);

    const tanggal = d.toLocaleDateString("id-ID", {
      day: "2-digit",
      month: "long",
      year: "numeric",
    });

    const jam = d.toLocaleTimeString("id-ID", {
      hour: "2-digit",
      minute: "2-digit",
      hour12: false,
    }).replace(":", ".");

    return `${tanggal}, ${jam} WIB`;
  };

  const statusChipClass = (status) => {
    return status === 1
      ? "bg-green-50 text-green-700 border border-green-200"
      : "bg-red-50 text-red-700 border border-red-200";
  };

  const statusLabel = (status) => {
    return status === 1 ? "Aktif" : "Tidak Aktif";
  };

  watch(search, () => {
    currentPage.value = 1;
  });

  return {
    currentPage,
    itemsPerPage,
    search,
    isModalOpen,
    form,
    filteredData,
    paginatedData,
    totalPages,
    paginatedPages,
    openModal,
    closeModal,
    saveData,
    deleteItem,
    formatDate,
    statusChipClass,
    statusLabel,
  };
}