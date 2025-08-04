<template>
  <div>
    <h2 class="text-lg font-semibold mb-4">Barang Masuk</h2>
    <div class="flex space-x-6">
      <button
        @click="activeTab = 'wait'"
        class="pb-1 text-sm relative"
        :class="activeTab === 'wait' ? 'text-black' : 'text-gray-500'"
      >
        Awaiting Stock
        <span
          v-if="activeTab === 'wait'"
          class="absolute left-0 right-0 -bottom-0.5 h-[2px] bg-red-900 mx-auto"
          style="width: 90%"
        ></span>
      </button>

      <button
        @click="activeTab = 'ready'"
        class="pb-1 text-sm relative"
        :class="activeTab === 'ready' ? 'text-black' : 'text-gray-500'"
      >
        Ready Stock
        <span
          v-if="activeTab === 'ready'"
          class="absolute left-0 right-0 -bottom-0.5 h-[2px] bg-red-900 mx-auto"
          style="width: 90%"
        ></span>
      </button>
    </div>
    <div class="mx-auto" v-show="activeTab === 'wait'">
      <!-- <div class="judul text-xs font-semibold mb-2">Wait to Entry</div> -->
      <div class="flex flex-wrap justify-end gap-4">
        <button
          v-if="isSearchActive"
          @click="resetSearch"
          class="btn-reset-pencarian bg-red-500 text-white text-center rounded hover:bg-red-600 btn-s w-[110px] h-[30px]"
        >
          Reset Pencarian
        </button>
        <button
          class="btn-add bg-yellow-500 text-white text-center rounded-md hover:bg-yellow-600 w-[75px] h-[30px]"
          @click="openSearchModal"
        >
          🔍 Search
        </button>
        <button
          class="btn-add bg-green-500 text-white text-center rounded-md hover:bg-green-600 w-[60px] h-[30px]"
          @click="openModal('desc')"
        >
          + Desc
        </button>
        <button
          class="btn-add bg-green-500 text-white text-center rounded-md hover:bg-green-600 w-[60px] h-[30px]"
          @click="openModal('size')"
        >
          + Size
        </button>
        <button
          class="btn-print bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[85px] h-[30px]"
          @click="openModal('priceTag')"
        >
          Print Price Tag
        </button>
      </div>

      <div class="overflow-x-auto w-full">
        <table class="datatable w-full rounded-md overflow-hidden text-sm">
          <thead class="bg-blue-100">
            <tr>
              <th class="px-4 py-2 text-left">Tanggal</th>
              <th class="px-4 py-2 text-left">Nama Ulos</th>
              <th class="px-4 py-2 text-left">Warna Ulos</th>
              <th class="px-4 py-2 text-left">Nama Penenun</th>
              <th class="px-4 py-2 text-left">Nama Panirat</th>
              <th class="px-4 py-2 text-left">Dyer</th>
              <th class="px-4 py-2 text-left">Modal</th>
              <th class="px-4 py-2 text-left">Price Tag</th>
              <th class="px-4 py-2 text-left">Harga Net</th>
              <th class="px-4 py-2 text-left">Jumlah</th>
              <th class="px-4 py-2 text-left">Acara</th>
              <th class="px-4 py-2 text-left">Ukuran Mandar</th>
              <th class="px-4 py-2 text-left">Ukuran Ulos</th>
              <!-- <th class="px-4 py-2 text-left">Aksi</th> -->
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="barang in isSearchActive ? filteredBarang : pagination"
              :key="barang.kode_barang"
              class="odd:bg-white even:bg-gray-50 hover:bg-gray-100"
            >
              <td class="px-4 py-2">
                {{ formatTanggal(barang.created_at) }}
              </td>
              <td class="px-4 py-2">{{ barang.barangentry_nama }}</td>
              <td class="px-4 py-2">{{ barang.barangentry_warna }}</td>
              <td class="px-4 py-2">{{ barang.barangentry_nama_penenun }}</td>
              <td class="px-4 py-2">{{ barang.barangentry_nama_panirat }}</td>
              <td class="px-4 py-2">{{ barang.barangentry_dryer }}</td>
              <td class="px-4 py-2">
                {{ formatRupiah(barang.barangentry_modal) }}
              </td>
              <td class="px-4 py-2">
                {{ formatRupiah(barang.barangentry_price_tag) }}
              </td>
              <td class="px-4 py-2">
                {{ formatRupiah(barang.barangentry_harga_net) }}
              </td>
              <td class="px-4 py-2">
                {{ barang.barangentry_jumlah_barang }}
              </td>
              <td class="px-4 py-2">{{ barang.barangentry_acara }}</td>
              <td class="px-4 py-2">
                {{ barang.barangentry_ukuran_mandar }}
              </td>
              <td class="px-4 py-2">{{ barang.barangentry_ukuran_ulos }}</td>
            </tr>
          </tbody>
        </table>

        <div class="flex justify-between items-center mt-4 text-xs">
          <div class="flex items-center space-x-2">
            <label for="perPage">Tampilkan:</label>
            <select
              id="perPage"
              v-model="itemsPerPage"
              class="border px-2 py-1 rounded text-xs"
            >
              <option :value="5">5</option>
              <option :value="10">10</option>
              <option :value="20">20</option>
              <option :value="50">50</option>
            </select>
          </div>

          <div class="flex items-center space-x-2">
            <button
              class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400 text-xs"
              :disabled="currentPage === 1"
              @click="currentPage--"
            >
              Sebelumnya
            </button>

            <button
              v-for="(page, index) in paginatedPages"
              :key="index"
              @click="typeof page === 'number' && (currentPage = page)"
              :class="[
                'px-3 py-1 rounded text-xs',
                currentPage === page ? 'bg-blue-500 text-white' : 'bg-gray-200',
                page === '...' ? 'cursor-default' : 'cursor-pointer',
              ]"
              :disabled="page === '...'"
            >
              {{ page }}
            </button>

            <button
              class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400 text-xs"
              :disabled="currentPage === totalPages"
              @click="currentPage++"
            >
              Selanjutnya
            </button>
          </div>
        </div>
      </div>
    </div>
    <div class="mx-auto" v-show="activeTab === 'ready'">
      <!-- <div class="judul text-xs font-semibold mb-2">Ready to Stock</div> -->
      <div class="flex flex-wrap justify-end gap-4">
        <button
          v-if="isSearchActive"
          @click="resetSearch"
          class="btn-reset-pencarian bg-red-500 text-white text-center rounded hover:bg-red-600 btn-s w-[110px] h-[30px]"
        >
          Reset Pencarian
        </button>
        <button
          class="btn-add bg-yellow-500 text-white text-center rounded-md hover:bg-yellow-600 w-[75px] h-[30px]"
          @click="openSearchModal"
        >
          🔍 Search
        </button>
        <button
          class="btn-print bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[85px] h-[30px]"
          @click="openModal('priceTag')"
        >
          Print Price Tag
        </button>
      </div>

      <div class="overflow-x-auto w-full">
        <table class="datatable w-full rounded-md overflow-hidden text-sm">
          <thead class="bg-blue-100">
            <tr>
              <th class="px-4 py-2 text-left">Tanggal</th>
              <th class="px-4 py-2 text-left">Nama Ulos</th>
              <th class="px-4 py-2 text-left">Warna Ulos</th>
              <th class="px-4 py-2 text-left">Nama Penenun</th>
              <th class="px-4 py-2 text-left">Nama Panirat</th>
              <th class="px-4 py-2 text-left">Dyer</th>
              <th class="px-4 py-2 text-left">Modal</th>
              <th class="px-4 py-2 text-left">Price Tag</th>
              <th class="px-4 py-2 text-left">Harga Net</th>
              <th class="px-4 py-2 text-left">Jumlah</th>
              <th class="px-4 py-2 text-left">Acara</th>
              <th class="px-4 py-2 text-left">Ukuran Mandar</th>
              <th class="px-4 py-2 text-left">Ukuran Ulos</th>
              <th class="px-4 py-2 text-left">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="barang in isSearchActive ? filteredBarang : pagination"
              :key="barang.kode_barang"
              class="odd:bg-white even:bg-gray-50 hover:bg-gray-100"
            >
              <td class="px-4 py-2">
                {{ formatTanggal(barang.created_at) }}
              </td>
              <td class="px-4 py-2">{{ barang.barangentry_nama }}</td>
              <td class="px-4 py-2">{{ barang.barangentry_warna }}</td>
              <td class="px-4 py-2">{{ barang.barangentry_nama_penenun }}</td>
              <td class="px-4 py-2">{{ barang.barangentry_nama_panirat }}</td>
              <td class="px-4 py-2">{{ barang.barangentry_dryer }}</td>
              <td class="px-4 py-2">
                {{ formatRupiah(barang.barangentry_modal) }}
              </td>
              <td class="px-4 py-2">
                {{ formatRupiah(barang.barangentry_price_tag) }}
              </td>
              <td class="px-4 py-2">
                {{ formatRupiah(barang.barangentry_harga_net) }}
              </td>
              <td class="px-4 py-2">
                {{ barang.barangentry_jumlah_barang }}
              </td>
              <td class="px-4 py-2">{{ barang.barangentry_acara }}</td>
              <td class="px-4 py-2">
                {{ barang.barangentry_ukuran_mandar }}
              </td>
              <td class="px-4 py-2">{{ barang.barangentry_ukuran_ulos }}</td>
              <td class="px-4 py-2">
                <div class="flex space-x-3">
                  <button
                    v-if="barang.barangentry_jumlah_barang > 1"
                    class="bg-green-500 text-white text-xs rounded-md hover:bg-green-600 px-2 py-1 h-[30px] w-[45px]"
                    @click="openModalEditStock(barang.barangentry_id)"
                  >
                    Edit
                  </button>
                  <button
                    v-if="barang.barangentry_jumlah_barang > 1"
                    class="bg-[#3D8BFD] text-white text-xs rounded-md hover:bg-[#367EE7] px-2 py-1 h-[30px] w-[60px]"
                    @click="tambahStock(barang.barangentry_id)"
                  >
                    + Stock
                  </button>
                  <button
                    class="bg-red-500 text-white text-xs rounded-md hover:bg-red-600 px-2 py-1 h-[30px] w-[50px]"
                    @click="deleteBarang(barang.barangentry_id)"
                  >
                    <!-- wait to entry api/entrybarang/getDataWaitForEntry -->
                    <!-- update stok api/entrybarang/6/updateStok -->
                    <!-- ready to stok api/entrybarang/getDataReady -->
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <div class="flex justify-between items-center mt-4 text-xs">
          <div class="flex items-center space-x-2">
            <label for="perPage">Tampilkan:</label>
            <select
              id="perPage"
              v-model="itemsPerPage"
              class="border px-2 py-1 rounded text-xs"
            >
              <option :value="5">5</option>
              <option :value="10">10</option>
              <option :value="20">20</option>
              <option :value="50">50</option>
            </select>
          </div>

          <div class="flex items-center space-x-2">
            <button
              class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400 text-xs"
              :disabled="currentPage === 1"
              @click="currentPage--"
            >
              Sebelumnya
            </button>

            <button
              v-for="(page, index) in paginatedPages"
              :key="index"
              @click="typeof page === 'number' && (currentPage = page)"
              :class="[
                'px-3 py-1 rounded text-xs',
                currentPage === page ? 'bg-blue-500 text-white' : 'bg-gray-200',
                page === '...' ? 'cursor-default' : 'cursor-pointer',
              ]"
              :disabled="page === '...'"
            >
              {{ page }}
            </button>

            <button
              class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400 text-xs"
              :disabled="currentPage === totalPages"
              @click="currentPage++"
            >
              Selanjutnya
            </button>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="showModalAdd"
      class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-50"
    >
      <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg">
        <h2 class="text-xl font-semibold mb-6 text-left">
          Tambah Barang Masuk
        </h2>
        <div class="grid grid-cols-2 gap-4">
          <div hidden>
            <label class="block text-gray-700 mb-1">Code ID:</label>
            <input
              v-model="selectedBarang.code_id"
              type="text"
              class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed"
              :readonly="true"
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Kode Barang:</label>
            <input
              v-model="selectedBarang.code_nama"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 autofocus cursor-not-allowed"
              :readonly="true"
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Nama Ulos:</label>
            <input
              v-model="selectedBarang.barangentry_nama"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 autofocus"
              placeholder="..."
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Warna Ulos:</label>
            <input
              v-model="selectedBarang.barangentry_warna"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 autofocus"
              placeholder="..."
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Nama Penenun:</label>
            <input
              v-model="selectedBarang.barangentry_nama_penenun"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 autofocus"
              placeholder="..."
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Nama Panirat:</label>
            <input
              v-model="selectedBarang.barangentry_nama_panirat"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 autofocus"
              placeholder="..."
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Dyer:</label>
            <input
              v-model="selectedBarang.barangentry_dryer"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 autofocus"
              placeholder="..."
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Modal:</label>
            <input
              v-model="selectedBarang.barangentry_modal"
              type="number"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 autofocus"
              placeholder="..."
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Harga Price Tag:</label>
            <input
              v-model="selectedBarang.barangentry_price_tag"
              type="number"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 autofocus"
              placeholder="..."
            />
            <!-- <div class="flex">
              <div
                class="bg-gray-100 border rounded-l-md px-3 flex items-center text-sm"
              >
                Rp
              </div>
              <input
                :value="formattedPriceTag"
                @input="onInputPriceTag"
                type="text"
                class="w-full border px-3 py-2"
                placeholder="Masukkan Harga Price Tag"
              />
            </div> -->
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Harga Net:</label>
            <input
              v-model="selectedBarang.barangentry_harga_net"
              type="number"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 autofocus"
              placeholder="..."
            />
            <!-- <div class="flex">
              <div
                class="bg-gray-100 border rounded-l-md px-3 flex items-center text-sm"
              >
                Rp
              </div>
              <input
                :value="formattedHargaNet"
                @input="onInputHargaNet"
                type="text"
                class="w-full border px-3 py-2"
                placeholder="Masukkan Harga Terjual"
              />
            </div> -->
          </div>

          <div>
            <label class="block text-gray-700 mb-1">Jumlah:</label>
            <input
              v-model="selectedBarang.barangentry_jumlah_barang"
              type="number"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 autofocus"
              placeholder="..."
            />
          </div>
        </div>

        <div class="flex justify-start space-x-3 mt-6">
          <button
            @click="cancelTambahBarang"
            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
          >
            Batal
          </button>
          <button
            @click="submitBarang"
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
          >
            Simpan
          </button>
        </div>
      </div>
    </div>

    <BaseModal
      :show="modalOpen"
      :type="modalType"
      :barang-database="barangDatabase"
      @close="modalOpen = false"
      @scanned="tambahBarang"
      @sizeSubmitted="handleSizeSubmitted"
    />

    <!-- Modal Add Size  -->
    <div
      v-if="showModalAddSize"
      class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-50"
    >
      <div class="bg-white rounded-lg shadow-lg p-6 w-[700px]">
        <h2 class="text-xl font-semibold mb-6 text-left">Tambah Size</h2>
        <div class="grid gap-4">
          <div hidden>
            <label class="block text-gray-700 mb-1">Code ID:</label>
            <input
              v-model="selectedBarang.code_id"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 autofocus cursor-not-allowed"
              :readonly="true"
            />
          </div>
          <div hidden>
            <label class="block text-gray-700 mb-1">Kode Barang:</label>
            <input
              v-model="selectedBarang.kode_barang"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 autofocus cursor-not-allowed"
              :readonly="true"
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Ukuran Ulos:</label>
            <input
              v-model="selectedBarang.ukuran_ulos"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 autofocus"
              placeholder="..."
            />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Ukuran Mandar:</label>
            <input
              v-model="selectedBarang.ukuran_mandar"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 autofocus"
              placeholder="..."
            />
          </div>
        </div>
        <div class="flex justify-end space-x-3 mt-6">
          <button
            @click="cancelSizeBarang"
            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
          >
            Batal
          </button>
          <button
            @click="submitSizeBarang"
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
          >
            Simpan
          </button>
        </div>
      </div>
    </div>

    <!-- Print -->
    <div
      ref="printContent"
      class="hidden print:block p-8 text-sm leading-relaxed"
    >
      <div
        v-for="item in priceTagData"
        :key="item.data.barangentry_id"
        style="page-break-after: always"
      >
        <div style="display: flex; gap: 40px">
          <div style="flex: 1">
            <h1>{{ item.data.barangentry_nama }}</h1>
            <p class="text-xl font-semibold mb-4">Horas!</p>
            <p>Mauliate atas dukungan dan pelestarian budaya Batak.</p>
            <p>
              Dengan membeli dan memiliki salah satu karya terbaik dari
              <strong>Dame Ulos</strong>,
            </p>
            <p>
              kamu telah ikut
              <strong>Menjaga Kehidupan dan Tradisi Batak</strong>.
            </p>

            <p class="mt-4">Salam Hangat,</p>
            <p><em>Artisan Dame Ulos</em></p>

            <table
              style="
                margin-top: 20px;
                width: 100%;
                border-collapse: collapse;
                font-size: 0.9rem;
              "
            >
              <tr>
                <td style="padding: 4px 8px; font-weight: bold">
                  Tahun Pembuatan
                </td>
                <td style="padding: 4px 8px">
                  {{ new Date(item.data.created_at).getFullYear() }}
                </td>
              </tr>
              <tr>
                <td style="padding: 4px 8px; font-weight: bold">
                  Ukuran Tenun
                </td>
                <td style="padding: 4px 8px">
                  {{ item.data.barangentry_ukuran_ulos ?? "-" }} x
                  {{ item.data.barangentry_ukuran_mandar ?? "-" }}
                </td>
              </tr>
              <tr>
                <td style="padding: 4px 8px; font-weight: bold">Warna</td>
                <td style="padding: 4px 8px">
                  {{ item.data.barangentry_warna }}
                </td>
              </tr>
              <tr>
                <td style="padding: 4px 8px; font-weight: bold">Maker</td>
                <td style="padding: 4px 8px">Dame Ulos Collective</td>
              </tr>
              <tr>
                <td style="padding: 4px 8px; padding-left: 1.5rem">
                  <strong>a. Penenun:</strong>
                </td>
                <td style="padding: 4px 8px">
                  {{ item.data.barangentry_nama_penenun }}
                </td>
              </tr>
              <tr>
                <td style="padding: 4px 8px; padding-left: 1.5rem">
                  <strong>b. Dyer:</strong>
                </td>
                <td style="padding: 4px 8px">
                  {{ item.data.barangentry_dryer }}
                </td>
              </tr>
            </table>
          </div>

          <div style="flex: 1">
            <p class="font-semibold mb-2">
              BAGAIMANA CARA PERAWATAN KAIN TENUN YANG BENAR?
            </p>
            <ol class="list-decimal list-inside">
              <li>Ulos tidak bisa dicuci/direndam dengan detergen</li>
              <li>
                Setelah dipakai jangan dilipat, cukup digantung dan dianginkan
              </li>
              <li>Jika tidak digunakan lama, jemur kain selama 1 jam</li>
              <li>Hindari tempat lembab dan penyimpanan dalam plastik</li>
              <li>Khusus kain pewarna tekstil bisa di dry clean</li>
            </ol>
            <p class="mt-4">Selamat Pakai</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Search -->
    <div
      v-if="showModalSearch"
      class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-50"
    >
      <div class="bg-white rounded-lg shadow-lg p-6 w-[500px]">
        <h2 class="text-xl font-semibold mb-4 text-center">Cari Barang</h2>
        <input
          v-model="searchCode"
          @keyup.enter="handleSearch"
          type="text"
          class="w-full border rounded px-3 py-2"
          placeholder="Scan atau ketik kode barang..."
        />
        <div class="flex justify-end space-x-3 mt-4">
          <button
            @click="closeSearchModal"
            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
          >
            Batal
          </button>
          <button
            @click="handleSearch"
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
          >
            Cari
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Edit Stock -->
    <div
      v-if="showModalEditStock"
      class="fixed inset-0 backdrop-blur-sm bg-white/30 flex items-center justify-center z-50"
    >
      <div class="bg-white p-6 rounded-md w-80 shadow-md">
        <h2 class="text-lg font-semibold mb-4">Edit Stock</h2>

        <label class="block text-sm font-medium text-gray-700 mb-1"
          >Jumlah</label
        >
        <input
          v-model="editJumlah"
          type="number"
          class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:border-blue-300"
          placeholder="Masukkan jumlah"
        />

        <div class="flex justify-end mt-6 space-x-2">
          <button
            class="px-4 py-2 bg-gray-300 text-sm rounded hover:bg-gray-400"
            @click="showModalEditStock = false"
          >
            Batal
          </button>
          <button
            class="px-4 py-2 bg-blue-500 text-white text-sm rounded hover:bg-blue-600"
            @click="editStockSubmit"
          >
            Simpan
          </button>
        </div>
      </div>
    </div>

    <!-- </div> -->
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from "vue";
import BaseModal from "../components/Modal.vue";
import axios from "axios";
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";

