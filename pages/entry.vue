<template>
  <div>
    <title>Barang Masuk</title>
    <h2 class="text-lg font-semibold mb-4">Barang Masuk</h2>
    <div class="flex space-x-6">
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
    <div class="mx-auto" v-show="activeTab === 'wait'">
      <div class="flex items-center justify-between pt-2">
        <div class="flex-1">
          <input class="search-box p-2 border rounded-md" v-model="searchQuery" type="text"
            placeholder="Search barang..." />
        </div>
        <div class="flex flex-wrap justify-end gap-4">
          <br />
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

      <div class="w-full">
        <table class="datatable w-full rounded-md overflow-hidden text-sm">
          <thead class="bg-blue-100">
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
              <!-- <th class="px-4 py-2 text-left">Aksi</th> -->
            </tr>
          </thead>
          <tbody>
            <tr v-for="barang in isSearchActive ? filteredBarang : pagination" :key="barang.kode_barang"
              class="odd:bg-white even:bg-gray-50 hover:bg-gray-100">
              <td class="px-4 py-2">
                {{ formatTanggal(barang.created_at) }}
              </td>
              <td class="px-4 py-2">{{ barangMap[barang.barangentry_id] }}</td>
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
            <select id="perPage" v-model="itemsPerPage" class="border px-2 py-1 rounded text-xs">
              <option :value="5">5</option>
              <option :value="10">10</option>
              <option :value="20">20</option>
              <option :value="50">50</option>
              <option value="all">All</option>
            </select>
          </div>

          <div class="flex items-center space-x-2">
            <button class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400 text-xs" :disabled="currentPage === 1"
              @click="currentPage--">
              Sebelumnya
            </button>

            <button v-for="(page, index) in paginatedPages" :key="index"
              @click="typeof page === 'number' && (currentPage = page)" :class="[
                'px-3 py-1 rounded text-xs',
                currentPage === page ? 'bg-blue-500 text-white' : 'bg-gray-200',
                page === '...' ? 'cursor-default' : 'cursor-pointer',
              ]" :disabled="page === '...'">
              {{ page }}
            </button>

            <button class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400 text-xs"
              :disabled="currentPage === totalPages" @click="currentPage++">
              Selanjutnya
            </button>
          </div>
        </div>
      </div>
    </div>
    <div class="mx-auto" v-show="activeTab === 'ready'">
      <!-- <div class="judul text-xs font-semibold mb-2">Ready to Stock</div> -->
      <div class="flex items-center justify-between pt-2">
        <div class="flex-1">
          <input class="search-box p-2 border rounded-md" v-model="searchQuery" type="text"
            placeholder="Search barang..." />
        </div>
        <div class="flex flex-wrap justify-end gap-4">
          <button class="btn-add bg-yellow-500 text-white text-center rounded-md hover:bg-yellow-600 w-[75px] h-[30px]"
            @click="openSearchModal">
            🔍 Search
          </button>
          <button class="btn-print bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[85px] h-[30px]"
            @click="openModal('priceTag')">
            Print Price Tag
          </button>
        </div>
      </div>

      <div class="w-full">
        <table class="datatable w-full rounded-md overflow-hidden text-sm">
          <thead class="bg-blue-100">
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
              <th class="px-4 py-2 text-left">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="barang in isSearchActive ? filteredBarang : pagination" :key="barang.kode_barang"
              class="odd:bg-white even:bg-gray-50 hover:bg-gray-100">
              <td class="px-4 py-2">
                {{ formatTanggal(barang.created_at) }}
              </td>
              <td class="px-4 py-2">{{ barangMap[barang.barangentry_id] }}</td>
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
              <td class="px-4 py-2">{{ barang.barangentry_acara }}</td>
              <td class="px-4 py-2">
                {{ barang.barangentry_ukuran_mandar }}
              </td>
              <td class="px-4 py-2">{{ barang.barangentry_ukuran_ulos }}</td>
              <td class="px-4 py-2">
                <div class="flex space-x-3">
                  <!-- v-if="barang.barangentry_jumlah_barang > 1" -->
                  <button v-if="barang.barangentry_jumlah_barang > 0"
                    class="bg-green-500 text-white text-xs rounded-md hover:bg-green-600 px-2 py-1 h-[30px] w-[45px]"
                    @click="openModalEditBarang(barang.barangentry_id)">
                    Edit
                  </button>
                  <button v-else disabled
                    class="bg-gray-400 text-white text-xs rounded-md px-2 py-1 h-[30px] w-[45px] cursor-not-allowed">
                    Edit
                  </button>
                  <button class="bg-red-500 text-white text-xs rounded-md hover:bg-red-600 px-2 py-1 h-[30px] w-[50px]"
                    @click="deleteBarang(barang.barangentry_id)">
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
            <select id="perPage" v-model="itemsPerPage" class="border px-2 py-1 rounded text-xs">
              <option :value="5">5</option>
              <option :value="10">10</option>
              <option :value="20">20</option>
              <option :value="50">50</option>
              <option value="all">All</option>
            </select>
          </div>

          <div class="flex items-center space-x-2">
            <button class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400 text-xs" :disabled="currentPage === 1"
              @click="currentPage--">
              Sebelumnya
            </button>

            <button v-for="(page, index) in paginatedPages" :key="index"
              @click="typeof page === 'number' && (currentPage = page)" :class="[
                'px-3 py-1 rounded text-xs',
                currentPage === page ? 'bg-blue-500 text-white' : 'bg-gray-200',
                page === '...' ? 'cursor-default' : 'cursor-pointer',
              ]" :disabled="page === '...'">
              {{ page }}
            </button>

            <button class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400 text-xs"
              :disabled="currentPage === totalPages" @click="currentPage++">
              Selanjutnya
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="mx-auto" v-show="activeTab === 'po'">
      <!-- <div class="judul text-xs font-semibold mb-2">Ready to Stock</div> -->
      <div class="flex items-center justify-between pt-2">
        <div class="flex-1">
          <input class="search-box p-2 border rounded-md" v-model="searchQuery" type="text"
            placeholder="Search barang..." />
        </div>
        <div class="flex flex-wrap justify-end gap-4">
          <button class="btn-print bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[85px] h-[30px]"
            @click="openModal('priceTag')">
            Print Price Tag
          </button>
        </div>
      </div>

      <div class="w-full">
        <table class="datatable w-full rounded-md overflow-hidden text-sm">
          <thead class="bg-blue-100">
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
              <th class="px-4 py-2 text-left">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="barang in isSearchActive ? filteredBarang : pagination" :key="barang.kode_barang"
              class="odd:bg-white even:bg-gray-50 hover:bg-gray-100">
              <!-- <pre>{{ barang }}</pre> -->
              <td class="px-4 py-2">
                {{ formatTanggal(barang.created_at) }}
              </td>
              <td class="px-4 py-2">{{ barangMap[barang.barangentry_id] }}</td>
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
              <td class="px-4 py-2">{{ barang.barangentry_acara }}</td>
              <td class="px-4 py-2">
                {{ barang.barangentry_ukuran_mandar }}
              </td>
              <td class="px-4 py-2">{{ barang.barangentry_ukuran_ulos }}</td>
              <td class="px-4 py-2">
                <div class="flex space-x-3">
                  <!-- v-if="barang.barangentry_jumlah_barang > 1" -->
                  <button
                    class="text-center rounded-md bg-[#FBBF24] text-white hover:bg-[#FFD15A] px-2 py-1 h-[30px] w-[90px]"
                    @click="printPreOrder(barang.barangentry_id)">
                    Print
                  </button>
                  <button
                    class=" bg-green-500 text-white text-center rounded-md hover:bg-green-600 px-2 py-1 h-[30px] w-[60px]"
                    @click="openModalEditPO(barang.barangentry_id)">
                    Edit
                  </button>
                  <button v-if="barang.barangfilled"
                    class="text-center rounded-md bg-[#3D8BFD] text-white hover:bg-[#6B9FEC] px-2 py-1 h-[30px] w-[90px]"
                    @click="handleSendClick(barang)">
                    {{ barang.status_barang === "PACKAGING" ? "Packaging" : "Send" }}
                  </button>
                  <template v-else>
                    <button
                      class="btn-add bg-green-500 text-white text-center rounded-md hover:bg-green-600 px-2 py-1 h-[30px] w-[60px]"
                      @click="openModalDesc(barang.barangentry_id)">
                      Desc +
                    </button>
                    <button
                      class="btn-add bg-green-500 text-white text-center rounded-md hover:bg-green-600 px-2 py-1 h-[30px] w-[60px]"
                      @click="openModalSize(barang.barangentry_id)">
                      Size +
                    </button>
                  </template>
                  <button class="bg-red-500 text-white text-xs rounded-md hover:bg-red-600 px-2 py-1 h-[30px] w-[50px]"
                    @click="deleteBarang(barang.barangentry_id)">
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
            <select id="perPage" v-model="itemsPerPage" class="border px-2 py-1 rounded text-xs">
              <option :value="5">5</option>
              <option :value="10">10</option>
              <option :value="20">20</option>
              <option :value="50">50</option>
              <option value="all">All</option>
            </select>
          </div>

          <div class="flex items-center space-x-2">
            <button class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400 text-xs" :disabled="currentPage === 1"
              @click="currentPage--">
              Sebelumnya
            </button>

            <button v-for="(page, index) in paginatedPages" :key="index"
              @click="typeof page === 'number' && (currentPage = page)" :class="[
                'px-3 py-1 rounded text-xs',
                currentPage === page ? 'bg-blue-500 text-white' : 'bg-gray-200',
                page === '...' ? 'cursor-default' : 'cursor-pointer',
              ]" :disabled="page === '...'">
              {{ page }}
            </button>

            <button class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400 text-xs"
              :disabled="currentPage === totalPages" @click="currentPage++">
              Selanjutnya
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showModalAdd" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-50">
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
            <input type="text" :value="formatRupiah2(selectedBarang.barangentry_modal)"
              @input="updateModal($event.target.value)"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
              placeholder="Masukkan Harga Modal" />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Harga Price Tag <span style="color:red">*</span></label>
            <input type="text" :value="formatRupiah2(selectedBarang.barangentry_price_tag)"
              @input="updatePriceTag($event.target.value)"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
              placeholder="Masukkan Harga Price Taf" />
          </div>
          <div>
            <label class="block text-gray-700 mb-1">Harga Net <span style="color:red">*</span></label>
            <input type="text" :value="formatRupiah2(selectedBarang.barangentry_harga_net)"
              @input="updateHargaNet($event.target.value)"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
              placeholder="Masukkan Harga Net" />
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
    <div v-if="showModalAddSize" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-50">
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

          <!-- Ukuran Ulos -->
          <div>
            <label class="block text-gray-700 mb-1">Ukuran Ulos</label>
            <div class="flex items-center gap-2">
              <input v-model="selectedBarang.ukuran_ulos" type="text"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                placeholder="Masukkan Ukuran Ulos" />
              <span class="text-gray-700 text-sm">cm</span>
            </div>
          </div>

          <!-- Ukuran Mandar -->
          <div>
            <label class="block text-gray-700 mb-1">Ukuran Mandar</label>
            <div class="flex items-center gap-2">
              <input v-model="selectedBarang.ukuran_mandar" type="text"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                placeholder="Masukkan Ukuran Mandar" />
              <span class="text-gray-700 text-sm">cm</span>
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

        <!-- KONTEN UTAMA -->
        <div style="display: flex; gap: 40px;">
          <!-- KIRI -->
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

            <!-- Harga -->
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


    <!-- Modal Search -->
    <div v-if="showModalSearch" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-50">
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

    <!-- Modal Edit Stock -->
    <div v-if="showModalEditStock" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50">
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

    <EditBarangPO :show="showEditPO" :id="selectedId" @close="showEditPO = false" @saved="loadData" />

    <EditBarangDesc :show="showEditDesc" :id="selectedId" @close="showEditDesc = false" @saved="loadData" />

    <EditBarangSize :show="showEditSize" :id="selectedId" @close="showEditSize = false" @saved="loadData" />

    <SendOrderModal :visible="showSendModal" @close="showSendModal = false" @submitted="handleSend" />

    <!-- </div> -->
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from "vue";
import BaseModal from "../components/Modal.vue";
import EditBarangReady from "../components/ModalEditBarang.vue";
import EditBarangDesc from "../components/ModalEditDesc.vue";
import EditBarangSize from "../components/ModalEditSize.vue";
import EditBarangPO from "../components/ModalEditPO.vue";
import SendOrderModal from "../components/ModalSendOrder.vue";
import axios from "axios";
import { useRuntimeConfig } from "#imports";
import Swal from "sweetalert2";
import { useRouter } from "vue-router";

