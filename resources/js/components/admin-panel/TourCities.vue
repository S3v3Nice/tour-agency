<script setup lang="ts">
import {onMounted, ref} from 'vue';
import {Modal} from 'bootstrap';
import axios from 'axios';
import {getAbsolutePath} from "../../helpers.js";
import {Country} from "./TourCountries.vue";

interface City {
  id?: bigint
  country_id?: bigint
  country?: Country
  name?: string
  description?: string
  image_path?: string
  image?: File
}

const apiUrl = 'tour-city';
const countryApiUrl = 'tour-country';

const isLoading = ref<boolean>(false)
const items = ref<City[]>([])
const editedItem = ref<City>({})
const addItemForm = ref<Modal>({})
const editItemForm = ref<Modal>({})
const deleteItemForm = ref<Modal>({})
const formErrors = ref<string[][]>([])

const countries = ref<Country[]>([])

onMounted(() => {
  load()
  loadCountries()
  addItemForm.value = Modal.getOrCreateInstance('#addItemForm', {})
  editItemForm.value = Modal.getOrCreateInstance('#editItemForm', {})
  deleteItemForm.value = Modal.getOrCreateInstance('#confirmDeleteItemForm', {})
})

async function load() {
  isLoading.value = true
  await axios.get(`/api/${apiUrl}`).then((response) => {
    items.value = response.data
  }).finally(() => {
    isLoading.value = false
  })
}

async function loadCountries() {
  await axios.get(`/api/${countryApiUrl}`).then((response) => {
    countries.value = response.data
  })
}

function showAddForm() {
  formErrors.value = {}
  editedItem.value = {}
  addItemForm.value.show()
}

function submitAddItem() {
  const formData = new FormData()
  Object.keys(editedItem.value).forEach(key => formData.append(key, editedItem.value[key]))

  axios.post(`/api/${apiUrl}`, formData).then((response) => {
    if (!response.data.success) {
      formErrors.value = response.data.errors
      return
    }

    load()
    editedItem.value = {}
    addItemForm.value.hide()
  })
}

function showEditForm(item: City) {
  formErrors.value = {}
  editedItem.value = {...item}
  editItemForm.value.show()
}

function submitEditItem() {
  let formData = new FormData()
  Object.keys(editedItem.value).forEach(key => formData.append(key, editedItem.value[key]))

  axios.put(`/api/${apiUrl}/${editedItem.value.id}`, formData).then((response) => {
    if (!response.data.success) {
      formErrors.value = response.data.errors
      return
    }

    load()
    editedItem.value = {}
    editItemForm.value.hide()
  })
}

function showDeleteForm(id) {
  deleteItemForm.value.show()
  editedItem.value = {id}
}

function confirmDeleteItem() {
  axios.delete(`/api/${apiUrl}/${editedItem.value.id}`).then(() => {
    load()
    editedItem.value = {}
    deleteItemForm.value.hide()
  })
}

function onImageUpload(event) {
  const file = event.target.files[0]
  if (file) {
    editedItem.value.image = file
  }
}
</script>

