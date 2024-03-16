<script setup lang="ts">
import 'bootstrap-icons/font/bootstrap-icons.css'
import {useRoute} from 'vue-router'
import {ref, watch} from 'vue'

interface AdminMenuSection {
    name: string
    icon: string
    route: string
}

const route = useRoute()
const isLoadingSection = ref(false)
const sections = ref<AdminMenuSection[]>([
    {
        name: 'Аналитика',
        icon: 'bi bi-graph-up',
        route: 'admin.analytics',
    },
    {
        name: 'Страны туров',
        icon: 'bi bi-flag',
        route: 'admin.countries',
    },
    {
        name: 'Города туров',
        icon: 'bi bi-building',
        route: 'admin.cities',
    },
    {
        name: 'Отели',
        icon: 'bi bi-house',
        route: 'admin.hotels',
    },
    {
        name: 'Туры',
        icon: 'bi bi-airplane',
        route: 'admin.tours',
    },
    {
        name: 'Записи на туры',
        icon: 'bi bi-calendar',
        route: 'admin.bookings',
    },
    {
        name: 'Платежи',
        icon: 'bi bi-credit-card',
        route: 'admin.payments',
    },
])

const currentSection = ref<AdminMenuSection>(getActualCurrentSection())

watch(route, () => {
    isLoadingSection.value = false
    currentSection.value = getActualCurrentSection()
})

function getActualCurrentSection() {
    return sections.value.find(item => item.route === route.name)!
}

function onSectionSelect(section: AdminMenuSection) {
    if (section !== currentSection.value) {
        currentSection.value = section
        isLoadingSection.value = true
    }
}

</script>

<template>
    <div class="d-flex gap-4 align-items-start">
        <nav id="sidebar" class="" style="width: 18rem;">
            <div class="sidebar-header">
                <h3>Управление</h3>
            </div>

            <ul class="list-unstyled components">
                <li v-for="(section, index) in sections" :key="index">
                    <router-link :to="{ name: section.route }" @click="onSectionSelect(section)">
                        <button class="btn btn-block text-left"
                                :class="{ active: currentSection.name == section.name }">
                            <i :class="section.icon"></i>
                            <span class="ms-2">{{ section.name }}</span>
                        </button>
                    </router-link>
                </li>
            </ul>
        </nav>
        <div class="overflow-auto" style="flex: 1 1 0;">
            <router-view v-if="!isLoadingSection"/>
            <div v-else>
                <h5 class="card-title placeholder-glow">
                    <span class="placeholder col-6"></span>
                </h5>
            </div>
        </div>
    </div>
</template>

<style scoped>
#sidebar {
    height: 100%;
}

.sidebar-header {
    text-align: left;
    margin-bottom: 20px;
}

.components {
    padding: 0;
}

.btn-block {
    width: 100%;
    height: 60px;
}

.text-left {
    text-align: left !important;
}

.btn-block:hover {
    background-color: #407bff;
    color: #fff;
}

.active {
    background-color: #407bff;
    color: #fff;
}
</style>
