<script setup>
import Header from "./Header.vue";
import {onMounted} from "vue";
import {useRouter} from "vue-router";

const props = defineProps({
  slug: String
})
const router = useRouter()

onMounted(() => {
  router.currentRoute.value.meta.title = props.slug;
})
</script>

<template>
  <Header></Header>
  <a>{{ slug }}</a>
</template>

<style scoped>

</style>