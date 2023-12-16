<script setup>
import Header from "./Header.vue";
import "bootstrap-icons/font/bootstrap-icons.css";
import {useRoute} from "vue-router";
import {computed} from "vue";

const sections = [
    {
        name: 'Аналитика',
        icon: 'bi bi-graph-up',
        route: 'analytics',
    },
    {
        name: 'Пользователи',
        icon: 'bi bi-person',
        route: 'users',
    },
    {
        name: 'Страны туров',
        icon: 'bi bi-flag',
        route: 'countries',
    },
    {
        name: 'Города туров',
        icon: 'bi bi-building',
        route: 'cities',
    },
    {
        name: 'Отели',
        icon: 'bi bi-house',
        route: 'hotels',
    },
    {
        name: 'Записи на туры',
        icon: 'bi bi-calendar',
        route: 'bookings',
    },
    {
        name: 'Платежи',
        icon: 'bi bi-credit-card',
        route: 'payments',
    },
]

const currentRoute = computed(() => useRoute().name);
</script>

<template>
    <div>
        <Header></Header>
    </div>
    <div class="row">
        <nav id="sidebar" class="col-md-3">
            <div class="sidebar-header">
                <h3>Управление</h3>
            </div>

            <ul class="list-unstyled components">
                <li v-for="(section, index) in sections" :key="index">
                    <router-link :to="{ name: section.route }">
                        <button
                            class="btn btn-block text-left"
                            :class="{ active: currentRoute === section.route }"
                        >
                            <i :class="section.icon"></i>
                            <span class="ms-2">{{ section.name }}</span>
                        </button>
                    </router-link>
                </li>
            </ul>
        </nav>
        <div class="col-md-9">
            <router-view></router-view>
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