const router = useRouter();
const modalOpen = ref(false);
const modalType = ref("desc");
const showModalAdd = ref(false);
const showModalAddSize = ref(false);
const selectedBarang = ref({});
const url = ref("");
const showModalEditStock = ref(false);
const editJumlah = ref(1);

const showEditModal = ref(false);
const showEditDesc = ref(false);
const showEditSize = ref(false);
const showEditPO = ref(false);
const selectedId = ref(null);
const showSendModal = ref(false);

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
const searchQuery = ref("");
const barangMap = ref({});
const isSubmitting = ref(false);
const isSubmittingSize = ref(false);
const pengirimanStatus = ref("");
const inputKodeDesc = ref(null);
const inputKodeSize = ref(null);

onMounted(async () => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;
  try {
    const token = sessionStorage.getItem("auth_token")
    const response = await axios.get(`${url.value}/api/codebarang`, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    });
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

async function openSendModal(id) {
  selectedId.value = id;

  const result = await Swal.fire({
    title: "Konfirmasi Pengiriman",
    text: "Pilih metode pengiriman",
    showDenyButton: true,
    showCancelButton: true,
    confirmButtonText: "DIKIRIM",
    denyButtonText: "DIJEMPUT",
    confirmButtonColor: "#3B82F6",
    denyButtonColor: "#FBBF24",
    cancelButtonColor: "#9CA3AF",
  });

  if (result.isConfirmed) {
    pengirimanStatus.value = "Dikirim";
    showSendModal.value = true;
  }

  if (result.isDenied) {
    pengirimanStatus.value = "Dijemput";

    // await updatePengiriman(
    //   selectedId.value,
    //   "-",
    //   "Dijemput"
    // );

    Swal.fire({
      icon: "success",
      title: "Penjemputan Berhasil",
      text: "Barang siap dijemput oleh pembeli",
      timer: 2000,
      showConfirmButton: false,
    });
  }
}


async function handleSend(data) {

  const pengiriman_id = await sendOrder(selectedId.value, data);
  if (!pengiriman_id) return;

  await updatePengiriman(pengiriman_id, data.nama_akun, pengirimanStatus.value);

  showSendModal.value = false;
}

async function updatePengiriman(pengiriman_id, namaAkun, status) {
  const token = sessionStorage.getItem("auth_token")
  await axios.put(`${url.value}/api/pengiriman-barang/${pengiriman_id}`, {
    pengirimanBarang_nama_penerima: namaAkun,
    pengirimanBarang_status: status
  }, {
    headers: {
      "Authorization": `Bearer ${token}`,
      "Content-Type": "application/json",
    }
  })

  Swal.fire({
    icon: "success",
    title: "Berhasil",
    text: `Barang akan ${status.toUpperCase()}`,
    timer: 2000,
    showConfirmButton: false
  })

  await getListBarangTemp();
}

const openModalEditBarang = (id) => {
  selectedId.value = id;
  showEditModal.value = true;
};

const openModalEditPO = (id) => {
  selectedId.value = id;
  showEditPO.value = true;
};

const openModalDesc = (id) => {
  selectedId.value = id;
  showEditDesc.value = true;
};

const openModalSize = (id) => {
  selectedId.value = id;
  showEditSize.value = true;
};

const loadData = async () => {
  try {
    await getListBarangTemp();
  } catch (err) {
    console.error("Gagal me-reload data:", err);
  }
};

const formatRupiah2 = (value) => {
  if (!value && value !== 0) return "";
  const number = parseInt(value.toString().replace(/\D/g, ""), 10);
  return number.toLocaleString("id-ID");
};

function formatUkuran(ulos, mandar) {
  const u = ulos ? `${ulos} cm` : "-";
  const m = mandar ? `${mandar} cm` : "-";
  return `${u} x ${m}`;
}

const updatePriceTag = (val) => {
  const numericValue = parseInt(val.replace(/\D/g, ""), 10) || 0;
  selectedBarang.value.barangentry_price_tag = numericValue;
};

const updateModal = (val) => {
  const numericValue = parseInt(val.replace(/\D/g, ""), 10) || 0;
  selectedBarang.value.barangentry_modal = numericValue;
};

const updateHargaNet = (val) => {
  const numericValue = parseInt(val.replace(/\D/g, ""), 10) || 0;
  selectedBarang.value.barangentry_harga_net = numericValue;
};

const fetchCodeBarang = async (data) => {
  try {
    const token = sessionStorage.getItem("auth_token")
    const ids = [...new Set(data.map((item) => item.barangentry_id))];
    const requests = ids.map((id) =>
      axios.get(`${url.value}/api/entrybarang/${id}`, {
        headers: {
          "Authorization": `Bearer ${token}`,
          "Content-Type": "application/json",
        }
      })
    );
    const responses = await Promise.all(requests);

    const codeBarangPromises = responses.map((res) =>
      axios.get(`${url.value}/api/codebarang/${res.data.data.barangentry_code_id}`, {
        headers: {
          "Authorization": `Bearer ${token}`,
          "Content-Type": "application/json",
        }
      })
    );
    const codeBarangResults = await Promise.all(codeBarangPromises);

    codeBarangResults.forEach((res, i) => {
      barangMap.value[ids[i]] = res.data.code_nama;
    });
  } catch (error) {
    console.error("Data gagal diambil", error);
  }
};

async function getListBarangTemp() {
  try {
    const token = sessionStorage.getItem("auth_token")
    let endpoint = "";
    if (activeTab.value === "wait") {
      endpoint = "/api/entrybarang/getDataWaitForEntry";
    } else if (activeTab.value === "ready") {
      endpoint = "/api/entrybarang/getDataReady";
    } else {
      endpoint = "/api/entrybarang/getDataPO";
    }
    const response = await axios.get(`${url.value}${endpoint}`, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    });
    // const response = await axios.get(`http://192.168.18.52:8080${endpoint}`);
    listBarang.value = response.data.data;
    await fetchCodeBarang(listBarang.value);
  } catch (error) {
    console.error("Gagal Memuat Data Barang: ", error);
  }
}

