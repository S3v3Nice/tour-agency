<script setup lang="ts">
import {computed, onMounted, ref} from 'vue'
import axios, {AxiosError} from 'axios'
import {formatDateTime, getAbsolutePath, getErrorMessageByCode, isEmptyObject} from '@/helpers'
import type {Tour, TourBooking, TourCity, TourCountry} from '@/types'
import {ru} from 'date-fns/locale'
import {toast} from 'vue3-toastify'
import {Modal} from 'bootstrap'
import {useAuthStore} from '@/stores/auth'
import {useModalStore} from '@/stores/modal'

const prepaymentRate = 0.5

const authStore = useAuthStore()
const modalStore = useModalStore()
const today = new Date()
const isLoading = ref(false)
const tourCountries = ref<TourCountry[]>([])
const tourCities = ref<TourCity[]>([])
const filteredTours = ref<Tour[]>([])
const filtersDefault = {
    sort_field: 'start_date',
    sort_order: 1,
    country_id: undefined,
    city_id: undefined,
    start_date_range: [today, undefined],
    min_days: 1,
    adults_count: 2,
    children_count: 0
}
const filters = ref({...filtersDefault})
const isProcessingBooking = ref<boolean>(false)
const selectedTour = ref<Tour>({})
const bookingDetails = ref<TourBooking>({})
const makeBookingModal = ref<Modal>({})
const authRequiredModal = ref<Modal>({})
const bookingErrors = ref<string[][]>([])

const tourCitiesForSelect = computed<TourCity[]>(() => {
    if (!filters.value.country_id) {
        return tourCities.value
    } else {
        return tourCities.value.filter(city => city.country_id === filters.value.country_id)
    }
})

onMounted(() => {
    loadTours()
    loadTourCountries()
    loadTourCities()

    makeBookingModal.value = Modal.getOrCreateInstance('#makeBookingModal', {})
    authRequiredModal.value = Modal.getOrCreateInstance('#authRequiredModal', {})
})

function loadTourCountries() {
    axios.get('/api/tour-countries').then(response => {
        if (response.data.success) {
            tourCountries.value = response.data.records
        }
    })
}

function loadTourCities() {
    axios.get('/api/tour-cities').then(response => {
        if (response.data.success) {
            tourCities.value = response.data.records
        }
    })
}

function onTourCountrySelect() {
    filters.value.city_id = undefined
}

function onTourCitySelect() {
    const city = tourCities.value.find((city) => city.id === filters.value.city_id)
    if (city) {
        filters.value.country_id = city.country_id
    }
}

function onStartDateRangeStartSelect(value) {
    filters.value.start_date_range = [value, undefined]
}

function decreaseDuration() {
    if (filters.value.min_days && filters.value.min_days > 1) {
        filters.value.min_days--
    }
}

function increaseDuration() {
    if (filters.value.min_days) {
        filters.value.min_days++
    } else {
        filters.value.min_days = 1
    }
}

function decreaseAdultsCount() {
    if (filters.value.adults_count && filters.value.adults_count > 1) {
        filters.value.adults_count--
    }
}

function increaseAdultsCount() {
    if (filters.value.adults_count) {
        filters.value.adults_count++
    } else {
        filters.value.adults_count = 1
    }
}

function decreaseChildrenCount() {
    if (filters.value.children_count && filters.value.children_count > 0) {
        filters.value.children_count--
    }
}

function increaseChildrenCount() {
    if (filters.value.children_count) {
        filters.value.children_count++
    } else {
        filters.value.children_count = 1
    }
}

function loadTours() {
    isLoading.value = true
    axios.get('/api/tours', {params: filters.value}).then(response => {
        if (response.data.success) {
            filteredTours.value = response.data.records
        }
    }).finally(() => {
        isLoading.value = false
    })
}