const modalOpen = ref(false);
const modalType = ref("desc");
const showModalAdd = ref(false);
const showModalAddSize = ref(false);
const selectedBarang = ref({});
const url = ref("");
const showModalEditStock = ref(false);
const editJumlah = ref(0);

const showModalAddStock = ref(false);

const barangDatabase = ref([]);
const listBarang = ref([]);
const priceTagData = ref([]);

const showModalSearch = ref(false);
const searchCode = ref("");
const filteredBarang = ref([]);
const isSearchActive = ref(false);
const currentPage = ref(1);
const itemsPerPage = ref(10);
const printContent = ref(null);
const activeTab = ref("wait");

onMounted(async () => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;
  try {
    const response = await axios.get(`${url.value}/api/codebarang`);
    barangDatabase.value = response.data;
    await getListBarangTemp();
  } catch (error) {
    console.error("Gagal mengambil data barang: ", error);
  }
});

function formatTanggal(tanggal) {
  const date = new Date(tanggal);
  return new Intl.DateTimeFormat("id-ID", {
    day: "2-digit",
    month: "long",
    year: "numeric",
  }).format(date);
}

function formatRupiah2(value) {
  const number = parseInt(value?.toString().replace(/\D/g, '') || '')
  if (isNaN(number)) return ''
  return number.toLocaleString('id-ID')
}

