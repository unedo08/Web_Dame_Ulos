<template>
  <div class="beranda">
    <div class="judul text-lg font-semibold mb-4">Beranda</div>

    <div class="card">
      <div class="card-icon">
        <CubeIcon class="w-8 h-8 text-blue-600" />
      </div>
      <div class="card-content">
        <div class="card-title">Total Barang Masuk</div>
        <div class="card-value">{{ totalBarang }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios'
import { ref, onMounted } from 'vue'
import { useRuntimeConfig } from '#imports'
import { CubeIcon } from '@heroicons/vue/24/solid'

const totalBarang = ref(0)
const url = ref('')

onMounted(async () => {
  const config = useRuntimeConfig()
  url.value = config.public.apiBase
  try {
    const response = await axios.get(`${url.value}/api/codebarang`)
    const data = response.data

    totalBarang.value = data.length
  } catch (error) {
    console.error('Gagal mengambil data:', error)
  }
})
</script>

<style scoped>
.beranda {
  max-width: 400px;
  padding: 16px;
}

.card {
  display: flex;
  align-items: center;
  padding: 16px;
  background: #fff;
  border: 2px solid #d9dce0;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
  transition: border-color 0.3s ease;
}

.card-icon {
  font-size: 2rem;
  color: #4a90e2;
  margin-right: 16px;
}

.card-content {
  display: flex;
  flex-direction: column;
}

.card-title {
  font-size: 1rem;
  font-weight: 500;
  color: #333;
}

.card-value {
  font-size: 1.5rem;
  font-weight: bold;
  color: #111;
}
</style>
