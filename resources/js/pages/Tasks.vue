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
        <Loader v-if="isLoading" />
        <div class="py-5" v-show="!isLoading">
            <div class="mb-3">
                <button class="bg-blue-600 text-white px-5 py-2 rounded-md ml-2 hover:bg-blue-400" @click="addTaskModal">Create Task</button>
                <button class="bg-blue-600 text-white px-5 py-2 rounded-md ml-2 hover:bg-blue-400" @click="isFilterModalVisible = true">Filter/Sort</button>
            </div>
            <div class="flex flex-col gap-2" v-if="tasks.length">
                <div class="max-md:flex-col flex gap-2 bg-blue-100 p-2 rounded-xl" v-for="(val, idx) in tasks" :key="val.id">
                    <div class="text-gray-600 grid grid-rows-3 grid-cols-2 grow p-2 cursor-pointer" @click="editTaskModal(val, idx)">
                        <span class="p-1 col-span-2">Status: <b>{{ val.is_completed ? 'Complete' : 'To-do' }} {{ val.is_archived ? '(Archived)' : '' }}</b></span>

                        <span class="p-1 col-span-2">Title: {{ val.title }} </span>
                        <span class="p-1 col-span-2">Description: {{ val.description }} </span>

                        <span class="max-md:col-span-2 p-1" v-if="val.due_date != null">Due Date: {{ val.due_date }} ({{ val.days_left > 1 ? `${val.days_left} days` : `${val.days_left} day` }} left)</span>
                        <span class="max-md:col-span-2 p-1" v-else>Due Date: None</span>

                        <span class="max-md:col-span-2 p-1">Priority Level: {{ val.priority_level ? priorityLevelOptions[val.priority_level].label : 'None' }} </span>
                        <span class="p-1 col-span-2">Tags:
                            <span class="bg-blue-600 p-1 mr-1 rounded-md text-white" v-for="(tagVal, tagIdx) in val.tags" :key="tagIdx">{{ tagVal }}</span>
                        </span>
                        <!-- <span class="pl-3">{{ val.file_attachments }} </span> -->
                    </div>
                    <div class="grid grid-rows-3 grid-cols-1 gap-1">
                        <button class="max-md:py-2 col-span-1 px-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-400" @click="toggleTaskField(val, idx, 'is_completed')" v-if="val.is_completed">To-do</button>
                        <button class="max-md:py-2 col-span-1 px-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-400" @click="toggleTaskField(val, idx, 'is_completed')" v-else>Complete</button>

                        <button class="max-md:py-2 col-span-1 px-1 bg-yellow-600 text-white text-sm rounded-md hover:bg-yellow-400" @click="toggleTaskField(val, idx, 'is_archived')" v-if="val.is_archived">Restore</button>
                        <button class="max-md:py-2 col-span-1 px-1 bg-yellow-600 text-white text-sm rounded-md hover:bg-yellow-400" @click="toggleTaskField(val, idx, 'is_archived')" v-else>Archive</button>

                        <button class="max-md:py-2 col-span-1 px-1 bg-red-600 text-white text-sm rounded-md hover:bg-red-400" @click="deleteTask(val, idx)">Delete</button>
                    </div>
                </div>
            </div>
            <div class="text-center" v-else>
                No task found.
            </div>
        </div>
    </div>

    <!-- Modal for Create and Edit Task -->
    <Modal v-show="isTaskModalVisible" @close="isTaskModalVisible = false">
        <!-- header -->
        <template v-slot:header>
            <h1 class="font-bold text-xl">{{ modalTitle }}</h1>
        </template>

        <!-- body -->
        <template v-slot:body>
            <div class="mb-4">
                <label class="block text-grey-darker text-sm font-bold mb-2" for="title">
                    Title
                </label>
                <input v-model="title" type="text" id="title" placeholder="Title" class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker" />
                <span class="text-red-500" v-if="errors.title">{{ errors.title[0] }}</span>
            </div>
            <div class="mb-4">
                <label class="block text-grey-darker text-sm font-bold mb-2" for="description">
                    Description
                </label>
                <textarea v-model="description" id="description" placeholder="Description" class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker"></textarea>
                <span class="text-red-500" v-if="errors.description">{{ errors.description[0] }}</span>
            </div>
            <div class="mb-4">
                <label class="block text-grey-darker text-sm font-bold mb-2" for="dueDate">
                    Due Date
                </label>
                <input v-model="dueDate" type="date" id="dueDate" placeholder="Due Date" class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker" />
                <span class="text-red-500" v-if="errors.due_date">{{ errors.due_date[0] }}</span>
            </div>
            <div class="mb-4">
                <label class="block text-grey-darker text-sm font-bold mb-2" for="priorityLevel">
                    Task Priority
                </label>
                <Multiselect v-model="priorityLevel" :options="priorityLevelOptions" id="priorityLevel" class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker" />
                <span class="text-red-500" v-if="errors.task_priority">{{ errors.task_priority[0] }}</span>
            </div>
            <div class="mb-4">
                <label class="block text-grey-darker text-sm font-bold mb-2" for="tags">
                    Tags
                </label>
                <Multiselect v-model="tags" :options="tagOptions" mode="tags" :close-on-select="false" id="tags" class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker" />
                <span class="text-red-500" v-if="errors.tags">{{ errors.tags[0] }}</span>
            </div>
            <div>
                <label class="block text-grey-darker text-sm font-bold mb-2" for="fileAttachments">
                    File Attachments
                </label>
                <input type="file" name="fileAttachments" id="fileAttachments" class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker" />
                <span class="text-red-500" v-if="errors.file_attachments">{{ errors.file_attachments[0] }}</span>
            </div>
        </template>

        <!-- footer -->
        <template v-slot:footer>
            <button type="button" class="rounded-md bg-blue-600 hover:bg-blue-400 p-2 text-white" @click="processTask">
                Save
            </button>
            <button type="button" class="rounded-md bg-red-600 hover:bg-red-400 p-2 text-white" @click="deleteTask(val, idx)" v-if="taskId > 0">
                Delete
            </button>
            <button type="button" class="rounded-md bg-slate-600 hover:bg-slate-400 p-2 text-white" @click="isTaskModalVisible = false">
                Cancel
            </button>
        </template>
    </Modal>

    <!-- Modal for Filter and Sorting -->
    <Modal v-show="isFilterModalVisible" @close="isFilterModalVisible = false">
        <!-- header -->
        <template v-slot:header>
            <h1 class="font-bold text-xl">Filter/Sort</h1>
        </template>

        <!-- body -->
        <template v-slot:body>
            <div class="mb-4">
                <label class="block text-grey-darker text-sm font-bold mb-2" for="title">
                    Title
                </label>
                <input v-model="filterTitle" type="text" id="title" placeholder="Title" class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker" />
                <span class="text-red-500" v-if="filterErrors.title">{{ filterErrors.title[0] }}</span>
            </div>
            <div class="mb-4">
                <label class="block text-grey-darker text-sm font-bold mb-2" for="description">
                    Description
                </label>
                <input v-model="filterDescription" type="text" id="description" placeholder="Description" class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker" />
                <span class="text-red-500" v-if="filterErrors.description">{{ filterErrors.description[0] }}</span>
            </div>
            <div class="mb-4">
                <label class="block text-grey-darker text-sm font-bold mb-2" for="priorityLevel">
                    Task Priority
                </label>
                <Multiselect v-model="filterPriorityLevel" :options="priorityLevelOptions" id="priorityLevel" class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker" />
                <span class="text-red-500" v-if="filterErrors.task_priority">{{ filterErrors.task_priority[0] }}</span>
            </div>
            <div class="mb-4">
                <label class="block text-grey-darker text-sm font-bold mb-2" for="dueDateFrom">
                    Due Date From
                </label>
                <input v-model="filterDueDateFrom" type="date" id="dueDateFrom" placeholder="Due Date From" class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker" />
                <span class="text-red-500" v-if="filterErrors.due_date_from">{{ filterErrors.due_date_from[0] }}</span>
            </div>
            <div class="mb-4">
                <label class="block text-grey-darker text-sm font-bold mb-2" for="dueDateTo">
                    Due Date To
                </label>
                <input v-model="filterDueDateTo" type="date" id="dueDateTo" placeholder="Due Date To" class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker" />
                <span class="text-red-500" v-if="filterErrors.due_date_to">{{ filterErrors.due_date_to[0] }}</span>
            </div>
            <div class="mb-4">
                <label class="block text-grey-darker text-sm font-bold mb-2" for="dueDateFrom">
                    Completed Date From
                </label>
                <input v-model="filterCompletedDateFrom" type="date" id="dueDateFrom" placeholder="Due Date From" class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker" />
                <span class="text-red-500" v-if="filterErrors.due_date_from">{{ filterErrors.due_date_from[0] }}</span>
            </div>
            <div class="mb-4">
                <label class="block text-grey-darker text-sm font-bold mb-2" for="dueDateTo">
                    Completed Date To
                </label>
                <input v-model="filterCompletedDateTo" type="date" id="dueDateTo" placeholder="Due Date To" class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker" />
                <span class="text-red-500" v-if="filterErrors.due_date_to">{{ filterErrors.due_date_to[0] }}</span>
            </div>
            <div class="mb-4">
                <label class="block text-grey-darker text-sm font-bold mb-2" for="dueDateFrom">
                    Archived Date From
                </label>
                <input v-model="filterArchivedDateFrom" type="date" id="dueDateFrom" placeholder="Due Date From" class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker" />
                <span class="text-red-500" v-if="filterErrors.due_date_from">{{ filterErrors.due_date_from[0] }}</span>
            </div>
            <div class="mb-4">
                <label class="block text-grey-darker text-sm font-bold mb-2" for="dueDateTo">
                    Archived Date To
                </label>
                <input v-model="filterArchivedDateTo" type="date" id="dueDateTo" placeholder="Due Date To" class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker" />
                <span class="text-red-500" v-if="filterErrors.due_date_to">{{ filterErrors.due_date_to[0] }}</span>
            </div>
        </template>

        <!-- footer -->
        <template v-slot:footer>
            <button type="button" class="rounded-md bg-blue-600 hover:bg-blue-400 p-2 text-white" @click="loadTasksWithParams">
                Save
            </button>
            <button type="button" class="rounded-md bg-red-600 hover:bg-red-400 p-2 text-white" @click="clearFilters">
                Clear
            </button>
            <button type="button" class="rounded-md bg-slate-600 hover:bg-slate-400 p-2 text-white" @click="isFilterModalVisible = false">
                Cancel
            </button>
        </template>
    </Modal>
