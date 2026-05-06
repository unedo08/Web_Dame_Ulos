<template>
  <div class="modal-tambah-overlay" @click.self="$emit('close')">
    <div class="modal-tambah-box">
      <div class="modal-tambah-header">
        <h2 class="modal-tambah-title">Tambah Kode Barang</h2>
        <button class="modal-tambah-close" @click="$emit('close')">×</button>
      </div>

      <div class="modal-tambah-body">
        <label class="modal-tambah-label">
          Kode Barang <span class="modal-tambah-required">*</span>
        </label>
        <input
          ref="inputRef"
          v-model="kodeBarang"
          type="text"
          class="modal-tambah-input"
          placeholder="Masukkan kode barang"
          @keyup.enter="handleTambah"
        />
      </div>

      <div class="modal-tambah-footer">
        <button class="btn-batal" @click="$emit('close')">Batal</button>
        <button
          class="btn-tambah"
          :disabled="!kodeBarang.trim()"
          @click="handleTambah"
        >
          Tambah
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick } from 'vue';

const emit = defineEmits(['close', 'tambah']);

const kodeBarang = ref('');
const inputRef = ref(null);

function handleTambah() {
  const kode = kodeBarang.value.trim();
  if (!kode) return;
  emit('tambah', kode);
}

function reset() {
  kodeBarang.value = '';
  nextTick(() => inputRef.value?.focus());
}

defineExpose({ reset });
</script>

<style>
@import '~/assets/css/kasir.css';
</style>
