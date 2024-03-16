<script setup lang="ts"></script>

<template>
    <div class="d-flex flex-column justify-content-center align-items-center">
        <h1 class="mb-3">Упс! Страница не найдена</h1>
        <p class="lead mb-4">Извините, запрашиваемая страница не была найдена.</p>
        <router-link :to="{ name: 'home' }" class="btn btn-primary">Вернуться на главную страницу</router-link>
    </div>
</template>

<style scoped>

</style>
