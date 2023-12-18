<script setup lang="ts">
import Header from "./Header.vue";
import {computed, onMounted, ref} from "vue";
import {useRouter} from "vue-router";
import {changeTitle, formatDateTime, getAbsolutePath, isEmptyObject} from "../helpers.js";
import axios from "axios";
import {Modal} from "bootstrap";
import {TourCountry} from "../types/tourCountry";
import {Tour} from "../types/tour";
import {TourBooking} from "../types/tourBooking";

const prepaymentRate = 0.5;

const props = defineProps({
  slug: String
})
const router = useRouter()
const isLoading = ref<boolean>(false)
const data = ref<TourCountry>({})
const selectedTour = ref<Tour>({})
const bookingDetails = ref<TourBooking>({})
const makeBookingForm = ref<Modal>({})
const formErrors = ref<string[][]>([])

onMounted(() => {
  loadTourCountry().then(() => {
    changeTitle(`Туры - ${data.value.name}`)
  })
  makeBookingForm.value = Modal.getOrCreateInstance('#makeBookingForm', {})
})

async function loadTourCountry() {
  isLoading.value = true

  await axios.get(`/api/tour-country/${props.slug}`).then((response) => {
    data.value = response.data
  }).finally(() => {
    isLoading.value = false
  })
}

function showMakeBookingForm(tour: Tour) {
  formErrors.value = {}
  selectedTour.value = {...tour};
  bookingDetails.value = {
    adults_count: 1,
    children_count: 0
  }
  makeBookingForm.value.show()
}

function submitMakeBooking() {
  let formData = new FormData()
  Object.keys(bookingDetails.value).forEach(key => formData.append(key, bookingDetails.value[key]))
  formData.append('tour_id', selectedTour.value.id)

  axios.post(`/api/tour-booking`, formData).then((response) => {
    if (!response.data.success) {
      formErrors.value = response.data.errors
      return
    }

    makeBookingForm.value.hide()
  })
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
  <Header></Header>

  <div v-if="!isLoading" class="container mt-4">
    <div v-if="!isEmptyObject(data)" class="row">
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
                <h4>{{ tour.hotel.city.name }}</h4>
                <p>{{ formatDateTime(tour.start_date) }} - {{ formatDateTime(tour.end_date) }}</p>
                <p>{{ tour.hotel.name }}</p>
                <p>{{ tour.hotel.city.description }}</p>
              </div>
              <div class="col-md-3">
                <button @click="showMakeBookingForm(tour)" class="btn btn-primary w-100 mb-2">Записаться</button>
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
  <div class="modal fade" id="makeBookingForm" tabindex="-1" aria-labelledby="makeBookingFormLabel"
       aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="makeBookingFormLabel">Запись на тур</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="submitMakeBooking">
            <div v-if="!isEmptyObject(selectedTour)" class="mb-4">
              <p>{{ selectedTour.hotel.name }} ({{ selectedTour.hotel.city.name }}, {{data.name}})</p>
              <p>{{ formatDateTime(selectedTour.start_date) }} — {{ formatDateTime(selectedTour.end_date) }}</p>
            </div>

            <div class="mb-3">
              <label for="makeBookingAdultsCount" class="form-label">Количество взрослых</label>
              <input v-model="bookingDetails.adults_count" type="number" min="1" :max="calculateTourSpacesLeft(selectedTour) - bookingDetails.children_count" class="form-control"
                     id="makeBookingAdultsCount"
                     :class="{ 'is-invalid': 'adults_count' in formErrors }">
              <span v-if="'adults_count' in formErrors" class="invalid-feedback mb-3" role="alert">
                    {{ formErrors['adults_count'][0] }}
                  </span>
            </div>

            <div class="mb-3">
              <label for="makeBookingChildrenCount" class="form-label">Количество детей</label>
              <input v-model="bookingDetails.children_count" type="number" min="0" :max="calculateTourSpacesLeft(selectedTour) - bookingDetails.adults_count" class="form-control"
                     id="makeBookingChildrenCount"
                     :class="{ 'is-invalid': 'children_count' in formErrors }">
              <span v-if="'children_count' in formErrors" class="invalid-feedback mb-3" role="alert">
                    {{ formErrors['children_count'][0] }}
                  </span>
            </div>

            <div class="mt-4">
              <p>Общая стоимость: <strong>{{ totalBookingCost }} ₽</strong></p>
              <p>Предоплата: <strong>{{ totalBookingCost * prepaymentRate }} ₽</strong></p>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
              <button type="submit" class="btn btn-primary">Записаться с предоплатой</button>
            </div>
          </form>
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