function parseRupiah(value) {
  return value?.toString().replace(/\D/g, '') || ''
}

// Saat input price tag
function onInputPriceTag(e) {
  const raw = parseRupiah2(e.target.value)
  selectedBarang.value.barangentry_price_tag = raw
}

function onInputHargaNet(e) {
  const raw = parseRupiah2(e.target.value)
  selectedBarang.value.barangentry_harga_net = raw
}

const formattedPriceTag = computed(() =>
  formatRupiah(selectedBarang.value.barangentry_price_tag)
)
const formattedHargaNet = computed(() =>
  formatRupiah(selectedBarang.value.barangentry_harga_net)
)


async function getListBarangTemp() {
  try {
    const endpoint =
      activeTab.value === "wait"
        ? "/api/entrybarang/getDataWaitForEntry"
        : "/api/entrybarang/getDataReady";
    const response = await axios.get(`${url.value}${endpoint}`);

    listBarang.value = response.data.data;
  } catch (error) {
    console.error("Gagal Memuat Data Barang: ", error);
  }
}

const pagination = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return listBarang.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(listBarang.value.length / itemsPerPage.value);
});

function openModal(type) {
  modalType.value = type;
  modalOpen.value = true;
}

function tambahBarang(barang) {
  selectedBarang.value = { ...barang };
  showModalAdd.value = true;
}

