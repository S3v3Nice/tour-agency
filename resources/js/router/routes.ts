import Home from '@/components/Home.vue'
import Login from '@/components/auth/Login.vue'
import Profile from '@/components/Profile.vue'
import NotFound from '@/components/NotFound.vue'
import TourCountry from '@/components/TourCountry.vue'
import AdminPanel from '@/components/AdminPanel.vue'
import Analytics from '@/components/admin-panel/Analytics.vue'
import TourCountries from '@/components/admin-panel/TourCountries.vue'
import TourCities from '@/components/admin-panel/TourCities.vue'
import TourHotels from '@/components/admin-panel/TourHotels.vue'
import TourBookings from '@/components/admin-panel/TourBookings.vue'
import TourPayments from '@/components/admin-panel/TourPayments.vue'
import Tours from '@/components/admin-panel/Tours.vue'
import type {Component} from 'vue'
import type {RouteRecordRaw} from 'vue-router'

declare module 'vue-router' {
    interface RouteMeta {
        title: string
        requiresAuth?: boolean
        requiresGuest?: boolean
        requiresAdmin?: boolean
        defaultComponent?: Component
    }
}

const routes: RouteRecordRaw[] = [
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
                requiresGuest: true,
            }
    },
    {
        path: '/profile',
        name: 'profile',
        component: Profile,
        meta:
            {
                title: 'Личный кабинет',
                requiresAuth: true,
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
            }
    },
    {
        path: '/admin',
        name: 'admin',
        component: AdminPanel,
        redirect: {name: 'admin.analytics'},
        meta:
            {
                title: 'Админ-панель',
                requiresAuth: true,
                requiresAdmin: true,
            },
        children:
            [
                {
                    path: 'analytics',
                    name: 'admin.analytics',
                    component: Analytics,
                    meta:
                        {
                            title: 'Аналитика',
                        }
                },
                {
                    path: 'countries',
                    name: 'admin.countries',
                    component: TourCountries,
                    meta:
                        {
                            title: 'Страны туров',
                        }
                },
                {
                    path: 'cities',
                    name: 'admin.cities',
                    component: TourCities,
                    meta:
                        {
                            title: 'Города туров',
                        }
                },
                {
                    path: 'hotels',
                    name: 'admin.hotels',
                    component: TourHotels,
                    meta:
                        {
                            title: 'Отели',
                        }
                },

                {
                    path: 'tours',
                    name: 'admin.tours',
                    component: Tours,
                    meta:
                        {
                            title: 'Туры',
                        }
                },
                {
                    path: 'bookings',
                    name: 'admin.bookings',
                    component: TourBookings,
                    meta:
                        {
                            title: 'Записи на туры',
                        }
                },
                {
                    path: 'payments',
                    name: 'admin.payments',
                    component: TourPayments,
                    meta:
                        {
                            title: 'Платежи',
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

export default routes
