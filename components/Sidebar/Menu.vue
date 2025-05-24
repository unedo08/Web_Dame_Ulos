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
  ChartPieIcon
} from "@heroicons/vue/24/outline";

const items = ref([
  {
    title: "Akun Pembeli",
    path: "#",
    icon: UserIcon
  },
  {
    title: "Beranda",
    path: "/beranda",
    icon: HomeIcon
  },
  {
    title: "Code",
    path: "/code",
    icon: CodeBracketIcon
  },
  {
    title: "Barang Masuk",
    path: "/entry",
    icon: ArrowDownTrayIcon
  },
  {
    title: "Live",
    path: "/live",
    icon: TvIcon
  },
  {
    title: "Kasir",
    path: "/kasir",
    icon: CreditCardIcon
  },
  {
    title: "Packaging",
    path: "/packaging",
    icon: CubeIcon
  },
  {
    title: "Staff",
    path: "/staff",
    icon: UserGroupIcon
  },
  {
    title: "Inventory",
    path: "/inventory",
    icon: ArchiveBoxIcon
  },
  {
    title: "Acara",
    path: "/acara",
    icon: CalendarDaysIcon
  },
  {
    title: "Pewarna Alam",
    path: "/pewarnaAlam",
    icon: PaintBrushIcon
  },
  {
    title: "Statistik",
    path: "/statistik",
    icon: ChartPieIcon
  },
  {
    title: "Database Penjualan",
    path: "/databasePenjualan",
    icon: DocumentChartBarIcon
  },
  {
    title: "Database Inventory",
    path: "/databaseInventory",
    icon: ClipboardDocumentIcon
  },
]);
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
        <NuxtLink
          v-for="(item, index) in items"
          :key="index"
          :to="item.path"
          class="flex items-center gap-2 hover:bg-gray-100 p-2 rounded transition"
        >
          <component :is="item.icon" class="w-5 h-5 text-white" />
          <span>{{ item.title }}</span>
        </NuxtLink>
      </div>
    </div>
  </aside>
</template>
