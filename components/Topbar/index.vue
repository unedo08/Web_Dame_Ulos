<template>
  <div class="topbar">
    <div class="topbar-content">
      <div class="text-right">
        <div class="user-info" @click="toggleDropdown">
          <img src="@/assets/image/avatar-account.png" alt="avatar" class="avatar" />
          <span class="username">{{ name}}</span>
          <img
            src="@/assets/image/arrow_drop_down.png"
            alt="arrow_dropdown"
            class="arrow_dropdown"
          />
        </div>

        <ul
          v-if="dropdownVisible"
          class="dropdown-menu dropdown-menu-end"
          style="
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 5px;
            z-index: 1000;
          "
        >
          <li>
            <a class="dropdown-item" href="#" @click="onMenuItemClick('Logout')"
              >Logout</a
            >
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<style>
.user-info {
  display: flex;
  align-items: center;
  cursor: pointer;
}

.arrow_dropdown {
  width: 16px;
  height: 16px;
  margin-left: 5px;
}
</style>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useRuntimeConfig } from "#imports";
import axios from "axios";

const router = useRouter();
const dropdownVisible = ref(false);
const url = ref("");
const name = ref("");

onMounted(async () => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;
  name.value = sessionStorage.getItem("name") || "User";
});

const toggleDropdown = () => {
  dropdownVisible.value = !dropdownVisible.value;
};

const onMenuItemClick = async (item) => {
  dropdownVisible.value = false;
  if (item === "Logout") {
    try {
      const token = sessionStorage.getItem("auth_token")
      await axios.post(
        `${url.value}/api/logout`,{},
        {
          headers: {
            "Authorization": `Bearer ${token}`,
          },
        }
      );
      await router.push("/");
      sessionStorage.removeItem("auth_token");
      sessionStorage.clear();
    } catch (error) {
      console.error("Error logout", error);
    }
  }
};
</script>

<style scoped>
.topbar {
  background-color: #fff;
  border-bottom: 2px solid #cfcfcf;
  width: 100%;
}

.topbar-content {
  display: flex;
  justify-content: flex-end;
  padding: 12px 16px;
}

.text-right {
  display: flex;
  text-align: right;
  align-items: center;
  position: relative;
}

.username {
  font-size: 14px;
}

.avatar {
  width: 24px;
  height: 24px;
  margin-right: 10px;
}

.dropdown-menu {
  background-color: #fff;
  border: 1px solid #cfcfcf;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  padding: 10px 15px;
  min-width: 140px;
  text-align: center;
}

.dropdown-item {
  font-size: 16px;
}
.dropdown-item:hover {
  background-color: #f0f0f0;
}
</style>
