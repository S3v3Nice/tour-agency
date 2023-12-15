<script setup>
import Header from "./Header.vue";
import {ref} from "vue";
import router from "../router";
import {useAuthStore} from "../stores/auth.ts";

const authStore = useAuthStore()
const loginData = ref({
  email: '',
  password: '',
  remember: false
})
const errors = ref({})
const isProcessing = ref(false)

function login() {
  if (isProcessing.value) return

  isProcessing.value = true
  axios.post('/login', loginData.value).then((response) => {
    if (!response.data.success) {
      errors.value = response.data.errors
      return
    }

    authStore.fetchUser().then(() => {
      router.push({name: 'home'})
    })
  }).finally(() => {
    isProcessing.value = false
  })
}

</script>

<template>
  <Header></Header>

  <main class="form-signin w-50 m-auto">
    <form @submit.prevent="login">
      <h1 class="h3 mb-3 fw-normal">Вход</h1>

      <div class="form-floating">
        <input v-model="loginData.email" name="email" type="email" class="form-control mb-2"
               :class="{ 'is-invalid': 'email' in errors }"
               id="floatingInput">
        <label for="floatingInput">Адрес электронной почты</label>
        <span v-if="'email' in errors" class="invalid-feedback mb-3" role="alert">
            {{ errors['email'][0] }}
          </span>
      </div>

      <div class="form-floating">
        <input v-model="loginData.password" name="password" type="password" class="form-control mb-2"
               :class="{ 'is-invalid': 'password' in errors }"
               id="floatingPassword">
        <label for="floatingPassword">Пароль</label>
        <span v-if="'password' in errors" class="invalid-feedback mb-3" role="alert">
            {{ errors['password'][0] }}
          </span>
      </div>

      <div class="checkbox mb-3">
        <label>
          <input v-model="loginData.remember" name="remember" type="checkbox"> Запомнить
        </label>
      </div>

      <button :disabled="isProcessing" class="w-100 btn btn-lg btn-primary mb-2">Войти</button>
      <router-link :to="{ name: 'register' }" class="w-100 btn btn-lg btn-outline-primary">
        Нет учетной записи?
      </router-link>
    </form>
  </main>
</template>

<style scoped>

</style>