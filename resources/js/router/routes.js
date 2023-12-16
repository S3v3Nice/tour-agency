import Home from "../components/Home.vue";
import Login from "../components/Login.vue";
import Register from "../components/Register.vue";
import Profile from "../components/Profile.vue";
import NotFound from "../components/NotFound.vue";
import TourCountry from "../components/TourCountry.vue";
import AdminPanel from "../components/AdminPanel.vue";
import Analytics from "../components/admin-panel/Analytics.vue";
import Users from "../components/admin-panel/Users.vue";
import TourCountries from "../components/admin-panel/TourCountries.vue";
import TourCities from "../components/admin-panel/TourCities.vue";
import TourHotels from "../components/admin-panel/TourHotels.vue";
import TourBookings from "../components/admin-panel/TourBookings.vue";
import Payments from "../components/admin-panel/Payments.vue";

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
        path: '/tours/:slug',
        name: 'tour-country',
        component: TourCountry,
        props: true,
        meta:
            {
                title: 'Туры по направлению',
                authenticated: true,
            }
    },
    {
        path: '/admin',
        name: 'admin',
        component: AdminPanel,
        meta:
            {
                title: 'Админ-панель',
                authenticated: true,
                admin: true,
            },
        children:
            [
                {
                    path: 'analytics',
                    name: 'analytics',
                    component: Analytics,
                    meta:
                        {
                            title: 'Аналитика',
                            authenticated: true,
                            admin: true,
                        }
                },
                {
                    path: 'users',
                    name: 'users',
                    component: Users,
                    meta:
                        {
                            title: 'Пользователи',
                            authenticated: true,
                            admin: true,
                        }
                },
                {
                    path: 'countries',
                    name: 'countries',
                    component: TourCountries,
                    meta:
                        {
                            title: 'Страны туров',
                            authenticated: true,
                            admin: true,
                        }
                },
                {
                    path: 'cities',
                    name: 'cities',
                    component: TourCities,
                    meta:
                        {
                            title: 'Города туров',
                            authenticated: true,
                            admin: true,
                        }
                },
                {
                    path: 'hotels',
                    name: 'hotels',
                    component: TourHotels,
                    meta:
                        {
                            title: 'Отели',
                            authenticated: true,
                            admin: true,
                        }
                },
                {
                    path: 'bookings',
                    name: 'bookings',
                    component: TourBookings,
                    meta:
                        {
                            title: 'Записи на туры',
                            authenticated: true,
                            admin: true,
                        }
                },
                {
                    path: 'payments',
                    name: 'payments',
                    component: Payments,
                    meta:
                        {
                            title: 'Платежи',
                            authenticated: true,
                            admin: true,
                        }
                },
            ],
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
