<script setup lang="ts">
import { ref, onMounted } from 'vue';
import api from '../services/api';
import { useAuth } from '../services/auth';

const { state, logout, resendVerification } = useAuth();
const user = ref<any>(null);
const loading = ref(false);
const resending = ref(false);
const resendSuccess = ref(false);
const error = ref('');

const fetchProfile = async () => {
  loading.value = true;
  try {
    const response = await api.get('/auth/user');
    user.value = response.data.user || response.data;
  } catch (err) {
    error.value = 'Erro ao carregar perfil.';
  } finally {
    loading.value = false;
  }
};

const handleResend = async () => {
  resending.value = true;
  resendSuccess.value = false;
  try {
    await resendVerification();
    resendSuccess.value = true;
  } catch (err) {
    alert('Erro ao reenviar e-mail de verificação.');
  } finally {
    resending.value = false;
  }
};

onMounted(fetchProfile);
</script>

<template>
  <div class="container">
    <header class="app-header">
      <div class="brand">
        <span style="font-size: 1.5rem;">👤</span>
        <h1>Perfil</h1>
      </div>
      <nav class="nav">
        <router-link v-if="state.user?.role === 'admin'" to="/users" class="nav-link">Usuários</router-link>
        <router-link to="/short-links" class="nav-link">Meus Links</router-link>
        <button @click="logout" class="btn ghost">Sair</button>
      </nav>
    </header>

    <div v-if="loading" style="text-align: center; padding: 2rem;">
      Carregando...
    </div>

    <div v-else-if="user" class="card" style="background: rgba(255,255,255,0.03); border-radius: 12px;">
      <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
        <div class="avatar" style="width: 64px; height: 64px; font-size: 1.5rem;">
          {{ user.name.charAt(0).toUpperCase() }}
        </div>
        <div>
          <h2 style="margin: 0;">{{ user.name }}</h2>
          <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem;">
            <p class="small" style="margin: 0;">{{ user.email }}</p>
            <span v-if="user.email_verified" style="background: rgba(74, 222, 128, 0.2); color: #4ade80; padding: 2px 8px; border-radius: 99px; font-size: 0.7rem; font-weight: 600;">Verificado</span>
            <template v-else>
              <span style="background: rgba(248, 113, 113, 0.2); color: #f87171; padding: 2px 8px; border-radius: 99px; font-size: 0.7rem; font-weight: 600;">Não verificado</span>
              <button 
                v-if="!resendSuccess"
                @click="handleResend" 
                class="btn ghost small" 
                style="font-size: 0.7rem; padding: 2px 8px;"
                :disabled="resending"
              >
                {{ resending ? 'Enviando...' : 'Reenviar link' }}
              </button>
              <span v-else style="color: #4ade80; font-size: 0.7rem; font-weight: 600;">✓ E-mail enviado!</span>
            </template>
          </div>
        </div>
      </div>

      <div style="display: flex; flex-direction: column; gap: 1rem;">
        <div>
          <label class="small">Nome</label>
          <div class="input" style="background: rgba(255,255,255,0.05);">{{ user.name }}</div>
        </div>
        <div>
          <label class="small">E-mail</label>
          <div class="input" style="background: rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center;">
            {{ user.email }}
            <span v-if="user.email_verified" title="E-mail verificado" style="color: #4ade80;">✓</span>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="error" style="color: #f87171; text-align: center; padding: 2rem;">
      {{ error }}
    </div>
  </div>
</template>
