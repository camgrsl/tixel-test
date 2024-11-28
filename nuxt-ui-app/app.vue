<template>
  <UContainer>
    <UCard class="mt-10">
      <template #header>
        <div class="flex justify-between">
          <h1>Pizza Order Management</h1>
        </div>
      </template>

      <template #default>
        <NuxtLayout>
          <NuxtPage />
        </NuxtLayout>
      </template>

      <template #footer>
        <div class="flex justify-between">
          <h1>Tixel Tech Test</h1>
        </div>
      </template>

    </UCard>
  </UContainer>
  <UNotifications />
</template>
<script setup lang="ts">
</script>