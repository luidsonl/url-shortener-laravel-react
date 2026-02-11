import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import Login from '../views/Login.vue';
import Register from '../views/Register.vue';
import ShortLinks from '../views/ShortLinks.vue';
import Profile from '../views/Profile.vue';
import Users from '../views/Users.vue';
import { useAuth } from '../services/auth';

const routes: Array<RouteRecordRaw> = [
    {
        path: '/',
        redirect: '/short-links',
    },
    {
        path: '/login',
        name: 'Login',
        component: Login,
        meta: { guest: true },
    },
    {
        path: '/register',
        name: 'Register',
        component: Register,
        meta: { guest: true },
    },
    {
        path: '/short-links',
        name: 'ShortLinks',
        component: ShortLinks,
        meta: { requiresAuth: true },
    },
    {
        path: '/profile',
        name: 'Profile',
        component: Profile,
        meta: { requiresAuth: true },
    },
    {
        path: '/users',
        name: 'Users',
        component: Users,
        meta: { requiresAuth: true, requiresAdmin: true },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to, _from, next) => {
    const { state, fetchUser } = useAuth();
    const isAuthenticated = !!localStorage.getItem('token');

    // If authenticated but user is not in state, fetch it
    if (isAuthenticated && !state.user && !state.loading) {
        await fetchUser();
    }

    if (to.meta.requiresAuth && !isAuthenticated) {
        next({ name: 'Login' });
    } else if (to.meta.guest && isAuthenticated) {
        next({ name: 'ShortLinks' });
    } else if (to.meta.requiresAdmin && state.user?.role !== 'admin') {
        next({ name: 'ShortLinks' });
    } else {
        next();
    }
});

export default router;
