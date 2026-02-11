<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue';
import { userService, type User } from '../services/user';
import { useAuth } from '../services/auth';

const { state, logout } = useAuth();
const users = ref<User[]>([]);
const loading = ref(false);
const error = ref('');
const currentPage = ref(1);
const totalPages = ref(1);

// Edit Modal State
const showEditModal = ref(false);
const editingUser = reactive({
  id: 0,
  name: '',
  email: '',
  role: ''
});
const saving = ref(false);

const fetchUsers = async (page = 1) => {
  loading.value = true;
  error.value = '';
  try {
    const response = await userService.listUsers(page);
    users.value = response.data;
    currentPage.value = response.meta.current_page;
    totalPages.value = response.meta.last_page;
  } catch (err: any) {
    error.value = 'Erro ao carregar usuários. Verifique se você tem permissão.';
  } finally {
    loading.value = false;
  }
};

const openEditModal = (user: User) => {
  editingUser.id = user.id;
  editingUser.name = user.name;
  editingUser.email = user.email;
  editingUser.role = user.role;
  showEditModal.value = true;
};

const closeEditModal = () => {
  showEditModal.value = false;
};

const handleUpdate = async () => {
  saving.value = true;
  try {
    await userService.updateUser(editingUser.id, {
      name: editingUser.name,
      email: editingUser.email,
      role: editingUser.role
    });
    showEditModal.value = false;
    await fetchUsers(currentPage.value);
  } catch (err: any) {
    alert(err.response?.data?.message || 'Erro ao atualizar usuário.');
  } finally {
    saving.value = false;
  }
};

const handleDelete = async (id: number) => {
  if (id === state.user?.id) {
    alert('Você não pode excluir sua própria conta por aqui.');
    return;
  }
  
  if (!confirm('Tem certeza que deseja excluir este usuário?')) return;
  
  try {
    await userService.deleteUser(id);
    await fetchUsers(currentPage.value);
  } catch (err: any) {
    alert('Erro ao excluir usuário.');
  }
};

onMounted(() => fetchUsers());
</script>

<template>
  <div class="container">
    <header class="app-header">
      <div class="brand">
        <span style="font-size: 1.5rem;">👥</span>
        <h1>Gerenciar Usuários</h1>
      </div>
      <nav class="nav">
        <router-link to="/short-links" class="nav-link">Meus Links</router-link>
        <router-link to="/profile" class="nav-link">Perfil</router-link>
        <button @click="logout" class="btn ghost">Sair</button>
      </nav>
    </header>

    <div v-if="error" class="card" style="background: rgba(248, 113, 113, 0.1); border-left: 4px solid #f87171;">
      <p style="color: #fca5a5; margin: 0;">{{ error }}</p>
    </div>

    <div v-if="loading && users.length === 0" style="text-align: center; padding: 2rem;">
      Carregando usuários...
    </div>

    <div v-else class="card" style="padding: 0; overflow: hidden;">
      <table class="users-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>E-mail</th>
            <th>Papel</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td>#{{ user.id }}</td>
            <td>{{ user.name }}</td>
            <td>{{ user.email }}</td>
            <td>
              <span :class="['badge', user.role]">
                {{ user.role === 'admin' ? 'Admin' : 'Usuário' }}
              </span>
            </td>
            <td>
              <div class="actions">
                <button @click="openEditModal(user)" class="btn ghost small">Editar</button>
                <button @click="handleDelete(user.id)" class="btn ghost small" style="color: #f87171;">Excluir</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="pagination" v-if="totalPages > 1">
        <button 
          @click="fetchUsers(currentPage - 1)" 
          :disabled="currentPage === 1"
          class="btn ghost small"
        >Anterior</button>
        <span class="small">Página {{ currentPage }} de {{ totalPages }}</span>
        <button 
          @click="fetchUsers(currentPage + 1)" 
          :disabled="currentPage === totalPages"
          class="btn ghost small"
        >Próxima</button>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEditModal" class="modal-overlay" @click.self="closeEditModal">
      <div class="modal">
        <h3>Editar Usuário</h3>
        <p class="small" style="margin-bottom: 1.5rem;">Alterando dados de #{{ editingUser.id }}</p>
        
        <form @submit.prevent="handleUpdate">
          <div class="form-group">
            <label>Nome</label>
            <input v-model="editingUser.name" type="text" class="input" required>
          </div>

          <div class="form-group">
            <label>E-mail</label>
            <input v-model="editingUser.email" type="email" class="input" required>
          </div>
          
          <div class="form-group">
            <label>Papel (Role)</label>
            <select v-model="editingUser.role" class="input">
              <option value="user">Usuário</option>
              <option value="admin">Administrador</option>
            </select>
          </div>
          
          <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn primary" :disabled="saving">
              {{ saving ? 'Salvando...' : 'Salvar Alterações' }}
            </button>
            <button type="button" @click="closeEditModal" class="btn ghost">Cancelar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.users-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}

.users-table th, .users-table td {
  padding: 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.users-table th {
  font-size: 0.75rem;
  text-transform: uppercase;
  color: #94a3b8;
  letter-spacing: 0.05em;
}

.badge {
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge.admin {
  background: rgba(139, 92, 246, 0.2);
  color: #a78bfa;
}

.badge.user {
  background: rgba(100, 116, 139, 0.2);
  color: #94a3b8;
}

.pagination {
  padding: 1rem;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1.5rem;
  background: rgba(255, 255, 255, 0.02);
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: #1e293b;
  padding: 2rem;
  border-radius: 12px;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
  color: #94a3b8;
}
</style>
