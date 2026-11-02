import { reactive, ref, onMounted, computed, watch, nextTick } from "vue";
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";

export function useLive() {
  const config = useRuntimeConfig();
  const { $api } = useNuxtApp();
  const url = ref(config.public.apiBase);

  const pengirimanData = ref({});
  const transactionData = ref([]);
  const searchQuery = ref("");
  const activeTab = ref("order");
  const currentPage = ref(1);
  const itemsPerPage = ref(10);

  const isModalOpenAddOrder = ref(false);
  const isModalOpenEditOrder = ref(false);
  const isSubmitting = ref(false);
  const isSubmittingEdit = ref(false);

  const jenisPlatform = ref([]);
  const selected = ref({ namaAkun: "", barang: [] });
  const errors = reactive({});
  const isModalOpen = ref(false);

  const form = ref({
    id: null,
    barang: "",
    namaAkun: "",
    platform: "",
    hargaTotal: "",
  });

  const barangInput = ref(null);

  onMounted(() => {
    fetchDataPengiriman();
  });

  // FETCH DATA
  const fetchDataPengiriman = async () => {
    try {
      if (activeTab.value === "order") {
        const res = await $api.get(
          `${url.value}/api/live-barang/data-live-grouped`
        );

        const resJenisPlatform = await $api.get(
          `${url.value}/api/platform`
        );

        const rawData = res.data.data || {};
        const filtered = {};

        for (const akun in rawData) {
          const items = rawData[akun].filter(
            (item) => Number(item.is_check) === 0
          );

          if (items.length > 0) {
            filtered[akun] = items;
          }
        }

        pengirimanData.value = filtered;
        jenisPlatform.value = resJenisPlatform.data.data;
      } else {
        const res = await $api.get(
          `${url.value}/api/live-barang/getAmountLive`
        );

        transactionData.value = res.data.data || [];
      }
    } catch (error) {
      console.error("Gagal fetch data:", error);
    }
  };

  // FLAT DATA
  const flatData = computed(() => {
    const result = [];

    for (const akun in pengirimanData.value) {
      pengirimanData.value[akun].forEach((item) => {
        result.push({
          ...item,
          akun,
        });
      });
    }

    return result;
  });

  // FILTER FLAT DATA
  const filteredFlatData = computed(() => {
    if (!searchQuery.value) return flatData.value;

    return flatData.value.filter((item) => {
      return (
        item.akun?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        item.barangentry_nama?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        item.code_nama?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        item.live_order_platform?.toLowerCase().includes(searchQuery.value.toLowerCase())
      );
    });
  });

  // SORT FLAT DATA
  const sortedFlatData = computed(() => {
    return [...filteredFlatData.value].sort((a, b) => {
      return new Date(b.created_at) - new Date(a.created_at);
    });
  });

  // PAGINATION FLAT DATA
  const paginatedFlatData = computed(() => {
    if (itemsPerPage.value === "all") return sortedFlatData.value;

    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;

    return sortedFlatData.value.slice(start, end);
  });

  // GROUPED SORTED DATA
  const groupedSortedData = computed(() => {
    const grouped = {};

    sortedFlatData.value.forEach((item) => {
      if (!grouped[item.akun]) {
        grouped[item.akun] = [];
      }

      grouped[item.akun].push(item);
    });

    return grouped;
  });

  const sortedGroupedEntries = computed(() => {
    return Object.entries(groupedSortedData.value).sort((a, b) => {
      const lastA = new Date(a[1][0]?.created_at || 0);
      const lastB = new Date(b[1][0]?.created_at || 0);

      return lastB - lastA;
    });
  });

  // GROUPED DATA
  const groupedData = computed(() => {
    const entries = Object.entries(pengirimanData.value);

    const sortedEntries = entries.map(([akun, items]) => {
      const sortedItems = [...items].sort((a, b) => {
        return new Date(b.created_at) - new Date(a.created_at);
      });

      return [akun, sortedItems];
    });

    sortedEntries.sort((a, b) => {
      const lastA = new Date(a[1][0]?.created_at || 0);
      const lastB = new Date(b[1][0]?.created_at || 0);

      return lastB - lastA;
    });

    const result = {};

    sortedEntries.forEach(([akun, items]) => {
      result[akun] = items;
    });

    return result;
  });

  // FILTER GROUPED DATA
  const filteredGroupedData = computed(() => {
    if (!searchQuery.value) return groupedData.value;

    const result = {};

    for (const akun in groupedData.value) {
      const filtered = groupedData.value[akun].filter((item) => {
        return (
          akun.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
          item.barangentry_nama?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
          item.code_nama?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
          item.live_order_platform?.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
      });

      if (filtered.length) result[akun] = filtered;
    }

    return result;
  });

  // PAGINATION GROUPED DATA
  const paginatedGroupedData = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;

    return Object.fromEntries(
      sortedGroupedEntries.value.slice(start, end)
    );
  });

  const totalGroups = computed(() =>
    Object.keys(filteredGroupedData.value).length
  );

  const startItem = computed(() => {
    if (totalGroups.value === 0) return 0;

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
    if (totalGroups.value === 0) return 0;

    if (itemsPerPage.value === "all") {
      return totalGroups.value;
    }

    return Math.min(
      currentPage.value * Number(itemsPerPage.value),
      totalGroups.value
    );
  });

  // TOTAL HARGA PER AKUN
  const totalHargaPerAkun = computed(() => {
    const result = {};

    for (const akun in groupedData.value) {
      let total = 0;

      groupedData.value[akun].forEach((item) => {
        if (Number(item.is_check) === 0) {
          total += Number(item.live_order_harga_terjual);
        }
      });

      result[akun] = total;
    }

    return result;
  });

  // TOTAL PAGES ORDER
  const totalPages = computed(() => {
    if (itemsPerPage.value === "all") return 1;

    return Math.ceil(
      sortedGroupedEntries.value.length / itemsPerPage.value
    );
  });

  const paginatedPages = computed(() => {
    const total = totalPages.value;
    const current = currentPage.value;
    const pages = [];

    if (total <= 5) {
      for (let i = 1; i <= total; i++) pages.push(i);
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

  // FILTER TRANSACTION
  const filteredTransaction = computed(() => {
    if (!searchQuery.value) return transactionData.value;

    return transactionData.value.filter((item) =>
      item.live_order_nama_akun
        ?.toLowerCase()
        .includes(searchQuery.value.toLowerCase())
    );
  });

  // PAGINATION TRANSACTION
  const pagination = computed(() => {
    if (itemsPerPage.value === "all") return filteredTransaction.value;

    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;

    return filteredTransaction.value.slice(start, end);
  });

  const transactionTotalPages = computed(() => {
    if (itemsPerPage.value === "all") return 1;

    return Math.max(
      1,
      Math.ceil(
        filteredTransaction.value.length /
        Number(itemsPerPage.value)
      )
    );
  });

  const transactionStartItem = computed(() => {
    if (filteredTransaction.value.length === 0) return 0;

    if (itemsPerPage.value === "all") {
      return 1;
    }

    return (
      (currentPage.value - 1) *
      Number(itemsPerPage.value) +
      1
    );
  });

  const transactionEndItem = computed(() => {
    if (filteredTransaction.value.length === 0) return 0;

    if (itemsPerPage.value === "all") {
      return filteredTransaction.value.length;
    }

    return Math.min(
      currentPage.value * Number(itemsPerPage.value),
      filteredTransaction.value.length
    );
  });

  const transactionPaginatedPages = computed(() => {
    const total = transactionTotalPages.value;
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

  // FORMAT CURRENCY
  const formatCurrency = (value) => {
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(Number(value));
  };

  const capitalizeFirst = (str) => {
    if (!str) return "";

    return str.charAt(0).toUpperCase() + str.slice(1);
  };

  // HANDLE MODAL
  const handleSave = () => {
    isModalOpen.value = false;
  };

  const handleRemoveItem = (index) => {
    selected.value.barang.splice(index, 1);
  };

  // OPEN MODAL EDIT TRANSACTION
  const openModalEditTransaksi = async (namaAkun) => {
    try {
      const { data } = await $api.get(
        `${url.value}/api/live-barang/data-live/akun/` + namaAkun
      );

      const barangBelumPackaging = data.data.filter(
        (item) => item.is_check === 0
      );

      if (barangBelumPackaging.length === 0) {
        Swal.fire({
          icon: "info",
          title: "Semua Barang Sudah di Packaging",
          text: "Tidak ada barang tersisa untuk diproses.",
        });

        return;
      }

      selected.value = {
        namaAkun: data.data[0].live_order_nama_akun,
        barang: barangBelumPackaging.map((item) => ({
          kode: item.code_nama,
          nama: item.barangentry_nama,
          jumlah: item.live_order_jumlah_barang,
          harga: parseFloat(item.live_order_harga_terjual),
          live_order_id: item.live_order_id,
          platform: item.live_order_platform,
          is_check: true,
        })),
      };

      isModalOpen.value = true;
    } catch (error) {
      console.error("Gagal ambil data:", error);
    }
  };

  // MODAL EDIT ORDER
  const closeModalEditOrder = () => {
    isModalOpenEditOrder.value = false;
    resetForm();
  };

  const resetForm = () => {
    form.value = {
      id: null,
      barang: "",
      namaAkun: "",
      platform: "",
      hargaTotal: "",
    };
  };

  // MODAL ADD ORDER
  const openModalAddOrder = () => {
    isModalOpenAddOrder.value = true;
  };

  const closeModalAddOrder = () => {
    isModalOpenAddOrder.value = false;
    resetForm();
  };

  // SUBMIT LIVE ORDER
  const submitLiveOrder = async () => {
    isSubmitting.value = true;

    try {
      const namaBarang = await $api.get(
        `${url.value}/api/entrybarang/getDataByCode/` + form.value.barang
      );

      await $api.post(`${url.value}/api/live-barang/store-live`, {
        live_order_barang_id: namaBarang.data.data.barangentry_id,
        live_order_nama_akun: form.value.namaAkun,
        live_order_platform: form.value.platform,
        live_order_harga_terjual: form.value.hargaTotal,
      });

      closeModalAddOrder();
      fetchDataPengiriman();

      Swal.fire(
        "Berhasil",
        "Order berhasil ditambahkan",
        "success"
      );
    } catch (e) {
      Swal.fire(
        "Error",
        "Gagal menambahkan order",
        "error"
      );
    }

    isSubmitting.value = false;
  };

  // EDIT ORDER
  const editOrderLive = async (id) => {
    try {
      const res = await $api.get(
        `${url.value}/api/live-barang/show-live/${id}`
      );

      const data = res.data.data;

      form.value.id = data.live_order_id;
      form.value.namaAkun = data.live_order_nama_akun;
      form.value.platform = data.live_order_platform;
      form.value.hargaTotal = data.live_order_harga_terjual;

      isModalOpenEditOrder.value = true;
    } catch (error) {
      console.error(error);
    }
  };

  // FORMAT TANGGAL
  function formatTanggal(dateStr) {
    if (!dateStr) return "-";

    const date = new Date(dateStr);

    return date.toLocaleString("id-ID", {
      day: "2-digit",
      month: "long",
      year: "numeric",
    });
  }

  // SUBMIT EDIT ORDER
  const submitLiveEditOrder = async () => {
    isSubmittingEdit.value = true;

    try {
      await $api.put(
        `${url.value}/api/live-barang/update-live/${form.value.id}`,
        {
          live_order_nama_akun: form.value.namaAkun,
          live_order_platform: form.value.platform,
          live_order_harga_terjual: form.value.hargaTotal,
        }
      );

      isModalOpenEditOrder.value = false;
      fetchDataPengiriman();

      Swal.fire(
        "Berhasil",
        "Order berhasil diupdate",
        "success"
      );
    } catch (e) {
      Swal.fire(
        "Error",
        "Gagal update",
        "error"
      );
    }

    isSubmittingEdit.value = false;
  };

  // DELETE ORDER
  const deleteOrder = async (id) => {
    const result = await Swal.fire({
      title: "Hapus Order?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Ya, hapus",
      cancelButtonText: "Batal",
      reverseButtons: true,
    });

    if (!result.isConfirmed) return;

    await $api.delete(
      `${url.value}/api/live-barang/delete-live/${id}`
    );

    fetchDataPengiriman();
  };

  // FORMAT HARGA INPUT
  const formattedHarga = computed(() => {
    if (!form.value.hargaTotal) return "";

    return form.value.hargaTotal
      .toString()
      .replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  });

  const updateHarga = (val) => {
    const number = val.replace(/\./g, "");
    form.value.hargaTotal = number;
  };

  watch(searchQuery, () => {
    currentPage.value = 1;
  });

  watch(activeTab, () => {
    currentPage.value = 1;
    fetchDataPengiriman();
  });

  watch(isModalOpenAddOrder, async (val) => {
    if (val) {
      await nextTick();
      barangInput.value?.focus();
    }
  });

  return {
    pengirimanData,
    transactionData,
    searchQuery,
    activeTab,
    currentPage,
    itemsPerPage,
    isModalOpenAddOrder,
    isModalOpenEditOrder,
    isSubmitting,
    isSubmittingEdit,
    jenisPlatform,
    selected,
    errors,
    isModalOpen,
    form,
    barangInput,
    fetchDataPengiriman,
    flatData,
    sortedFlatData,
    paginatedFlatData,
    filteredFlatData,
    groupedSortedData,
    sortedGroupedEntries,
    groupedData,
    filteredGroupedData,
    paginatedGroupedData,
    totalGroups,
    startItem,
    endItem,
    totalHargaPerAkun,
    totalPages,
    paginatedPages,
    filteredTransaction,
    pagination,
    transactionTotalPages,
    transactionStartItem,
    transactionEndItem,
    transactionPaginatedPages,
    formatCurrency,
    capitalizeFirst,
    handleSave,
    handleRemoveItem,
    openModalEditTransaksi,
    closeModalEditOrder,
    resetForm,
    openModalAddOrder,
    closeModalAddOrder,
    submitLiveOrder,
    editOrderLive,
    formatTanggal,
    submitLiveEditOrder,
    deleteOrder,
    formattedHarga,
    updateHarga,
  };
}