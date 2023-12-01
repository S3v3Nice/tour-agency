import './bootstrap';
import {createApp} from "vue";
import {createRouter, createWebHistory} from "vue-router";
import App from "./components/App.vue";
import Home from "./components/Home.vue";
import Login from "./components/Login.vue";
import NotFound from "./components/NotFound.vue";
import Register from "./components/Register.vue";

const routes = [
    {path: '/', component: Home},
    {path: '/login', component: Login},
    {path: '/register', component: Register},
    {path: '/:pathMatch(.*)*', component: NotFound},
]

const router = createRouter({
    history: createWebHistory(),
    routes: routes,
})

const app = createApp(App)
app.use(router)
app.mount('#app')
