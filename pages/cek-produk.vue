<template>
    <div>
        <title>Cek Produk</title>
        <div class="check-container">
            <div class="check-header">
                <div class="header-icon">
                    📦
                </div>

                <div>
                    <div class="header-title">
                        Pencarian Produk
                    </div>
                    <div class="header-subtitle">
                        Ketik atau scan code untuk informasi produk
                    </div>
                </div>
            </div>

            <div class="check-body">
                <div class="mb-4">
                    <label class="input-label">
                        🔍 Ketik/scan kode barang
                    </label>

                    <div class="relative">
                        <input ref="inputCodeBarang" v-model="codeBarang" type="text" placeholder="Masukkan kode barang..."
                            class="input-search" @keyup.enter="searchProduk" />

                        <button class="search-button" @click="searchProduk">
                            🔎
                        </button>
                    </div>
                </div>

                <div v-if="!product" class="empty-state">
                    <div class="empty-icon">
                        📷
                    </div>

                    <div class="empty-text">
                        Masukkan kode barang untuk melihat detail
                    </div>
                </div>

                <div v-else class="product-card">
                    <div class="product-row">
                        <span class="label">🏷️ KODE BARANG</span>
                        <span class="value">{{ product.code_barang }}</span>
                    </div>

                    <div class="product-row">
                        <span class="label">🧵 NAMA ULOS</span>
                        <span class="value">{{ product.barangentry_nama }}</span>
                    </div>

                    <div class="product-row">
                        <span class="label">🎨 WARNA</span>
                        <span class="value">{{ product.barangentry_warna }}</span>
                    </div>

                    <div class="product-row">
                        <span class="label">✨ NAMA PANIRAT</span>
                        <span class="value">{{ product.barangentry_nama_panirat }}</span>
                    </div>

                    <div class="product-row">
                        <span class="label">📏 UKURAN MANDAR</span>
                        <span class="value">{{ product.barangentry_ukuran_mandar == 0 ? '-' :
                            product.barangentry_ukuran_mandar +' cm' }}</span>
                    </div>

                    <div class="product-row">
                        <span class="label">📐 UKURAN ULOS</span>
                        <span class="value">{{ product.barangentry_ukuran_ulos }} cm</span>
                    </div>

                    <!-- PRICE -->
                    <div class="price-grid">

                        <div class="price-box blue">
                            <div class="price-title">
                                HARGA PRICE TAG
                            </div>

                            <div class="price-value">
                                {{ formatRupiah(product.barangentry_price_tag) }}
                            </div>
                        </div>

                        <div class="price-box gray">
                            <div class="price-title">
                                HARGA MODAL
                            </div>

                            <div class="price-value">
                                {{ formatRupiah(product.barangentry_modal) }}
                            </div>
                        </div>

                        <div class="price-box green">
                            <div class="price-title">
                                HARGA NET
                            </div>

                            <div class="price-value">
                                {{ formatRupiah(product.barangentry_harga_net) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import "@/assets/css/cek-produk.css";
import { useCheckProduk } from "@/composables/useCheckProduk";

const {
    codeBarang,
    product,
    handleSearch,
    formatRupiah,
} = useCheckProduk();

const inputCodeBarang = ref(null);

onMounted(() => {
    inputCodeBarang.value?.focus();
});
const searchProduk = async () => {
    await handleSearch();

    inputCodeBarang.value?.focus();
};

</script>