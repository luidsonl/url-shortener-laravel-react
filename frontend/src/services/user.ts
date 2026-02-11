import api from './api';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified: boolean;
    role: string;
    created_at: string;
    updated_at: string;
}

export interface UserListResponse {
    data: User[];
    links: {
        first: string;
        last: string;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        from: number;
        last_page: number;
        path: string;
        per_page: number;
        to: number;
        total: number;
    };
}

export const userService = {
    async listUsers(page: number = 1, search: string = '', role: string = '') {
        const params: any = { page };
        if (search) params.search = search;
        if (role) params.role = role;

        try {
            const response = await api.get<UserListResponse>('/users', { params });
            return response.data;
        } catch (error) {
            throw error;
        }
    },

    async getUser(id: number) {
        try {
            const response = await api.get<User>(`/users/${id}`);
            return response.data;
        } catch (error) {
            throw error;
        }
    },

    async updateUser(id: number, data: Partial<User>) {
        try {
            const response = await api.put<User>(`/users/${id}`, data);
            return response.data;
        } catch (error) {
            throw error;
        }
    },

    async deleteUser(id: number) {
        try {
            await api.delete(`/users/${id}`);
            return true;
        } catch (error) {
            throw error;
        }
    }
};