function resetFilters() {
    filters.value = {...filtersDefault}
    loadTours()
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
            loadTours()
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
    <div class="container mt-4">
        <h5 class="mb-4">Доступные туры</h5>
        <!-- Форма для фильтров -->
        <form @submit.prevent="loadTours" class="row g-3">
            <div class="row g-2">
                <!-- Страна тура -->
                <div class="col" style="min-width: 15em; max-width: 100%">
                    <label for="countryFilter" class="form-label">Страна тура</label>
                    <select
                        v-model="filters.country_id"
                        class="form-select"
                        id="countryFilter"
                        aria-label="Выберите страну"
                        @change="onTourCountrySelect"
                    >
                        <option v-for="country in tourCountries" :key="country.id" :value="country.id">
                            {{country.name }}
                        </option>
                    </select>
                </div>
                <!-- Город тура -->
                <div class="col" style="min-width: 15em; max-width: 100%">
                    <label for="cityFilter" class="form-label">Город тура</label>
                    <select
                        v-model="filters.city_id"
                        class="form-select"
                        id="cityFilter"
                        aria-label="Выберите город"
                        @change="onTourCitySelect()"
                    >
                        <option v-for="city in tourCitiesForSelect" :key="city.id" :value="city.id">
                            {{city.name }}
                        </option>
                    </select>
                </div>
                <!-- Даты вылета -->
                <div class="col" style="min-width: 18em; max-width: 100%">
                    <label for="dateRangeFilter" class="form-label">Даты вылета</label>
                    <VueDatePicker
                        v-model="filters.start_date_range"
                        range
                        :min-date="today"
                        hide-offset-dates
                        :enable-time-picker="false"
                        locale="ru"
                        :format-locale="ru"
                        format="dd MMM yyyy"
                        month-name-format="long"
                        cancelText="Отменить"
                        selectText="Выбрать"
                        id="dateRangeFilter"
                        auto-apply
                        :config="{closeOnAutoApply: false}"
                        @range-start="onStartDateRangeStartSelect"
                    />
                </div>
                <!-- Количество суток -->
                <div class="col" style="min-width: 9em; max-width: 100%">
                    <label for="durationFilter" class="form-label">Суток</label>
                    <div class="input-group">
                        <button class="btn btn-outline-secondary" type="button" @click="decreaseDuration">-</button>
                        <input
                            :value="`От ${filters.min_days}`"
                            type="text"
                            class="form-control"
                            id="durationFilter"
                            readonly
                        >
                        <button class="btn btn-outline-secondary" type="button" @click="increaseDuration">+</button>
                    </div>
                </div>
                <!-- Количество взрослых -->
                <div class="col" style="min-width: 7em; max-width: 100%">
                    <label for="adultsCountFilter" class="form-label">Взрослых</label>
                    <div class="input-group">
                        <button class="btn btn-outline-secondary" type="button" @click="decreaseAdultsCount">-</button>
                        <input
                            v-model="filters.adults_count"
                            type="number"
                            min="1"
                            class="form-control"
                            id="adultsCountFilter" readonly
                        >
                        <button class="btn btn-outline-secondary" type="button" @click="increaseAdultsCount">+</button>
                    </div>
                </div>
                <!-- Количество детей -->
                <div class="col" style="min-width: 7em; max-width: 100%">
                    <label for="childrenCountFilter" class="form-label">Детей</label>
                    <div class="input-group">
                        <button class="btn btn-outline-secondary" type="button" @click="decreaseChildrenCount">-
                        </button>
                        <input
                            v-model="filters.children_count"
                            type="number"
                            min="0"
                            class="form-control"
                            id="adultsCountFilter"
                            readonly
                        >
                        <button class="btn btn-outline-secondary" type="button" @click="increaseChildrenCount">+
                        </button>
                    </div>
                </div>
            </div>

            <div class="row g-2">
                <!-- Кнопка для сброса фильтров -->
                <div class="col-auto">
                    <button type="button" @click="resetFilters" class="btn btn-secondary">Сбросить</button>
                </div>
                <!-- Кнопка для применения фильтров -->
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Найти</button>
                </div>
            </div>
        </form>

        <!-- Отображение отфильтрованных туров -->
        <div class="mt-4">
            <div v-if="isLoading" class="text-center">
                <div class="d-flex justify-content-center align-items-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Загрузка...</span>
                    </div>
                </div>
            </div>
            <div v-else-if="filteredTours.length === 0">
                Нет доступных туров по вашему запросу.
            </div>
            <ul v-else class="list-group">
                <li v-for="tour in filteredTours" :key="tour.id" class="list-group-item">
                    <div class="row">
                        <div class="col-md-3">
                            <img
                                :src="getAbsolutePath(tour.hotel.city.image_path)"
                                :alt="tour.hotel.city.name"
                                class="img-fluid rounded"
                            />
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
                            <p class="mb-2">Осталось мест: {{ tour.max_participant_count - tour.participant_count }}</p>
                            <p>Стоимость: {{ tour.adult_price }} ₽</p>
                        </div>
                    </div>
                </li>
            </ul>
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
                    <form @submit.prevent="submitMakeBooking">
                        <div v-if="!isEmptyObject(selectedTour)" class="mb-4">
                            <p>
                                {{ selectedTour.hotel!.name! }} ({{ selectedTour.hotel!.city!.name }},
                                {{ selectedTour.hotel!.city!.country!.name }})
                            </p>
                            <p>
                                {{ formatDateTime(selectedTour.start_date!) }} —
                                {{ formatDateTime(selectedTour.end_date!) }}
                            </p>
                        </div>

                        <div class="mb-3">
                            <label for="makeBookingAdultsCount" class="form-label">Количество взрослых</label>
                            <input
                                v-model="bookingDetails.adults_count" type="number" min="1"
                                :max="calculateTourSpacesLeft(selectedTour) - bookingDetails.children_count"
                                class="form-control"
                                id="makeBookingAdultsCount"
                                :class="{ 'is-invalid': 'adults_count' in bookingErrors }"
                            >
                            <span v-if="'adults_count' in bookingErrors" class="invalid-feedback mb-3" role="alert">
                                {{ bookingErrors['adults_count'][0] }}
                            </span>
                        </div>

                        <div class="mb-3">
                            <label for="makeBookingChildrenCount" class="form-label">Количество детей</label>
                            <input
                                v-model="bookingDetails.children_count" type="number" min="0"
                                :max="calculateTourSpacesLeft(selectedTour) - bookingDetails.adults_count"
                                class="form-control"
                                id="makeBookingChildrenCount"
                                :class="{ 'is-invalid': 'children_count' in bookingErrors }"
                            >
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

</style>
