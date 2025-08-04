<script>
import { onMounted, ref } from 'vue'
import axios from 'axios'

const barcodeInput = ref('')
let barcodeTimeout = null

const datatableItems = ref([]) // <-- ini untuk datatable

const handleBarcodeInput = (e) => {
  if (barcodeTimeout) clearTimeout(barcodeTimeout)

  // abaikan jika bukan karakter alfanumerik atau enter
  if (e.key === 'Enter') {
    const scannedCode = barcodeInput.value.trim()
    if (scannedCode) {
      fetchDataByBarcode(scannedCode)
    }
    barcodeInput.value = ''
  } else if (/^[a-zA-Z0-9]$/.test(e.key)) {
    barcodeInput.value += e.key
  }

  // reset jika tidak ada input 300ms
  barcodeTimeout = setTimeout(() => {
    barcodeInput.value = ''
  }, 300)
}

const fetchDataByBarcode = async (code) => {
  try {
    const { data } = await axios.get(`/api/entrybarang/getDataByCode/${code}`)
    if (data) {
      datatableItems.value.push({
        name: data.name,
        quantity: 1,
        price: data.price
      })
    } else {
      alert('Data tidak ditemukan')
    }
  } catch (error) {
    console.error(error)
    alert('Gagal mengambil data barang')
  }
}

async function printPriceTag(id) {
  // try {
  const results = [];
  try {
    const responseCode = await axios.get(`${url.value}/api/codebarang/` + id);
    const code = responseCode.data.code_nama;
    const res = await axios.get(
      `${url.value}/api/entrybarang/getDataByCode/` + code
    );
    if (res.data) results.push(res.data);

    priceTagData.value = results;

    await nextTick();
    const content = printContent.value;
    if (!content) return;

    const printWindow = window.open("", "", "width=800,height=600");
    printWindow.document.write(`
      <html>
        <head>
          <title>Price Tag</title>
          <style>
            body { font-family: sans-serif; padding: 20px; line-height: 1.6; }
            ol { padding-left: 1rem; }
            table { width: 100%; border-collapse: collapse; }
            td { padding: 4px 8px; }
            @media print {
              body { margin: 0; }
              div { page-break-inside: avoid; }
            }
          </style>
        </head>
        <body>
          ${content.innerHTML}
        </body>
      </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
  } catch (err) {
    console.error(`Gagal ambil data untuk`, err);
  }
}

// print transaksi
function printToNewTab(data, items) {
  const printWindow = window.open("", "_blank");
  if (!printWindow) {
    alert("Pop-up blocker menghalangi membuka tab baru.");
    return;
  }

  const formatTanggal = (dateStr) => {
    if (!dateStr) return "-";
    const date = new Date(dateStr);
    return date.toLocaleString("id-ID", {
      day: "2-digit",
      month: "long",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  };

  const htmlContent = `
  <!DOCTYPE html>
  <html lang="id">
  <head>
    <meta charset="UTF-8" />
    <title>Print Transaksi</title>
    <style>
      /* Reset & base */
      body {
        font-family: Arial, sans-serif;
        margin: 0; padding: 20px;
        background: #fff;
        color: #000;
      }
      .print-area {
        max-width: 600px;
        margin: 0 auto;
        padding: 20px;
        box-sizing: border-box;
      }
      .text-center {
        text-align: center;
      }
      .mb-2 { margin-bottom: 0.5rem; }
      .mb-4 { margin-bottom: 1rem; }
      .my-4 { margin-top: 1rem; margin-bottom: 1rem; }
      .font-bold { font-weight: 700; }
      .font-semibold { font-weight: 600; }
      .text-sm { font-size: 0.875rem; }
      .text-base { font-size: 1rem; }
      .w-24 { width: 96px; }
      .h-auto { height: auto; }
      .mx-auto { margin-left: auto; margin-right: auto; }
      .table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
      }
      table th, table td {
        padding: 8px 6px;
        border-bottom: 1px solid #ccc;
      }
      table th {
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #444;
      }
      table td {
        vertical-align: top;
      }
      table td.text-left {
        text-align: left;
      }
      .table tbody tr:last-child td {
        border-bottom: 2px solid #000;
      }
      .total-bayar {
        text-align: right;
        font-weight: 700;
        font-size: 1.1rem;
        margin-top: 20px;
      }
      /* Print styles */
      @media print {
        body {
          margin: 0; padding: 0;
        }
        .print-area {
          box-shadow: none;
          width: 100%;
          max-width: none;
          margin: 0;
          padding: 0;
        }
      }
    </style>
  </head>
  <body>
    <div class="print-area">

      <div class="text-center mb-4">
        <img src="/image/DameUlosLogo2.png" alt="Logo" class="w-24 h-auto mx-auto mb-2" />
        <h2 class="font-bold text-base">Dame Ulos Tarutung</h2>
      </div>

      <h2 class="font-bold text-center mb-2">Struk Transaksi</h2>
      <p class="text-center text-sm mb-4">Terima kasih telah berbelanja!</p>

      <div class="mb-4 text-sm">
        <p><strong>Nama Customer:</strong> ${
          data.transaksi_nama_customer || "-"
        }</p>
        <p><strong>No Telepon:</strong> ${
          data.transaksi_nomor_telepon || "-"
        }</p>
        <p><strong>Metode Pembayaran:</strong> ${
          data.transaksi_cara_bayar || "-"
        }</p>
        <p><strong>Jumlah Barang:</strong> ${
          data.transaksi_jumlah_barang ||
          items.reduce((acc, i) => acc + i.transaksidetail_jumlah_barang, 0)
        }</p>
        <p><strong>Total:</strong> Rp. ${formatRupiahSubtotal(
          parseFloat(data.transaksi_total_harga)
        )}</p>
        <p><strong>Waktu:</strong> ${formatTanggal(data.created_at)}</p>
      </div>

      <div class="my-4">
        <h3 class="font-semibold mb-2">Detail Barang:</h3>
        <table class="table">
          <thead>
            <tr>
              <th>Nama</th>
              <th class="text-left">Qty</th>
              <th class="text-left">Harga</th>
              <th class="text-left">Total</th>
            </tr>
          </thead>
          <tbody>
            ${items
              .map(
                (item) => `
              <tr>
                <td>${item.barangentry_nama}</td>
                <td class="text-left">${item.transaksidetail_jumlah_barang}</td>
                <td class="text-left">${formatRupiah(
                  item.transaksidetail_harga_barang
                )}</td>
                <td class="text-left">${formatRupiah(
                  item.transaksidetail_jumlah_barang *
                    item.transaksidetail_harga_barang
                )}</td>
              </tr>
            `
              )
              .join("")}
          </tbody>
        </table>
      </div>

      <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 1.1rem; margin-top: 20px;">
  <div>Jumlah Barang: ${items.reduce(
    (acc, i) => acc + i.transaksidetail_jumlah_barang,
    0
  )}</div>
  <div>Subtotal: Rp. ${formatRupiahSubtotal(
    parseFloat(data.transaksi_total_harga)
  )}</div>
</div>

    </div>

    <script>
      window.onload = function() {
        window.print();
      };
    <\/script>
  </body>
  </html>
  `;

  printWindow.document.write(htmlContent);
  printWindow.document.close();
}

onMounted(() => {
  window.addEventListener('keydown', handleBarcodeInput)
})
</script>