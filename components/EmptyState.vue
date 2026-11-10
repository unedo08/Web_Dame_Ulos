<template>
  <div class="empty-state" :class="{ 'empty-state--compact': compact }">
    <div class="empty-state__icon-wrap">
      <svg
        class="empty-state__icon"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.6"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
      >
        <circle cx="11" cy="11" r="7" />
        <line x1="21" y1="21" x2="16.65" y2="16.65" />
        <line x1="8" y1="11" x2="14" y2="11" />
      </svg>
    </div>
    <h3 class="empty-state__title">{{ title }}</h3>
    <p class="empty-state__message">
      <template v-if="searchQuery">
        Tidak ada hasil untuk
        <span class="empty-state__keyword">"{{ searchQuery }}"</span>.
        Coba gunakan kata kunci lain.
      </template>
      <template v-else>
        {{ message }}
      </template>
    </p>
  </div>
</template>

<script setup>
defineProps({
  searchQuery: { type: String, default: '' },
  title: { type: String, default: 'Data tidak ditemukan' },
  message: { type: String, default: 'Belum ada data yang tersedia.' },
  compact: { type: Boolean, default: false },
})
</script>

<style>
/* Unscoped: keep the row/cell containing an EmptyState white even on hover */
tr:has(.empty-state),
tr:has(.empty-state) > td,
tr:hover:has(.empty-state),
tr:hover:has(.empty-state) > td,
table tbody > tr:hover > td:has(.empty-state) {
  background-color: #ffffff !important;
}
</style>

<style scoped>
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 48px 24px;
  color: #FFFF;
  background-color: #ffffff;
  width: 100%;
  min-height: 240px;
  box-sizing: border-box;
}

.empty-state--compact {
  padding: 24px 16px;
  min-height: 160px;
}

.empty-state__icon-wrap {
  width: 88px;
  height: 88px;
  border-radius: 50%;
  background: #f3f4f6;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
}

.empty-state--compact .empty-state__icon-wrap {
  width: 64px;
  height: 64px;
  margin-bottom: 12px;
}

.empty-state__icon {
  width: 44px;
  height: 44px;
  color: #9ca3af;
}

.empty-state--compact .empty-state__icon {
  width: 32px;
  height: 32px;
}

.empty-state__title {
  font-size: 16px;
  font-weight: 600;
  color: #374151;
  margin: 0 0 6px 0;
}

.empty-state__message {
  font-size: 14px;
  color: #6b7280;
  margin: 0;
  max-width: 420px;
  line-height: 1.5;
}

.empty-state__keyword {
  font-weight: 600;
  color: #374151;
}
</style>
