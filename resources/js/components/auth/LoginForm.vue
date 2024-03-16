<script setup lang="ts">
import {ref} from 'vue'
import axios, {AxiosError} from 'axios'
import router from '@/router'
import {toast} from 'vue3-toastify'
import {getErrorMessageByCode} from '@/helpers'

defineEmits(['switch-to-register'])

const loginData = ref({
    email: '',
    password: '',
    remember: false
})
const errors = ref({})
const isProcessing = ref(false)

function login() {
    isProcessing.value = true
    errors.value = []

    axios.post('/login', loginData.value).then((response) => {
        if (response.data.success) {
            router.go(0)
        } else {
            if (response.data.errors) {
                errors.value = response.data.errors
            }
            if (response.data.message) {
                toast.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toast.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isProcessing.value = false
    })
}
</script>

<template>
    <form @submit.prevent="login">
        <div class="form-floating mb-3">
            <input v-model="loginData.email" type="email" class="form-control"
                   :class="{ 'is-invalid': 'email' in errors }"
                   id="email-input">
            <label for="email-input">E-mail</label>
            <span class="invalid-feedback">{{ errors['email']?.[0] }}</span>
        </div>

        <div class="form-floating mb-3">
            <input v-model="loginData.password" type="password" class="form-control"
                   :class="{ 'is-invalid': 'password' in errors }"
                   id="password-input">
            <label for="password-input">Пароль</label>
            <span class="invalid-feedback">{{ errors['password']?.[0] }}</span>
        </div>

        <div class="form-check mb-3">
            <input v-model="loginData.remember" type="checkbox" class="form-check-input" id="remember-input">
            <label for="remember-input">Запомнить</label>
        </div>

        <button type="submit" :disabled="isProcessing" class="w-100 btn btn-primary mb-2">Войти</button>

        <button type="button" class="w-100 btn btn-link" @click="$emit('switch-to-register')">
            Нет учетной записи?
        </button>
    </form>
</template>

<style scoped>

</style>
