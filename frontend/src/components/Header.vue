<template>
  <header class="app-header">
    <router-link to="/" class="brand">
      <span style="font-size: 1.5rem;">🔗</span>
      <h1 style="font-size: 1.5rem; margin: 0;">Meus Links</h1>
    </router-link>
    <div class="header-right">
      <slot name="right" />
    </div>
  </header>
</template>

<style scoped>
.header-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}
</style>