function formatRupiah(value) {
  const number = parseInt(value);
  return "Rp. " + number.toLocaleString("id-ID");
}

async function submitBarang() {
  try {
    const payload = {
      barangentry_code_id: String(selectedBarang.value.code_id),
      barangentry_nama: selectedBarang.value.barangentry_nama,
      barangentry_warna: selectedBarang.value.barangentry_warna,
      barangentry_nama_penenun: selectedBarang.value.barangentry_nama_penenun,
      barangentry_nama_panirat: selectedBarang.value.barangentry_nama_panirat,
      barangentry_dryer: selectedBarang.value.barangentry_dryer,
      barangentry_modal: selectedBarang.value.barangentry_modal,
      barangentry_price_tag: Number(selectedBarang.value.barangentry_price_tag),
      barangentry_harga_net: Number(selectedBarang.value.barangentry_harga_net),
      barangentry_jumlah_barang: selectedBarang.value.barangentry_jumlah_barang,
    };

    await axios.post(`${url.value}/api/entrybarang/storeDescription`, payload);
    await getListBarangTemp();

    selectedBarang.value = {};
    showModalAdd.value = false;
    modalOpen.value = false;
  } catch (error) {
    console.error("Gagal meyimpan barang:", error);
  }
}

function cancelTambahBarang() {
  selectedBarang.value = {};
  showModalAdd.value = false;
}

