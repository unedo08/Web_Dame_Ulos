<template>
    <div class="flex flex-col h-screen bg-gray-100">
        <div class="flex flex-grow">
            <!-- Sidebar -->
            <div class="w-56 md:w-48 lg:w-56 bg-[#520000] text-white p-6">
               <Sidebar />
            </div>

            <!-- Main Content -->
            <div class="flex-1 p-8 bg-white">
                <Topbar />
                <div class="mt-6">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
// import { definePageMeta } from '/nuxt/dist/pages/runtime';
import Topbar from '../components/Topbar/index.vue'; 

export default {
    name: 'DefaultLayout',
    components: {
        Topbar
    }
};
definePageMeta({
    middleware: 'auth'
})
</script>

<style scoped>
/* Custom styling for DefaultLayout */
</style>