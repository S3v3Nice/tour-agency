<script setup>
import Header from "./Header.vue";
import {onMounted, ref} from "vue";
import {getAbsolutePath} from "../helpers.js";

const isLoading = ref(false)
const tourCountries = ref([])

onMounted(() => {
  loadTourCountries()
})

function loadTourCountries() {
  isLoading.value = true

  axios.get('/api/tour-countries').then((response) => {
    tourCountries.value = response.data
  }).finally(() => {
    isLoading.value = false
  })
}

</script>

<template>
  <Header></Header>
  <h5 class="mb-4">Направления туров</h5>
  <div class="row">
    <div v-for="country in tourCountries" :key="country.id" class="col-md-4 mb-4">
      <router-link :to="{name: 'tour-country', params: {slug: country.slug}}">
        <div class="card">
          <img :src="getAbsolutePath(country.image_path)" class="card-img-top" :alt="country.name">
          <div class="card-body">
            <a class="card-title">{{ country.name }}</a>
          </div>
        </div>
      </router-link>
    </div>
  </div>
</template>

<style scoped>
.card {
  transition: box-shadow 0.3s, filter 0.3s;
}

.card:hover {
  filter: brightness(95%);
}

.card img {
  height: 200px;
  object-fit: cover;
  pointer-events: none
}

.card-title {
  font-size: 18px;
}

/** HACK: Removing the underline from text of the card that is wrapped by router-link */
a {
  text-decoration: none;
}
</style>