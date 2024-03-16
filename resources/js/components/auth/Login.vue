<script setup lang="ts">
import {useRouter} from 'vue-router'
import LoginForm from '@/components/auth/LoginForm.vue'
import {onMounted, ref, watch} from 'vue'
import RegisterForm from '@/components/auth/RegisterForm.vue'
import {changeTitle} from '@/helpers'

const isRegistration = ref(false)
const router = useRouter()

onMounted(() => {
    changeTitle('Вход')
})

watch(isRegistration, (value) => {
    changeTitle(value ? 'Регистрация' : 'Вход')
})
</script>

<template>
    <main class="form-signin w-50 m-auto">
        <h1 class="h3 mb-3 fw-normal">{{ isRegistration ? 'Регистрация' : 'Вход' }}</h1>
        <register-form v-if="isRegistration" @switch-to-login="isRegistration = false"></register-form>
        <login-form v-else @switch-to-register="isRegistration = true"/>
    </main>
</template>

<style scoped>

</style>
