<script setup lang="ts">
import {useAuthStore} from '@/stores/auth'
import axios from 'axios'
import {useRouter} from 'vue-router'
import LoginForm from '@/components/auth/LoginForm.vue'
import RegisterForm from '@/components/auth/RegisterForm.vue'
import {onMounted, ref, watch} from 'vue'
import {Modal} from 'bootstrap'
import {useModalStore} from '@/stores/modal'
import {storeToRefs} from 'pinia'

const router = useRouter()
const authStore = useAuthStore()
const modalStore = useModalStore()
const { isAuth: isAuthModalVisible } = storeToRefs(modalStore)
const authModal = ref<Modal>({})
const isAuthLogin = ref(true)

onMounted(() => {
    const element = document.getElementById('authModal');
    authModal.value = Modal.getOrCreateInstance(element, {})
    element.addEventListener('hidden.bs.modal', () => {
        isAuthModalVisible.value = false
    })
})

watch(isAuthModalVisible, (showAuthModal: boolean) => {
    if (showAuthModal) {
        isAuthLogin.value = true
        authModal.value.show()
    }
})

function logout() {
    axios.post('/logout').then(() => {
        router.go(0)
    })
}
</script>

<template>
    <div class="header-custom">
        <div class="container">
            <header
                class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-between py-3 mb-4 border-bottom">
                <router-link :to="{ name: 'home' }">
                    <img class="logo" src="/images/logo.png" height="45" alt="TourAgency"/>
                </router-link>

                <ul class="nav col-12 col-md-auto mb-2 justify-content-center mb-md-0 navs">
                </ul>

                <div v-if="!authStore.isAuthenticated" class="col-md-3 text-end">
                    <button type="button" class="btn btn-outline-primary me-2" @click="isAuthModalVisible = true">
                        Войти
                    </button>
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
                    <router-link :to="{ name: 'admin' }" v-if="authStore.isAdmin" class="dropdown-item">
                        Админ-панель
                    </router-link>
                    <a type="button" class="dropdown-item" @click="logout">
                        Выйти
                    </a>
                </div>
            </header>
        </div>
    </div>

    <div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="authModalTitle">
                        {{ isAuthLogin ? 'Вход в аккаунт' : 'Регистрация' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"/>
                </div>
                <div v-if="isAuthModalVisible" class="modal-body">
                    <login-form v-if="isAuthLogin" @switch-to-register="isAuthLogin = false"/>
                    <register-form v-else @switch-to-login="isAuthLogin = true"/>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