function handleSizeSubmitted(barang) {
  selectedBarang.value = { ...barang };
  showModalAddSize.value = true;
}

async function submitSizeBarang() {
  try {
    const payload = {
      barangentry_code_id: String(selectedBarang.value.code_id),
      barangentry_ukuran_mandar: selectedBarang.value.ukuran_mandar,
      barangentry_ukuran_ulos: selectedBarang.value.ukuran_ulos,
    };

    await axios.post(`${url.value}/api/entrybarang/storeSize`, payload);

    const index = listBarang.value.findIndex(
      (item) => item.barangentry_code_id === selectedBarang.value.code_id
    );

    if (index !== -1) {
      listBarang.value[index].barangentry_ukuran_mandar =
        payload.barangentry_ukuran_mandar;
      listBarang.value[index].barangentry_ukuran_ulos =
        payload.barangentry_ukuran_ulos;
    } else {
      listBarang.value.push({
        no: listBarang.value.length + 1,
        ...selectedBarang.value,
        barangentry_ukuran_ulos: payload.barangentry_ukuran_ulos,
        barangentry_ukuran_mandar: payload.barangentry_ukuran_mandar,
      });
    }

    selectedBarang.value = {};
    showModalAddSize.value = false;
    modalOpen.value = false;
  } catch (error) {
    console.error("Gagal menyimpan size:", error);
  }
}

