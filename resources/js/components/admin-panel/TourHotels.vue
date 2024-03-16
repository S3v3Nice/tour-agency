<script setup lang="ts">
import {onMounted, ref} from 'vue'
import {Modal} from 'bootstrap'
import axios, {AxiosError} from 'axios'
import type {TourCity, TourHotel} from '@/types'
import {toast} from 'vue3-toastify'
import {getErrorMessageByCode} from '@/helpers'

const apiUrl = 'tour-hotels'
const cityApiUrl = 'tour-cities'

const isLoading = ref<boolean>(false)
const isProcessingAdd = ref<boolean>(false)
const isProcessingEdit = ref<boolean>(false)
const isProcessingDelete = ref<boolean>(false)
const records = ref<TourHotel[]>([])
const editedRecord = ref<TourHotel>({})
const addRecordForm = ref<Modal>({})
const editRecordForm = ref<Modal>({})
const deleteRecordForm = ref<Modal>({})
const formErrors = ref<string[][]>([])

const cities = ref<TourCity[]>([])

onMounted(() => {
    load()
    loadCities()
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

function loadCities() {
    axios.get(`/api/${cityApiUrl}`).then((response) => {
        if (response.data.success) {
            cities.value = response.data.records
        } else {
            if (response.data.message) {
                toast.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toast.error(getErrorMessageByCode(error.response!.status))
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
            toast.success(`Отель '${editedRecord.value.name}' успешно добавлен.`)
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

function showEditForm(record: TourHotel) {
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
            toast.success(`Отель '${editedRecord.value.name}' успешно отредактирован.`)
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

function showDeleteForm(record: TourHotel) {
    deleteRecordForm.value.show()
    editedRecord.value = {...record}
}

function confirmDeleteRecord() {
    isProcessingDelete.value = true

    axios.delete(`/api/${apiUrl}/${editedRecord.value.id}`).then((response) => {
        if (response.data.success) {
            toast.success(`Отель '${editedRecord.value.name}' успешно удален.`)
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
                <th scope="col">Название</th>
                <th scope="col">Город</th>
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
                <td>{{ record.name }}</td>
                <td>{{ record.city!.name }}</td>
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

        <!-- Модальное окно для добавления отеля -->
        <div class="modal fade" id="addRecordForm" tabindex="-1" aria-labelledby="addRecordFormLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addRecordFormLabel">Добавление отеля</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submitAddRecord">
                            <div class="mb-3">
                                <label for="addRecordName" class="form-label">Название</label>
                                <input v-model="editedRecord.name" type="text" class="form-control" id="addRecordName"
                                       :class="{ 'is-invalid': 'name' in formErrors }">
                                <span v-if="'name' in formErrors" class="invalid-feedback mb-3" role="alert">
                                    {{ formErrors['name'][0] }}
                                </span>
                            </div>
                            <div class="mb-3">
                                <label for="addRecordCity" class="form-label">Город</label>
                                <select v-model="editedRecord.city_id" class="form-select" id="addRecordCity"
                                        :class="{ 'is-invalid': 'city_id' in formErrors }">
                                    <option v-for="city in cities" :key="city.id" :value="city.id">{{
                                            city.name
                                        }}
                                    </option>
                                </select>
                                <span v-if="'city_id' in formErrors" class="invalid-feedback mb-3" role="alert">
                                    {{ formErrors['city_id'][0] }}
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

        <!-- Модальное окно для редактирования отеля -->
        <div class="modal fade" id="editRecordForm" tabindex="-1" aria-labelledby="editFormLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editFormLabel">Редактирование отеля</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submitEditRecord">
                            <div class="mb-3">
                                <label for="editRecordName" class="form-label">Название</label>
                                <input v-model="editedRecord.name" type="text" class="form-control" id="editRecordName"
                                       :class="{ 'is-invalid': 'name' in formErrors }">
                                <span v-if="'name' in formErrors" class="invalid-feedback mb-3" role="alert">
                                    {{ formErrors['name'][0] }}
                                </span>
                            </div>
                            <div class="mb-3">
                                <label for="editRecordCity" class="form-label">Город</label>
                                <select v-model="editedRecord.city_id" class="form-select" id="editRecordCity">
                                    <option v-for="city in cities" :key="city.id" :value="city.id">{{
                                            city.name
                                        }}
                                    </option>
                                </select>
                                <span v-if="'city_id' in formErrors" class="invalid-feedback mb-3" role="alert">
                                    {{ formErrors['city_id'][0] }}
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

        <!-- Модальное окно для удаления отеля -->
        <div class="modal fade" id="confirmDeleteRecordForm" tabindex="-1" aria-labelledby="confirmDeleteModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmDeleteModalLabel">Удаление отеля</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Вы уверены, что хотите удалить отель '{{ editedRecord.name }}'?
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