<template>
  <div>
    <button @click="showAddForm" class="btn btn-primary mb-3 float-end">
      <i class="bi bi-plus"></i> Добавить
    </button>

    <!--  Табличная часть-->
    <table class="table mt-3">
      <thead class="thead-dark">
      <tr>
        <th scope="col">Изображение</th>
        <th scope="col">Название</th>
        <th scope="col">Страна</th>
        <th scope="col">Действия</th>
      </tr>
      </thead>

      <tbody>
      <tr v-if="isLoading">
        <td colspan="100%">
          <div class="d-flex justify-content-center align-items-center">
            <div class="spinner-border" role="status">
              <span class="visually-hidden">Загрузка...</span>
            </div>
          </div>
        </td>
      </tr>
      <tr v-else v-for="item in items" :key="item.id">
        <td>
          <img :src="getAbsolutePath(item.image_path)" :alt="item.name">
        </td>
        <td>{{ item.name }}</td>
        <td>{{ item.country.name }}</td>
        <td>
          <button @click="showEditForm(item)" class="btn btn-sm btn-success me-2">
            <i class="bi bi-pencil-fill"></i>
          </button>
          <button @click="showDeleteForm(item.id)" class="btn btn-sm btn-danger">
            <i class="bi bi-trash-fill"></i>
          </button>
        </td>
      </tr>
      </tbody>
    </table>

    <!-- Модальное окно для добавления элемента -->
    <div class="modal fade" id="addItemForm" tabindex="-1" aria-labelledby="addItemFormLabel"
         aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addItemFormLabel">Добавить элемент</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitAddItem">
              <div class="mb-3">
                <label for="addItemName" class="form-label">Имя</label>
                <input v-model="editedItem.name" type="text" class="form-control" id="addItemName"
                       :class="{ 'is-invalid': 'name' in formErrors }">
                <span v-if="'name' in formErrors" class="invalid-feedback mb-3" role="alert">
                  {{ formErrors['name'][0] }}
                </span>
              </div>
              <div class="mb-3">
                <label for="addItemCountry" class="form-label">Страна</label>
                <select v-model="editedItem.country_id" class="form-select" id="addItemCountry"
                        :class="{ 'is-invalid': 'country_id' in formErrors }">
                  <option v-for="country in countries" :key="country.id" :value="country.id">{{ country.name }}</option>
                </select>
                <span v-if="'country_id' in formErrors" class="invalid-feedback mb-3" role="alert">
                  {{ formErrors['country_id'][0] }}
                </span>
              </div>
              <div class="mb-3">
                <label for="addItemDescription" class="form-label">Описание</label>
                <textarea v-model="editedItem.description" class="form-control" id="addItemDescription"
                          :class="{ 'is-invalid': 'description' in formErrors }"></textarea>
                <span v-if="'description' in formErrors" class="invalid-feedback mb-3" role="alert">
                  {{ formErrors['description'][0] }}
                </span>
              </div>
              <div class="mb-3">
                <label for="addItemImage" class="form-label">Изображение</label>
                <input type="file" @change="onImageUpload" class="form-control" id="addItemImage"
                       :class="{ 'is-invalid': 'image' in formErrors }">
                <span v-if="'image' in formErrors" class="invalid-feedback mb-3" role="alert">
                  {{ formErrors['image'][0] }}
                </span>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" class="btn btn-primary">Добавить</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Модальное окно для редактирования элемента -->
    <div class="modal fade" id="editItemForm" tabindex="-1" aria-labelledby="editFormLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editFormLabel">Редактировать</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitEditItem">
              <div class="mb-3">
                <label for="editItemName" class="form-label">Имя</label>
                <input v-model="editedItem.name" type="text" class="form-control" id="editItemName"
                       :class="{ 'is-invalid': 'name' in formErrors }">
                <span v-if="'name' in formErrors" class="invalid-feedback mb-3" role="alert">
                  {{ formErrors['name'][0] }}
                </span>
              </div>
              <div class="mb-3">
                <label for="editItemCountry" class="form-label">Страна</label>
                <select v-model="editedItem.country_id" class="form-select" id="editItemCountry">
                  <option v-for="country in countries" :key="country.id" :value="country.id">{{ country.name }}</option>
                </select>
                <span v-if="'country_id' in formErrors" class="invalid-feedback mb-3" role="alert">
                  {{ formErrors['country_id'][0] }}
                </span>
              </div>
              <div class="mb-3">
                <label for="editItemDescription" class="form-label">Описание</label>
                <textarea v-model="editedItem.description" class="form-control" id="editItemDescription"
                          :class="{ 'is-invalid': 'description' in formErrors }"></textarea>
                <span v-if="'description' in formErrors" class="invalid-feedback mb-3" role="alert">
                  {{ formErrors['description'][0] }}
                </span>
              </div>
              <div class="mb-3">
                <label for="editItemImage" class="form-label">Изображение</label>
                <input type="file" @change="onImageUpload" class="form-control" id="editItemImage"
                       :class="{ 'is-invalid': 'image' in formErrors }">
                <span v-if="'image' in formErrors" class="invalid-feedback mb-3" role="alert">
                  {{ formErrors['image'][0] }}
                </span>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" class="btn btn-primary">Сохранить изменения</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Модальное окно для подтверждения удаления элемента -->
    <div class="modal fade" id="confirmDeleteItemForm" tabindex="-1" aria-labelledby="confirmDeleteModalLabel"
         aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="confirmDeleteModalLabel">Подтверждение удаления</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Вы уверены, что хотите удалить этот элемент?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
            <button @click="confirmDeleteItem" type="button" class="btn btn-danger">Удалить</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.table {
  width: 100%;
  margin-bottom: 1rem;
  color: #212529;
  border-collapse: collapse;
  border-spacing: 0;
}

.table th,
.table td {
  padding: 0.75rem;
  vertical-align: top;
  border-top: 1px solid #dee2e6;
}

.table thead th {
  vertical-align: bottom;
  border-bottom: 2px solid #dee2e6;
}

.table tbody + tbody {
  border-top: 2px solid #dee2e6;
}

.table .thead-dark th {
  color: #fff;
  background-color: #709CFF;
}

.table .btn {
  margin-right: 2px;
}

.table img {
  max-width: 60px;
  max-height: 60px;
}

.modal-content {
  background-color: #fff;
  border: 1px solid rgba(0, 0, 0, 0.125);
  border-radius: 0.25rem;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.modal-header {
  padding: 1rem;
  border-bottom: 1px solid #dee2e6;
}

.modal-body {
  padding: 1rem;
}

.modal-footer {
  padding: 1rem;
  border-top: 1px solid #dee2e6;
}

.modal-footer .btn {
  margin-right: 5px;
}

.spinner-border {
  width: 3rem;
  height: 3rem;
  border: 0.4rem solid rgba(0, 0, 0, 0.125);
  border-right: 0.4rem solid #709CFF;
  border-radius: 50%;
  animation: spin 0.75s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
