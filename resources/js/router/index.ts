import {createRouter, createWebHistory} from 'vue-router'
import type {Component} from 'vue'
import {nextTick} from 'vue'
import routes from './routes.js'
import {useAuthStore} from '@/stores/auth'
import {changeTitle} from '@/helpers'
import Login from '@/components/auth/Login.vue'

const router = createRouter({
    history: createWebHistory(),
    routes: routes,
})

router.beforeEach((to, _from, next) => {
    const authStore = useAuthStore()

    function displayComponent(component?: Component) {
        to.matched[0].components!.default = component ?? to.matched[0].meta.defaultComponent!
        next()
    }

    function processRouteNavigation() {
        if (
            to.matched.some(record => record.meta.requiresAuth) &&
            !authStore.isAuthenticated
        ) {
            displayComponent(Login)
            return
        }

        if (
            to.matched.some(record => record.meta.requiresGuest) && authStore.isAuthenticated
        ) {
            next({name: 'home'})
            return
        }

        if (
            to.matched.some(record => record.meta.requiresAdmin) &&
            !authStore.isAdmin
        ) {
            next({name: 'home'})
            return
        }

        displayComponent()
    }

    if (!to.matched[0].meta.defaultComponent) {
        to.matched[0].meta.defaultComponent = to.matched[0].components!.default
    }

    if (!authStore.isFetched) {
        authStore.fetchUser().then(() => {
            processRouteNavigation()
        })
    } else {
        processRouteNavigation()
    }
})

router.afterEach((to) => {
    nextTick(() => {
        changeTitle(to.meta['title'])
    }).then()
})

export default router
