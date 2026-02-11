<script setup lang="ts">
import { ref, onMounted } from 'vue';
import api from '../services/api';
import { useAuth } from '../services/auth';

const { state, logout } = useAuth();

interface ShortLink {
  id: number;
  original_url: string;
  short_code: string;
  short_url: string;
  visits_count: number;
  created_at: string;
}

const links = ref<ShortLink[]>([]);
const original_url = ref('');
const loading = ref(false);
const error = ref('');
const success = ref('');

const fetchLinks = async () => {
  loading.value = true;
  try {
    const response = await api.get('/short-links');
    links.value = response.data.data;
  } catch (err: any) {
    error.value = 'Erro ao carregar links.';
  } finally {
    loading.value = false;
  }
};

const handleShorten = async () => {
  if (!original_url.value) return;
  
  loading.value = true;
  error.value = '';
  success.value = '';
  
  try {
    await api.post('/short-links', {
      original_url: original_url.value,
    });
    original_url.value = '';
    success.value = 'Link encurtado com sucesso!';
    await fetchLinks();
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Erro ao encurtar link.';
  } finally {
    loading.value = false;
  }
};

const handleDelete = async (id: number) => {
  if (!confirm('Tem certeza que deseja excluir este link?')) return;
  
  try {
    await api.delete(`/short-links/${id}`);
    await fetchLinks();
  } catch (err) {
    error.value = 'Erro ao excluir link.';
  }
};

const copyToClipboard = (text: string) => {
  navigator.clipboard.writeText(text);
  alert('Copiado para a área de transferência!');
};

onMounted(fetchLinks);
</script>

<template>
  <div class="container">
    <header class="app-header">
      <div class="brand">
        <span style="font-size: 1.5rem;">🔗</span>
        <h1>Meus Links</h1>
      </div>
      <nav class="nav">
        <router-link v-if="state.user?.role === 'admin'" to="/users" class="nav-link">Usuários</router-link>
        <router-link to="/profile" class="nav-link">Perfil</router-link>
        <button @click="logout" class="btn ghost">Sair</button>
      </nav>
    </header>

    <div class="card" style="margin-bottom: 2rem; background: rgba(255,255,255,0.03); border-radius: 12px;">
      <h3 style="margin-bottom: 1rem;">Encurtar novo URL</h3>
      
      <div v-if="state.user && !state.user.email_verified" style="background: rgba(248, 113, 113, 0.1); border-left: 4px solid #f87171; padding: 1rem; margin-bottom: 1.5rem; border-radius: 0 4px 4px 0;">
        <p class="small" style="color: #fca5a5; margin-bottom: 0;">
          <strong>Acesso restrito:</strong> Você precisa verificar seu e-mail para encurtar novos links.
        </p>
      </div>

      <form @submit.prevent="handleShorten" style="display: flex; gap: 0.5rem;">
        <input 
          v-model="original_url" 
          type="url" 
          class="input" 
          placeholder="Cole seu link longo aqui..." 
          required 
          style="flex: 1;"
          :disabled="loading || !!(state.user && !state.user.email_verified)"
        >
        <button type="submit" class="btn primary" :disabled="loading || !!(state.user && !state.user.email_verified)">
          {{ loading ? 'Encurtando...' : 'Encurtar' }}
        </button>
      </form>
      <p v-if="success" style="color: #4ade80; margin-top: 0.5rem; font-size: 0.9rem;">{{ success }}</p>
      <p v-if="error" style="color: #f87171; margin-top: 0.5rem; font-size: 0.9rem;">{{ error }}</p>
    </div>

    <div v-if="loading && links.length === 0" style="text-align: center; padding: 2rem;">
      Carregando seus links...
    </div>

    <div v-else-if="links.length === 0" style="text-align: center; padding: 3rem; background: rgba(255,255,255,0.01); border-radius: 12px;">
      <p class="small">Você ainda não tem links encurtados.</p>
    </div>

    <ul v-else class="links">
      <li v-for="link in links" :key="link.id" class="link-item">
        <div class="link-meta">
          <strong style="color: #646cff; cursor: pointer;" @click="copyToClipboard(link.short_url)">
            {{ link.short_code }}
          </strong>
          <span class="small" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
            {{ link.original_url }}
          </span>
          <span class="small" style="font-size: 0.75rem;">
            {{ new Date(link.created_at).toLocaleDateString() }} • {{ link.visits_count }} cliques
          </span>
        </div>
        <div class="actions">
          <button @click="copyToClipboard(link.short_url)" class="btn ghost small">Copiar</button>
          <button @click="handleDelete(link.id)" class="btn ghost small" style="color: #f87171;">Excluir</button>
        </div>
      </li>
    </ul>
  </div>
</template>

<style>
/* ... existing styles if any in ShortLinks.vue ... */
</style>
