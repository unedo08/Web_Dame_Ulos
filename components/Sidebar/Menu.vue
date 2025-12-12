<script setup>
import { defineNuxtLink } from "nuxt/app";
import Logo from "../../components/ui/Logo";
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

const role = sessionStorage.getItem("role")?.toLowerCase();
const items = ref([
  {
    title: "Akun Pembeli",
    path: "/customer",
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
  },
  {
    title: "Packaging",
    path: "/packaging-page",
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

const roleAccess = {
  "super-admin": "all",

  admin: [
    "Akun Pembeli",
    "Beranda",
    "Code",
    "Barang Masuk",
    "Live",
    "Kasir",
    "Inventory",
    "Acara",
    "Database Penjualan",
    "Database Inventory",
  ],

  marketing: [
    "Akun Pembeli",
    "Beranda",
    "Barang Masuk",
    "Live",
    "Kasir",
    "Packaging",
    "Acara",
    "Database Penjualan",
    "Database Inventory",
  ],

  "quality-control": [
    "Code",
    "Barang Masuk",
    "Kasir",
    "Inventory",
    "Database Penjualan",
    "Database Inventory",
  ],

  packaging: [
    "Akun Pembeli",
    "Live",
    "Kasir",
    "Packaging",
    "Inventory",
    "Database Penjualan",
    "Database Inventory",
  ],

  "pewarna-alam": ["Pewarna Alam", "Beranda"],

  "sosial-media": ["Kasir", "Acara"],
};
const filteredMenu = computed(() => {
  if (roleAccess[role] === "all") return items.value;

  return items.value.filter(item =>
    roleAccess[role]?.includes(item.title)
  );
});

const activeDropdown = ref(null);

function toggleDropdown(index) {
  activeDropdown.value = activeDropdown.value === index ? null : index;
}
</script>

<template>
  <aside class="w-56 min-h-screen text-white overflow-y-auto">
    <header class="flex items-center gap-2 p-4 hover:scale-[101%] transition cursor-pointer">
      <NuxtLink to="/beranda" class="flex items-center gap-2">
        <Logo />
      </NuxtLink>
    </header>

    <div class="grow">
      <div class="grid gap-2 text-left">
        <div v-for="(item, index) in filteredMenu" :key="index">
          <NuxtLink v-if="!item.children" :to="item.path"
            class="flex items-center gap-2 hover:bg-gray-500 p-2 rounded transition">
            <component :is="item.icon" class="w-5 h-5 text-white" />
            <span class="truncate">{{ item.title }}</span>
          </NuxtLink>

          <div v-else>
            <div @click="toggleDropdown(index)"
              class="flex items-center justify-between hover:bg-gray-500 p-2 rounded transition cursor-pointer">
              <div class="flex items-center gap-2">
                <component :is="item.icon" class="w-5 h-5 text-white" />
                <span class="truncate">{{ item.title }}</span>
              </div>
              <component :is="activeDropdown === index ? ChevronUpIcon : ChevronDownIcon" class="w-4 h-4 text-white" />
            </div>
            <div v-if="activeDropdown === index" class="ml-4">
              <NuxtLink v-for="(child, idx) in item.children" :key="idx" :to="child.path"
                class="flex items-center gap-2 hover:bg-gray-500 p-2 rounded transition">
                <span class="truncate">{{ child.title }}</span>
              </NuxtLink>
            </div>
          </div>
        </div>
      </div>
    </div>
  </aside>
</template>
