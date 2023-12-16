<script setup>
import {useAuthStore} from "../stores/auth.ts";

const authStore = useAuthStore()

function logout() {
    axios.post('/logout').then(() => {
        authStore.reset()
    })
}
</script>

<template>
    <div class="header-custom">
        <div class="container">
            <header
                class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-between py-3 mb-4 border-bottom">
                <router-link :to="{ name: 'home' }">
                    <img class="logo" src="/public/images/logo.png" height="45" alt="TourAgency"/>
                </router-link>

                <ul class="nav col-12 col-md-auto mb-2 justify-content-center mb-md-0 navs">
                </ul>

                <div v-if="!authStore.isAuthenticated" class="col-md-3 text-end">
                    <router-link :to="{ name: 'login' }" type="button" class="btn btn-outline-primary me-2">Войти
                    </router-link>
                </div>
                <a v-else id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                   data-bs-toggle="dropdown"
                   aria-haspopup="true" aria-expanded="false">
                    {{ authStore.email }}
                </a>

                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <router-link :to="{ name: 'profile' }" class="dropdown-item">
                        Личный кабинет
                    </router-link>
                    <router-link :to="{ name: 'admin' }" v-if="authStore.is_admin" class="dropdown-item">
                        Админ-панель
                    </router-link>
                    <a type="button" class="dropdown-item" @click="logout">
                        Выйти
                    </a>
                </div>
            </header>
        </div>
    </div>
</template>

<style scoped>

</style>
