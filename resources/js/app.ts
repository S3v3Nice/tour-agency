import '@/bootstrap'
import {createApp} from 'vue'
import App from '@/components/App.vue'
import router from '@/router/index'
import {createPinia} from 'pinia'
import Vue3Toasity from 'vue3-toastify'
import VueDatePicker from '@vuepic/vue-datepicker'

const pinia = createPinia()
const app = createApp(App)

app.use(router)
app.use(pinia)
app.use(
    Vue3Toasity,
    {
        theme: 'colored',
    },
)

app.component('VueDatePicker', VueDatePicker);

app.mount('#app')
