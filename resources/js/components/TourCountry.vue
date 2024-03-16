<script setup lang="ts">
import {computed, onMounted, ref} from 'vue'
import {useRouter} from 'vue-router'
import {changeTitle, formatDateTime, getAbsolutePath, getErrorMessageByCode, isEmptyObject} from '@/helpers'
import axios, {AxiosError} from 'axios'
import {Modal} from 'bootstrap'
import type {Tour, TourBooking, TourCountry} from '@/types'
import {toast} from 'vue3-toastify'
import {useAuthStore} from '@/stores/auth'
import {useModalStore} from '@/stores/modal'

const prepaymentRate = 0.5

const props = defineProps({
    slug: String
})

const authStore = useAuthStore()
const modalStore = useModalStore()
const router = useRouter()
const isLoading = ref<boolean>(false)
const isProcessingBooking = ref<boolean>(false)
const data = ref<TourCountry | null>(null)
const selectedTour = ref<Tour>({})
const bookingDetails = ref<TourBooking>({})
const makeBookingModal = ref<Modal>({})
const authRequiredModal = ref<Modal>({})
const bookingErrors = ref<string[][]>([])

onMounted(() => {
    loadTourCountry()
    makeBookingModal.value = Modal.getOrCreateInstance('#makeBookingModal', {})
    authRequiredModal.value = Modal.getOrCreateInstance('#authRequiredModal', {})
})

function loadTourCountry() {
    isLoading.value = true

    axios.get(`/api/tour-countries/${props.slug}`).then((response) => {
        if (response.data.success) {
            data.value = response.data.record
            changeTitle(`Туры - ${data.value.name}`)
        } else {
            if (response.data.message) {
                toast.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toast.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isLoading.value = false
    })
}

function showMakeBookingModal(tour: Tour) {
    if (!authStore.isAuthenticated) {
        authRequiredModal.value.show()
        return
    }

    selectedTour.value = {...tour}
    bookingDetails.value = {
        tour_id: selectedTour.value.id,
        adults_count: 1,
        children_count: 0,
    }
    makeBookingModal.value.show()
}

function submitMakeBooking() {
    isProcessingBooking.value = true
    bookingErrors.value = []

    axios.post('/api/tour-bookings', bookingDetails.value).then((response) => {
        if (response.data.success) {
            toast.success(`Вы записались на тур.`)
            makeBookingModal.value.hide()
            loadTourCountry()
        } else {
            if (response.data.errors) {
                bookingErrors.value = response.data.errors
            }
            if (response.data.message) {
                toast.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toast.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isProcessingBooking.value = false
    })
}

function showAuthModal() {
    authRequiredModal.value.hide()
    modalStore.isAuth = true
}

function calculateTourSpacesLeft(tour: Tour) {
    return tour.max_participant_count - tour.participant_count
}

const totalBookingCost = computed(() => {
    if (selectedTour.value) {
        const {adults_count, children_count} = bookingDetails.value
        const adultPrice = selectedTour.value.adult_price
        const childPrice = adultPrice / 2

        return adults_count * adultPrice + children_count * childPrice
    }
    return 0
})
</script>

<template>
    <div v-if="!isLoading" class="container mt-4">
        <div v-if="data" class="row">
            <div class="col-md-6">
                <img :src="getAbsolutePath(data.image_path)" :alt="data.name" class="img-fluid rounded"/>
            </div>
            <div class="col-md-6">
                <h2>{{ data.name }}</h2>
                <p>{{ data.description }}</p>
            </div>

            <div class="mt-5">
                <h4 class="mb-4">Доступные туры:</h4>
                <ul class="list-group">
                    <li v-for="tour in data.tours" :key="tour.id" class="list-group-item">
                        <div class="row">
                            <div class="col-md-3">
                                <img :src="getAbsolutePath(tour.hotel.city.image_path)" :alt="tour.hotel.city.name"
                                     class="img-fluid rounded"/>
                            </div>
                            <div class="col-md-6">
                                <h4>{{ tour.hotel!.city!.name }}</h4>
                                <p>{{ formatDateTime(tour.start_date!) }} — {{ formatDateTime(tour.end_date!) }}</p>
                                <p>{{ tour.hotel!.name }}</p>
                                <p>{{ tour.hotel!.city!.description }}</p>
                            </div>
                            <div class="col-md-3">
                                <button @click="showMakeBookingModal(tour)" class="btn btn-primary w-100 mb-2">
                                    Записаться
                                </button>
                                <p class="mb-2">Осталось мест: {{ calculateTourSpacesLeft(tour) }}</p>
                                <p>Стоимость: {{ tour.adult_price }} ₽</p>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Модальное окно для записи на тур -->
    <div class="modal fade" id="makeBookingModal" tabindex="-1" aria-labelledby="makeBookingModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="makeBookingModalLabel">Запись на тур</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form v-if="data" @submit.prevent="submitMakeBooking">
                        <div v-if="!isEmptyObject(selectedTour)" class="mb-4">
                            <p>
                                {{ selectedTour.hotel!.name! }} ({{ selectedTour.hotel!.city!.name }},
                                {{ data.name }})
                            </p>
                            <p>
                                {{ formatDateTime(selectedTour.start_date!) }} —
                                {{ formatDateTime(selectedTour.end_date!) }}
                            </p>
                        </div>

                        <div class="mb-3">
                            <label for="makeBookingAdultsCount" class="form-label">Количество взрослых</label>
                            <input v-model="bookingDetails.adults_count" type="number" min="1"
                                   :max="calculateTourSpacesLeft(selectedTour) - bookingDetails.children_count"
                                   class="form-control"
                                   id="makeBookingAdultsCount"
                                   :class="{ 'is-invalid': 'adults_count' in bookingErrors }">
                            <span v-if="'adults_count' in bookingErrors" class="invalid-feedback mb-3" role="alert">
                                {{ bookingErrors['adults_count'][0] }}
                            </span>
                        </div>

                        <div class="mb-3">
                            <label for="makeBookingChildrenCount" class="form-label">Количество детей</label>
                            <input v-model="bookingDetails.children_count" type="number" min="0"
                                   :max="calculateTourSpacesLeft(selectedTour) - bookingDetails.adults_count"
                                   class="form-control"
                                   id="makeBookingChildrenCount"
                                   :class="{ 'is-invalid': 'children_count' in bookingErrors }">
                            <span v-if="'children_count' in bookingErrors" class="invalid-feedback mb-3"
                                  role="alert">
                                {{ bookingErrors['children_count'][0] }}
                            </span>
                        </div>

                        <div class="mt-4">
                            <p>Общая стоимость: <strong>{{ totalBookingCost }} ₽</strong></p>
                            <p>Предоплата: <strong>{{ totalBookingCost * prepaymentRate }} ₽</strong></p>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                            <button type="submit" :disabled="isProcessingBooking" class="btn btn-primary">
                                Записаться с предоплатой
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="authRequiredModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="authRequiredModalTitle">Необходима авторизация</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Для записи на тур необходима авторизация.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button @click="showAuthModal" type="button" class="btn btn-primary">Авторизоваться</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.list-group-item:hover {
    filter: brightness(98%);
}
</style>
