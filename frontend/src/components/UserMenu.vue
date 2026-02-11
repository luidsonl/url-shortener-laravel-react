<script setup lang="ts">
import { useRouter } from 'vue-router';

const router = useRouter();

const handleLogout = () => {
  localStorage.removeItem('token');
  router.push('/login');
};
</script>

<template>
  <div class="user-menu">
    <nav class="nav">
      <router-link to="/profile" class="nav-link">Perfil</router-link>
      <button @click="handleLogout" class="btn ghost">Sair</button>
    </nav>
  </div>
</template>

<style scoped>
.user-menu {
  display: flex;
  align-items: center;
}
</style>
