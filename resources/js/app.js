import './bootstrap';
import '../css/app.css';
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import App from './App.vue';
import Dashboard from './components/Dashboard.vue';
import Tasks from './components/Tasks.vue';
import Lendings from './components/Lendings.vue';
import Expenses from './components/Expenses.vue';
import Groups from './components/Groups.vue';
import GroupDetail from './components/GroupDetail.vue';
import Settings from './components/Settings.vue';
import LoginForm from './components/auth/LoginForm.vue';
import RegisterForm from './components/auth/RegisterForm.vue';
import { useAuth } from './composables/useAuth';

const routes = [
    { path: '/login', component: LoginForm, meta: { requiresGuest: true } },
    { path: '/register', component: RegisterForm, meta: { requiresGuest: true } },
    { path: '/', component: Dashboard, meta: { requiresAuth: true } },
    { path: '/tasks', component: Tasks, meta: { requiresAuth: true } },
    { path: '/lendings', component: Lendings, meta: { requiresAuth: true } },
    { path: '/expenses', component: Expenses, meta: { requiresAuth: true } },
    { path: '/groups', component: Groups, meta: { requiresAuth: true } },
    { path: '/groups/:id', component: GroupDetail, meta: { requiresAuth: true } },
    { path: '/settings', component: Settings, meta: { requiresAuth: true } },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const { isAuthenticated } = useAuth();
    
    if (to.meta.requiresAuth && !isAuthenticated.value) {
        next('/login');
    } else if (to.meta.requiresGuest && isAuthenticated.value) {
        next('/');
    } else {
        next();
    }
});

const app = createApp(App);
app.use(router);
app.mount('#app');

