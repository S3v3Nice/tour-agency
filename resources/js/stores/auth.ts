import axios from 'axios'
import {defineStore} from 'pinia'
import type {User} from '@/types'

interface AuthUser {
    isAuthenticated: boolean
    isFetched: boolean
    user: User | null
}

export const useAuthStore = defineStore('auth', {
    state: (): AuthUser => ({
        isAuthenticated: false,
        isFetched: false,
        user: null
    }),
    getters: {
        id: (state) => state.user?.id,
        email: (state) => state.user?.email,
        firstName: (state) => state.user?.first_name,
        lastName: (state) => state.user?.last_name,
        isAdmin: (state) => state.user?.is_admin,
        createdAt: (state) => state.user?.created_at,
        updatedAt: (state) => state.user?.updated_at,
    },
    actions: {
        async fetchUser() {
            await axios.get('/api/auth/user').then(({data}) => {
                if (Object.keys(data).length === 0) {
                    this.reset()
                    return
                }

                this.isAuthenticated = true
                this.isFetched = true
                this.user = {
                    id: data.id,
                    email: data.email,
                    first_name: data.first_name,
                    last_name: data.last_name,
                    is_admin: data.is_admin,
                    created_at: data.created_at,
                    updated_at: data.updated_at,
                }
            }).catch(() => {
                this.reset()
            })
        },

        reset() {
            this.isAuthenticated = false
            this.user = null
        }
    }
})
