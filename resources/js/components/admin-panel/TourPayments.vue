<script setup lang="ts">
import {onMounted, ref} from 'vue'
import {Modal} from 'bootstrap'
import axios, {AxiosError} from 'axios'
import {formatDateTime, getErrorMessageByCode} from '@/helpers'
import type {TourPayment} from '@/types'
import {toast} from 'vue3-toastify'

const apiUrl = 'tour-payments'

const isLoading = ref<boolean>(false)
const isProcessingDelete = ref<boolean>(false)
const records = ref<TourPayment[]>([])
const editedRecord = ref<TourPayment>({})
const deleteRecordForm = ref<Modal>({})

onMounted(() => {
    load()
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

function showDeleteForm(record: TourPayment) {
    editedRecord.value = {...record}
    deleteRecordForm.value.show()
}

function confirmDeleteRecord() {
    isProcessingDelete.value = true

    axios.delete(`/api/${apiUrl}/${editedRecord.value.id}`).then((response) => {
        if (response.data.success) {
            toast.success(`Платёж успешно удален.`)
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
        <!--  Табличная часть-->
        <table class="table mt-3">
            <thead class="thead-dark">
            <tr>
                <th scope="col">Пользователь</th>
                <th scope="col">Тур</th>
                <th scope="col">Сумма</th>
                <th scope="col">Дата</th>
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
                <td>{{ record.booking!.user!.email }}</td>
                <td>{{
                        `${record.booking!.tour!.hotel!.name} (${record.booking!.tour!.hotel!.city!.name}, ${record.booking!.tour!.hotel!.city!.country!.name}), ${formatDateTime(record.booking!.tour!.start_date!)}`
                    }}
                </td>
                <td>{{ record.amount }} р.</td>
                <td>{{ formatDateTime(record.created_at!) }}</td>
                <td>
                    <button @click="showDeleteForm(record)" class="btn btn-sm btn-danger">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </td>
            </tr>
            </tbody>
        </table>

        <!-- Модальное окно для удаления платежа -->
        <div class="modal fade" id="confirmDeleteRecordForm" tabindex="-1" aria-labelledby="confirmDeleteModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmDeleteModalLabel">Удаление платежа</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Вы уверены, что хотите удалить этот платёж?
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
