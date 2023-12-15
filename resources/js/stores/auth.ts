import axios from 'axios'
import { defineStore } from 'pinia'

interface UserState {
    id: bigint | null
    email: string
    first_name: string
    last_name: string
    is_admin: boolean
}

export const useAuthStore = defineStore('auth', {
    state: (): UserState => ({
        id: null,
        email: '',
        first_name: '',
        last_name: '',
        is_admin: false,
    }),
    getters: {
        isAuthenticated: (state) => state.id != null,
    },
    actions: {
        async fetchUser() {
            await axios.get('/api/user').then(({data}) => {
                if (Object.keys(data).length === 0) {
                    this.reset()
                    return
                }

                this.id = data.id
                this.email = data.email
                this.first_name = data.first_name
                this.last_name = data.last_name
                this.is_admin = data.is_admin

            }).catch(() => {
                this.reset()
            })
        },

        reset() {
            this.$reset()
        }
    }
})
