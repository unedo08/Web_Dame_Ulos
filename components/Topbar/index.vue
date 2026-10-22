<template>
  <header class="topbar">
    <div class="topbar-content">
      <div class="text-right">
        <div class="user-info" @click="toggleDropdown">
          <img src="@/assets/image/avatar-account.png" alt="avatar" class="avatar" />
          <span class="username">
            {{ name }}
          </span>
          <img src="@/assets/image/arrow_drop_down.png" alt="arrow_dropdown" class="arrow-dropdown" />
        </div>

        <ul v-if="dropdownVisible" class="dropdown-menu">
          <li>
            <a class="dropdown-item" href="#" @click.prevent="onMenuItemClick('Logout')">
              Logout
            </a>
          </li>
        </ul>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

const router = useRouter();
const dropdownVisible = ref(false);
const url = ref("");
const name = ref("");

onMounted(async () => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;

  name.value =
    localStorage.getItem("name") ||
    sessionStorage.getItem("name") ||
    "User";

});

const toggleDropdown = () => {

  dropdownVisible.value =
    !dropdownVisible.value;

};

const onMenuItemClick = async (item) => {
  dropdownVisible.value = false;

  if (item !== "Logout") {
    return;
  }
  try {
    const token =
      localStorage.getItem("auth_token") ||
      sessionStorage.getItem("auth_token");

    await axios.post(
      `${url.value}/api/logout`,
      {},
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      }
    );

  } catch (error) {
    console.error(
      "Error logout",
      error
    );
  } finally {
    sessionStorage.removeItem(
      "auth_token"
    );
    sessionStorage.clear();
    localStorage.removeItem(
      "auth_token"
    );
    localStorage.clear();
    await router.push("/");
  }
};
</script>

<style scoped>
.topbar {
  position: fixed;
  top: 0;
  left: 16rem;
  right: 0;
  height: 60px;
  z-index: 1000;
  background-color: #fff;
  border-bottom:
    2px solid #cfcfcf;
}


.topbar-content {
  height: 100%;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  padding:
    0 16px;
}


.text-right {
  position: relative;
  display: flex;
  align-items: center;
  text-align: right;
}

.user-info {
  display: flex;
  align-items: center;
  cursor: pointer;
  user-select: none;
}

.username {
  font-size: 14px;
}

.avatar {
  width: 24px;
  height: 24px;
  margin-right: 10px;
}


.arrow-dropdown {
  width: 16px;
  height: 16px;
  margin-left: 5px;
}

/* =========================================
   DROPDOWN
========================================= */

.dropdown-menu {
  position: absolute;
  right: 0;
  top: calc(100% + 10px);
  z-index: 1000;
  margin: 0;
  padding: 4px 8px;
  width: 112px;
  box-sizing: border-box;
  background-color: #fff;
  border: 1px solid #f1f1f1;
  border-radius: 8px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
  text-align: left;
  list-style: none;
}

.dropdown-menu::before {
  content: "";
  position: absolute;
  top: -7px;
  right: 16px;
  width: 12px;
  height: 12px;
  background: #fff;
  border-top: 1px solid #f1f1f1;
  border-left: 1px solid #f1f1f1;
  transform: rotate(45deg);
}

.dropdown-menu li {
  position: relative;
}

.dropdown-item {
  display: block;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  line-height: 1.4;
  color: inherit;
  text-decoration: none;
}

.dropdown-item:hover {
  background-color: #f5f5f5;
}


/* =========================================
   MOBILE
========================================= */

@media (max-width: 767px) {
  .topbar {
    left: 0;
    width: 100%;
    height: 60px;
  }

  .topbar-content {
    padding-left: 76px;
    padding-right: 16px;
  }

  .dropdown-menu {
    width: min(112px, calc(100vw - 32px));
  }

}
</style>