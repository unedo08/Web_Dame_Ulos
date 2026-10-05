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
  ArchiveBoxIcon,
  CalendarDaysIcon,
  DocumentChartBarIcon,
  Cog6ToothIcon,
  ChevronDownIcon,
  ChevronUpIcon
} from "@heroicons/vue/24/outline";

const isMobileSidebarOpen = ref(false);

const toggleMobileSidebar = () => {
  isMobileSidebarOpen.value =
    !isMobileSidebarOpen.value;
};

const closeMobileSidebar = () => {
  isMobileSidebarOpen.value = false;
};


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
  const savedRole =
    localStorage.getItem("role") ||
    sessionStorage.getItem("role");

  role.value =
    roleMap[savedRole] || null;
});

const items = ref([
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
    title: "Barang Keluar",
    path: "/barang-keluar",
    icon: ArchiveBoxIcon
  },
  {
    title: "Cek Produk",
    path: "/cek-produk",
    icon: DocumentChartBarIcon
  },
  {
    title: "Kasir",
    path: "/kasir",
    icon: CreditCardIcon
  },
  {
    title: "Live",
    path: "/live",
    icon: TvIcon
  },
  {
    title: "Packaging",
    path: "/packaging-page",
    icon: CubeIcon
  },
  {
    title: "Acara",
    path: "/acara",
    icon: CalendarDaysIcon
  },
  {
    title: "Database Penjualan",
    path: "/databasePenjualan",
    icon: DocumentChartBarIcon
  },
  {
    title: "Keuangan",
    path: "/keuangan",
    icon: CreditCardIcon
  },
  {
    title: "Benang",
    path: "/pewarnaAlam",
    icon: UserIcon
  },
  {
    title: "Staff",
    path: "/staff",
    icon: UserIcon
  },
  {
    title: "Akun Pembeli",
    path: "/customer",
    icon: UserIcon
  },

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
    activeDropdown.value === index
      ? null
      : index;
};

const roleAccess = {
  "super-admin": "all",

  admin: [
    "Akun Pembeli",
    "Beranda",
    "Code",
    "Barang Masuk",
    "Live",
    "Kasir",
    "Acara",
    "Database Penjualan",
    "Cek Produk",
    "Keuangan",
    "Barang Keluar",
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
    "Cek Produk",
  ],

  "quality-control": [
    "Code",
    "Barang Masuk",
    "Kasir",
    "Database Penjualan",
    "Barang Keluar",
  ],

  packaging: [
    "Akun Pembeli",
    "Live",
    "Kasir",
    "Packaging",
    "Database Penjualan",
  ],

  "pewarna-alam": [
    "Benang",
    "Beranda"
  ],

  "sosial-media": [
    "Kasir",
    "Acara"
  ],
};

const filteredMenu = computed(() => {

  if (!role.value) {
    return [];
  }

  if (roleAccess[role.value] === "all") {
    return items.value;
  }

  const access =
    roleAccess[role.value] || [];

  return items.value
    .map((item) => {
      if (!item.children) {

        return access.includes(item.title)
          ? item
          : null;
      }
      const filteredChildren =
        item.children.filter((child) =>
          access.includes(child.title)
        );

      if (filteredChildren.length > 0) {
        return {
          ...item,
          children: filteredChildren
        };

      }
      return null;
    })
    .filter(Boolean);
});
</script>

<template>
  <button
    class="mobile-menu-button"
    @click="toggleMobileSidebar"
    aria-label="Toggle menu"
  >
    <svg
      xmlns="http://www.w3.org/2000/svg"
      class="hamburger-icon"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
      stroke-width="2"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        d="M4 6h16M4 12h16M4 18h16"
      />
    </svg>
  </button>

  <div
    v-if="isMobileSidebarOpen"
    class="mobile-overlay"
    @click="closeMobileSidebar"
  ></div>

  <aside
    class="sidebar"
    :class="{
      'sidebar-open': isMobileSidebarOpen
    }"
  >
    <header class="sidebar-header">

      <NuxtLink
        to="/beranda"
        class="logo-link"
        @click="closeMobileSidebar"
      >
        <Logo />
      </NuxtLink>

      <button
        class="mobile-close-button"
        @click="closeMobileSidebar"
        aria-label="Close menu"
      >
      </button>

    </header>

    <div class="sidebar-menu custom-scrollbar">

      <div class="menu-list">

        <div
          v-for="(item, index) in filteredMenu"
          :key="index"
          class="menu-item-wrapper"
        >

          <NuxtLink
            v-if="!item.children"
            :to="item.path"
            class="menu-link"
            @click="closeMobileSidebar"
          >
            <component
              :is="item.icon"
              class="menu-icon"
            />

            <span class="menu-text">
              {{ item.title }}
            </span>
          </NuxtLink>

          <div v-else>
            <button
              class="menu-link dropdown-button"
              @click="toggleDropdown(index)"
            >
              <div class="menu-left">
                <component
                  :is="item.icon"
                  class="menu-icon"
                />
                <span class="menu-text">
                  {{ item.title }}
                </span>
              </div>

              <component
                :is="
                  activeDropdown === index
                    ? ChevronUpIcon
                    : ChevronDownIcon
                "
                class="dropdown-icon"
              />

            </button>

            <div
              v-if="activeDropdown === index"
              class="children-menu"
            >

              <NuxtLink
                v-for="(
                  child,
                  childIndex
                ) in item.children"
                :key="childIndex"
                :to="child.path"
                class="child-link"
                @click="closeMobileSidebar"
              >

                <span>
                  {{ child.title }}
                </span>

              </NuxtLink>
            </div>
          </div>
        </div>
      </div>
    </div>
  </aside>