let barcodeTimeoutDesc = null;

async function handleBarcodeDesc() {
  clearTimeout(barcodeTimeoutDesc);
  barcodeTimeoutDesc = setTimeout(async () => {
    if (!selectedBarang.value.code_nama) return;

    try {
      const token = sessionStorage.getItem("auth_token")
      const response = await axios.get(
        `${url.value}/api/codebarang/getDataByCode/${selectedBarang.value.code_nama}`, {
        headers: {
          "Authorization": `Bearer ${token}`,
          "Content-Type": "application/json",
        }
      }
      );
      const data = response.data.data;

      if (!data) {
        Swal.fire({
          icon: "error",
          title: "Kode Tidak Ditemukan",
          text: "Pastikan kode barang valid",
          timer: 2000,
          showConfirmButton: false,
        });
        return;
      }

      selectedBarang.value.code_id = data.code_id;
    } catch (err) {
      console.error("Gagal mengambil data kode:", err);
      Swal.fire({
        icon: "error",
        title: "Gagal",
        text: "Terjadi kesalahan saat memeriksa kode.",
        timer: 2000,
        showConfirmButton: false,
      });
    }
  }, 600);
}

let barcodeTimeoutSize = null;

async function handleBarcodeSize() {
  clearTimeout(barcodeTimeoutSize);
  barcodeTimeoutSize = setTimeout(async () => {
    if (!selectedBarang.value.kode_barang) return;

    try {
      const token = sessionStorage.getItem("auth_token")
      const response = await axios.get(
        `${url.value}/api/codebarang/getDataByCode/${selectedBarang.value.kode_barang}`, {
        headers: {
          "Authorization": `Bearer ${token}`,
          "Content-Type": "application/json",
        }
      }
      );
      const data = response.data.data;

      if (!data) {
        Swal.fire({
          icon: "error",
          title: "Kode Tidak Ditemukan",
          text: "Pastikan kode barang valid",
          timer: 2000,
          showConfirmButton: false,
        });
        return;
      }

      selectedBarang.value.code_id = data.code_id;
    } catch (err) {
      console.error("Gagal mengambil data kode:", err);
      Swal.fire({
        icon: "error",
        title: "Gagal",
        text: "Terjadi kesalahan saat memeriksa kode.",
        timer: 2000,
        showConfirmButton: false,
      });
    }
  }, 600);
}

