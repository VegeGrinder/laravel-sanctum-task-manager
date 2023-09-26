import { createRouter, createWebHistory } from 'vue-router';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            name: 'login',
            path: '/',
            component: () => import('./pages/Login.vue')
        },
        {
            name: 'register',
            path: '/register',
            component: () => import('./pages/Register.vue')
        },
        {
            name: 'home',
            path: '/home',
            component: () => import('./pages/Home.vue')
        },
        {
            name: 'tasks',
            path: '/tasks',
            component: () => import('./pages/Tasks.vue')
        }
    ],
})

router.beforeEach((to, from, next) => {
    if (!isAuthenticated() && to.name !== 'login' && to.name !== 'register') {
        return next({ name: 'login' })
    }

    if (isAuthenticated() && to.name !== 'home' && to.name !== 'tasks') {
        return next({ name: 'home' })
    }

    return next()
})

function isAuthenticated() {
    return Boolean(localStorage.getItem('APP_USER_TOKEN'))
}

export default router;
