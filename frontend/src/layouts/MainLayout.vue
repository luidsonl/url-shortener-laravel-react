<template>
  <div id="app">
    <div class="container">
      <Header>
        <template #right>
          <UserMenu />
        </template>
      </Header>

      <main>
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import Header from '../components/Header.vue';
import UserMenu from '../components/UserMenu.vue';
</script>

<style scoped>
.nav-link { padding: 0.4rem 0.8rem; border-radius: 8px; color: inherit; text-decoration: none; }
.nav-link.router-link-active { background: rgba(100,108,255,0.12); }
.app-header { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1rem }
</style>