function handleSendClick(barang) {
  if (barang.status_barang === "PACKAGING") {
    router.push(`/packaging-page`);
  } else {
    openSendModal(barang.barangentry_id);
  }
}

async function getListBarangPreOrder() {
  try {
    const token = sessionStorage.getItem("auth_token")
    const responseEntry = await axios.get(`${url.value}/api/preOrderEntry/$`, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    })
    const response = await axios.get(`${url.value}${endpoint}`);
    listBarang.value = response.data.data;
    await fetchCodeBarang(listBarang.value);
  } catch (error) {
    console.error("Gagal Memuat Data Barang: ", error);
  }
}

const searchFilterData = computed(() => {
  const sorted = [...listBarang.value].sort((a, b) => {
    return new Date(b.created_at) - new Date(a.created_at);
  });

  sorted.sort((a, b) => {
    if (a.barangentry_jumlah_barang === 0 && b.barangentry_jumlah_barang > 0) return 1;
    if (a.barangentry_jumlah_barang > 0 && b.barangentry_jumlah_barang === 0) return -1;
    return 0;
  });

  if (!searchQuery.value) return sorted;

  const q = searchQuery.value.toLowerCase();
  return sorted.filter((barang) => {
    return (
      barang.barangentry_nama?.toLowerCase().includes(q) ||
      barang.barangentry_warna?.toLowerCase().includes(q)
    );
  });
});