function cancelSizeBarang() {
  selectedBarang.value == {};
  showModalAddSize.value = false;
}

function openSearchModal() {
  showModalSearch.value = true;
  searchCode.value = "";
}

function closeSearchModal() {
  showModalSearch.value = false;
  searchCode.value = "";
}

async function handleSearch() {
  const keyword = searchCode.value.trim();
  if (!keyword) return;

  try {
    const response = await axios.get(
      `${url.value}/api/entrybarang/getDataByCode/${keyword}`
    );
    const code = response.data.data.barangentry_code_id;
    console.log("asdsa", code);

    if (!code) {
      Swal.fire({
        icon: 'warning',
        title: 'Kode tidak ditemukan',
        text: 'Kode tidak ditemukan dari server.',
      });
      return;
    }

    filteredBarang.value = listBarang.value.filter((item) =>
      String(item.barangentry_code_id).includes(String(code))
    );
    console.log("sadas", filteredBarang.value);

    isSearchActive.value = true;
    showModalSearch.value = false;

    if (filteredBarang.value.length === 0) {
      Swal.fire({
        icon: 'info',
        title: 'Barang tidak ditemukan',
        text: 'Barang tidak ditemukan di daftar.',
      });
    }
  } catch (error) {
    console.error("Gagal mencari data kode:", error);
    Swal.fire({
      icon: 'error',
      title: 'Terjadi kesalahan',
      text: 'Terjadi kesalahan saat mencari kode.',
    });
  }
}

