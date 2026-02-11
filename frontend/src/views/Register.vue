<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';

const router = useRouter();
const name = ref('');
const email = ref('');
const password = ref('');
const password_confirmation = ref('');
const error = ref('');
const loading = ref(false);

const handleRegister = async () => {
  if (password.value !== password_confirmation.value) {
    error.value = 'As senhas não coincidem.';
    return;
  }

  loading.value = true;
  error.value = '';
  try {
    await api.post('/auth/register', {
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: password_confirmation.value,
    });
    // Redirect to login after successful registration
    router.push('/login');
  } catch (err: any) {
    if (err.response?.data?.errors) {
      error.value = Object.values(err.response.data.errors).flat().join(' ');
    } else {
      error.value = err.response?.data?.message || 'Erro ao criar conta. Tente novamente.';
    }
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="container">
    <div style="max-width: 400px; margin: 0 auto; padding: 2rem 0;">
      <h2 style="margin-bottom: 0.5rem;">Crie sua conta</h2>
      <p class="small" style="margin-bottom: 2rem;">Comece a encurtar seus links agora mesmo.</p>

      <form @submit.prevent="handleRegister" class="form">
        <div v-if="error" style="color: #ff4d4d; margin-bottom: 1rem; font-size: 0.9rem;">
          {{ error }}
        </div>

        <div style="display: flex; flex-direction: column; gap: 0.4rem;">
          <label class="small">Nome</label>
          <input 
            v-model="name" 
            type="text" 
            class="input" 
            placeholder="Seu nome" 
            required
            :disabled="loading"
          >
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
            placeholder="Mínimo 8 caracteres" 
            required
            :disabled="loading"
          >
        </div>

        <div style="display: flex; flex-direction: column; gap: 0.4rem;">
          <label class="small">Confirme a Senha</label>
          <input 
            v-model="password_confirmation" 
            type="password" 
            class="input" 
            placeholder="Repita sua senha" 
            required
            :disabled="loading"
          >
        </div>

        <button type="submit" class="btn primary" style="margin-top: 1rem;" :disabled="loading">
          {{ loading ? 'Criando conta...' : 'Cadastrar' }}
        </button>
      </form>

      <p class="small" style="margin-top: 2rem; text-align: center;">
        Já tem uma conta? 
        <router-link to="/login" style="color: #646cff;">Entre aqui</router-link>
      </p>
    </div>
  </div>
</template>