const pagination = computed(() => {
  if (itemsPerPage.value === "all") {
    return searchFilterData.value;
  }

  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return searchFilterData.value.slice(start, end);
});

const totalPages = computed(() => {
  if (itemsPerPage.value === "all") return 1;
  return Math.ceil(listBarang.value.length / itemsPerPage.value);
});

function openModal(type) {
  modalType.value = type;
  modalOpen.value = true;
}

function tambahBarang(barang) {
  selectedBarang.value = {
    ...barang,
    barangentry_jumlah_barang: barang.barangentry_jumlah_barang ?? 1
  };
  showModalAdd.value = true;
}

function formatRupiah(value) {
  const number = parseInt(value);
  return "Rp. " + number.toLocaleString("id-ID");
}

async function sendOrder(barangentry_id, formData) {
  try {
    const token = sessionStorage.getItem("auth_token")
    const res = await axios.get(`${url.value}/api/pre-order-barang/preOrderEntry/${barangentry_id}`, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    });

    const dataPreOrder = res.data.data;
    const payloadTransaksi = {
      transaksi_nama_customer: formData.nama_akun,
      transaksi_nomor_telepon: "",
      transaksi_jumlah_barang: 1,
      transaksi_total_harga: parseInt(formData.total_pembayaran),
      transaksi_cara_bayar: formData.cara_bayar,
      transaksi_tipe: "PREORDER",
      transaksi_status: "PREORDER",
      transaksi_catatan: "",
    };

    const { data } = await axios.post(
      `${url.value}/api/transaksi`,
      payloadTransaksi, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    }
    );
    const transaksi_id = data.data.transaksi_id;

    const barang = await axios.get(`${url.value}/api/entrybarang/${barangentry_id}`, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    })

    const code = await axios.get(`${url.value}/api/codebarang/${barang.data.data.barangentry_code_id}`, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    });

    const { data: barangResponse } = await axios.get(
      `${url.value}/api/entrybarang/getDataByCode/${code.data.code_nama}`, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    }
    );
    const barangData = barangResponse.data;

    const detailPayload = {
      transaksidetail_transaksi_id: transaksi_id,
      transaksidetail_barang_id: barangData.barangentry_id,
      transaksidetail_jumlah_barang: 1,
      transaksidetail_harga_barang: Number(barangData.barangentry_harga_net),
    };
    await axios.post(`${url.value}/api/transaksi-detail`, detailPayload, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    });

    const pengirimanPayload = {
      pengirimanBarang_transaksi_id: transaksi_id,
      pengirimanBarang_nama_penerima: formData.nama_akun,
      pengirimanBarang_akun_penerima: formData.nama_akun,
      pengirimanBarang_no_telepon: formData.no_telepon,
      pengirimanBarang_harga_kirim_barang: parseInt(formData.harga_kirim),
      pengirimanBarang_jenis_pengiriman_barang: formData.jenis_pengiriman,
      pengirimanBarang_alamat_pengiriman_barang: formData.alamat,
      pengirimanBarang_catatan: "",
      pengirimanBarang_status: "Proses",
    };

    const resPengiriman = await axios.post(`${url.value}/api/pengiriman-barang`, pengirimanPayload, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    });
    const dataPengiriman = resPengiriman.data.data;

    await getListBarangTemp();
    showSendModal.value = false;

    return dataPengiriman.pengiriman.pengirimanBarang_id;
  } catch (err) {
    // console.error('sad', err);

    Swal.fire({
      title: "Gagal!",
      text: "Silahkan lengkapi preorder",
      icon: "error",
      confirmButtonText: "OK",
      timer: 3000,
      timerProgressBar: true,
    });

  }
}

