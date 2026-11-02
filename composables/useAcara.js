import {
  ref,
  computed,
  onMounted,
  watch,
  reactive
} from "vue";

import * as XLSX from "xlsx";
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";

export function useAcara() {
  const { $api } = useNuxtApp();

  // =========================================
  // STATE
  // =========================================

  const scannedCodes = ref([]);
  const listBarangAcara = ref([]);
  const searchQuery = ref("");

  const url = ref("");
  const acara = ref([]);
  const acaraCounter = ref(1);

  const isEditModalOpen = ref(false);
  const editForm = ref({});
  const barcodeInput = ref("");
  const tempBarangList = ref([]);
  const listAcara = ref([]);

  const errors = reactive({});

  const currentPage = ref(1);
  const itemsPerPage = ref(10);

  const newProduct = ref({
    acara_id: 0,
    acara_nama: "",
    acara_jumlahbarang: 0,
    acara_modalbarang: "",
    acara_harganetbarang: 0,
    acara_hargapricetagbarang: 0,
    acara_keterangan: "",
    acara_status: "Belum Selesai",
  });

  const isModalOpen = ref(false);
  const isModalPrintOpen = ref(false);
  const selectedProduct = ref(null);
  const printJumlah = ref(1);


  // =========================================
  // INITIALIZATION
  // =========================================

  onMounted(async () => {
    const config = useRuntimeConfig();

    url.value = config.public.apiBase;

    try {
      await getListAcara();
    } catch (error) {
      console.error("Gagal mengambil data acara:", error);
    }

    isModalPrintOpen.value = false;
  });


  // =========================================
  // SORTING DAN SEARCH
  // =========================================

  const sortedListAcara = computed(() => {
    const sortedSearch = [...listAcara.value].sort((a, b) => {
      return new Date(b.created_at) - new Date(a.created_at);
    });

    const q = searchQuery.value.toLowerCase();

    return q
      ? sortedSearch.filter((item) =>
          item.acara_nama?.toLowerCase().includes(q)
        )
      : sortedSearch;
  });


  // =========================================
  // PAGINATION
  // =========================================

  const pagination = computed(() => {
    const start =
      (currentPage.value - 1) * itemsPerPage.value;

    const end = start + itemsPerPage.value;

    return sortedListAcara.value.slice(start, end);
  });

  const totalPages = computed(() => {
    return Math.ceil(
      sortedListAcara.value.length / itemsPerPage.value
    );
  });

  const startItem = computed(() => {
    if (sortedListAcara.value.length === 0) return 0;

    return (
      (currentPage.value - 1) * itemsPerPage.value + 1
    );
  });

  const endItem = computed(() => {
    if (sortedListAcara.value.length === 0) return 0;

    return Math.min(
      currentPage.value * itemsPerPage.value,
      sortedListAcara.value.length
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


  // =========================================
  // VALIDASI
  // =========================================

  function validate() {
    errors.acara_nama = !newProduct.value.acara_nama
      ? "Nama Acara wajib diisi"
      : "";

    errors.acara_keterangan = !newProduct.value.acara_keterangan
      ? "Keterangan wajib diisi"
      : "";

    return Object.values(errors).every((err) => !err);
  }


  // =========================================
  // FORMAT RUPIAH
  // =========================================

  const formatRupiah = (harga) => {
    if (!harga) return "";

    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(harga);
  };


  // =========================================
  // GET DATA ACARA
  // =========================================

  async function getListAcara() {
    try {
      const response = await $api.get(
        `${url.value}/api/acara`
      );

      listAcara.value = response.data.data || [];

    } catch (error) {
      console.error("Gagal memuat data acara:", error);
    }
  }


  // =========================================
  // EDIT ACARA
  // =========================================

  const editItem = async (item) => {
    isEditModalOpen.value = true;
    editForm.value = { ...item };
    scannedCodes.value = [];

    try {
      const res = await $api.get(
        `${url.value}/api/acaradet/getListBarangAcara/${item.acara_id}`
      );

      listBarangAcara.value = res.data.data || [];

    } catch {
      listBarangAcara.value = [];
    }
  };


  // =========================================
  // BARCODE
  // =========================================

  const addToTemp = () => {
    const code = barcodeInput.value.trim();

    if (!code) return;

    if (scannedCodes.value.includes(code)) {
      Swal.fire(
        "Duplikat",
        "Kode sudah ada",
        "warning"
      );

      return;
    }

    scannedCodes.value.push(code);
    barcodeInput.value = "";
  };


  const addListBarangAcara = async () => {
    if (scannedCodes.value.length === 0) {
      Swal.fire(
        "Kosong",
        "Tidak ada barcode",
        "warning"
      );

      return;
    }

    try {
      const res = await $api.post(
        `${url.value}/api/acaradet/addListBarangAcara`,
        {
          code_nama: scannedCodes.value
        }
      );

      const newItems = res.data.data || [];
      const map = new Map();

      [
        ...listBarangAcara.value,
        ...newItems
      ].forEach((item) => {
        map.set(item.barangentry_id, item);
      });

      listBarangAcara.value = Array.from(map.values());
      scannedCodes.value = [];

    } catch (err) {
      Swal.fire(
        "Gagal",
        "Gagal tambah ke list",
        "error"
      );
    }
  };


  const removeItem = (id) => {
    listBarangAcara.value =
      listBarangAcara.value.filter(
        (i) => i.barangentry_id !== id
      );
  };


  // =========================================
  // SUBMIT EDIT ACARA
  // =========================================

  const submitEdit = async () => {
    if (listBarangAcara.value.length === 0) {
      Swal.fire(
        "Kosong",
        "Tidak ada barang",
        "warning"
      );

      return;
    }

    try {
      const ids = listBarangAcara.value.map(
        (i) => i.barangentry_id
      );

      await $api.post(
        `${url.value}/api/acaradet/addDetAcara`,
        {
          acaradet_acara_id: editForm.value.acara_id,
          acaradet_barangentry_id: ids
        }
      );

      const jumlahBarang = listBarangAcara.value.length;

      const totalModal = listBarangAcara.value.reduce(
        (sum, i) =>
          sum + Number(i.barangentry_modal || 0),
        0
      );

      const totalHargaNet = listBarangAcara.value.reduce(
        (sum, i) =>
          sum + Number(i.barangentry_harga_net || 0),
        0
      );

      const totalPriceTag = listBarangAcara.value.reduce(
        (sum, i) =>
          sum + Number(i.barangentry_price_tag || 0),
        0
      );

      await $api.put(
        `${url.value}/api/acara/updateAcara/${editForm.value.acara_id}`,
        {
          acara_jumlahbarang: jumlahBarang,
          acara_modalbarang: totalModal,
          acara_harganetbarang: totalHargaNet,
          acara_hargapricetagbarang: totalPriceTag,
          acara_keterangan: "Ready To store",
          acara_status: "Ready"
        }
      );

      Swal.fire(
        "Berhasil",
        "Acara berhasil diupdate",
        "success"
      );

      closeEditModal();
      await getListAcara();

    } catch (err) {
      console.error(err);

      Swal.fire(
        "Gagal",
        "Gagal menyimpan acara",
        "error"
      );
    }
  };


  // =========================================
  // TANDAI SELESAI
  // =========================================

  const markAsDone = async (item) => {
    const result = await Swal.fire({
      title: "Tandai Selesai?",
      text: `Acara "${item.acara_nama}" akan diubah menjadi Selesai.`,
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Ya, Selesaikan",
      cancelButtonText: "Batal",
      reverseButtons: true,
    });

    if (!result.isConfirmed) return;

    try {
      await $api.put(
        `${url.value}/api/acara/updateStatusAcara/${item.acara_id}`,
        {
          acara_status: "Selesai",
        }
      );

      Swal.fire({
        title: "Berhasil",
        text: `Acara "${item.acara_nama}" telah diselesaikan.`,
        icon: "success",
        timer: 1500,
        showConfirmButton: false,
      });

      await getListAcara();

    } catch (error) {
      console.error(
        "Gagal update status acara:",
        error
      );

      Swal.fire({
        title: "Gagal",
        text: "Terjadi kesalahan saat mengubah status.",
        icon: "error",
      });
    }
  };


  // =========================================
  // CLOSE EDIT MODAL
  // =========================================

  const closeEditModal = () => {
    isEditModalOpen.value = false;
    barcodeInput.value = "";
    scannedCodes.value = [];
    listBarangAcara.value = [];
  };


  // =========================================
  // WATCHER
  // =========================================

  watch(currentPage, (val) => {
    if (val < 1) {
      currentPage.value = 1;
    }

    if (val > totalPages.value) {
      currentPage.value = totalPages.value;
    }
  });

  watch(
    acara,
    (val) => {
      localStorage.setItem(
        "acaraList",
        JSON.stringify(val)
      );
    },
    { deep: true }
  );

  watch(acaraCounter, (val) => {
    localStorage.setItem(
      "acaraIdCounter",
      val.toString()
    );
  });


  // =========================================
  // MODAL TAMBAH ACARA
  // =========================================

  const openModal = () => {
    isModalOpen.value = true;
  };

  const closeModal = () => {
    isModalOpen.value = false;
  };


  const submitAcara = async () => {
    if (!validate()) {
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

    try {
      await $api.post(
        `${url.value}/api/acara/addAcara`,
        {
          acara_nama: newProduct.value.acara_nama,
          acara_keterangan: newProduct.value.acara_keterangan,
        }
      );

      await getListAcara();

      newProduct.value = {
        acara_id: 0,
        acara_nama: "",
        acara_jumlahbarang: 0,
        acara_modalbarang: "",
        acara_harganetbarang: 0,
        acara_hargapricetagbarang: 0,
        acara_keterangan: "",
        acara_status: "Belum Selesai",
      };

      closeModal();

    } catch (error) {
      console.error(
        "Gagal menambahkan data acara:",
        error
      );
    }
  };


  // =========================================
  // DELETE ACARA
  // =========================================

  const deleteProduct = async (id, acara_nama) => {
    const result = await Swal.fire({
      title: "Konfirmasi Hapus",
      text: `Anda yakin ingin menghapus "${acara_nama}"?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Ya, Hapus!",
      cancelButtonText: "Batal",
      reverseButtons: true,
    });

    if (result.isConfirmed) {
      try {
        const response = await $api.delete(
          `${url.value}/api/acara/deleteAcara/${id}`
        );

        if (response.status === 200) {
          listAcara.value = listAcara.value.filter(
            (item) => item.acara_id !== id
          );

          await Swal.fire({
            title: "Berhasil!",
            text: `"${acara_nama}" telah dihapus.`,
            icon: "success",
            timer: 1500,
            showConfirmButton: false,
          });
        }

      } catch (error) {
        console.error(
          "Error deleting product:",
          error
        );

        Swal.fire({
          title: "Gagal",
          text: "Terjadi kesalahan saat menghapus data.",
          icon: "error",
        });
      }
    }
  };


  // =========================================
  // BARCODE TAMBAHAN
  // =========================================

  const addToTempBarang = () => {
    const code = barcodeInput.value.trim();

    if (!code) return;

    if (scannedCodes.value.includes(code)) {
      Swal.fire(
        "Duplikat",
        "Kode sudah ada di list",
        "warning"
      );

      return;
    }

    scannedCodes.value.push(code);
    barcodeInput.value = "";
  };


  const removeFromTempBarang = async (id) => {
    if (confirm("Anda yakin ingin menghapus data ini?")) {
      try {
        const response = await $api.delete(
          `${url.value}/api/acaradet/deleteDetAcara/` + id
        );

        if (response.status === 200) {
          tempBarangList.value =
            tempBarangList.value.filter(
              (item) => item.acaradet_id !== id
            );
        }

      } catch (error) {
        console.error(
          "Gagal menghapus data acara:",
          error
        );
      }
    }
  };


  // =========================================
  // EXPORT EXCEL
  // =========================================

  const exportItem = async (id) => {
    try {
      const response = await $api.get(
        `${url.value}/api/acara/export/${id}`
      );

      const acaraData = response.data.data.acara;
      const detailData = response.data.data.detail;

      const headers = [
        "Nama Acara",
        "Jumlah Barang",
        "Modal",
        "Harga Net",
        "Price Tag",
        "Keterangan",
        "Status",
        "ID Detail",
        "ID Barang Entry",
        "Created At",
        "Updated At",
      ];

      const rows = detailData.map((item) => [
        acaraData.acara_nama,
        acaraData.acara_jumlahbarang,
        acaraData.acara_modalbarang,
        acaraData.acara_harganetbarang,
        acaraData.acara_hargapricetagbarang,
        acaraData.acara_keterangan,
        acaraData.acara_status,
        item.acaradet_id,
        item.acaradet_barangentry_id,
        item.created_at,
        item.updated_at,
      ]);

      const data = [headers, ...rows];

      const worksheet = XLSX.utils.aoa_to_sheet(data);

      const mergeRange = {
        s: { r: 1, c: 0 },
        e: { r: detailData.length, c: 0 },
      };

      worksheet["!merges"] = [mergeRange];

      const workbook = XLSX.utils.book_new();

      XLSX.utils.book_append_sheet(
        workbook,
        worksheet,
        "Export Acara"
      );

      const filename = `Export_Acara_${acaraData.acara_nama.replace(
        /\s+/g,
        "_"
      )}.xlsx`;

      XLSX.writeFile(workbook, filename);

    } catch (error) {
      console.error(
        "Gagal mengekspor data acara:",
        error
      );

      Swal.fire(
        "Gagal",
        "Gagal mengekspor data.",
        "error"
      );
    }
  };


  // =========================================
  // PRINT
  // =========================================

  const closePrintModal = () => {
    isModalPrintOpen.value = false;
    selectedProduct.value = null;
  };

  const handlePrint = () => {
    if (!selectedProduct.value) return;

    alert(
      "Simulasi print acara: " +
      selectedProduct.value.nama_acara
    );

    closePrintModal();
  };


  // =========================================
  // RETURN
  // =========================================

  return {
    searchQuery,
    pagination,
    sortedListAcara,

    currentPage,
    itemsPerPage,
    totalPages,
    startItem,
    endItem,
    paginatedPages,

    isModalOpen,
    isModalPrintOpen,
    isEditModalOpen,

    newProduct,
    errors,
    editForm,
    barcodeInput,
    scannedCodes,
    listBarangAcara,
    printJumlah,

    formatRupiah,

    openModal,
    closeModal,
    submitAcara,

    editItem,
    closeEditModal,
    addToTemp,
    addListBarangAcara,
    removeItem,
    submitEdit,

    markAsDone,
    deleteProduct,
    exportItem,

    closePrintModal,
    handlePrint,

    // State/function tambahan dari script lama
    acara,
    acaraCounter,
    selectedProduct,
    tempBarangList,
    listAcara,
    addToTempBarang,
    removeFromTempBarang,
    getListAcara,
  };
}