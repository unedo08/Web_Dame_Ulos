<template>
    <div>
        <title>Packaging</title>

        <!-- TAB -->
        <div class="flex space-x-4 mb-4">
            <button @click="activeTab = 'pending'" :class="[
                'px-4 py-2 text-sm font-semibold',
                activeTab === 'pending' ? 'border-b-2 border-blue-500 text-red-900 font-semibold' : 'text-gray-500'
            ]">
                Packaging Belum Diproses
            </button>

            <button @click="activeTab = 'done'" :class="[
                'px-4 py-2 text-sm font-semibold',
                activeTab === 'done' ? 'border-b-2 border-blue-500 text-red-900 font-semibold' : 'text-gray-500'
            ]">
                Packaging Sudah Dikirim
            </button>
        </div>

        <!-- TAB 1 -->
        <div v-if="activeTab === 'pending'">
            <PackagingPending />
        </div>

        <!-- TAB 2 -->
        <div v-if="activeTab === 'done'">
            <PackagingDone />
        </div>

    </div>
</template>

<script setup>
import { ref } from "vue";
import PackagingPending from "./packaging.vue";
import PackagingDone from "./packaging-pengiriman.vue";

const activeTab = ref("pending");
</script>

<style scoped>
button {
    transition: 0.2s;
}
</style>