</template>

<style scoped>

/* =========================================
   SIDEBAR
========================================= */

.sidebar {
  position: fixed;

  top: 0;
  left: 0;

  width: 256px;
  height: 100vh;

  z-index: 1100;

  display: flex;
  flex-direction: column;

  background: #520000;
  color: white;

  overflow: hidden;

  transform: translateX(0);

  transition:
    transform 0.3s ease-in-out;
}


/* =========================================
   HEADER
========================================= */

.sidebar-header {
  flex-shrink: 0;

  display: flex;
  align-items: center;
  justify-content: space-between;

  gap: 8px;

  padding: 16px;
}


.logo-link {
  display: flex;
  align-items: center;

  min-width: 0;

  text-decoration: none;
}


/* =========================================
   MENU
========================================= */

.sidebar-menu {
  flex: 1;

  min-height: 0;

  overflow-y: auto;
  overflow-x: hidden;
}


.menu-list {
  display: grid;

  gap: 4px;

  padding: 0 8px 16px;
}


.menu-link {
  width: 100%;

  display: flex;
  align-items: center;

  gap: 8px;

  padding: 8px;

  border-radius: 4px;

  color: white;

  text-decoration: none;

  background: transparent;

  border: none;

  cursor: pointer;

  font-family: inherit;

  font-size: inherit;

  text-align: left;

  transition:
    background-color 0.2s ease;
}


.menu-link:hover {
  background: #6b2020;
}


.menu-icon {
  width: 20px;
  height: 20px;

  flex-shrink: 0;

  color: white;
}


.menu-text {
  min-width: 0;

  overflow: hidden;

  text-overflow: ellipsis;

  white-space: nowrap;
}


/* =========================================
   DROPDOWN
========================================= */

.dropdown-button {
  justify-content: space-between;
}


.menu-left {
  display: flex;
  align-items: center;

  gap: 8px;

  min-width: 0;
}


.dropdown-icon {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
  color: white;
}

.children-menu {
  margin-left: 20px;
  display: grid;
  gap: 2px;
  padding-top: 2px;
}

.child-link {
  display: flex;
  align-items: center;
  padding: 7px 8px;
  border-radius: 4px;
  color: white;
  text-decoration: none;
  font-size: 14px;
  transition:
    background-color 0.2s ease;
}

.child-link:hover {
  background: #6b2020;
}

.mobile-menu-button {
  display: none;
  position: fixed;
  top: 16px;
  left: 16px;
  z-index: 1200;
  width: 48px;
  height: 48px;
  align-items: center;
  justify-content: center;
  border: none;
  border-radius: 10px;
  background: #520000;
  color: white;
  cursor: pointer;
  box-shadow:
    0 4px 12px rgba(0, 0, 0, 0.2);
}


.hamburger-icon {
  width: 28px;
  height: 28px;
}

.mobile-close-button {
  display: none;
  border: none;
  background: transparent;
  color: white;
  font-size: 28px;
  line-height: 1;
  cursor: pointer;
}

.mobile-overlay {
  display: none;
  position: fixed;
  inset: 0;
  z-index: 1050;
  background: rgba(0, 0, 0, 0.5);
}

.custom-scrollbar {
  overflow-y: auto;
  overflow-x: hidden;
}

/* Chrome / Edge / Safari */
.custom-scrollbar::-webkit-scrollbar {
  width: 2px !important;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent !important;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #999 !important;
  border-radius: 999px !important;
}

.custom-scrollbar::-webkit-scrollbar-button {
  display: none !important;
  width: 0 !important;
  height: 0 !important;
}

.custom-scrollbar {
  scrollbar-width: thin;

  scrollbar-color:
    #999
    transparent;
}

@media (max-width: 767px) {

  .sidebar {
    width: 280px;
    transform: translateX(-100%);
  }

  .sidebar.sidebar-open {
    transform: translateX(0);
  }

  .mobile-menu-button {
    display: flex;
  }

  .mobile-close-button {
    display: block;
  }

  .mobile-overlay {
    display: block;
  }

}

@media (min-width: 768px) {
  .sidebar {
    width: 16rem;
    transform: translateX(0) !important;
    transition: none;
  }
}
</style>