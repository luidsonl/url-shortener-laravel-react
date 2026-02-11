<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useAuth } from './services/auth';

const { state, fetchUser, resendVerification } = useAuth();
const resending = ref(false);
const resendSuccess = ref(false);

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

onMounted(async () => {
  if (localStorage.getItem('token')) {
    await fetchUser();
  }
});
</script>

<template>
  <div v-if="state.user && !state.user.email_verified" class="verification-banner">
    <div class="container banner-content">
      <span>
        ⚠️ Sua conta ainda não foi verificada. Verifique seu e-mail para ter acesso total.
        <template v-if="resendSuccess">
          <span style="margin-left: 0.5rem; font-weight: 600; color: #166534;">✓ Link enviado!</span>
        </template>
      </span>
      <div style="display: flex; gap: 1rem; align-items: center;">
        <button 
          v-if="!resendSuccess"
          @click="handleResend" 
          class="banner-action" 
          :disabled="resending"
        >
          {{ resending ? 'Enviando...' : 'Reenviar link' }}
        </button>
        <router-link to="/profile" class="banner-link">Ver perfil</router-link>
      </div>
    </div>
  </div>
  <router-view />
</template>

<style>
.verification-banner {
  background-color: #fef08a;
  color: #854d0e;
  padding: 0.75rem 0;
  font-size: 0.875rem;
  border-bottom: 1px solid #fde047;
}

.banner-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.banner-action {
  background: none;
  border: none;
  padding: 0;
  color: #854d0e;
  text-decoration: underline;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.875rem;
  font-family: inherit;
}

.banner-action:hover {
  color: #713f12;
}

.banner-action:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.banner-link {
  color: #854d0e;
  text-decoration: underline;
  font-weight: 600;
}

.banner-link:hover {
  color: #713f12;
}

/* Global resets or overrides if needed */
</style>
