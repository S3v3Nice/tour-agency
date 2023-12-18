import axios from 'axios'
import {defineStore} from 'pinia'
import {User} from "../types/user";

export const useAuthStore = defineStore('auth', {
    state: (): User => ({
        id: null,
        email: '',
        first_name: '',
        last_name: '',
        is_admin: false,
        created_at: '',
        updated_at: '',
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
                this.created_at = data.created_at
                this.updated_at = data.updated_at
            }).catch(() => {
                this.reset()
            })
        },

        reset() {
            this.$reset()
        }
    }
})
