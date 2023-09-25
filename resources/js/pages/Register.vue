<template>
    <div class="mx-auto w-12/12 md:w-6/12 md:my-10 bg-blue-200 p-4 rounded-lg">
        <!-- component -->
        <div class="bg-white shadow-lg rounded-lg px-8 pt-6 pb-8 mb-2 flex flex-col">
            <h1 class="text-gray-600 py-5 font-bold text-3xl"> Create Account </h1>
            <span class="text-red-500" v-if="errors.message">{{ errors.message }}</span>
            <form method="post" @submit="handleSubmit">
                <div class="mb-4 mt-3">
                    <label class="block text-grey-darker text-sm font-bold mb-2" for="name">
                        Name
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker" id="name" type="text" required v-model="form.name" />
                    <span class="text-red-500" v-if="errors.name">{{ errors.name[0] }}</span>
                </div>
                <div class="mb-4">
                    <label class="block text-grey-darker text-sm font-bold mb-2" for="email">
                        Email Address
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker" type="email" id="email" required v-model="form.email" />
                    <span class="text-red-500" v-if="errors.email">{{ errors.email[0] }}</span>
                </div>
                <div class="mb-4">
                    <label class="block text-grey-darker text-sm font-bold mb-2" for="password">
                        Password
                    </label>
                    <input class="shadow appearance-none border border-red rounded w-full py-2 px-3 text-grey-darker" id="password" type="password" required v-model="form.password" />
                    <span class="text-red-500" v-if="errors.password">{{ errors.password[0] }}</span>
                </div>
                <div class="mb-4">
                    <label class="block text-grey-darker text-sm font-bold mb-2" for="password_confirmation">
                        Password Confirmation
                    </label>
                    <input class="shadow appearance-none border border-red rounded w-full py-2 px-3 text-grey-darker" id="password_confirmation" type="password" required v-model="form.password_confirmation" />
                </div>
                <div class="flex items-center justify-between">
                    <button class="bg-blue-500 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded" type="submit">
                        Register
                    </button>
                    <router-link class="inline-block align-baseline font-bold text-sm text-blue hover:text-blue-darker" to="/">
                        Login
                    </router-link>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import { reactive, ref } from 'vue';
import axios from 'axios';
import { useRouter } from "vue-router";

export default {
    setup() {
        const errors = ref({})
        let router = useRouter()
        const form = reactive({
            name: '',
            email: '',
            password: '',
        })
        const handleSubmit = async (evt) => {
            evt.preventDefault()
            try {
                const result = await axios.post('/api/auth/register', form);

                if (result.data && result.data.token) {
                    localStorage.setItem('APP_USER_TOKEN', result.data.token)
                    await router.push('home')
                }
            } catch (e) {
                if (e.response.data && e.response.data.errors) {
                    errors.value = e.response.data.errors
                } else {
                    errors.value = e.response.data.message
                }
            }
        }
        return {
            form,
            errors,
            handleSubmit,
        }
    }
}
</script>
