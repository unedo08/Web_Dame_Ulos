import { ref, onMounted, computed, watch } from "vue";
import Swal from "sweetalert2";
import { useRuntimeConfig } from "#imports";
import * as XLSX from "xlsx";
import { saveAs } from "file-saver";

export function usePackaging() {
  const config = useRuntimeConfig();
  const url = config.public.apiBase;
  const { $api } = useNuxtApp();

  const pengirimanData = ref([]);
  const searchQuery = ref("");
  const currentPage = ref(1);
  const itemsPerPage = ref(10);

  const isModalOpen = ref(false);
  const selectedPengiriman = ref(null);
  const selectedBarang = ref([]);

  // FETCH DATA
  const fetchData = async () => {
    const res = await $api.get(
      `${url}/api/pengiriman-barang/get-pengiriman`
    );

    pengirimanData.value = res.data.data;
  };

  onMounted(fetchData);

  // FORMAT TANGGAL
  function formatTanggal(tanggal) {
    const date = new Date(tanggal);

    return new Intl.DateTimeFormat("id-ID", {
      day: "2-digit",
      month: "long",
      year: "numeric",
    }).format(date);
  }

  // OPEN MODAL EDIT
  const openModalEdit = async (row) => {
    selectedPengiriman.value = row;

    const trx_id = row.pengirimanBarang_transaksi_id;

    try {
      const res = await $api.get(
        `${url}/api/pengiriman-barang/get-transaksi-detail/${trx_id}`
      );

      if (!res.data.data || res.data.data.length === 0) {
        Swal.fire({
          icon: "warning",
          title: "Tidak ada barang!",
          text: "Detail barang untuk transaksi ini tidak ditemukan.",
        });

        return;
      }

      selectedBarang.value = res.data.data.map((item) => ({
        kode: item.code_nama,
        nama: item.barangentry_nama,
        jumlah: item.transaksidetail_jumlah_barang,
        harga: Number(item.transaksidetail_harga_barang),
        trx_detail_id: item.transaksidetail_id,
        is_check: true,
      }));

      isModalOpen.value = true;

    } catch (err) {
      console.error(err);

      Swal.fire({
        icon: "error",
        title: "Gagal!",
        text: "Barang Tidak Ditemukan",
      });
    }
  };

  // DELETE DATA
  const deleteData = async (id) => {
    const c = await Swal.fire({
      icon: "warning",
      title: "Hapus?",
      text: "Data akan dihapus permanen.",
      showCancelButton: true,
      confirmButtonText: "Ya, hapus",
      cancelButtonText: "Batal",
      reverseButtons: true,
    });

    if (!c.isConfirmed) return;

    await $api.delete(
      `${url}/api/pengiriman-barang/${id}`
    );

    fetchData();

    Swal.fire(
      "Berhasil",
      "Data terhapus",
      "success"
    );
  };

  // SELESAIKAN PACKAGING
  const selesaikanPackaging = async (row) => {
    try {
      const trxId = row.pengirimanBarang_transaksi_id;

      const res = await $api.get(
        `${url}/api/pengiriman-barang/get-transaksi-detail/${trxId}`
      );

      const barangList = res.data.data;

      if (!barangList || barangList.length === 0) {
        Swal.fire(
          "Tidak ada barang!",
          "Transaksi ini tidak memiliki barang.",
          "warning"
        );

        return;
      }

      for (const item of barangList) {
        await $api.post(`${url}/api/packaging`, {
          packaging_transactiondetail_id: item.transaksidetail_id,
          packaging_nama_akun: row.pengirimanBarang_nama_penerima,
          packaging_alamat: row.pengirimanBarang_alamat_pengiriman_barang,
        });
      }

      setTimeout(() => {
        fetchData();
      }, 50);

      Swal.fire({
        title: "Berhasil!",
        text: "Semua barang telah dimasukkan ke packaging.",
        icon: "success",
        timer: 900,
        showConfirmButton: false,
      });

    } catch (err) {
      console.error(err);

      Swal.fire(
        "Error!",
        "Gagal memproses packaging.",
        "error"
      );
    }
  };

  // EXPORT EXCEL
  const exportToExcel = async () => {
    try {
      Swal.fire({
        title: "Menyiapkan data…",
        text: "Mohon tunggu sebentar",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
      });

      const res = await $api.get(
        `${url}/api/pengiriman-barang/export-data`
      );

      const data = res.data.data;

      if (!data || !data.length) {
        Swal.close();

        Swal.fire(
          "Tidak ada data",
          "Data kosong",
          "info"
        );

        return;
      }

      data.sort((a, b) => {
        const keyA =
          `${a.pengirimanBarang_nama_penerima}|||${a.pengirimanBarang_alamat_pengiriman_barang}`.toLowerCase();

        const keyB =
          `${b.pengirimanBarang_nama_penerima}|||${b.pengirimanBarang_alamat_pengiriman_barang}`.toLowerCase();

        if (keyA !== keyB) {
          return keyA < keyB ? -1 : 1;
        }

        return new Date(a.created_at) - new Date(b.created_at);
      });

      const exportRows = data.map((r) => ({
        Tanggal: new Date(r.created_at),
        "Nama Penerima": r.pengirimanBarang_nama_penerima,
        "Nama Akun": r.pengirimanBarang_akun_penerima,
        Alamat: r.pengirimanBarang_alamat_pengiriman_barang,
        "Kode Barang": r.code_nama,
        Qty: r.transaksidetail_jumlah_barang,
        Harga: Number(r.transaksidetail_harga_barang),
      }));

      const worksheet = XLSX.utils.json_to_sheet(exportRows, {
        cellDates: true,
        dateNF: "dd-mmm-yyyy",
      });

      Object.keys(worksheet).forEach((cell) => {
        if (cell.startsWith("G") && cell !== "G1") {
          worksheet[cell].z = '"Rp" #,##0';
        }
      });

      const merges = [];
      let start = 0;

      while (start < exportRows.length) {
        const key =
          `${exportRows[start]["Nama Penerima"]}|||${exportRows[start]["Alamat"]}`;

        let end = start;

        while (
          end + 1 < exportRows.length &&
          `${exportRows[end + 1]["Nama Penerima"]}|||${exportRows[end + 1]["Alamat"]}` === key
        ) {
          end++;
        }

        if (end > start) {
          merges.push({
            s: { r: start + 1, c: 1 },
            e: { r: end + 1, c: 1 },
          });

          merges.push({
            s: { r: start + 1, c: 3 },
            e: { r: end + 1, c: 3 },
          });
        }

        start = end + 1;
      }

      worksheet["!merges"] = merges;

      worksheet["!cols"] = [
        { wch: 14 },
        { wch: 22 },
        { wch: 22 },
        { wch: 40 },
        { wch: 16 },
        { wch: 6 },
        { wch: 14 },
      ];

      const workbook = XLSX.utils.book_new();

      XLSX.utils.book_append_sheet(
        workbook,
        worksheet,
        "Packaging"
      );

      const buffer = XLSX.write(workbook, {
        bookType: "xlsx",
        type: "array",
      });

      const blob = new Blob([buffer], {
        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
      });

      saveAs(
        blob,
        `Packaging_${new Date().toISOString().split("T")[0]}.xlsx`
      );

      Swal.close();

      Swal.fire(
        "Berhasil",
        "Export Excel selesai.",
        "success"
      );

      fetchData();

    } catch (err) {
      console.error(err);

      Swal.close();

      Swal.fire(
        "Gagal",
        "Terjadi kesalahan saat export.",
        "error"
      );
    }
  };

  // HANDLE SAVE MODAL
  const handleSave = () => {
    isModalOpen.value = false;
    fetchData();
  };

  // SORT DATA
  const sortedPengirimanData = computed(() => {
    return [...pengirimanData.value].sort(
      (a, b) => b.pengirimanBarang_id - a.pengirimanBarang_id
    );
  });

  // FILTER DATA
  const filteredData = computed(() => {
    const q = searchQuery.value.toLowerCase();

    return sortedPengirimanData.value.filter((x) =>
      x.pengirimanBarang_nama_penerima?.toLowerCase().includes(q) ||
      x.pengirimanBarang_akun_penerima?.toLowerCase().includes(q) ||
      x.pengirimanBarang_alamat_pengiriman_barang?.toLowerCase().includes(q)
    );
  });

  // PAGINATION
  const pagination = computed(() => {
    if (itemsPerPage.value === "all") {
      return filteredData.value;
    }

    const start = (currentPage.value - 1) * itemsPerPage.value;

    return filteredData.value.slice(
      start,
      start + itemsPerPage.value
    );
  });

  const totalPages = computed(() =>
    itemsPerPage.value === "all"
      ? 1
      : Math.ceil(filteredData.value.length / itemsPerPage.value)
  );

  const startItem = computed(() => {
    if (filteredData.value.length === 0) return 0;

    return (
      (currentPage.value - 1) * Number(itemsPerPage.value) + 1
    );
  });

  const endItem = computed(() => {
    if (filteredData.value.length === 0) return 0;

    return Math.min(
      currentPage.value * Number(itemsPerPage.value),
      filteredData.value.length
    );
  });

  const paginatedPages = computed(() => {
    const total = totalPages.value;
    const current = currentPage.value;

    if (total <= 5) {
      return [...Array(total).keys()].map((i) => i + 1);
    }

    if (current <= 3) {
      return [1, 2, 3, "...", total];
    }

    if (current >= total - 2) {
      return [1, "...", total - 2, total - 1, total];
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

  // STATUS BADGE
  const statusChipClass = (status) => {
    switch (status) {
      case "DONE":
        return "bg-green-100 text-green-700 border border-green-300";

      case "IN PROGRESS":
        return "bg-yellow-100 text-yellow-700 border border-yellow-300";

      default:
        return "bg-gray-100 text-gray-600 border border-gray-300";
    }
  };

  // PRINT LABEL
  const printLabel = (row) => {
    const printWindow = window.open("", "_blank");

    const logoPath = `/image/DameUlosLogo2.png`;

    printWindow.document.write(`
      <!DOCTYPE html>
      <html>
      <head>
        <meta charset="UTF-8" />
        <title>Label Pengiriman</title>

        <style>
          @page {
            size: 100mm 150mm;
            margin: 0;
          }

          body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 14mm 10mm;
          }

          .container {
            width: 100%;
            height: 100%;
            box-sizing: border-box;
          }

          .logo {
            text-align: center;
            margin-bottom: 8px;
          }

          .logo img {
            width: 130px;
          }

          .section {
            margin-top: 6px;
            font-size: 14px;
          }

          .label {
            font-weight: bold;
          }

          .value {
            margin-top: 2px;
            margin-bottom: 8px;
          }

          .divider {
            border-top: 2px solid #000;
            margin: 10px 0;
          }

          .store {
            font-size: 13px;
          }

          .quote {
            margin-top: 10px;
            font-style: italic;
            font-size: 12px;
            line-height: 1.45;
          }

          .courier {
            text-align: right;
            font-weight: bold;
            font-size: 16px;
            margin-top: 8px;
          }
        </style>
      </head>

      <body>
        <div class="container">
          <div class="logo">
            <img src="${logoPath}" />
          </div>

          <div class="section">
            <div class="label">Nama :</div>
            <div class="value">${row.pengirimanBarang_nama_penerima || "-"}</div>

            <div class="label">Nomor HP :</div>
            <div class="value">${row.pengirimanBarang_no_telepon || "-"}</div>

            <div class="label">Alamat :</div>
            <div class="value">${row.pengirimanBarang_alamat_pengiriman_barang || "-"}</div>
          </div>

          <div class="divider"></div>

          <div class="store">
            <b>Galeri Dame Ulos</b><br>
            081262804500<br>
            Jl. Gereja HKBP Dame, Desa Lumban Matio, Saitnihuta, Kec. Tarutung, Kab. Tapanuli Utara
          </div>

          <div class="quote">
            “Terima kasih telah memilih Dame Ulos. Semoga ulos ini menjadi simbol kasih, kehangatan, dan doa baik dalam setiap kesempatan istimewa.”
          </div>

          <div class="courier">
            ${row.pengirimanBarang_jenis_pengiriman_barang || ""}
          </div>
        </div>

        <script>
          window.onload = function() {
            window.print();
            window.onafterprint = () => window.close();
          }
        <\/script>
      </body>
      </html>
    `);

    printWindow.document.close();
  };

  // WATCH CURRENT PAGE
  watch(currentPage, (v) => {
    if (v < 1) currentPage.value = 1;

    if (v > totalPages.value) {
      currentPage.value = totalPages.value;
    }
  });

  const formatCurrency = (val) =>
    new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
    }).format(val);

  return {
    pengirimanData,
    searchQuery,
    currentPage,
    itemsPerPage,
    isModalOpen,
    selectedPengiriman,
    selectedBarang,
    fetchData,
    formatTanggal,
    openModalEdit,
    deleteData,
    selesaikanPackaging,
    exportToExcel,
    handleSave,
    sortedPengirimanData,
    filteredData,
    pagination,
    totalPages,
    startItem,
    endItem,
    paginatedPages,
    statusChipClass,
    printLabel,
    formatCurrency,
  };
}