async function submitBarang() {

  const requiredFields = [
    'code_id',
    'barangentry_nama',
    'barangentry_warna',
    'barangentry_nama_penenun',
    'barangentry_nama_panirat',
    'barangentry_dryer',
    'barangentry_modal',
    'barangentry_price_tag',
    'barangentry_harga_net',
    'barangentry_jumlah_barang'
  ];

  const missingFields = requiredFields.filter(
    (field) => !selectedBarang.value[field] && selectedBarang.value[field] !== 0
  );

  if (missingFields.length > 0) {
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

  isSubmitting.value = true;
  try {
    const token = sessionStorage.getItem("auth_token")
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

    await axios.post(`${url.value}/api/entrybarang/storeDescription`, payload, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    });
    await getListBarangTemp();

    selectedBarang.value = {};
    showModalAdd.value = false;
    modalOpen.value = false;

    Swal.fire({
      title: "Sukses!",
      text: "Berhasil Menambahkan Deskripsi",
      icon: "success",
      confirmButtonText: "OK",
      timer: 3000,
      timerProgressBar: true,
    });
  } catch (error) {
    console.error("Gagal meyimpan barang:", error);
  } finally {
    isSubmitting.value = false;
  }
}

async function openTambahBarang() {
  selectedBarang.value = {
    barangentry_jumlah_barang: 1
  };
  showModalAdd.value = true;
  await nextTick();
  if (inputKodeDesc.value) inputKodeDesc.value.focus();
}

