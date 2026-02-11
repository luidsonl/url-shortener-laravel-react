import { reactive, readonly } from 'vue';
import api from './api';

interface User {
    id: number;
    name: string;
    email: string;
    email_verified: boolean;
    role: string;
    created_at: string;
    updated_at: string;
}

interface AuthState {
    user: User | null;
    loading: boolean;
    initialized: boolean;
}

const state = reactive<AuthState>({
    user: null,
    loading: false,
    initialized: false,
});

export const useAuth = () => {
    const fetchUser = async () => {
        if (!localStorage.getItem('token')) {
            state.user = null;
            state.initialized = true;
            return null;
        }

        state.loading = true;
        try {
            const response = await api.get('/auth/user');
            // The backend returns { user: { ... } } or just the user depending on the endpoint
            // My previous audit showed AuthController@user returns { user: $user }
            // ProfileController@show returns new UserResource($user) which is { id, name, ... } direct or { data: { ... } }
            // Resource by default wraps in 'data' unless disabled.

            const userData = response.data.user || response.data;
            state.user = userData;
            return userData;
        } catch (error) {
            state.user = null;
            return null;
        } finally {
            state.loading = false;
            state.initialized = true;
        }
    };

    const resendVerification = async () => {
        if (!state.user?.email) return;

        try {
            await api.post('/email/resend', { email: state.user.email });
            return true;
        } catch (error) {
            throw error;
        }
    };

    const logout = () => {
        localStorage.removeItem('token');
        state.user = null;
        window.location.href = '/login';
    };

    return {
        state: readonly(state),
        fetchUser,
        resendVerification,
        logout,
    };
};
