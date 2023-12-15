import {createRouter, createWebHistory} from "vue-router";
import {nextTick} from "vue";
import routes from "./routes.js";
import {useAuthStore} from "../stores/auth.ts";

const router = createRouter({
    history: createWebHistory(),
    routes: routes,
})

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore()

    authStore.fetchUser().then(() => {
        let isAuthenticated = authStore.isAuthenticated

        if (to.matched.some(record => record.meta.authenticated)) {
            if (!isAuthenticated) {
                next({ name: 'login' })
                return
            }
        }

        if (to.matched.some(record => record.meta.guest)) {
            if (isAuthenticated) {
                next({ name: 'home' })
                return
            }
        }

        next()
    })
})

router.afterEach((to) => {
    nextTick(() => {
        document.title = (to.meta['title'] + ' - ' + import.meta.env.VITE_APP_NAME)
    })
})

export default router