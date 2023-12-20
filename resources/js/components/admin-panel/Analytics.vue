<script setup lang="ts">
import {computed, onMounted, ref} from "vue";
import axios from "axios";
import {
  ArcElement,
  BarElement,
  CategoryScale,
  Chart as ChartJS,
  Legend,
  LinearScale,
  LineElement,
  PointElement,
  Title,
  Tooltip
} from 'chart.js'
import {Bar, Doughnut, Pie} from 'vue-chartjs'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, ArcElement, Tooltip, Legend)

const apiUrl = 'analytics'

const tourBookingsCountByCityData = ref([])
const tourBookingsCountByCityPeriod = ref({})

const tourBookingsCountByCountryData = ref([])
const tourBookingsCountByCountryPeriod = ref({})

const tourOccupancyByCityData = ref([])
const tourOccupancyByCityPeriod = ref({})

onMounted(() => {
  loadTourBookingsCountByCityData()
  loadTourBookingsCountByCountryData()
  loadTourOccupancyByCityData()
})

function loadTourBookingsCountByCityData() {
  const params = {
    type: 'tour_bookings_count_by_city',
    start_date: tourBookingsCountByCityPeriod.value.start_date,
    end_date: tourBookingsCountByCityPeriod.value.end_date
  }

  axios.get(`/api/${apiUrl}`, {params: params}).then((response) => {
    tourBookingsCountByCityData.value = response.data
  })
}

function loadTourBookingsCountByCountryData() {
  const params = {
    type: 'tour_bookings_count_by_country',
    start_date: tourBookingsCountByCountryPeriod.value.start_date,
    end_date: tourBookingsCountByCountryPeriod.value.end_date
  }

  axios.get(`/api/${apiUrl}`, {params: params}).then((response) => {
    tourBookingsCountByCountryData.value = response.data
  })
}

function loadTourOccupancyByCityData() {
  const params = {
    type: 'tour_occupancy_by_city',
    start_date: tourOccupancyByCityPeriod.value.start_date,
    end_date: tourOccupancyByCityPeriod.value.end_date
  }

  axios.get(`/api/${apiUrl}`, {params: params}).then((response) => {
    tourOccupancyByCityData.value = response.data
  })
}

const tourBookingsCountByCityChartData = computed(() => {
  return {
    labels: Object.keys(tourBookingsCountByCityData.value),
    datasets: [
      {
        label: 'Количество записей на туры',
        backgroundColor: [
          '#41B883', '#E46651', '#00D8FF', '#DD1B16',
          '#5E2D79', '#FF8C42', '#48CAE4', '#F3722C',
          '#43AA8B', '#A8DADC'
        ],
        data: Object.values(tourBookingsCountByCityData.value)
      }
    ]
  }
})

const tourBookingsCountByCountryChartData = computed(() => {
  return {
    labels: Object.keys(tourBookingsCountByCountryData.value),
    datasets: [
      {
        label: 'Количество записей на туры',
        backgroundColor: [
          '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
          '#D7263D', '#9D75CB', '#C5D86D', '#F49D37',
          '#A2D5F2', '#78DEC7'
        ],
        data: Object.values(tourBookingsCountByCountryData.value)
      }
    ]
  }
})

const tourOccupancyByCityChartData = computed(() => {
  return {
    labels: Object.keys(tourOccupancyByCityData.value),
    datasets: [
      {
        label: 'Заполненность туров',
        backgroundColor: [
          '#E63946', '#F1FAEE', '#A8DADC', '#457B9D',
          '#1D3557', '#F4A261', '#2A9D8F', '#D9BF77',
          '#553C9A', '#4ECDC4'
        ],
        data: Object.values(tourOccupancyByCityData.value)
      }
    ]
  }
})

const tourBookingsCountByCityChartOptions = {
  responsive: false,
  maintainAspectRatio: false
}

const tourBookingsCountByCountryChartOptions = {
  responsive: false,
  maintainAspectRatio: false
}

const tourOccupancyByCityChartOptions = {
  responsive: true,
  maintainAspectRatio: false
}
</script>

<template>
  <div class="d-flex">
    <div class="me-auto mb-5">
      <h5 class="mb-3">Записи на туры по городам</h5>
      <div class="mb-3 d-flex">
        <div class="me-2">
          <label for="tourBookingsCountByCityStartDate">Начало:</label>
          <input
              class="form-control"
              type="date"
              id="tourBookingsCountByCityStartDate"
              v-model="tourBookingsCountByCityPeriod.start_date"
              @change="loadTourBookingsCountByCityData"
          />
        </div>

        <div>
          <label for="tourBookingsCountByCityEndDate">Окончание:</label>
          <input
              class="form-control"
              type="date"
              id="tourBookingsCountByCityEndDate"
              v-model="tourBookingsCountByCityPeriod.end_date"
              @change="loadTourBookingsCountByCityData"
          />
        </div>
      </div>

      <Doughnut :data="tourBookingsCountByCityChartData" :options="tourBookingsCountByCityChartOptions"/>
    </div>

    <div class="me-auto">
      <h5 class="mb-3">Записи на туры по странам</h5>
      <div class="mb-3 d-flex">
        <div class="me-2">
          <label for="tourBookingsCountByCountryStartDate">Начало:</label>
          <input
              class="form-control"
              type="date"
              id="tourBookingsCountByCountryStartDate"
              v-model="tourBookingsCountByCountryPeriod.start_date"
              @change="loadTourBookingsCountByCountryData"
          />
        </div>

        <div>
          <label for="tourBookingsCountByCountryEndDate">Окончание:</label>
          <input
              class="form-control"
              type="date"
              id="tourBookingsCountByCountryEndDate"
              v-model="tourBookingsCountByCountryPeriod.end_date"
              @change="loadTourBookingsCountByCountryData"
          />
        </div>
      </div>

      <Pie :data="tourBookingsCountByCountryChartData" :options="tourBookingsCountByCountryChartOptions"/>
    </div>
  </div>

  <div class="me-auto mb-5">
    <h5 class="mb-3">Заполненность мест в турах по городам</h5>
    <div class="mb-3 d-flex">
      <div class="me-2">
        <label for="tourOccupancyByCityStartDate">Начало:</label>
        <input
            class="form-control"
            type="date"
            id="tourOccupancyByCityStartDate"
            v-model="tourOccupancyByCityPeriod.start_date"
            @change="loadTourOccupancyByCityData"
        />
      </div>

      <div>
        <label for="tourOccupancyByCityEndDate">Окончание:</label>
        <input
            class="form-control"
            type="date"
            id="tourOccupancyByCityEndDate"
            v-model="tourOccupancyByCityPeriod.end_date"
            @change="loadTourOccupancyByCityData"
        />
      </div>
    </div>

    <div class="chart-container" style="height:40vh">
      <Bar :data="tourOccupancyByCityChartData" :options="tourOccupancyByCityChartOptions"/>
    </div>
  </div>
</template>

<style scoped>

</style>
