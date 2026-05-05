<template>
  <div>
    <title>Metode Pembayaran</title>
    <div class="judul text-xl font-semibold mb-2">
      Metode Pembayaran
    </div>
    <div class="flex justify-between items-center mb-4">
      <input v-model="search" type="text" placeholder="Cari metode pembayaran" class="search-box" />

      <button class="btn-add" @click="openModal()">
        + Tambah
      </button>
    </div>

    <table class="datatable w-full rounded-md">
      <thead class="bg-blue-100">
        <tr>
          <th>No</th>
          <th>Nama Metode Pembayaran</th>
          <th>Terakhir Diperbaharui</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>

      <tbody v-if="filteredData.length > 0">
        <tr v-for="(item, index) in paginatedData" :key="item.id" class="odd:bg-white even:bg-gray-50">
          <td>
            {{ (currentPage - 1) * itemsPerPage + index + 1 }}
          </td>
          <td>{{ item.name }}</td>

          <td>{{ formatDate(item.updatedAt) }}</td>
          <td>
            <span :class="[
              'inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-full',
              statusChipClass(item.status)
            ]">
              <span class="w-2 h-2 rounded-full" :class="item.status === 1 ? 'bg-green-500' : 'bg-red-500'">
              </span>

              {{ statusLabel(item.status) }}
            </span>
          </td>
          <td class="px-4 py-2 flex items-center gap-3">
            <button @click="openModal(item)" class="text-gray-400 hover:text-blue-500 transition" title="Edit">
              <PencilSquareIcon class="w-5 h-5" />
            </button>

            <button @click="deleteItem(item)" class="text-gray-400 hover:text-red-500 transition" title="Delete">
              <TrashIcon class="w-5 h-5" />
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <div class="flex justify-between items-center mt-6 text-xs">

      <div class="flex items-center gap-2">
        <span>Tampilkan:</span>
        <select v-model="itemsPerPage" class="border px-2 py-1 rounded">
          <option :value="5">5</option>
          <option :value="10">10</option>
          <option :value="20">20</option>
        </select>
      </div>
      <div class="flex items-center gap-2">
        <button class="px-3 py-1 bg-gray-300 rounded" :disabled="currentPage === 1" @click="currentPage--">
          Sebelumnya
        </button>

        <button v-for="(page, i) in paginatedPages" :key="i" @click="typeof page === 'number' && (currentPage = page)"
          :class="[
            'px-3 py-1 rounded',
            currentPage === page ? 'bg-blue-500 text-white' : 'bg-gray-200',
            page === '...' ? 'cursor-default' : 'cursor-pointer'
          ]" :disabled="page === '...'">
          {{ page }}
        </button>

        <button class="px-3 py-1 bg-gray-300 rounded" :disabled="currentPage === totalPages" @click="currentPage++">
          Selanjutnya
        </button>
      </div>
    </div>

    <div v-if="filteredData.length === 0" class="text-center mt-10 text-gray-400">
      Data Tidak Ditemukan
    </div>

    <div v-if="isModalOpen" class="fixed inset-0 flex justify-center items-center bg-black/40 z-50">
      <div class="bg-white w-[420px] rounded-lg shadow-lg p-6">

        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-700">
            {{ form.id ? "Edit" : "Tambah" }} Metode Pembayaran
          </h3>
          <button @click="closeModal" class="text-gray-500 hover:text-black text-xl">
            ×
          </button>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-600 mb-1">
            Nama Metode Pembayaran <span class="text-red-500">*</span>
          </label>
          <input v-model="form.name" type="text" placeholder="Masukkan nama metode pembayaran"
            class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-cyan-500" />
        </div>

        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-600 mb-2">
            Status
          </label>
          <div class="border rounded-md px-4 py-3 flex justify-between items-center bg-gray-50">
            <div>
              <div class="text-sm font-medium text-gray-700">
                Status metode pembayaran
              </div>
              <div class="text-xs text-gray-400">
                Akan dapat digunakan dalam transaksi
              </div>
            </div>

            <label class="switch">
              <input type="checkbox" :checked="form.status === 1"
                @change="form.status = $event.target.checked ? 1 : 0" />
              <span class="slider"></span>
            </label>
          </div>
        </div>

        <div class="flex justify-end gap-3">
          <button @click="closeModal" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
            Batal
          </button>

          <button :disabled="!form.name" @click="saveData"
            class="px-5 py-2 bg-cyan-600 text-white rounded-md hover:bg-cyan-700 disabled:opacity-50">
            Tambah
          </button>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import "@/assets/css/payment-method.css";
import {
  PencilSquareIcon,
  TrashIcon
} from "@heroicons/vue/24/outline";

import { usePaymentMethod } from "@/composables/usePaymentMethod";

const {
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
} = usePaymentMethod();
</script>