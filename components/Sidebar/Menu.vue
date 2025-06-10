<script setup>
import { defineNuxtLink } from "nuxt/app";
import Logo from "../ui/Logo";
import {
  UserIcon,
  HomeIcon,
  CodeBracketIcon,
  ArrowDownTrayIcon,
  TvIcon,
  CreditCardIcon,
  CubeIcon,
  UserGroupIcon,
  ArchiveBoxIcon,
  CalendarDaysIcon,
  PaintBrushIcon,
  DocumentChartBarIcon,
  ClipboardDocumentIcon,
  ChartPieIcon,
  ChevronDownIcon,
  ChevronUpIcon,
} from "@heroicons/vue/24/outline";

const items = ref([
  {
    title: "Akun Pembeli",
    path: "#",
    icon: UserIcon,
  },
  {
    title: "Beranda",
    path: "/beranda",
    icon: HomeIcon,
  },
  {
    title: "Code",
    path: "/code",
    icon: CodeBracketIcon,
  },
  {
    title: "Barang Masuk",
    path: "/entry",
    icon: ArrowDownTrayIcon,
  },
  {
    title: "Live",
    path: "/live",
    icon: TvIcon,
  },
  {
    title: "Kasir",
    path: "/kasir",
    icon: CreditCardIcon,
    children: [
      { title: "List Transaksi", path: "/listTransaksi" },
      { title: "Transaksi", path: "/kasir" },
      { title: "Pre-Order", path: "/preorder" },
      { title: "Online Transaksi", path: "/kasir-online" },
    ],
  },
  {
    title: "Packaging",
    path: "/packaging",
    icon: CubeIcon,
  },
  {
    title: "Staff",
    path: "/staff",
    icon: UserGroupIcon,
  },
  {
    title: "Inventory",
    path: "/inventory",
    icon: ArchiveBoxIcon,
  },
  {
    title: "Acara",
    path: "/acara",
    icon: CalendarDaysIcon,
  },
  {
    title: "Pewarna Alam",
    path: "/pewarnaAlam",
    icon: PaintBrushIcon,
  },
  {
    title: "Statistik",
    path: "/statistik",
    icon: ChartPieIcon,
  },
  {
    title: "Database Penjualan",
    path: "/databasePenjualan",
    icon: DocumentChartBarIcon,
  },
  {
    title: "Database Inventory",
    path: "/databaseInventory",
    icon: ClipboardDocumentIcon,
  },
]);
const activeDropdown = ref(null);

function toggleDropdown(index) {
  activeDropdown.value = activeDropdown.value === index ? null : index;
}
</script>

<template>
  <aside class="w-56 min-h-screen">
    <header
      class="flex items-center gap-2 p-4 hover:scale-[101%] transition cursor-pointer"
    >
      <NuxtLink to="/beranda" class="flex items-center gap-2">
        <Logo />
      </NuxtLink>
    </header>

    <div class="grow">
      <div class="grid gap-2 text-left">
        <div v-for="(item, index) in items" :key="index">
          <!-- Jika tidak punya children (biasa) -->
          <NuxtLink
            v-if="!item.children"
            :to="item.path"
            class="flex items-center gap-2 hover:bg-gray-100 p-2 rounded transition"
          >
            <component :is="item.icon" class="w-5 h-5 text-white" />
            <span>{{ item.title }}</span>
          </NuxtLink>

          <!-- Jika punya submenu (dropdown) -->
          <div v-else>
            <div
              @click="toggleDropdown(index)"
              class="flex items-center justify-between hover:bg-gray-100 p-2 rounded transition cursor-pointer"
            >
              <div class="flex items-center gap-2">
                <component :is="item.icon" class="w-5 h-5 text-white" />
                <span>{{ item.title }}</span>
              </div>
              <!-- Dropdown Icon -->
              <component
                :is="activeDropdown === index ? ChevronUpIcon : ChevronDownIcon"
                class="w-4 h-4 text-white"
              />
            </div>
            <div v-if="activeDropdown === index" class="ml-4">
              <NuxtLink
                v-for="(child, idx) in item.children"
                :key="idx"
                :to="child.path"
                class="flex items-center gap-2 hover:bg-gray-100 p-2 rounded transition"
              >
                <span>{{ child.title }}</span>
              </NuxtLink>
            </div>
          </div>
        </div>
      </div>
    </div>
  </aside>
</template>