function resetSearch() {
  isSearchActive.value = false;
  filteredBarang.value = [];
  searchCode.value = "";
}

const openModalEditStock = (barang) => {
  selectedBarang.value = barang;
  editJumlah.value = barang.jumlah || 0;
  showModalEditStock.value = true;
};

const editStockSubmit = async () => {
  if (!selectedBarang.value) return;

  try {
    const responseEdit = await axios.post(
      `${url.value}/api/entrybarang/${selectedBarang.value}/updateStok`,
      { jumlah_barang: editJumlah.value }
    );

    if (responseEdit.data.code == 200) {
      showModalEditStock.value = false;
      Swal.fire({
        title: "Berhasil!",
        text: "Stock Berhasil di update.",
        icon: "success",
        timer: 1500,
        showConfirmButton: false,
      });
    }
    await getListBarangTemp();
  } catch (error) {
    console.error("error", error);
    Swal.fire({
      title: "Gagal!",
      text: "Terjadi kesalahan saat update stock.",
      icon: "error",
      timer: 1500,
    });
  }
};

const deleteBarang = async (id) => {
  const result = await Swal.fire({
    title: "Konfirmasi Hapus",
    text: `Anda yakin ingin menghapus barang ini?`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, Hapus!",
    cancelButtonText: "Batal",
    reverseButtons: true,
  });

  if (result.isConfirmed) {
    try {
      const response = await axios.post(
        `${url.value}/api/entrybarang/${id}/deleteBarangEntry`,
        { status: "DELETED" }
      );

      if (response.data.code === 200) {
        Swal.fire({
          title: "Berhasil!",
          text: `Barang telah dihapus.`,
          icon: "success",
          timer: 1500,
          showConfirmButton: false,
        });
      }
      await getListBarangTemp();
    } catch (error) {
      console.error("error:", error);
      Swal.fire({
        title: "Gagal",
        text: "Terjadi kesalahan saat menghapus barang ini.",
        icon: "error",
      });
    }
  }
};

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
      pages.push(1, "...", total - 2, total - 1, total);
    } else {
      pages.push(1, "...", current - 1, current, current + 1, "...", total);
    }
  }

  return pages;
});

