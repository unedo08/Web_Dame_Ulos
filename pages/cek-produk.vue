<template>
    <div>
        <title>Cek Produk</title>
        <div class="check-container">
            <div class="check-header">
                <div class="header-icon">
                    <img :src="PencarianProdukIcon" alt="Pencarian Produk" class="header-image" />
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
                        <img :src="Pencarian" class="label-icon" /> Ketik/scan kode barang
                    </label>

                    <div class="relative">
                        <input ref="inputCodeBarang" v-model="codeBarang" type="text"
                            placeholder="Masukkan kode barang..." class="input-search" @keyup.enter="searchProduk" />

                        <button class="search-button" @click="searchProduk">
                            <img :src="ButtonSearchIcon" alt="Pencarian Produk" class="label-icon" />
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
                        <span class="label"><img :src="KodeBarangIcon" class="label-icon" /> KODE BARANG</span>
                        <span class="value">{{ product.code_barang }}</span>
                    </div>

                    <div class="product-row">
                        <span class="label"><img :src="NamaUlosIcon" class="label-icon" /> NAMA ULOS</span>
                        <span class="value">{{ product.barangentry_nama }}</span>
                    </div>

                    <div class="product-row">
                        <span class="label"><img :src="WarnaIcon" class="label-icon" /> WARNA</span>
                        <span class="value">{{ product.barangentry_warna }}</span>
                    </div>

                    <div class="product-row">
                        <span class="label"><img :src="NamaPaniratIcon" class="label-icon" /> NAMA PANIRAT</span>
                        <span class="value">{{ product.barangentry_nama_panirat }}</span>
                    </div>

                    <div class="product-row">
                        <span class="label"><img :src="UkuranMandarIcon" class="label-icon" /> UKURAN MANDAR</span>
                        <span class="value">{{ product.barangentry_ukuran_mandar == 0 ? '-' :
                            product.barangentry_ukuran_mandar + ' cm' }}</span>
                    </div>

                    <div class="product-row">
                        <span class="label"><img :src="UkuranUlosIcon" class="label-icon" /> UKURAN ULOS</span>
                        <span class="value">{{ product.barangentry_ukuran_ulos }} cm</span>
                    </div>

                    <!-- PRICE -->
                    <div class="price-grid">

                        <div class="price-box blue">
                            <div class="price-header">
                                <div class="price-title">
                                    HARGA PRICE TAG
                                </div>
                                <img :src="HargaPriceTagIcon" class="price-icon" />
                            </div>

                            <div class="price-value">
                                {{ formatRupiah(product.barangentry_price_tag) }}
                            </div>
                        </div>

                        <div class="price-box gray">
                            <div class="price-header">

                                <div class="price-title">
                                    HARGA MODAL
                                </div>
                                <img :src="HargaModalIcon" class="price-icon" />
                            </div>

                            <div class="price-value">
                                {{ formatRupiah(product.barangentry_modal) }}
                            </div>
                        </div>

                        <div class="price-box green">
                            <div class="price-header">
                                <div class="price-title">

                                    HARGA NET
                                </div>
                                <img :src="HargaNetIcon" class="price-icon" />
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
import PencarianProdukIcon from "@/assets/image/Pencarian Produk.png";
import Pencarian from "@/assets/image/Pencarian.png";
import KodeBarangIcon from "@/assets/image/Kode Barang.png";
import NamaUlosIcon from "@/assets/image/Nama Ulos.png";
import WarnaIcon from "@/assets/image/Warna.png";
import NamaPaniratIcon from "@/assets/image/Nama Panirat.png";
import UkuranMandarIcon from "@/assets/image/Ukuran Mandar.png";
import UkuranUlosIcon from "@/assets/image/Ukuran Ulos.png";
import HargaPriceTagIcon from "@/assets/image/Harga Price Tag.png";
import HargaModalIcon from "@/assets/image/Harga Modal.png";
import HargaNetIcon from "@/assets/image/Harga Net.png";
import ButtonSearchIcon from "@/assets/image/Button Search.png";

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