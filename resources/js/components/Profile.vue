<script setup lang="ts">
import {useAuthStore} from '@/stores/auth'
import type {TourBooking} from '@/types'
import {onMounted, ref} from 'vue'
import axios, {type AxiosError} from 'axios'
import {formatDateTime, getAbsolutePath, getErrorMessageByCode} from '@/helpers'
import {toast} from 'vue3-toastify'

const activeTab = ref('tours')
const authStore = useAuthStore()
const tourBookings = ref<TourBooking[]>([])
const profileForm = ref({
    first_name: authStore.firstName,
    last_name: authStore.lastName,
})
const emailForm = ref({
    email: '',
    email_confirmation: '',
})
const passwordForm = ref({
    password: '',
    password_confirmation: '',
})
const profileFormErrors = ref<string[][]>([])
const emailFormErrors = ref<string[][]>([])
const passwordFormErrors = ref<string[][]>([])
const isProcessingProfileForm = ref(false)
const isProcessingEmailForm = ref(false)
const isProcessingPasswordForm = ref(false)

onMounted(() => {
    loadTourBookings()
})

function loadTourBookings() {
    axios.get(`/api/users/${authStore.id}/tour-bookings`).then((response) => {
        if (response.data.success) {
            tourBookings.value = response.data.records
        } else {
            if (response.data.message) {
                toast.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toast.error(getErrorMessageByCode(error.response!.status))
    })
}

function payRemainingAmount(booking: TourBooking) {
    console.log(JSON.stringify(booking))
    axios.put(`/api/tour-bookings/${booking.id}/pay-remaining`, profileForm.value).then((response) => {
        if (response.data.success) {
            toast.success('Доплата записи на тур прошла успешно.')
            loadTourBookings()
        } else {
            if (response.data.message) {
                toast.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toast.error(getErrorMessageByCode(error.response!.status))
    })
}

function submitProfileForm() {
    isProcessingProfileForm.value = true
    profileFormErrors.value = []

    axios.put('/api/settings/profile', profileForm.value).then((response) => {
        if (response.data.success) {
            toast.success('Настройки профиля успешно изменены.')
            authStore.fetchUser()
        } else {
            if (response.data.errors) {
                profileFormErrors.value = response.data.errors
            }
            if (response.data.message) {
                toast.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toast.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isProcessingProfileForm.value = false
    })
}

function submitEmailForm() {
    isProcessingEmailForm.value = true
    emailFormErrors.value = []

    axios.put('/api/settings/security/email', emailForm.value).then((response) => {
        if (response.data.success) {
            toast.success('E-mail успешно изменён.')
            authStore.fetchUser()
            emailForm.value.email = ''
            emailForm.value.email_confirmation = ''
        } else {
            if (response.data.errors) {
                emailFormErrors.value = response.data.errors
            }
            if (response.data.message) {
                toast.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toast.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isProcessingEmailForm.value = false
    })
}

function submitPasswordForm() {
    isProcessingPasswordForm.value = true
    passwordFormErrors.value = []

    axios.put('/api/settings/security/password', passwordForm.value).then((response) => {
        if (response.data.success) {
            toast.success('Пароль успешно изменён.')
            passwordForm.value.password = ''
            passwordForm.value.password_confirmation = ''
        } else {
            if (response.data.errors) {
                passwordFormErrors.value = response.data.errors
            }
            if (response.data.message) {
                toast.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toast.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isProcessingPasswordForm.value = false
    })
}
</script>

<template>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h2 class="mb-4">Личный кабинет</h2>
                <div class="row">
                    <div class="col-auto">
                        <p class="mb-2"><strong>Имя:</strong></p>
                        <p class="mb-2"><strong>Фамилия:</strong></p>
                        <p class="mb-2"><strong>Email:</strong></p>
                    </div>
                    <div class="col-auto">
                        <p class="mb-2">{{ authStore.firstName || '–' }}</p>
                        <p class="mb-2">{{ authStore.lastName || '–' }}</p>
                        <p class="mb-2">{{ authStore.email }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <ul class="nav nav-tabs">
                    <li class="nav-item" @click="activeTab = 'tours'">
                        <a class="nav-link" :class="{ active: activeTab === 'tours' }">Записи на туры</a>
                    </li>
                    <li class="nav-item" @click="activeTab = 'profile-settings'">
                        <a class="nav-link" :class="{ active: activeTab === 'profile-settings' }">Настройки профиля</a>
                    </li>
                    <li class="nav-item" @click="activeTab = 'private-settings'">
                        <a
                            class="nav-link"
                            :class="{ active: activeTab === 'private-settings' }"
                        >
                            Настройки безопасности
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div v-if="activeTab === 'tours'">
                    <ul class="list-group">
                        <li v-for="booking in tourBookings" :key="booking.id" class="list-group-item">
                            <div class="row">
                                <div class="col-md-2">
                                    <img
                                        :src="getAbsolutePath(booking.tour!.hotel!.city!.image_path)"
                                        :alt="booking.tour!.hotel!.city!.name"
                                        class="img-fluid rounded"
                                    />
                                </div>
                                <div class="col-md-7">
                                    <div>
                                        <small class="text-muted">{{ formatDateTime(booking.created_at!) }}</small>
                                        <span
                                            v-if="!booking.is_verified"
                                            class="ms-3 badge bg-secondary"
                                        >
                                            На рассмотрении
                                        </span>
                                    </div>

                                    <h4>{{ booking.tour!.hotel!.city!.name }}</h4>
                                    <p class="mb-2">
                                        {{ formatDateTime(booking.tour!.start_date!) }} —
                                        {{ formatDateTime(booking.tour!.end_date!) }}
                                    </p>
                                    <p>Отель: {{ booking.tour!.hotel!.name }}</p>
                                </div>
                                <div class="col-md-3">
                                    <p class="fw-bold mb-1">{{ booking.total_amount }} ₽</p>
                                    <p
                                        class="mb-3"
                                        :class="{'text-danger': booking.payed_amount != booking.total_amount, 'text-success': booking.payed_amount == booking.total_amount}"
                                    >
                                        Оплачено: {{ booking.payed_amount }} ₽
                                        ({{ booking.payed_amount / booking.total_amount * 100 }}%)
                                    </p>
                                    <p class="mb-1">Взрослых: {{ booking.adults_count }}</p>
                                    <p class="mb-3">Детей: {{ booking.children_count }}</p>

                                    <div v-if="booking.payed_amount < booking.total_amount">
                                        <button
                                            v-if="booking.is_verified"
                                            class="btn btn-primary w-100 mb-2"
                                            @click="payRemainingAmount(booking)"
                                        >
                                            Доплатить
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                <div v-if="activeTab === 'profile-settings'">
                    <form @submit.prevent="submitProfileForm">
                        <div class="form-floating mb-3">
                            <input v-model="profileForm.first_name" class="form-control"
                                   :class="{ 'is-invalid': 'first_name' in profileFormErrors }"
                                   id="first-name-input">
                            <label for="first-name-input">Имя</label>
                            <span class="invalid-feedback">{{ profileFormErrors['first_name']?.[0] }}</span>
                        </div>

                        <div class="form-floating mb-3">
                            <input v-model="profileForm.last_name" class="form-control"
                                   :class="{ 'is-invalid': 'last_name' in profileFormErrors }"
                                   id="last-name-input">
                            <label for="last-name-input">Фамилия</label>
                            <span class="invalid-feedback">{{ profileFormErrors['last_name']?.[0] }}</span>
                        </div>

                        <button type="submit" class="btn btn-primary" :disabled="isProcessingProfileForm">
                            Сохранить
                        </button>
                    </form>
                </div>

                <div v-if="activeTab === 'private-settings'">
                    <h5 class="mb-3">E-mail</h5>

                    <form @submit.prevent="submitEmailForm">
                        <div class="form-floating mb-3">
                            <input v-model="emailForm.email" class="form-control"
                                   :class="{ 'is-invalid': 'email' in emailFormErrors }"
                                   id="email-input">
                            <label for="email-input">Новый E-mail</label>
                            <span class="invalid-feedback">{{ emailFormErrors['email']?.[0] }}</span>
                        </div>
                        <div class="form-floating mb-3">
                            <input v-model="emailForm.email_confirmation" class="form-control"
                                   :class="{ 'is-invalid': 'email_confirmation' in emailFormErrors }"
                                   id="email-confirmation-input">
                            <label for="email-confirmation-input">Новый E-mail ещё раз</label>
                            <span class="invalid-feedback">{{ emailFormErrors['email_confirmation']?.[0] }}</span>
                        </div>
                        <button type="submit" class="btn btn-primary" :disabled="isProcessingEmailForm">Сохранить
                        </button>
                    </form>

                    <hr class="hr"/>

                    <h5 class="mb-3">Пароль</h5>

                    <form @submit.prevent="submitPasswordForm">
                        <div class="form-floating mb-3">
                            <input v-model="passwordForm.password" type="password" class="form-control"
                                   :class="{ 'is-invalid': 'password' in passwordFormErrors }"
                                   id="password-input">
                            <label for="password-input">Новый пароль</label>
                            <span class="invalid-feedback">{{ passwordFormErrors['password']?.[0] }}</span>
                        </div>
                        <div class="form-floating mb-3">
                            <input v-model="passwordForm.password_confirmation" type="password" class="form-control"
                                   :class="{ 'is-invalid': 'password_confirmation' in passwordFormErrors }"
                                   id="password-confirmation-input">
                            <label for="password-confirmation-input">Новый пароль ещё раз</label>
                            <span class="invalid-feedback">{{ passwordFormErrors['password_confirmation']?.[0] }}</span>
                        </div>
                        <button type="submit" class="btn btn-primary" :disabled="isProcessingPasswordForm">
                            Сохранить
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.nav-item {
    cursor: pointer;
}
</style>
