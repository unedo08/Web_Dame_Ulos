<script setup>
import { ref, computed, onMounted } from "vue";
import Logo from "../../components/Logo";
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
  Cog6ToothIcon,
  ChevronDownIcon,
  ChevronUpIcon
} from "@heroicons/vue/24/outline";

const role = ref(null);

const roleMap = {
  "1": "super-admin",
  "2": "admin",
  "3": "marketing",
  "4": "quality-control",
  "5": "packaging",
  "6": "pewarna-alam",
  "7": "sosial-media",
};

onMounted(() => {
  role.value = roleMap[localStorage.getItem("role") || sessionStorage.getItem("role")] || null;
});

const items = ref([
  { title: "Beranda", path: "/beranda", icon: HomeIcon },
  { title: "Akun Pembeli", path: "/customer", icon: UserIcon },
  { title: "Staff", path: "/staff", icon: UserIcon },
  { title: "Code", path: "/code", icon: CodeBracketIcon },
  { title: "Barang Masuk", path: "/entry", icon: ArrowDownTrayIcon },
  { title: "Live", path: "/live", icon: TvIcon },
  { title: "Kasir", path: "/kasir", icon: CreditCardIcon },
  { title: "Packaging", path: "/packaging-page", icon: CubeIcon },
  // { title: "Inventory", path: "/inventory", icon: ArchiveBoxIcon },
  { title: "Acara", path: "/acara", icon: CalendarDaysIcon },
  // { title: "Pewarna Alam", path: "/pewarnaAlam", icon: PaintBrushIcon },
  // { title: "Statistik", path: "/statistik", icon: ChartPieIcon },
  { title: "Database Penjualan", path: "/databasePenjualan", icon: DocumentChartBarIcon },
  { title: "Cek Produk", path: "/cek-produk", icon: DocumentChartBarIcon },
  { title: "Keuangan", path: "/keuangan", icon: CreditCardIcon },
  { title: "Barang Keluar", path: "/barang-keluar", icon: ArchiveBoxIcon },
  // { title: "Database Inventory", path: "/databaseInventory", icon: ClipboardDocumentIcon },
  {
    title: "Settings",
    icon: Cog6ToothIcon,
    children: [
      {
        title: "Metode Pembayaran",
        path: "/setting/metode-pembayaran",
      },
      {
        title: "Jenis Pengiriman",
        path: "/setting/jenis-pengiriman",
      },
      {
        title: "Jenis Pengeluaran",
        path: "/setting/pengeluaran",
      },
      {
        title: "Divisi",
        path: "/setting/divisi",
      },
      {
        title: "Sumber Dana",
        path: "/setting/sumber-dana",
      },
      {
        title: "Platform",
        path: "/setting/platform",
      },
      {
        title: "Benang",
        path: "/setting/jenis-benang",
      },
    ],
  },
]);

const activeDropdown = ref(null);

const toggleDropdown = (index) => {
  activeDropdown.value =
    activeDropdown.value === index ? null : index;
};

const roleAccess = {
  "super-admin": "all",
  admin: ["Akun Pembeli", "Beranda", "Code", "Barang Masuk", "Live", "Kasir", "Inventory", "Acara", "Database Penjualan", "Cek Produk","Database Inventory", "Keuangan", "Barang Keluar"],
  marketing: ["Akun Pembeli", "Beranda", "Barang Masuk", "Live", "Kasir", "Packaging", "Acara", "Database Penjualan", "Database Inventory"],
  "quality-control": ["Code", "Barang Masuk", "Kasir", "Inventory", "Database Penjualan", "Database Inventory"],
  packaging: ["Akun Pembeli", "Live", "Kasir", "Packaging", "Inventory", "Database Penjualan", "Database Inventory"],
  "pewarna-alam": ["Pewarna Alam", "Beranda"],
  "sosial-media": ["Kasir", "Acara"],
};

const filteredMenu = computed(() => {
  if (!role.value) return [];

  if (roleAccess[role.value] === "all") return items.value;

  return items.value
    .map(item => {
      if (!item.children) {
        return roleAccess[role.value]?.includes(item.title) ? item : null;
      }
      const filteredChildren = item.children.filter(child =>
        roleAccess[role.value]?.includes(item.title)
      );

      if (filteredChildren.length > 0) {
        return { ...item, children: filteredChildren };
      }

      return null;
    })
    .filter(Boolean);
});
</script>

<template>
  <aside class="w-56 h-full flex flex-col text-white">
    <header class="flex-shrink-0 flex items-center gap-2 p-4 hover:scale-[101%] transition cursor-pointer">
      <NuxtLink to="/beranda" class="flex items-center gap-2">
        <Logo />
      </NuxtLink>
    </header>

    <div class="flex-1 overflow-y-auto">
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