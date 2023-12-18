<script setup lang="ts">
import Header from "./Header.vue";
import {onMounted, ref} from "vue";
import {useRouter} from "vue-router";
import {changeTitle, formatDateTime, getAbsolutePath, isEmptyObject} from "../helpers.js";
import {Country} from 'admin-panel/TourCountries.vue'
import axios from "axios";

const props = defineProps({
  slug: String
})
const router = useRouter()
const isLoading = ref<boolean>(false)
const data = ref<Country>({})

onMounted(() => {
  loadTourCountry().then(() => {
    changeTitle(`Туры - ${data.value.name}`)
  })
})

async function loadTourCountry() {
  isLoading.value = true

  await axios.get(`/api/tour-country/${props.slug}`).then((response) => {
    data.value = response.data
  }).finally(() => {
    isLoading.value = false
  })
}
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
                <button class="btn btn-primary w-100 mb-2">Записаться</button>
                <p class="mb-2">Осталось мест: {{ tour.max_participant_count - tour.participant_count }}</p>
                <p>Стоимость: {{ tour.adult_price }} ₽</p>
              </div>
            </div>
          </li>
        </ul>
      </div>

    </div>
  </div>
</template>

<style scoped>
.list-group-item:hover {
  filter: brightness(98%);
}
</style>