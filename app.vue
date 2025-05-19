<template>
  <div>
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>

<script setup>
useHead({
  link: [
    {
      rel: 'icon',
      type: 'image/x-icon',
      href: '/LogoHead.ico'
    }
  ]
})
</script>