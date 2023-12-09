import Home from "./components/Home.vue";
import Login from "./components/Login.vue";
import Register from "./components/Register.vue";
import NotFound from "./components/NotFound.vue";
import {createRouter, createWebHistory} from "vue-router";
import {nextTick} from "vue";

const APP_NAME = 'Tour Agency';

const routes = [
    {
        path: '/',
        name: 'home',
        component: Home,
        meta:
            {
                title: 'Добро пожаловать'
            }
    },
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta:
            {
                title: 'Вход в аккаунт'
            }
    },
    {
        path: '/register',
        name: 'register',
        component: Register,
        meta:
            {
                title: 'Регистрация'
            }
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: NotFound,
        meta:
            {
                title: 'Не найдено'
            }
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes: routes,
})

router.afterEach((to, from) => {
    nextTick(() => {
        document.title = (to.meta.title + ' - ' + APP_NAME) || APP_NAME
    })
})

export default router;