async function openAddSize() {
  selectedBarang.value = {};
  showModalAddSize.value = true
  await nextTick();
  if (inputKodeSize.value) inputKodeSize.value.focus();
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

  const requiredFields = [
    'code_id',
    'ukuran_mandar',
    'ukuran_ulos'
  ];

  const missingFields = requiredFields.filter(
    (field) => !selectedBarang.value[field] && selectedBarang.value[field] !== 0
  );

  if (missingFields.length > 0) {
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
  isSubmittingSize.value = true;
  try {
    const token = sessionStorage.getItem("auth_token")
    const payload = {
      barangentry_code_id: String(selectedBarang.value.code_id),
      barangentry_ukuran_mandar: String(selectedBarang.value.ukuran_mandar),
      barangentry_ukuran_ulos: String(selectedBarang.value.ukuran_ulos),
    };

    await axios.post(`${url.value}/api/entrybarang/storeSize`, payload, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    });

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
    await getListBarangTemp();

    selectedBarang.value = {};
    showModalAddSize.value = false;
    modalOpen.value = false;

    Swal.fire({
      title: "Sukses!",
      text: "Berhasil Menambahkan Size",
      icon: "success",
      confirmButtonText: "OK",
      timer: 3000,
      timerProgressBar: true,
    });
  } catch (error) {
    console.error("Gagal menyimpan size:", error);
  } finally {
    isSubmittingSize.value = false;
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
    const token = sessionStorage.getItem("auth_token")
    const response = await axios.get(
      `${url.value}/api/entrybarang/getDataByCode/${keyword}`, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    }
    );
    const code = response.data.data.barangentry_code_id;

    if (!code) {
      Swal.fire({
        icon: "warning",
        title: "Kode tidak ditemukan",
        text: "Kode tidak ditemukan dari server.",
      });
      return;
    }

    filteredBarang.value = listBarang.value.filter((item) =>
      String(item.barangentry_code_id).includes(String(code))
    );

    isSearchActive.value = true;
    showModalSearch.value = false;

    if (filteredBarang.value.length === 0) {
      Swal.fire({
        icon: "info",
        title: "Barang tidak ditemukan",
        text: "Barang tidak ditemukan di daftar.",
      });
    }
  } catch (error) {
    console.error("Gagal mencari data kode:", error);
    Swal.fire({
      icon: "error",
      title: "Terjadi kesalahan",
      text: "Terjadi kesalahan saat mencari kode.",
    });
  }
}

const openModalTambahStock = (barang) => {
  selectedBarang.value = barang;
  editJumlah.value = barang.jumlah || 0;
  showModalEditStock.value = true;
};

