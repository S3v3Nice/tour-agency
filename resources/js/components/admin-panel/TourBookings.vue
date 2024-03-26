<script setup lang="ts">
import {onMounted, ref} from 'vue'
import {Modal} from 'bootstrap'
import axios, {AxiosError} from 'axios'
import {formatDateTime, getErrorMessageByCode} from '@/helpers'
import type {Tour, TourBooking} from '@/types'
import {toast} from 'vue3-toastify'

const apiUrl = 'tour-bookings'

const isLoading = ref<boolean>(false)
const isProcessingVerify = ref<boolean>(false)
const isProcessingDelete = ref<boolean>(false)
const records = ref<TourBooking[]>([])
const editedRecord = ref<TourBooking>({})
const verifyBookingForm = ref<Modal>({})
const deleteRecordForm = ref<Modal>({})
const formErrors = ref<string[][]>([])

onMounted(() => {
    load()
    verifyBookingForm.value = Modal.getOrCreateInstance('#verifyBookingForm', {})
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

function showVerifyBookingForm(record: Tour) {
    formErrors.value = {}
    editedRecord.value = {...record}
    verifyBookingForm.value.show()
}

function verifyBooking() {
    isProcessingVerify.value = true

    axios.put(`/api/${apiUrl}/${editedRecord.value.id}`).then((response) => {
        if (response.data.success) {
            toast.success('Запись на тур успешно подтверждена.')
            load()
        } else {
            if (response.data.message) {
                toast.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toast.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        verifyBookingForm.value.hide()
        isProcessingVerify.value = false
    })
}

function showDeleteForm(id) {
    deleteRecordForm.value.show()
    editedRecord.value = {id: id}
}

function confirmDeleteRecord() {
    isProcessingDelete.value = true

    axios.delete(`/api/${apiUrl}/${editedRecord.value.id}`).then((response) => {
        if (response.data.success) {
            toast.success('Запись на тур успешно удалена.')
            load()
        } else {
            if (response.data.message) {
                toast.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toast.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        deleteRecordForm.value.hide()
        isProcessingDelete.value = false
    })
}

</script>

<template>
    <div>
        <!--  Табличная часть-->
        <table class="table mt-3">
            <thead class="thead-dark">
            <tr>
                <th scope="col">Пользователь</th>
                <th scope="col">Тур</th>
                <th scope="col">Дата записи</th>
                <th scope="col">Статус</th>
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
                <td>{{ record.user!.email }}</td>
                <td>{{ record.tour!.hotel!.name }} ({{ record.tour!.hotel!.city!.name }},
                    {{ record.tour!.hotel!.city!.country!.name }}), {{ formatDateTime(record.tour!.start_date!) }}
                </td>
                <td>{{ formatDateTime(record.created_at!) }}</td>
                <td>
                    <span class="badge" :class="{'bg-secondary': !record.is_verified, 'bg-success': record.is_verified}">
                        {{ record.is_verified ? 'Подтверждено' : 'На рассмотрении' }}
                    </span>
                </td>
                <td>
                    <button v-if="!record.is_verified" @click="showVerifyBookingForm(record)"
                            class="btn btn-sm btn-success me-2">
                        <i class="bi bi-check"></i>
                    </button>
                    <button @click="showDeleteForm(record.id)" class="btn btn-sm btn-danger">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </td>
            </tr>
            </tbody>
        </table>

        <!-- Модальное окно для подтверждения записи на тур -->
        <div class="modal fade" id="verifyBookingForm" tabindex="-1" aria-labelledby="verifyBookingModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="verifyBookingModalLabel">Подтверждение записи</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Вы уверены, что хотите подтвердить эту запись на тур?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button @click="verifyBooking" type="button" class="btn btn-primary">Подтвердить</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно для удаления записи -->
        <div class="modal fade" id="confirmDeleteRecordForm" tabindex="-1" aria-labelledby="confirmDeleteModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmDeleteModalLabel">Удаление записи</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                                :disabled="isProcessingVerify"></button>
                    </div>
                    <div class="modal-body">
                        Вы уверены, что хотите удалить эту запись на тур?
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
