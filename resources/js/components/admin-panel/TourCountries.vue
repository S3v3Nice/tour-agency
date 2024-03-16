<script setup lang="ts">
import {onMounted, ref} from 'vue'
import {Modal} from 'bootstrap'
import axios, {AxiosError} from 'axios'
import {getAbsolutePath, getErrorMessageByCode} from '@/helpers'
import type {TourCountry} from '@/types'
import {toast} from 'vue3-toastify'

const apiUrl = 'tour-countries'

const isLoading = ref<boolean>(false)
const isProcessingAdd = ref<boolean>(false)
const isProcessingEdit = ref<boolean>(false)
const isProcessingDelete = ref<boolean>(false)
const records = ref<TourCountry[]>([])
const editedRecord = ref<TourCountry>({})
const addRecordForm = ref<Modal>({})
const editRecordForm = ref<Modal>({})
const deleteRecordForm = ref<Modal>({})
const formErrors = ref<string[][]>([])

onMounted(() => {
    load()
    addRecordForm.value = Modal.getOrCreateInstance('#addRecordForm', {})
    editRecordForm.value = Modal.getOrCreateInstance('#editRecordForm', {})
    deleteRecordForm.value = Modal.getOrCreateInstance('#confirmDeleteRecordForm', {})
})

function load() {
    isLoading.value = true

    axios.get(`/api/${apiUrl}`).then((response) => {
        if (response.data.success) {
            records.value = response.data.records
        } else {
            if (response.data.message) {
                toast.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toast.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isLoading.value = false
    })
}

function showAddForm() {
    formErrors.value = {}
    editedRecord.value = {}
    addRecordForm.value.show()
}

function submitAddRecord() {
    isProcessingAdd.value = true

    const formData = new FormData()
    Object.keys(editedRecord.value).forEach(key => formData.append(key, editedRecord.value[key]))

    axios.post(`/api/${apiUrl}`, formData).then((response) => {
        if (response.data.success) {
            toast.success(`Страна '${editedRecord.value.name}' успешно добавлена.`)
            addRecordForm.value.hide()
            load()
        } else {
            if (response.data.errors) {
                formErrors.value = response.data.errors
            }
            if (response.data.message) {
                toast.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toast.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isProcessingAdd.value = false
    })
}

function showEditForm(record: TourCountry) {
    formErrors.value = {}
    editedRecord.value = {...record}
    editRecordForm.value.show()
}

function submitEditRecord() {
    isProcessingEdit.value = true

    const formData = new FormData()
    Object.keys(editedRecord.value).forEach(key => formData.append(key, editedRecord.value[key]))

    axios.put(`/api/${apiUrl}/${editedRecord.value.id}`, formData).then((response) => {
        if (response.data.success) {
            toast.success(`Страна '${editedRecord.value.name}' успешно отредактирована.`)
            editRecordForm.value.hide()
            load()
        } else {
            if (response.data.errors) {
                formErrors.value = response.data.errors
            }
            if (response.data.message) {
                toast.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toast.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isProcessingEdit.value = false
    })
}

function showDeleteForm(record: TourCountry) {
    deleteRecordForm.value.show()
    editedRecord.value = {...record}
}

function confirmDeleteRecord() {
    isProcessingDelete.value = true

    axios.delete(`/api/${apiUrl}/${editedRecord.value.id}`).then((response) => {
        if (response.data.success) {
            toast.success(`Страна '${editedRecord.value.name}' успешно удалена.`)
            load()
        } else {
            if (response.data.message) {
                toast.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toast.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isProcessingDelete.value = false
        deleteRecordForm.value.hide()
    })
}

function onImageUpload(event) {
    const file = event.target.files[0]
    if (file) {
        editedRecord.value.image = file
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
                <th scope="col">Ярлык</th>
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
            <tr v-else v-for="record in records" :key="record.id">
                <td>
                    <img :src="getAbsolutePath(record.image_path)" :alt="record.name">
                </td>
                <td>{{ record.name }}</td>
                <td>{{ record.slug }}</td>
                <td>
                    <button @click="showEditForm(record)" class="btn btn-sm btn-success me-2">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button @click="showDeleteForm(record)" class="btn btn-sm btn-danger">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </td>
            </tr>
            </tbody>
        </table>

        <!-- Модальное окно для добавления страны -->
        <div class="modal fade" id="addRecordForm" tabindex="-1" aria-labelledby="addRecordFormLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addRecordFormLabel">Добавление страны</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submitAddRecord">
                            <div class="mb-3">
                                <label for="addRecordName" class="form-label">Имя</label>
                                <input v-model="editedRecord.name" type="text" class="form-control" id="addRecordName"
                                       :class="{ 'is-invalid': 'name' in formErrors }">
                                <span v-if="'name' in formErrors" class="invalid-feedback mb-3" role="alert">
                                    {{ formErrors['name'][0] }}
                                </span>
                            </div>
                            <div class="mb-3">
                                <label for="addRecordSlug" class="form-label">Ярлык</label>
                                <input v-model="editedRecord.slug" type="text" class="form-control" id="addRecordSlug"
                                       :class="{ 'is-invalid': 'slug' in formErrors }">
                                <span v-if="'slug' in formErrors" class="invalid-feedback mb-3" role="alert">
                                    {{ formErrors['slug'][0] }}
                                </span>
                            </div>
                            <div class="mb-3">
                                <label for="addRecordDescription" class="form-label">Описание</label>
                                <textarea v-model="editedRecord.description" class="form-control"
                                          id="addRecordDescription"
                                          :class="{ 'is-invalid': 'description' in formErrors }"></textarea>
                                <span v-if="'description' in formErrors" class="invalid-feedback mb-3" role="alert">
                                    {{ formErrors['description'][0] }}
                                </span>
                            </div>
                            <div class="mb-3">
                                <label for="addRecordImage" class="form-label">Изображение</label>
                                <input type="file" @change="onImageUpload" class="form-control" id="addRecordImage"
                                       :class="{ 'is-invalid': 'image' in formErrors }">
                                <span v-if="'image' in formErrors" class="invalid-feedback mb-3" role="alert">
                                    {{ formErrors['image'][0] }}
                                </span>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                <button type="submit" class="btn btn-primary" :disabled="isProcessingAdd">Добавить
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно для редактирования страны -->
        <div class="modal fade" id="editRecordForm" tabindex="-1" aria-labelledby="editFormLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editFormLabel">Редактирование страны</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submitEditRecord">
                            <div class="mb-3">
                                <label for="editRecordName" class="form-label">Имя</label>
                                <input v-model="editedRecord.name" type="text" class="form-control" id="editRecordName"
                                       :class="{ 'is-invalid': 'name' in formErrors }">
                                <span v-if="'name' in formErrors" class="invalid-feedback mb-3" role="alert">
                                    {{ formErrors['name'][0] }}
                                </span>
                            </div>
                            <div class="mb-3">
                                <label for="editRecordSlug" class="form-label">Ярлык</label>
                                <input v-model="editedRecord.slug" type="text" class="form-control" id="editRecordSlug"
                                       :class="{ 'is-invalid': 'slug' in formErrors }">
                                <span v-if="'slug' in formErrors" class="invalid-feedback mb-3" role="alert">
                                    {{ formErrors['slug'][0] }}
                                </span>
                            </div>
                            <div class="mb-3">
                                <label for="editRecordDescription" class="form-label">Описание</label>
                                <textarea v-model="editedRecord.description" class="form-control"
                                          id="editRecordDescription"
                                          :class="{ 'is-invalid': 'description' in formErrors }"></textarea>
                                <span v-if="'description' in formErrors" class="invalid-feedback mb-3" role="alert">
                                    {{ formErrors['description'][0] }}
                                </span>
                            </div>
                            <div class="mb-3">
                                <label for="editRecordImage" class="form-label">Изображение</label>
                                <input type="file" @change="onImageUpload" class="form-control" id="editRecordImage"
                                       :class="{ 'is-invalid': 'image' in formErrors }">
                                <span v-if="'image' in formErrors" class="invalid-feedback mb-3" role="alert">
                                    {{ formErrors['image'][0] }}
                                </span>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                <button type="submit" class="btn btn-primary" :disabled="isProcessingEdit">Сохранить
                                    изменения
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно для удаления страны -->
        <div class="modal fade" id="confirmDeleteRecordForm" tabindex="-1" aria-labelledby="confirmDeleteModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmDeleteModalLabel">Удаление страны</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Вы уверены, что хотите удалить страну '{{ editedRecord.name }}'?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button @click="confirmDeleteRecord" type="button" class="btn btn-danger"
                                :disabled="isProcessingDelete">Удалить
                        </button>
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
