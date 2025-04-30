<template>
    <div class="topbar">
        <div class="topbar-content">
            <div class="text-right">
                <img src="@/assets/image/avatar.png" alt="avatar" class="avatar">
                <span class="username">Nama User</span>
                <button class="btn btn-link dropdown-toggle" type="button" @click="toggleDropdown"
                    aria-expanded="dropdownVisible.toString()" style="border: none; background: none; padding: 0;">
                    <img src="@/assets/image/arrow_drop_down.png" alt="arrow_dropdown" class="arrow_dropdown">
                </button>
                <ul v-if="dropdownVisible" class="dropdown-menu dropdown-menu-end"
                    style="position: absolute; right: 0; top: 100%; margin-top: 5px; z-index: 1000;">
                    <li><a class="dropdown-item" href="#" @click="onMenuItemClick('Profile')">Profile</a></li>
                    <li><a class="dropdown-item" href="#" @click="onMenuItemClick('Settings')">Settings</a></li>
                    <li><a class="dropdown-item" href="#" @click="onMenuItemClick('Logout')">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
const dropdownVisible = ref(false);

const toggleDropdown = () => {
    dropdownVisible.value = !dropdownVisible.value;
};

// Function to handle item click
const onMenuItemClick = (item) => {
    dropdownVisible.value = false; 
};
</script>


<style scoped>
.topbar {
    background-color: #fff;
    color: #424242;
    padding: 15px;
    border-bottom: 2px solid #CFCFCF;
}

.topbar-content {
    display: flex;
    justify-content: flex-end;
    max-width: 1200px;
    margin: 0 auto;
}

.text-right {
    display: flex;
    text-align: right;
    align-items: center;
    position: relative;
}

.username {
    font-size: 16px;
}

.avatar {
    width: 24px;
    height: 24px;
    margin-right: 10px;
}

.dropdown-menu {
    background-color: #fff;
    border: 1px solid #CFCFCF;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 10px 15px;
    min-width: 140px;
    text-align: center;
}

.dropdown-item{
    font-size: 16px;
}
.dropdown-item:hover {
    background-color: #f0f0f0;
}
</style>