const editStockSubmit = async () => {
  if (!selectedBarang.value) return;

  try {
    const token = sessionStorage.getItem("auth_token")
    const responseEdit = await axios.post(
      `${url.value}/api/entrybarang/${selectedBarang.value}/updateStok`,
      { jumlah_barang: editJumlah.value }, {
      headers: {
        "Authorization": `Bearer ${token}`,
        "Content-Type": "application/json",
      }
    }
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
      const token = sessionStorage.getItem("auth_token")
      const response = await axios.post(
        `${url.value}/api/entrybarang/${id}/deleteBarangEntry`,
        { status: "DELETED" }, {
        headers: {
          "Authorization": `Bearer ${token}`,
          "Content-Type": "application/json",
        }
      }
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
  if (itemsPerPage.value === "all") return [1];

  const total = totalPages.value;
  const current = currentPage.value;
  const pages = [];

  if (total <= 5) {
    for (let i = 1; i <= total; i++) pages.push(i);
  } else {
    if (current <= 3) pages.push(1, 2, 3, "...", total);
    else if (current >= total - 2) pages.push(1, "...", total - 2, total - 1, total);
    else pages.push(1, "...", current - 1, current, current + 1, "...", total);
  }

  return pages;
});


async function printPreOrder(id) {
  const token = sessionStorage.getItem("auth_token")
  const response = await axios.get(`${url.value}/api/pre-order-barang/preOrderEntry/${id}`, {
    headers: {
      "Authorization": `Bearer ${token}`,
      "Content-Type": "application/json",
    }
  });

  const data = response.data.data;

  const resEntry = await axios.get(`${url.value}/api/entrybarang/${id}`, {
    headers: {
      "Authorization": `Bearer ${token}`,
      "Content-Type": "application/json",
    }
  });
  const barangEntry = resEntry.data.data;

  const code = await axios.get(`${url.value}/api/codebarang/${barangEntry.barangentry_code_id}`, {
    headers: {
      "Authorization": `Bearer ${token}`,
      "Content-Type": "application/json",
    }
  })
  const code_nama = code.data.code_nama;

  printData(data, barangEntry, code_nama);

}

function printData(data, barangEntry, code_nama) {

  const printWindow = window.open("", "_blank");
  if (!printWindow) {
    alert("Pop-up blocker menghalangi membuka tab baru.");
    return;
  }

  const formatTanggal = (dateStr) => {
    if (!dateStr) return "-";
    const date = new Date(dateStr);
    return date.toLocaleDateString("id-ID", {
      day: "2-digit",
      month: "long",
      year: "numeric",
    });
  };

  const formatRupiah = (value) => {
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(value || 0);
  };

  const htmlContent = `
  <!DOCTYPE html>
  <html lang="id">
  <head>
    <meta charset="UTF-8" />
    <title>Pre Order Invoice</title>
    <style>
      body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 40px;
        background: #fff;
        color: #000;
      }

      .print-area {
        max-width: 800px;
        margin: auto;
        box-sizing: border-box;
      }

      .header {
        text-align: center;
        margin-bottom: 20px;
      }

      .logo {
        width: 100%;
        max-width: 800px;
      }

      .info {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
        font-size: 14px;
        gap: 40px;
      }
      .info-col {
        flex: 1;
      }
      .info-row {
        display: flex;
        margin-bottom: 6px;
      }
      .info-label {
        width: 150px; /* pastikan lebar sama biar titik dua sejajar */
        font-weight: bold;
      }
      .info-separator {
        margin-right: 6px;
      }
      .info-value {
        flex: 1;
        font-weight: normal;
      }

      h3 {
        margin-top: 30px;
        font-size: 15px;
      }

      table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        margin-top: 12px;
        border: 1px solid #eee;
        border-radius: 8px;
        overflow: hidden;
      }

      thead {
        background-color: #f9f9f9;
        font-weight: bold;
      }

      th, td {
        padding: 8px;
        text-align: left;
      }

      tbody tr:not(:last-child) {
        border-bottom: 1px dashed #ccc;
      }

      .total-row td {
        font-weight: bold;
        text-align: right;
        padding-top: 12px;
        border-top: 1px solid #ddd;
      }

      .signatures {
        display: flex;
        justify-content: flex-end; /* rata kanan */
        gap: 40px;
        margin: 40px 0;
      }
      .sign-box {
        border: 1px solid #000;
        width: 120px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
      }
      .footer {
        text-align: center;
        font-size: 12px;
        color: #555;
        margin-top: 40px;
      }

      @media print {
        body {
          margin: 0;
          padding: 0;
        }
      }
    </style>
  </head>
  <body>
    <div class="print-area">
      <div class="header">
        <img src="/image/DameUlosPO.png" alt="Logo Dame Ulos" class="logo"/>
      </div>

      <div class="info">
        <div class="info-col">
          <div class="info-row">
            <div class="info-label">Kode PO</div>
            <div class="info-separator">:</div>
            <div class="info-value">${code_nama || "-"}</div>
          </div>
          <div class="info-row">
            <div class="info-label">Nama Ulos</div>
            <div class="info-separator">:</div>
            <div class="info-value">${barangEntry.barangentry_nama || "-"}</div>
          </div>
          <div class="info-row">
            <div class="info-label">Deskripsi</div>
            <div class="info-separator">:</div>
            <div class="info-value">${data.preOrderBarang_deskripsi || "-"}</div>
          </div>
        </div>

        <div class="info-col">
          <div class="info-row">
            <div class="info-label">Tanggal Dimulai</div>
            <div class="info-separator">:</div>
            <div class="info-value">${formatTanggal(data.updated_at)}</div>
          </div>
          <div class="info-row">
            <div class="info-label">Tanggal Selesai</div>
            <div class="info-separator">:</div>
            <div class="info-value">${formatTanggal(data.preOrderBarang_target_selesai)}</div>
          </div>
          <div class="info-row">
            <div class="info-label">Author</div>
            <div class="info-separator">:</div>
            <div class="info-value">${data.preOrderBarang_nama_akun || "-"}</div>
          </div>
        </div>
      </div>

      <h3>Rincian Pembayaran</h3>
      <table>
        <thead>
          <tr>
            <th>Total Pembayaran</th>
            <th>Down Payment (DP)</th>
            <th>Pelunasan</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>${formatRupiah(data.preOrderBarang_total_pembayaran)}</td>
            <td>${formatRupiah(data.preOrderBarang_uang_muka)}</td>
            <td>${formatRupiah(data.preOrderBarang_sisa_pembayaran)}</td>
          </tr>
        </tbody>
      </table>

      <div class="signatures">
        <div class="sign-box">Tanda Masuk</div>
        <div class="sign-box">Tanda Pengambilan</div>
      </div>

      <div class="footer">
        <p>Transaksi ini diproses berdasarkan Purchase Order yang berlaku.</p>
        <p>Terima kasih telah mempercayakan kebutuhan Anda kepada kami.</p>
      </div>
    </div>

    <script>
      window.onload = function () {
        window.print();
      };
    <\/script>
  </body>
  </html>
  `;

  printWindow.document.write(htmlContent);
  printWindow.document.close();
}

watch(editJumlah, (val) => {
  if (val < 1 || !val) {
    editJumlah.value = 1;
  }
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
  height: 30px;
  font-size: 12px;
}

.datatable {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
  margin-top: 20px;
}

.datatable th,
.datatable td {
  padding: 10px;
  /* border: 1px solid #ddd; */
  text-align: left;
  font-size: 12px;
  word-wrap: break-word;
  white-space: normal;
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

.btn-reset-pencarian {
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

.fixed {
  position: fixed;
}

.bg-gray-800 {
  background-color: rgba(0, 0, 0, 0.5);
}
</style>