watch(activeTab, () => {
  getListBarangTemp();
});
</script>

<style>
.judul {
  font-size: 20px;
}
</style>
<style scoped>
* {
  font-family: "Nunito", sans-serif;
}
.search-box {
  border: 1px solid #ccc;
  padding: 10px;
  width: 385px;
  height: 34px;
}

.datatable {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

.datatable th,
.datatable td {
  padding: 10px;
  /* border: 1px solid #ddd; */
  text-align: left;
  font-size: 12px;
}

.datatable th {
  background-color: #f4f4f4;
}

button {
  transition: background-color 0.2s ease;
}

.btn-print {
  background-color: #12c90e;
  color: white;
  border-radius: 5px;
  cursor: pointer;
  font-size: 12px;
}

.btn-print:hover {
  background-color: #7df67b;
}

.btn-print-click {
  background-color: #12c90e;
  color: white;
  border-radius: 5px;
  cursor: pointer;
}

.btn-print-click:hover {
  background-color: #7df67b;
}

.btn-reset-pencarian{
  border-radius: 5px;
  cursor: pointer;
  font-size: 12px;
}

.btn-add {
  background-color: #2e26d0;
  color: white;
  border-radius: 5px;
  cursor: pointer;
  font-size: 12px;
}

.btn-add:hover {
  background-color: #665eed;
}

.btn-tab {
  font-size: 12px;
  height: 30px;
  width: 110px;
}

/* Modal styles */
.fixed {
  position: fixed;
}

.bg-gray-800 {
  background-color: rgba(0, 0, 0, 0.5);
}
</style>