</template>
<script>
import { ref, onMounted, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import { request } from '../helper'
import Loader from '../components/Loader.vue'
import Modal from '../components/Modal.vue'
import Multiselect from '@vueform/multiselect'
import { notify } from 'notiwind'

export default {
    components: {
        Loader,
        Modal,
        Multiselect,
    },
    setup() {
        const task = ref('')
        const tasks = ref([])
        const user = ref()
        const isLoading = ref()

        // Task modal variables
        const isTaskModalVisible = ref(false)
        const modalTitle = ref('Create Task')
        const taskId = ref(0)  // Required
        const title = ref('')  // Required
        const description = ref('')  // Required
        const dueDate = ref('')
        const priorityLevel = ref(null)
        const tags = ref([])
        const fileAttachments = reactive([])
        const errors = ref({})
        const priorityLevelOptions = reactive([
            {
                label: 'Urgent',
                value: 1
            },
            {
                label: 'High',
                value: 2
            },
            {
                label: 'Normal',
                value: 3
            },
            {
                label: 'Low',
                value: 4
            },
        ])
        const tagOptions = reactive(['In-progress', 'On-hold', 'Blocked'])

        // Filter modal variables
        const isFilterModalVisible = ref(false)
        const filterTitle = ref('')
        const filterDescription = ref('')
        const filterPriorityLevel = ref(null)
        const filterDueDateFrom = ref('')
        const filterDueDateTo = ref('')
        const filterCompletedDateFrom = ref('')
        const filterCompletedDateTo = ref('')
        const filterArchivedDateFrom = ref('')
        const filterArchivedDateTo = ref('')
        const filterErrors = ref({})

        let router = useRouter()

        onMounted(() => {
            authentication()
            loadTasks()
        });

        const tagsLength = computed(() => Object.keys(tags.value).length)

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

        // Task functions
        const loadTasks = async () => {
            isLoading.value = true

            try {
                const req = await request('get', '/api/tasks')
                tasks.value = req.data.tasks
            } catch (e) {
                if (e.response.data && e.response.data.message) {
                    notify({
                        group: "error",
                        title: "Fail",
                        text: e.response.data.message
                    }, 4000)
                }
            }

            isLoading.value = false
        }

        const processTask = () => {
            if (taskId.value == 0)
                createTask()
            else
                updateTask()
        }

        const createTask = async () => {
            try {
                isLoading.value = true

                let data = {
                    title: title.value,
                    description: description.value,
                }

                if (dueDate.value)
                    data.due_date = dueDate.value

                if (priorityLevel.value)
                    data.priority_level = parseInt(priorityLevel.value)

                if (tagsLength)
                    data.tags = tags.value

                const req = await request('post', '/api/tasks', data)

                if (req.data.message) {
                    isLoading.value = false
                    isTaskModalVisible.value = false

                    notify({
                        group: "generic",
                        title: "Success",
                        text: req.data.message
                    }, 4000)
                }

                tasks.value.push(req.data.task)
            } catch (e) {
                if (e.response.data && e.response.data.errors) {
                    errors.value = e.response.data.errors
                }

                if (e.response.data && e.response.data.message) {
                    notify({
                        group: "error",
                        title: "Fail",
                        text: e.response.data.message
                    }, 4000)
                }
            }

            isLoading.value = false
        }

        const updateTask = async () => {
            try {
                isLoading.value = true

                let data = {
                    title: title.value,
                    description: description.value,
                }

                if (dueDate.value)
                    data.due_date = dueDate.value

                if (priorityLevel.value)
                    data.priority_level = parseInt(priorityLevel.value)

                if (tagsLength)
                    data.tags = tags.value

                const req = await request('put', `/api/tasks/${taskId.value}`, data)

                if (req.data.message) {
                    isLoading.value = false
                    isTaskModalVisible.value = false

                    notify({
                        group: "generic",
                        title: "Success",
                        text: req.data.message
                    }, 4000)
                }

                loadTasks()
            } catch (e) {
                if (e.response.data && e.response.data.errors) {
                    errors.value = e.response.data.errors
                }

                if (e.response.data && e.response.data.message) {
                    notify({
                        group: "error",
                        title: "Fail",
                        text: e.response.data.message
                    }, 4000)
                }
            }

            isLoading.value = false
        }

        const deleteTask = async (val, index) => {
            if (window.confirm("Delete this Task?")) {
                try {
                    const req = await request('delete', `/api/tasks/${val.id}`)

                    if (req.data.message) {
                        isLoading.value = false
                        tasks.value.splice(index, 1)
                    }
                } catch (e) {
                    if (e.response.data && e.response.data.message) {
                        notify({
                            group: "error",
                            title: "Fail",
                            text: e.response.data.message
                        }, 4000)
                    }
                }

                isLoading.value = false
            }
        }

        const addTaskModal = () => {
            modalTitle.value = 'Create Task'
            isTaskModalVisible.value = true

            taskId.value = 0
            title.value = ''
            description.value = ''
            dueDate.value = ''
            priorityLevel.value = ''
            tags.value = []
        }

        const editTaskModal = (val, idx) => {
            modalTitle.value = 'Edit Task'
            isTaskModalVisible.value = true

            taskId.value = val.id
            title.value = val.title
            description.value = val.description
            dueDate.value = val.due_date
            priorityLevel.value = val.priority_level
            tags.value = val.tags
            // fileAttachments.value = reactive([])
        }

        const toggleTaskField = async (val, index, field) => {
            try {
                isLoading.value = true

                let data = {}
                data[field] = !val[field]

                const req = await request('put', `/api/tasks/${val.id}`, data)

                if (req.data.message) {
                    notify({
                        group: "generic",
                        title: "Success",
                        text: req.data.message
                    }, 4000)
                }

                loadTasks()
            } catch (e) {
                if (e.response.data && e.response.data.errors) {
                    errors.value = e.response.data.errors
                }

                if (e.response.data && e.response.data.message) {
                    notify({
                        group: "error",
                        title: "Fail",
                        text: e.response.data.message
                    }, 4000)
                }
            }
        }

        // Filter functions
        const loadTasksWithParams = async (val, index, field) => {
            isLoading.value = true

            try {
                let data = {
                    title: filterTitle.value,
                    description: filterDescription.value,
                    priority_level: filterPriorityLevel.value,
                    due_date_from: filterDueDateFrom.value,
                    due_date_to: filterDueDateTo.value,
                    completed_date_from: filterCompletedDateFrom.value,
                    completed_date_to: filterCompletedDateTo.value,
                    archived_date_from: filterArchivedDateFrom.value,
                    archived_date_to: filterArchivedDateTo.value,
                }

                const req = await request('get', '/api/tasks', { params: data })
                tasks.value = req.data.tasks
            } catch (e) {
                if (e.response.data && e.response.data.errors) {
                    filterErrors.value = e.response.data.errors
                }

                if (e.response.data && e.response.data.message) {
                    notify({
                        group: "error",
                        title: "Fail",
                        text: e.response.data.message
                    }, 4000)
                }
            }

            isLoading.value = false
        }

        const clearFilters = () => {
            filterTitle.value = ''
            filterDescription.value = ''
            filterPriorityLevel.value = ''
            filterDueDateFrom.value = ''
            filterDueDateTo.value = ''
            filterCompletedDateFrom.value = ''
            filterCompletedDateTo.value = ''
            filterArchivedDateFrom.value = ''
            filterArchivedDateTo.value = ''
        }

        return {
            task,
            tasks,
            user,
            isLoading,
            deleteTask,
            handleLogout,
            toggleTaskField,

            // Task modal variables
            isTaskModalVisible,
            modalTitle,
            taskId,
            title,
            description,
            dueDate,
            priorityLevel,
            tags,
            fileAttachments,
            priorityLevelOptions,
            tagOptions,
            errors,
            // Task modal functions
            addTaskModal,
            editTaskModal,
            processTask,

            // Filter modal variables
            isFilterModalVisible,
            filterTitle,
            filterDescription,
            filterPriorityLevel,
            filterDueDateFrom,
            filterDueDateTo,
            filterCompletedDateFrom,
            filterCompletedDateTo,
            filterArchivedDateFrom,
            filterArchivedDateTo,
            filterErrors,
            // Filter modal functions
            loadTasksWithParams,
            clearFilters,
        }
    },
}
</script>

<style src="@vueform/multiselect/themes/default.css"></style>
<style>
.multiselect-wrapper {
    min-height: auto !important;
}
</style>
