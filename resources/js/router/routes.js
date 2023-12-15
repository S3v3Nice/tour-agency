import Home from "../components/Home.vue";
import Login from "../components/Login.vue";
import Register from "../components/Register.vue";
import Profile from "../components/Profile.vue";
import NotFound from "../components/NotFound.vue";

export default [
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
                title: 'Вход в аккаунт',
                guest: true,
            }
    },
    {
        path: '/register',
        name: 'register',
        component: Register,
        meta:
            {
                title: 'Регистрация',
                guest: true,
            }
    },

    {
        path: '/profile',
        name: 'profile',
        component: Profile,
        meta:
            {
                title: 'Личный кабинет',
                authenticated: true,
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
