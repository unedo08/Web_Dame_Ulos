<template>
  <div>
    <title>Barang Masuk</title>
    <h2 class="text-lg font-semibold mb-4">Barang Masuk</h2>
    <div class="page-submenu flex space-x-6">
      <button @click="activeTab = 'wait'" class="pb-1 text-sm relative"
        :class="activeTab === 'wait' ? 'text-red-900 font-semibold' : 'text-gray-500'">
        Awaiting Stock
        <span v-if="activeTab === 'wait'" class="absolute left-0 right-0 -bottom-0.5 h-[2px] bg-red-900 mx-auto"
          style="width: 100%"></span>
      </button>

      <button @click="activeTab = 'ready'" class="pb-1 text-sm relative"
        :class="activeTab === 'ready' ? 'text-red-900 font-semibold' : 'text-gray-500'">
        Ready Stock
        <span v-if="activeTab === 'ready'" class="absolute left-0 right-0 -bottom-0.5 h-[2px] bg-red-900 mx-auto"
          style="width: 100%"></span>
      </button>

      <button @click="activeTab = 'po'" class="pb-1 text-sm relative"
        :class="activeTab === 'po' ? 'text-red-900 font-semibold' : 'text-gray-500'">
        List Pre-Order
        <span v-if="activeTab === 'po'" class="absolute left-0 right-0 -bottom-0.5 h-[2px] bg-red-900 mx-auto"
          style="width: 100%"></span>
      </button>
    </div>
    <div class="w-full" v-show="activeTab === 'wait'">
      <div class="page-table-toolbar flex items-center justify-between">
        <div class="flex-1">
          <input class="search-box rounded-md" v-model="searchQuery" type="text" placeholder="Search barang..." />
        </div>
        <div class="flex flex-wrap justify-end gap-4">
          <button class="btn-add bg-green-500 text-white text-center rounded-md hover:bg-green-600 w-[60px] h-[30px]"
            @click="openTambahBarang()">
            + Desc
          </button>

          <button class="btn-add bg-green-500 text-white text-center rounded-md hover:bg-green-600 w-[60px] h-[30px]"
            @click="openAddSize()">
            + Size
          </button>
        </div>
      </div>

      <div class="datatable-wrapper">
        <table class="datatable rounded-md text-sm">
          <thead>
            <tr>
              <th class="px-4 py-2 text-left">Tanggal</th>
              <th class="px-4 py-2 text-left">Kode Barang</th>
              <th class="px-4 py-2 text-left">Nama Ulos</th>
              <th class="px-4 py-2 text-left">Warna Ulos</th>
              <th class="px-4 py-2 text-left">Nama Penenun</th>
              <th class="px-4 py-2 text-left">Nama Panirat</th>
              <th class="px-4 py-2 text-left">Dyer</th>
              <th class="px-4 py-2 text-left">Modal</th>
              <th class="px-4 py-2 text-left">Price Tag</th>
              <th class="px-4 py-2 text-left">Harga Net</th>
              <th class="px-4 py-2 text-left">Jumlah</th>
              <th class="px-4 py-2 text-left">Ukuran Mandar</th>
              <th class="px-4 py-2 text-left">Ukuran Ulos</th>
              <th class="px-4 py-2 text-left sticky-action">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="barang in isSearchActive ? filteredBarang : pagination" :key="barang.kode_barang"
              class="odd:bg-white even:bg-gray-50 hover:bg-gray-100">
              <td class="px-4 py-2">
                {{ formatTanggal(barang.created_at) }}
              </td>
              <td class="px-4 py-2">{{ barang.code_nama }}</td>
              <td class="px-4 py-2">{{ (barang.barangentry_nama || "") }}</td>
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
              <td class="px-4 py-2">
                {{ barang.barangentry_ukuran_mandar }}
              </td>
              <td class="px-4 py-2">{{ barang.barangentry_ukuran_ulos }}</td>
              <td class="px-4 py-2 sticky-action">
                <div class="flex space-x-3">
                  <button class="bg-red-500 text-white text-xs rounded-md hover:bg-red-600 px-2 py-1 h-[25px] w-[60px]"
                    @click="deleteBarang(barang.barangentry_id)">
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="cl-pagination">
        <div class="cl-pagination-left">
          <span>
            Menampilkan {{ startItem }} sampai {{ endItem }} dari {{ searchFilterData.length }}
          </span>

          <span class="cl-pagination-sep">|</span>

          <span>Tampilkan</span>

          <select v-model="itemsPerPage" class="cl-perpage-select" @change="currentPage = 1">
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>

          <span>data</span>
        </div>

        <div class="cl-pagination-pages">
          <!-- First -->
          <button class="cl-page-nav" :disabled="currentPage === 1" @click="currentPage = 1">
            &#xAB;
          </button>

          <!-- Previous -->
          <button class="cl-page-nav" :disabled="currentPage === 1" @click="currentPage--">
            &#x3C;
          </button>

          <!-- Number -->
          <template v-for="(page, idx) in paginatedPages" :key="idx">
            <button v-if="page !== '...'" class="cl-page-btn" :class="{ 'cl-page-btn--active': currentPage === page }"
              @click="currentPage = page">
              {{ page }}
            </button>

            <span v-else class="cl-page-btn cl-page-btn--dots">
              ...
            </span>
          </template>

          <!-- Next -->
          <button class="cl-page-nav" :disabled="currentPage === totalPages" @click="currentPage++">
            &#x3E;
          </button>

          <!-- Last -->
          <button class="cl-page-nav" :disabled="currentPage === totalPages" @click="currentPage = totalPages">
            &#xBB;
          </button>
        </div>
      </div>
    </div>
    <div class="w-full" v-show="activeTab === 'ready'">
      <!-- <div class="judul text-xs font-semibold mb-2">Ready to Stock</div> -->
      <div class="page-table-toolbar flex items-center justify-between">
        <div class="flex-1">
          <input class="search-box rounded-md" v-model="searchQuery" type="text" placeholder="Search barang..." />
        </div>
        <div class="flex flex-wrap justify-end gap-4">
          <!-- <button class="btn-add bg-yellow-500 text-white text-center rounded-md hover:bg-yellow-600 w-[75px] h-[30px]"
            @click="openSearchModal">
            🔍 Search
          </button> -->
          <button class="entry-price-tag-button btn-print bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[85px] h-[30px]"
            @click="openModal('priceTag')">
            Print Price Tag
          </button>
          <button class="btn-add bg-green-600 text-white text-center rounded-md hover:bg-green-700 w-[130px] h-[30px]"
            @click="exportReadyStock">
            Export Ready Stock
          </button>
        </div>
      </div>

      <div class="datatable-wrapper">
        <table class="datatable rounded-md text-sm">
          <thead>
            <tr>
              <th class="px-4 py-2 text-left">Tanggal</th>
              <th class="px-4 py-2 text-left">Kode Barang</th>
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
              <th class="px-4 py-2 text-left sticky-action">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="barang in isSearchActive ? filteredBarang : pagination" :key="barang.kode_barang"
              class="odd:bg-white even:bg-gray-50 hover:bg-gray-100">
              <td class="px-4 py-2">
                {{ formatTanggal(barang.created_at) }}
              </td>
              <td class="px-4 py-2">{{ barang.code_nama }}</td>
              <td class="px-4 py-2">{{ (barang.barangentry_nama || "") }}</td>
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
              <td class="px-4 py-2">{{ barang.acara_nama ?? '-' }}</td>
              <td class="px-4 py-2">
                {{ barang.barangentry_ukuran_mandar }}
              </td>
              <td class="px-4 py-2">{{ barang.barangentry_ukuran_ulos }}</td>
              <!-- <td class="px-4 py-2">
                <div class="flex space-x-3">
                  <button class="er-btn-view" @click="openRiwayat(barang.barangentry_id)">View</button>
                  <button :class="['er-btn-edit', { 'er-btn-edit--disabled': barang.barangentry_jumlah_barang <= 0 }]"
                    :disabled="barang.barangentry_jumlah_barang <= 0"
                    @click="barang.barangentry_jumlah_barang > 0 && openModalEditBarang(barang.barangentry_id)">
                    Edit
                  </button>
                  <button class="er-btn-delete" @click="deleteBarang(barang.barangentry_id)">
                    Delete
                  </button>
                </div>
              </td> -->
              <td class="px-4 py-2 sticky-action">
                <div class="relative inline-block text-left">
                  <button class="er-btn-action" @click.stop="toggleActionDropdown(`wait-${barang.barangentry_id}`, $event)">
                    Aksi
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>

                  <Teleport to="body">
                    <div v-if="activeDropdown === `wait-${barang.barangentry_id}`" :ref="setActionDropdownMenu"
                      class="er-action-dropdown" @click.stop>
                      <button class="er-dropdown-item" @click="closeActionDropdown(); openRiwayat(barang.barangentry_id)">
                        <span>View</span>
                      </button>

                      <button :class="[
                        'er-dropdown-item',
                        { 'er-dropdown-item--disabled': barang.barangentry_jumlah_barang <= 0 }
                      ]" :disabled="barang.barangentry_jumlah_barang <= 0"
                        @click="closeActionDropdown(); openModalEditBarang(barang.barangentry_id)">
                        <span>Edit</span>
                      </button>

                      <button class="er-dropdown-item er-dropdown-item--delete"
                        @click="closeActionDropdown(); deleteBarang(barang.barangentry_id)">
                        <span>Delete</span>
                      </button>
                    </div>
                  </Teleport>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="cl-pagination">
        <div class="cl-pagination-left">
          <span>
            Menampilkan {{ startItem }} sampai {{ endItem }} dari {{ searchFilterData.length }}
          </span>

          <span class="cl-pagination-sep">|</span>

          <span>Tampilkan</span>

          <select v-model="itemsPerPage" class="cl-perpage-select" @change="currentPage = 1">
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>

          <span>data</span>
        </div>

        <div class="cl-pagination-pages">
          <!-- First -->
          <button class="cl-page-nav" :disabled="currentPage === 1" @click="currentPage = 1">
            &#xAB;
          </button>

          <!-- Previous -->
          <button class="cl-page-nav" :disabled="currentPage === 1" @click="currentPage--">
            &#x3C;
          </button>

          <!-- Number -->
          <template v-for="(page, idx) in paginatedPages" :key="idx">
            <button v-if="page !== '...'" class="cl-page-btn" :class="{ 'cl-page-btn--active': currentPage === page }"
              @click="currentPage = page">
              {{ page }}
            </button>

            <span v-else class="cl-page-btn cl-page-btn--dots">
              ...
            </span>
          </template>

          <!-- Next -->
          <button class="cl-page-nav" :disabled="currentPage === totalPages" @click="currentPage++">
            &#x3E;
          </button>

          <!-- Last -->
          <button class="cl-page-nav" :disabled="currentPage === totalPages" @click="currentPage = totalPages">
            &#xBB;
          </button>
        </div>
      </div>
    </div>

    <div class="w-full" v-show="activeTab === 'po'">
      <!-- <div class="judul text-xs font-semibold mb-2">Ready to Stock</div> -->
      <div class="page-table-toolbar flex items-center justify-between">
        <div class="flex-1">
          <input class="search-box rounded-md" v-model="searchQuery" type="text" placeholder="Search barang..." />
        </div>
        <div class="flex flex-wrap justify-end gap-4">
          <button class="entry-price-tag-button btn-print bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[85px] h-[30px]"
            @click="openModal('priceTag')">
            Print Price Tag
          </button>
        </div>
      </div>

      <div class="datatable-wrapper">
        <table class="datatable rounded-md text-sm">
          <thead>
            <tr>
              <th class="px-4 py-2 text-left">Tanggal</th>
              <th class="px-4 py-2 text-left">Kode Barang</th>
              <th class="px-4 py-2 text-left">Nama Akun</th>
              <th class="px-4 py-2 text-left">Nama Ulos</th>
              <th class="px-4 py-2 text-left">Warna Ulos</th>
              <th class="px-4 py-2 text-left">Nama Penenun</th>
              <th class="px-4 py-2 text-left">Nama Panirat</th>
              <th class="px-4 py-2 text-left">Dyer</th>
              <th class="px-4 py-2 text-left">Modal</th>
              <th class="px-4 py-2 text-left">Price Tag</th>
              <th class="px-4 py-2 text-left">Harga Net</th>
              <th class="px-4 py-2 text-left">Jumlah</th>
              <th class="px-4 py-2 text-left">Ukuran Mandar</th>
              <th class="px-4 py-2 text-left">Ukuran Ulos</th>
              <th class="px-4 py-2 text-left sticky-action">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="barang in isSearchActive ? filteredBarang : pagination" :key="barang.kode_barang"
              class="odd:bg-white even:bg-gray-50 hover:bg-gray-100">
              <!-- <pre>{{ barang }}</pre> -->
              <td class="px-4 py-2">
                {{ formatTanggal(barang.created_at) }}
              </td>
              <td class="px-4 py-2">{{ barang.code_nama }}</td>
              <td class="px-4 py-2">{{ barang.nama_akun_po ?? "" }}</td>
              <td class="px-4 py-2">{{ (barang.barangentry_nama || "") }}</td>
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
              <td class="px-4 py-2">
                {{ barang.barangentry_ukuran_mandar }}
              </td>
              <td class="px-4 py-2">{{ barang.barangentry_ukuran_ulos }}</td>

              <td class="px-4 py-2 sticky-action">
                <div class="relative inline-block text-left">
                  <button class="er-btn-action" @click.stop="toggleActionDropdown(`ready-${barang.barangentry_id}`, $event)">
                    Aksi
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>

                  <Teleport to="body">
                    <div v-if="activeDropdown === `ready-${barang.barangentry_id}`" :ref="setActionDropdownMenu"
                      class="er-action-dropdown" @click.stop>
                      <button class="er-dropdown-item er-dropdown-item--print"
                        @click="closeActionDropdown(); printPreOrder(barang.barangentry_id)">
                        Print
                      </button>

                      <button class="er-dropdown-item er-dropdown-item--edit" @click="closeActionDropdown(); openModalEditPO(
                        barang.barangentry_id,
                        barang.pre_order_barang?.preOrdeBarang_id
                      )">
                        Edit
                      </button>

                      <template v-if="barang.barangfilled">
                        <button class="er-dropdown-item er-dropdown-item--send"
                          @click="closeActionDropdown(); handleSendClick(barang)">
                          {{ barang.status_barang === "PACKAGING" ? "Packaging" : "Send" }}
                        </button>
                      </template>

                      <template v-else>
                        <button class="er-dropdown-item er-dropdown-item--add"
                          @click="closeActionDropdown(); openModalDesc(barang.barangentry_id)">
                          Desc +
                        </button>

                        <button class="er-dropdown-item er-dropdown-item--add"
                          @click="closeActionDropdown(); openModalSize(barang.barangentry_id)">
                          Size +
                        </button>
                      </template>

                      <button class="er-dropdown-item er-dropdown-item--delete"
                        @click="closeActionDropdown(); deleteBarang(barang.barangentry_id)">
                        Delete
                      </button>
                    </div>
                  </Teleport>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="cl-pagination">
        <div class="cl-pagination-left">
          <span>
            Menampilkan {{ startItem }} sampai {{ endItem }} dari {{ searchFilterData.length }}
          </span>

          <span class="cl-pagination-sep">|</span>

          <span>Tampilkan</span>

          <select v-model="itemsPerPage" class="cl-perpage-select" @change="currentPage = 1">
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>

          <span>data</span>
        </div>

        <div class="cl-pagination-pages">
          <!-- First -->
          <button class="cl-page-nav" :disabled="currentPage === 1" @click="currentPage = 1">
            &#xAB;
          </button>

          <!-- Previous -->
          <button class="cl-page-nav" :disabled="currentPage === 1" @click="currentPage--">
            &#x3C;
          </button>

          <!-- Number -->
          <template v-for="(page, idx) in paginatedPages" :key="idx">
            <button v-if="page !== '...'" class="cl-page-btn" :class="{ 'cl-page-btn--active': currentPage === page }"
              @click="currentPage = page">
              {{ page }}
            </button>

            <span v-else class="cl-page-btn cl-page-btn--dots">
              ...
            </span>
          </template>

          <!-- Next -->
          <button class="cl-page-nav" :disabled="currentPage === totalPages" @click="currentPage++">
            &#x3E;
          </button>

          <!-- Last -->
          <button class="cl-page-nav" :disabled="currentPage === totalPages" @click="currentPage = totalPages">
            &#xBB;
          </button>
        </div>
      </div>
    </div>

    <div v-if="showModalAdd" class="fixed inset-0 z-[2000] flex items-center justify-center bg-gray-800 bg-opacity-50" @click.self="cancelTambahBarang">
      <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-3xl">
        <h2 class="text-xl font-semibold mb-6 text-left">
          Tambah Barang Masuk
        </h2>
        <div class="grid grid-cols-2 gap-4">
          <div hidden>
            <label class="block text-gray-700 mb-1">Code ID</label>
            <input v-model="selectedBarang.code_id" type="text"
              class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed" :readonly="true" />
          </div>
          <div>
            <label class="block mb-1 font-medium">Kode Barang <span style="color:red">*</span></label>
            <input ref="inputKodeDesc" v-model="selectedBarang.code_nama" @input="handleBarcodeDesc"
              :readonly="!!selectedBarang.code_id" :class="[
                'w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 autofocus',
                selectedBarang.code_id ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'
              ]" placeholder="Scan atau ketik kode barang..." />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Nama Ulos <span style="color:red">*</span></label>
            <input v-model="selectedBarang.barangentry_nama" type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 autofocus"
              placeholder="Masukkan Nama Ulos" />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Warna Ulos <span style="color:red">*</span></label>
            <input v-model="selectedBarang.barangentry_warna" type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 autofocus"
              placeholder="Masukkan Warna Ulos" />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Nama Penenun <span style="color:red">*</span></label>
            <input v-model="selectedBarang.barangentry_nama_penenun" type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 autofocus"
              placeholder="Masukkan Nama Penenun" />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Nama Panirat <span style="color:red">*</span></label>
            <input v-model="selectedBarang.barangentry_nama_panirat" type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 autofocus"
              placeholder="Masukkan Nama Panirat" />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Dyer <span style="color:red">*</span></label>
            <input v-model="selectedBarang.barangentry_dryer" type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 autofocus"
              placeholder="Masukkan Dyer" />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Modal <span style="color:red">*</span></label>
            <div class="flex">
              <div class="modal-currency-prefix bg-gray-100 border border-gray-300 rounded-l-md px-3 flex items-center text-gray-600 text-sm">
                Rp
              </div>
              <input type="text" :value="formatRupiah2(selectedBarang.barangentry_modal)"
                @input="updateModal($event.target.value)"
                class="w-full border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                placeholder="Masukkan Harga Modal" />
            </div>
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Harga Price Tag <span style="color:red">*</span></label>
            <div class="flex">
              <div class="modal-currency-prefix bg-gray-100 border border-gray-300 rounded-l-md px-3 flex items-center text-gray-600 text-sm">
                Rp
              </div>
              <input type="text" :value="formatRupiah2(selectedBarang.barangentry_price_tag)"
                @input="updatePriceTag($event.target.value)"
                class="w-full border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                placeholder="Masukkan Harga Price Tag" />
            </div>
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Harga Net <span style="color:red">*</span></label>
            <div class="flex">
              <div class="modal-currency-prefix bg-gray-100 border border-gray-300 rounded-l-md px-3 flex items-center text-gray-600 text-sm">
                Rp
              </div>
              <input type="text" :value="formatRupiah2(selectedBarang.barangentry_harga_net)"
                @input="updateHargaNet($event.target.value)"
                class="w-full border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                placeholder="Masukkan Harga Net" />
            </div>
          </div>

          <div>
            <label class="block text-gray-700 mb-1">Jumlah <span style="color:red">*</span></label>
            <input v-model="selectedBarang.barangentry_jumlah_barang" type="number" min="1"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 autofocus"
              placeholder="Masukkan Jumlah" />
          </div>
        </div>

        <div class="flex justify-end space-x-3 mt-6">
          <button @click="cancelTambahBarang" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
            Batal
          </button>
          <button @click="submitBarang" :disabled="isSubmitting"
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            {{ isSubmitting ? 'Sedang Menyimpan' : 'Simpan' }}
          </button>
        </div>
      </div>
    </div>

    <BaseModal :show="modalOpen" :type="modalType" :barang-database="barangDatabase" @close="modalOpen = false"
      @scanned="tambahBarang" @sizeSubmitted="handleSizeSubmitted" />

    <!-- Modal Add Size  -->
    <div v-if="showModalAddSize"
      class="fixed inset-0 z-[2000] flex items-center justify-center bg-gray-800 bg-opacity-50" @click.self="cancelSizeBarang">
      <div class="bg-white rounded-lg shadow-lg p-6 w-[700px]">
        <h2 class="text-xl font-semibold mb-6 text-left">Tambah Size</h2>

        <div class="grid gap-4">

          <div hidden>
            <label class="block text-gray-700 mb-1">Code ID</label>
            <input v-model="selectedBarang.code_id" type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none cursor-not-allowed"
              :readonly="true" />
          </div>

          <div>
            <label class="block text-gray-700 mb-1">Kode Barang</label>
            <input ref="inputKodeSize" v-model="selectedBarang.kode_barang" @input="handleBarcodeSize"
              :readonly="!!selectedBarang.code_id" :class="[
                'w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500',
                selectedBarang.code_id ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'
              ]" placeholder="Scan atau ketik kode barang..." />
          </div>

          <div>
            <label class="block text-gray-700 mb-1">Ukuran Ulos</label>
            <div class="flex">
              <input v-model="selectedBarang.ukuran_ulos" type="text"
                class="w-full border border-gray-300 rounded-l-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                placeholder="Masukkan Ukuran Ulos" />
              <div class="bg-gray-100 border border-gray-300 rounded-r-md px-3 flex items-center text-gray-600 text-sm">
                cm
              </div>
            </div>
          </div>

          <div>
            <label class="block text-gray-700 mb-1">Ukuran Mandar</label>
            <div class="flex">
              <input v-model="selectedBarang.ukuran_mandar" type="text"
                class="w-full border border-gray-300 rounded-1-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                placeholder="Masukkan Ukuran Mandar" />
              <div class="bg-gray-100 border border-gray-300 rounded-r-md px-3 flex items-center text-gray-600 text-sm">
                cm
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-end space-x-3 mt-6">
          <button @click="cancelSizeBarang" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
            Batal
          </button>
          <button @click="submitSizeBarang" :disabled="isSubmittingSize"
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            {{ isSubmittingSize ? 'Sedang Meyimpan' : 'Simpan' }}
          </button>
        </div>
      </div>
    </div>

    <div ref="printContent" class="hidden print:block p-8 text-sm leading-relaxed">
      <div v-for="item in priceTagData" :key="item.data.barangentry_id"
        style="page-break-after: always; border: 1px solid #999; padding: 28px; font-family: 'Times New Roman', serif;">
        <div style="text-align: left; margin-bottom: 8px;">
          <h1 style="
          font-size: 20px;
          font-weight: 700;
          text-transform: uppercase;
          letter-spacing: 0.5px;
          margin: 0;
        ">
            {{ item.data.barangentry_nama }}
          </h1>
          <hr style="border: none; border-top: 1px solid #bbb; margin-top: 6px; margin-bottom: 14px;" />
        </div>

        <div style="display: flex; gap: 40px;">
          <div style="flex: 1;">
            <p style="margin: 0; font-weight: 600;">Horas!</p>
            <p style="margin: 6px 0;">
              Mauliate atas dukungan dan pelestarian budaya Batak.
              Dengan membeli dan memiliki salah satu karya terbaik dari
              <strong>Dame Ulos</strong>, kamu telah ikut
              <em style="font-weight: 600;">Menjaga Kehidupan dan Tradisi Batak.</em>
            </p>

            <p style="margin-top: 16px;">Salam Hangat,</p>
            <p style="font-style: italic; font-weight: 600;">Artisan Dame Ulos</p>

            <table style="
            margin-top: 16px;
            border-collapse: collapse;
            font-size: 13px;
            width: 100%;
          ">
              <tbody>
                <tr>
                  <td style="padding: 3px 8px 3px 0;">Tahun Pembuatan</td>
                  <td>: {{ new Date(item.data.created_at).getFullYear() }}</td>
                </tr>
                <tr>
                  <td style="padding: 3px 8px 3px 0;">Ukuran Tenun</td>
                  <td>: {{ formatUkuran(item.data.barangentry_ukuran_ulos, item.data.barangentry_ukuran_mandar) }}</td>
                </tr>
                <tr>
                  <td style="padding: 3px 8px 3px 0;">Warna</td>
                  <td>: {{ item.data.barangentry_warna }}</td>
                </tr>
                <tr>
                  <td style="padding: 3px 8px 3px 0;">Maker</td>
                  <td>: Dame Ulos Collective</td>
                </tr>
                <tr>
                  <td style="padding: 3px 8px 3px 0;">a. Penenun</td>
                  <td>: {{ item.data.barangentry_nama_penenun }}</td>
                </tr>
                <tr>
                  <td style="padding: 3px 8px 3px 0;">b. Dyer</td>
                  <td>: {{ item.data.barangentry_dryer }}</td>
                </tr>
                <tr v-if="item.data.barangentry_pemintal">
                  <td style="padding: 3px 8px 3px 0;">c. Panirat</td>
                  <td>: {{ item.data.barangentry_pemintal }}</td>
                </tr>
              </tbody>
            </table>

            <p style="margin-top: 22px; font-weight: 700; font-size: 18px;">
              Rp {{ Number(item.data.barangentry_harga).toLocaleString("id-ID") }}
            </p>
          </div>

          <div style="flex: 1; font-size: 13px;">
            <p style="
            font-weight: 700;
            text-align: center;
            margin-bottom: 12px;
            text-transform: uppercase;
          ">
              BAGAIMANA CARA PERAWATAN <br />
              KAIN TENUN YANG BENAR?
            </p>

            <ol style="margin: 0; padding-left: 18px; line-height: 1.6;">
              <li>Ulos tidak bisa dicuci/direndam dengan detergen</li>
              <li>
                Setelah dipakai jangan dilipat, cukup digantung dan dianginkan
                (keringat dapat menimbulkan jamur)
              </li>
              <li>
                Apabila tidak digunakan dalam kurun waktu yang lama, sebaiknya kain
                dijemur di luar ruangan selama satu jam untuk menghindari jamur
              </li>
              <li>Hindari menyimpan di tempat yang lembab dan di dalam plastik</li>
              <li>
                Khusus kain dengan pewarna tekstil dapat di <em>dry clean</em>
              </li>
            </ol>

            <p style="
            text-align: right;
            margin-top: 60px;
            font-style: italic;
            font-size: 15px;
          ">
              Selamat Pakai
            </p>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showModalSearch"
      class="fixed inset-0 z-[2000] flex items-center justify-center bg-gray-800 bg-opacity-50" @click.self="closeSearchModal">
      <div class="bg-white rounded-lg shadow-lg p-6 w-[500px]">
        <h2 class="text-xl font-semibold mb-4 text-center">Cari Barang</h2>
        <input v-model="searchCode" @keyup.enter="handleSearch" type="text" class="w-full border rounded px-3 py-2"
          placeholder="Scan atau ketik kode barang..." />
        <div class="flex justify-end space-x-3 mt-4">
          <button @click="closeSearchModal" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
            Batal
          </button>
          <button @click="handleSearch" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            Cari
          </button>
        </div>
      </div>
    </div>

    <div v-if="showModalEditStock" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50" @click.self="showModalEditStock = false">
      <div class="bg-white p-6 rounded-md w-80 shadow-md">
        <h2 class="text-lg font-semibold mb-4">Tambah Stock</h2>

        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah <span style="color: red">*</span></label>
        <input v-model="editJumlah" type="number"
          class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:border-blue-300"
          placeholder="Masukkan jumlah" />

        <div class="flex justify-end mt-6 space-x-2">
          <button class="px-4 py-2 bg-gray-300 text-sm rounded hover:bg-gray-400" @click="showModalEditStock = false">
            Batal
          </button>
          <button class="px-4 py-2 bg-blue-500 text-white text-sm rounded hover:bg-blue-600" @click="editStockSubmit">
            Simpan
          </button>
        </div>
      </div>
    </div>
    <EditBarangReady :show="showEditModal" :id="selectedId" @close="showEditModal = false" @saved="loadData" />

    <EditBarangPO :show="showEditPO" :id="selectedId" :preorder-id="selectedPreOrderId" @close="showEditPO = false"
      @saved="loadData" />

    <EditBarangDesc :show="showEditDesc" :id="selectedId" @close="showEditDesc = false" @saved="loadData" />

    <EditBarangSize :show="showEditSize" :id="selectedId" @close="showEditSize = false" @saved="loadData" />

    <SendOrderModal :visible="showSendModal" @close="showSendModal = false" @submitted="handleSend" />

    <!-- Modal Riwayat Perubahan -->
    <div v-if="showRiwayatModal" class="er-overlay" @click.self="showRiwayatModal = false">
      <div class="er-modal">
        <div class="er-header">
          <p class="er-title">Riwayat Perubahan</p>
          <button class="er-close" @click="showRiwayatModal = false">&#x2715;</button>
        </div>

        <div class="er-table-wrap">
          <div v-if="riwayatLoading" class="er-loading">Memuat data...</div>
          <table v-else class="er-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Aktivitas</th>
                <th>Tanggal</th>
                <th>Author</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="riwayatData.length === 0">
                <td colspan="4" class="er-table-empty">Belum ada riwayat.</td>
              </tr>
              <tr v-for="row in riwayatData" :key="row.no">
                <td>{{ row.no }}</td>
                <td>{{ row.aktivitas }}</td>
                <td>{{ formatTanggalWIB(row.tanggal) }}</td>
                <td>{{ row.author }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import BaseModal from "../components/Modal.vue";
import EditBarangReady from "../components/ModalEditBarang.vue";
import EditBarangDesc from "../components/ModalEditDesc.vue";
import EditBarangSize from "../components/ModalEditSize.vue";
import EditBarangPO from "../components/ModalEditPO.vue";
import SendOrderModal from "../components/ModalSendOrder.vue";

import { useEntry } from "~/composables/useEntry";

const {
  router,
  modalOpen,
  modalType,
  showModalAdd,
  showModalAddSize,
  selectedBarang,
  url,
  showModalEditStock,
  editJumlah,
  showEditModal,
  showEditDesc,
  showEditSize,
  showEditPO,
  selectedId,
  showSendModal,
  showRiwayatModal,
  riwayatLoading,
  riwayatData,
  barangDatabase,
  listBarang,
  priceTagData,
  showModalSearch,
  searchCode,
  filteredBarang,
  isSearchActive,
  currentPage,
  itemsPerPage,
  printContent,
  activeTab,
  searchQuery,
  barangMap,
  isSubmitting,
  isSubmittingSize,
  pengirimanStatus,
  inputKodeDesc,
  inputKodeSize,
  selectedPreOrderId,
  activeDropdown,
  closeActionDropdown,
  setActionDropdownMenu,
  toggleActionDropdown,
  formatTanggal,
  openSendModal,
  processOrderDijemput,
  handleSend,
  updatePengiriman,
  openModalEditBarang,
  openModalEditPO,
  openModalDesc,
  openModalSize,
  loadData,
  formatRupiah2,
  formatUkuran,
  updatePriceTag,
  updateModal,
  updateHargaNet,
  fetchCodeBarang,
  getListBarangTemp,
  openRiwayat,
  formatTanggalWIB,
  exportReadyStock,
  handleBarcodeDesc,
  handleBarcodeSize,
  handleSendClick,
  getListBarangPreOrder,
  searchFilterData,
  pagination,
  startItem,
  endItem,
  totalPages,
  openModal,
  tambahBarang,
  formatRupiah,
  sendOrder,
  submitBarang,
  openTambahBarang,
  openAddSize,
  cancelTambahBarang,
  handleSizeSubmitted,
  submitSizeBarang,
  cancelSizeBarang,
  openSearchModal,
  closeSearchModal,
  handleSearch,
  openModalTambahStock,
  editStockSubmit,
  deleteBarang,
  paginatedPages,
  printPreOrder,
  printData,
} = useEntry();
</script>

<style>
@import "~/assets/css/entry-riwayat.css";
</style>

<style scoped src="~/assets/css/entry.css"></style>

<style>
@import "~/assets/css/entry-riwayat.css";
</style>

<style scoped src="~/assets/css/entry.css"></style>
