<script setup lang="ts">
import {onMounted, ref} from 'vue'
import {Modal} from 'bootstrap'
import axios, {AxiosError} from 'axios'
import {formatDateTime, getErrorMessageByCode} from '@/helpers'
import type {Tour, TourHotel} from '@/types'
import {toast} from 'vue3-toastify'

const apiUrl = 'tours'
const hotelApiUrl = 'tour-hotels'

const isLoading = ref<boolean>(false)
const isProcessingAdd = ref<boolean>(false)
const isProcessingEdit = ref<boolean>(false)
const isProcessingDelete = ref<boolean>(false)
const records = ref<Tour[]>([])
const editedRecord = ref<Tour>({})
const addRecordForm = ref<Modal>({})
const editRecordForm = ref<Modal>({})
const deleteRecordForm = ref<Modal>({})
const formErrors = ref<string[][]>([])

const hotels = ref<TourHotel[]>([])

onMounted(() => {
    load()
    loadHotels()
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

function loadHotels() {
    axios.get(`/api/${hotelApiUrl}`).then((response) => {
        if (response.data.success) {
            hotels.value = response.data.records
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
            toast.success(`Тур успешно добавлен.`)
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

function showEditForm(record: Tour) {
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
            toast.success(`Тур успешно отредактирован.`)
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

function showDeleteForm(record: Tour) {
    deleteRecordForm.value.show()
    editedRecord.value = {...record}
}

function confirmDeleteRecord() {
    isProcessingDelete.value = true

    axios.delete(`/api/${apiUrl}/${editedRecord.value.id}`).then((response) => {
        if (response.data.success) {
            toast.success(`Тур успешно удален.`)
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
                <th scope="col">Отель</th>
                <th scope="col">Период</th>
                <th scope="col">Кол-во участников</th>
                <th scope="col">Стоимость</th>
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
                <td>{{
                        `${record.hotel!.name} (${record.hotel!.city!.name}, ${record.hotel!.city!.country!.name})`
                    }}
                </td>
                <td>{{ `${formatDateTime(record.start_date!)} - ${formatDateTime(record.end_date!)}` }}</td>
                <td>{{ record.max_participant_count }}</td>
                <td>{{ record.adult_price }} ₽</td>
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

        <!-- Модальное окно для добавления тура -->
        <div class="modal fade" id="addRecordForm" tabindex="-1" aria-labelledby="addRecordFormLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addRecordFormLabel">Добавление тура</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submitAddRecord">
                            <div class="mb-3">
                                <label for="addRecordHotel" class="form-label">Отель</label>
                                <select v-model="editedRecord.hotel_id" class="form-select" id="addRecordHotel"
                                        :class="{ 'is-invalid': 'hotel_id' in formErrors }">
                                    <option v-for="hotel in hotels" :key="hotel.id" :value="hotel.id">
                                        {{ `${hotel.name} (${hotel.city!.name}, ${hotel.city!.country!.name})` }}
                                    </option>
                                </select>
                                <span v-if="'hotel_id' in formErrors" class="invalid-feedback mb-3" role="alert">
                                    {{ formErrors['hotel_id'][0] }}
                                </span>
                            </div>
                            <div class="mb-3">
                                <label for="addRecordStartDate" class="form-label">Дата начала</label>
                                <input v-model="editedRecord.start_date" type="datetime-local" class="form-control"
                                       id="addRecordStartDate"
                                       :class="{ 'is-invalid': 'start_date' in formErrors }">
                                <span v-if="'start_date' in formErrors" class="invalid-feedback mb-3" role="alert">
                                    {{ formErrors['start_date'][0] }}
                                </span>
                            </div>
                            <div class="mb-3">
                                <label for="addRecordEndDate" class="form-label">Дата окончания</label>
                                <input v-model="editedRecord.end_date" type="datetime-local" class="form-control"
                                       id="addRecordEndDate"
                                       :class="{ 'is-invalid': 'end_date' in formErrors }">
                                <span v-if="'end_date' in formErrors" class="invalid-feedback mb-3" role="alert">
                                    {{ formErrors['end_date'][0] }}
                                </span>
                            </div>
                            <div class="mb-3">
                                <label for="addRecordMaxParticipantCount" class="form-label">Количество
                                    участников</label>
                                <input v-model="editedRecord.max_participant_count" type="number" class="form-control"
                                       id="addRecordMaxParticipantCount"
                                       :class="{ 'is-invalid': 'max_participant_count' in formErrors }">
                                <span v-if="'max_participant_count' in formErrors" class="invalid-feedback mb-3"
                                      role="alert"
                                >
                                    {{ formErrors['max_participant_count'][0] }}
                                </span>
                            </div>
                            <div class="mb-3">
                                <label for="addRecordAdultPrice" class="form-label">Стоимость за 1 взрослого</label>
                                <input v-model="editedRecord.adult_price" type="number" step="any" class="form-control"
                                       id="addRecordAdultPrice"
                                       :class="{ 'is-invalid': 'adult_price' in formErrors }">
                                <span v-if="'adult_price' in formErrors" class="invalid-feedback mb-3" role="alert">
                                    {{ formErrors['adult_price'][0] }}
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

        <!-- Модальное окно для редактирования тура -->
        <div class="modal fade" id="editRecordForm" tabindex="-1" aria-labelledby="editFormLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editFormLabel">Редактирование тура</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submitEditRecord">
                            <div class="mb-3">
                                <label for="editRecordHotel" class="form-label">Отель</label>
                                <select v-model="editedRecord.hotel_id" class="form-select" id="editRecordHotel">
                                    <option v-for="hotel in hotels" :key="hotel.id" :value="hotel.id">
                                        {{ `${hotel.name} (${hotel.city!.name}, ${hotel.city!.country!.name})` }}
                                    </option>
                                </select>
                                <span v-if="'hotel_id' in formErrors" class="invalid-feedback mb-3" role="alert">
                                    {{ formErrors['hotel_id'][0] }}
                                </span>
                            </div>
                            <div class="mb-3">
                                <label for="editRecordStartDate" class="form-label">Дата начала</label>
                                <input v-model="editedRecord.start_date" type="datetime-local" class="form-control"
                                       id="editRecordStartDate"
                                       :class="{ 'is-invalid': 'start_date' in formErrors }">
                                <span v-if="'start_date' in formErrors" class="invalid-feedback mb-3" role="alert">
                                    {{ formErrors['start_date'][0] }}
                                </span>
                            </div>
                            <div class="mb-3">
                                <label for="editRecordEndDate" class="form-label">Дата окончания</label>
                                <input v-model="editedRecord.end_date" type="datetime-local" class="form-control"
                                       id="editRecordEndDate"
                                       :class="{ 'is-invalid': 'end_date' in formErrors }">
                                <span v-if="'end_date' in formErrors" class="invalid-feedback mb-3" role="alert">
                                    {{ formErrors['end_date'][0] }}
                                </span>
                            </div>
                            <div class="mb-3">
                                <label for="editRecordMaxParticipantCount" class="form-label">Количество
                                    участников</label>
                                <input v-model="editedRecord.max_participant_count" type="number" class="form-control"
                                       id="editRecordMaxParticipantCount"
                                       :class="{ 'is-invalid': 'max_participant_count' in formErrors }">
                                <span v-if="'max_participant_count' in formErrors" class="invalid-feedback mb-3"
                                      role="alert"
                                >
                                    {{ formErrors['max_participant_count'][0] }}
                                </span>
                            </div>
                            <div class="mb-3">
                                <label for="editRecordAdultPrice" class="form-label">Стоимость за 1 взрослого</label>
                                <input v-model="editedRecord.adult_price" type="number" step="any" class="form-control"
                                       id="editRecordAdultPrice"
                                       :class="{ 'is-invalid': 'adult_price' in formErrors }">
                                <span v-if="'adult_price' in formErrors" class="invalid-feedback mb-3" role="alert">
                                    {{ formErrors['adult_price'][0] }}
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

        <!-- Модальное окно для удаления тура -->
        <div class="modal fade" id="confirmDeleteRecordForm" tabindex="-1" aria-labelledby="confirmDeleteModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmDeleteModalLabel">Удаление тура</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Вы уверены, что хотите удалить этот тур?
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
