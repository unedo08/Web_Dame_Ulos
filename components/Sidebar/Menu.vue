<script setup>
import Logo from '../ui/Logo'
const items = ref([
    {
        title: 'Staff',
        path: '/about'
    },
    {
        title: 'Akun Pembeli',
        path: '/'
    },
    {
        title: 'Beranda',
        path: '/'
    },
    {
        title: 'Barcode',
        path: '/'
    },
    {
        title: 'Barang Masuk',
        path: '/about'
    },
    {
        title: 'Live',
        path: '/'
    },
    {
        title: 'Kasir',
        path: '/'
    },
    {
        title: 'Packaging',
        path: '/'
    },
    {
        title: 'Staff',
        path: '/about'
    },
    {
        title: 'Inventory',
        path: '/'
    },
    {
        title: 'Acara',
        path: '/'
    },
    {
        title: 'Pewarna Alam',
        path: '/'
    },
    {
        title: 'Database Penjualan',
        path: '/about'
    },
    {
        title: 'Database Inventory',
        path: '/'
    }
])
</script>

<template>
    <div>
        <header class="flex items-center gap-2 p-4 hover:scale-[101%] transition cursor-pointer">
            <Logo />
            <!-- <p class="font-bold"> test test</p> -->
        </header>
        <div class="px-4 grow">
            <div class="grid gap-2 text-left">
                <div v-for='(item, index) in items' :key='index'>
                    <span>{{ item.title }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
