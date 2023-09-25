<template>
    <div class="mx-auto w-12/12 md:w-8/12 p-10 mx-auto">
        <div class="flex justify-evenly md:justify-start border-b">
            <router-link class="rounded-lg px-3 py-2 text-slate-700 font-medium hover:bg-slate-100 hover:text-slate-900" to="home">
                Home
            </router-link>
            <router-link class="rounded-lg px-3 py-2 text-slate-700 font-medium hover:bg-slate-100 hover:text-slate-900" to="tasks">
                Tasks
            </router-link>
            <a class="md:hidden rounded-lg px-3 py-2 text-slate-700 font-medium hover:bg-slate-100 hover:text-slate-900" @click="handleLogout">Logout</a>
            <span class="max-md:hidden capitalize px-3 py-2 ml-auto">Welcome {{ user && user.name }}, <button class="text-orange-500 underline hover:no-underline rounded-md" @click="handleLogout">Logout</button></span>
        </div>
        <div class="text-2xl text-center py-5">Welcome, please click on the 'Tasks' button to continue.</div>
    </div>
</template>
<script>
import { ref, onMounted } from 'vue'
import { useRouter } from "vue-router"
import { request } from '../helper'
import Loader from '../components/Loader.vue'

export default {
    components: {
        Loader,
    },
    setup() {
        const user = ref()
        const isLoading = ref()

        let router = useRouter();

        onMounted(() => {
            authentication()
        });

        const authentication = async () => {
            isLoading.value = true
            try {
                const req = await request('get', '/api/user')
                user.value = req.data
            } catch (e) {
                await router.push('/')
            }
        }

        const handleLogout = () => {
            localStorage.removeItem('APP_USER_TOKEN')
            router.push('/')
        }

        return {
            user,
            isLoading,
            handleLogout,
        }
    },
}
</script>
