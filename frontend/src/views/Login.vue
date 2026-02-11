<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';

const router = useRouter();
const email = ref('');
const password = ref('');
const error = ref('');
const loading = ref(false);

const handleLogin = async () => {
  loading.value = true;
  error.value = '';
  try {
    const response = await api.post('/auth/login', {
      email: email.value,
      password: password.value,
    });
    localStorage.setItem('token', response.data.access_token);
    router.push('/short-links');
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Falha ao autenticar. Verifique suas credenciais.';
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="container">
    <div style="max-width: 400px; margin: 0 auto; padding: 2rem 0;">
      <h2 style="margin-bottom: 0.5rem;">Boas-vindas de volta</h2>
      <p class="small" style="margin-bottom: 2rem;">Entre na sua conta para gerenciar seus links.</p>

      <form @submit.prevent="handleLogin" class="form">
        <div v-if="error" style="background: rgba(248, 113, 113, 0.1); border-left: 4px solid #f87171; padding: 0.75rem; margin-bottom: 1.5rem; border-radius: 0 4px 4px 0; color: #fca5a5; font-size: 0.875rem;">
          {{ error }}
        </div>

        <div style="display: flex; flex-direction: column; gap: 0.4rem;">
          <label class="small">E-mail</label>
          <input 
            v-model="email" 
            type="email" 
            class="input" 
            placeholder="seu@email.com" 
            required
            :disabled="loading"
          >
        </div>

        <div style="display: flex; flex-direction: column; gap: 0.4rem;">
          <label class="small">Senha</label>
          <input 
            v-model="password" 
            type="password" 
            class="input" 
            placeholder="••••••••" 
            required
            :disabled="loading"
          >
        </div>

        <button type="submit" class="btn primary" style="margin-top: 1rem;" :disabled="loading">
          {{ loading ? 'Entrando...' : 'Entrar' }}
        </button>
      </form>

      <p class="small" style="margin-top: 2rem; text-align: center;">
        Não tem uma conta? 
        <router-link to="/register" style="color: #646cff;">Cadastre-se</router-link>
      </p>
    </div>
  </div>
</template>
