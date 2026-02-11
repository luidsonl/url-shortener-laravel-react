import axios from 'axios';

const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Add a request interceptor to attach the token
api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Add a response interceptor to handle errors
api.interceptors.response.use(
    (response) => {
        return response;
    },
    (error) => {
        if (error.response && error.response.status === 401) {
            // Clear token and redirect to login if unauthorized
            localStorage.removeItem('token');

            // Don't redirect if we are already trying to login
            if (!error.config.url?.includes('/auth/login')) {
                window.location.href = '/login';
            }
        }

        if (error.response && error.response.status === 403) {
            // If we get a 403, it might be due to email verification
            // The auth state should be refreshed to reflect this
            console.warn('Access forbidden: Email may not be verified');
        }
        return Promise.reject(error);
    }
);

export default api;
