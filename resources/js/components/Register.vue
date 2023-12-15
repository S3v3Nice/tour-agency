<script setup>
import Header from "./Header.vue";
import {ref} from "vue";
import router from "../router";
import {useAuthStore} from "../stores/auth.ts";

const authStore = useAuthStore()
const registerData = ref({
  email: '',
  password: '',
  password_confirmation: '',
  remember: false
})
const errors = ref({})
const isProcessing = ref(false)

function register() {
  if (isProcessing.value) return

  isProcessing.value = true
  axios.post('/register', registerData.value).then((response) => {
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

  <div class="container register-container">
    <main class="form-signin w-50 m-auto">
      <form @submit.prevent="register">
        <h1 class="h3 mb-3 fw-normal">Регистрация</h1>

        <div class="form-floating">
          <input v-model="registerData.email" type="email" class="form-control mb-2"
                 :class="{ 'is-invalid': 'email' in errors }"
                 id="floatingInput">
          <label for="floatingInput">Адрес электронной почты</label>
          <span v-if="'email' in errors" class="invalid-feedback mb-3" role="alert">
            {{ errors['email'][0] }}
          </span>
        </div>

        <div class="form-floating">
          <input v-model="registerData.password" type="password" class="form-control mb-2"
                 :class="{ 'is-invalid': 'password' in errors }"
                 id="floatingPassword">
          <label for="floatingPassword">Пароль</label>
          <span v-if="'password' in errors" class="invalid-feedback mb-3" role="alert">
            {{ errors['password'][0] }}
          </span>
        </div>

        <div class="form-floating">
          <input v-model="registerData.password_confirmation" type="password" class="form-control mb-2"
                 :class="{ 'is-invalid': 'password_confirmation' in errors }"
                 id="floatingPasswordConfirm">
          <label for="floatingPasswordConfirm">Подтверждение пароля</label>
          <span v-if="'password_confirmation' in errors" class="invalid-feedback mb-3" role="alert">
            {{ errors['password_confirmation'][0] }}
          </span>
        </div>

        <div class="checkbox mb-3">
          <label>
            <input v-model="registerData.remember" type="checkbox"> Запомнить
          </label>
        </div>

        <button :disabled="isProcessing" class="w-100 btn btn-lg btn-primary mb-2">Зарегистрироваться</button>
        <router-link :to="{ name: 'login' }" class="w-100 btn btn-lg btn-outline-primary">
          Уже есть учетная запись?
        </router-link>
      </form>
    </main>
  </div>
</template>

<style scoped>

</style>