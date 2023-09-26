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
                <button class="bg-blue-600 text-white px-5 py-2 rounded-md m-2 hover:bg-blue-400" @click="addTaskModal">Create Task</button>
                <button class="bg-blue-600 text-white px-5 py-2 rounded-md m-2 hover:bg-blue-400" @click="isFilterModalVisible = true">Filter/Sort</button>
            </div>
            <div class="flex flex-col gap-2" v-if="tasks.length">
                <div class="max-md:flex-col flex gap-2 bg-blue-100 p-2 rounded-xl" v-for="(val, idx) in tasks" :key="val.id">
                    <div class="text-gray-600 grid grid-cols-2 grow p-2 cursor-pointer" @click="editTaskModal(val, idx)">
                        <span class="p-1 col-span-2"><b>Status</b>: {{ val.is_completed ? 'Complete' : 'To-do' }} {{ val.is_archived ? '(Archived)' : '' }}</span>

                        <span class="p-1 col-span-2"><b>Title</b>: {{ val.title }} </span>
                        <span class="p-1 col-span-2"><b>Description</b>: {{ val.description }} </span>

                        <span class="max-md:col-span-2 p-1" v-if="val.due_date != null"><b>Due Date</b>: {{ val.due_date }} ({{ val.days_left > 1 ? `${val.days_left} days` : `${val.days_left} day` }} left)</span>
                        <span class="max-md:col-span-2 p-1" v-else><b>Due Date</b>: None</span>

                        <span class="max-md:col-span-2 p-1"><b>Priority Level</b>: {{ val.priority_level ? priorityLevelOptions[val.priority_level].label : 'None' }} </span>
                        <span class="p-1 col-span-2"><b>Tags</b>:
                            <span class="bg-blue-600 p-1 mr-1 rounded-md text-white" v-for="(tagVal, tagIdx) in val.tags" :key="tagIdx">{{ tagVal }}</span>
                        </span>
                        <div class="col-span-2" v-if="val.task_files.length">
                            <span class="p-1 col-span-2"><b>Files</b>:</span>
                            <ol class="list-none">
                                <li v-for="(taskFile, tfIdx) in val.task_files" :key="taskFile.id" class="border-2 border-blue-600 rounded flex cursor-pointer">
                                    <div class="grow p-2" @click="downloadFile(taskFile.id, taskFile.filename)">{{ taskFile.filename }}</div>
                                </li>
                            </ol>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <button class="max-md:py-2 max-md:w-full flex-auto col-span-1 px-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-400 w-16" @click="toggleTaskField(val, idx, 'is_completed')" v-if="val.is_completed">To-do</button>
                        <button class="max-md:py-2 max-md:w-full flex-auto col-span-1 px-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-400 w-16" @click="toggleTaskField(val, idx, 'is_completed')" v-else>Complete</button>

                        <button class="max-md:py-2 max-md:w-full flex-auto col-span-1 px-1 bg-yellow-600 text-white text-sm rounded-md hover:bg-yellow-400 w-16" @click="toggleTaskField(val, idx, 'is_archived')" v-if="val.is_archived">Restore</button>
                        <button class="max-md:py-2 max-md:w-full flex-auto col-span-1 px-1 bg-yellow-600 text-white text-sm rounded-md hover:bg-yellow-400 w-16" @click="toggleTaskField(val, idx, 'is_archived')" v-else>Archive</button>

                        <button class="max-md:py-2 max-md:w-full flex-auto col-span-1 px-1 bg-red-600 text-white text-sm rounded-md hover:bg-red-400 w-16" @click="deleteTask(val, idx)">Delete</button>
                    </div>
                </div>
            </div>
            <div class="text-center" v-else>
                No task found.
            </div>
        </div>

        <div class="text-center">
            <TailwindPagination :item-classes="['h-full', 'w-16']" :active-classes="['bg-blue-100']" :data="laravelData" @pagination-change-page="loadTasksWithParams" />
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
            <div v-if="taskId > 0">
                <label class="block text-grey-darker text-sm font-bold mb-2">
                    File Attachments
                </label>
                <ol class="list-none">
                    <li v-for="(fileAttachment, faIdx) in fileAttachments" :key="fileAttachment.id" class="border-2 border-blue-600 rounded flex cursor-pointer">
                        <div class="grow p-2" @click="downloadFile(fileAttachment.id, fileAttachment.filename)">{{ fileAttachment.filename }}</div>
                        <div class="p-2 bg-red-600 text-white" @click="deleteFile(fileAttachment.id, faIdx)">Delete</div>
                    </li>
                </ol>
                <input type="file" class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker" @change="uploadFile($event.target.files)" />
                <span class="text-red-500" v-if="errors.file">{{ errors.file[0] }}</span>
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
                <label class="block text-grey-darker text-sm font-bold mb-2" for="sortBy">
                    Sort By
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <div class="col-span-1">
                        <Multiselect v-model="filterSortBy" :options="filterSortByOptions" id="sortBy" class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker" />
                        <span class="text-red-500" v-if="filterErrors.sort_by">{{ filterErrors.sort_by[0] }}</span>
                    </div>
                    <div class="col-span-1">
                        <Multiselect v-model="filterSortDirection" :options="filterSortDirectionOptions" class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker" />
                        <span class="text-red-500" v-if="filterErrors.sort_direction">{{ filterErrors.sort_direction[0] }}</span>
                    </div>
                </div>
            </div>
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
                <label class="block text-grey-darker text-sm font-bold mb-2" for="archivedDateFrom">
                    Archived Date From
                </label>
                <input v-model="filterArchivedDateFrom" type="date" id="archivedDateFrom" placeholder="Archived Date From" class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker" />
                <span class="text-red-500" v-if="filterErrors.archived_date_from">{{ filterErrors.archived_date_from[0] }}</span>
            </div>
            <div class="mb-4">
                <label class="block text-grey-darker text-sm font-bold mb-2" for="archivedDateTo">
                    Archived Date To
                </label>
                <input v-model="filterArchivedDateTo" type="date" id="archivedDateTo" placeholder="Archived Date To" class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker" />
                <span class="text-red-500" v-if="filterErrors.archived_date_to">{{ filterErrors.archived_date_to[0] }}</span>
            </div>
            <div class="mb-4">
                <label class="block text-grey-darker text-sm font-bold mb-2" for="createdDateFrom">
                    Created Date From
                </label>
                <input v-model="filterCreatedDateFrom" type="date" id="createdDateFrom" placeholder="Created Date From" class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker" />
                <span class="text-red-500" v-if="filterErrors.created_date_from">{{ filterErrors.created_date_from[0] }}</span>
            </div>
            <div class="mb-4">
                <label class="block text-grey-darker text-sm font-bold mb-2" for="createdDateTo">
                    Created Date To
                </label>
                <input v-model="filterCreatedDateTo" type="date" id="createdDateTo" placeholder="Created Date To" class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker" />
                <span class="text-red-500" v-if="filterErrors.created_date_from">{{ filterErrors.created_date_to[0] }}</span>
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
import { TailwindPagination } from 'laravel-vue-pagination'
import fileDownload from 'js-file-download'

export default {
    components: {
        Loader,
        Modal,
        Multiselect,
        TailwindPagination,
    },
    setup() {
        const task = ref('')
        const tasks = ref([])
        const user = ref()
        const isLoading = ref()
        const laravelData = ref({})
        const currentPage = ref(1)

        // Task modal variables
        const isTaskModalVisible = ref(false)
        const modalTitle = ref('Create Task')
        const taskId = ref(0)  // Required
        const title = ref('')  // Required
        const description = ref('')  // Required
        const dueDate = ref('')
        const priorityLevel = ref(null)
        const tags = ref([])
        const fileAttachments = ref([]);
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
        const filterSortBy = ref('')
        const filterSortDirection = ref('')
        const filterTitle = ref('')
        const filterDescription = ref('')
        const filterPriorityLevel = ref(null)
        const filterDueDateFrom = ref('')
        const filterDueDateTo = ref('')
        const filterCompletedDateFrom = ref('')
        const filterCompletedDateTo = ref('')
        const filterArchivedDateFrom = ref('')
        const filterArchivedDateTo = ref('')
        const filterCreatedDateFrom = ref('')
        const filterCreatedDateTo = ref('')
        const filterErrors = ref({})
        const filterSortByOptions = reactive([
            {
                label: 'Title',
                value: 'title'
            },
            {
                label: 'Description',
                value: 'description'
            },
            {
                label: 'Due Date',
                value: 'due_date'
            },
            {
                label: 'Completed Date',
                value: 'completed_date'
            },
            {
                label: 'Archived Date',
                value: 'archived_date'
            },
            {
                label: 'Created Date',
                value: 'created_date'
            },
            {
                label: 'Priority Level',
                value: 'priority_level'
            },
        ])
        const filterSortDirectionOptions = reactive([
            {
                label: 'Ascending',
                value: 'ASC'
            },
            {
                label: 'Descending',
                value: 'DESC'
            }
        ])

        let router = useRouter()

        onMounted(() => {
            authentication()
            loadTasks()
        });

        const tagsLength = computed(() => Object.keys(tags.value ?? []).length)

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
        const loadTasks = async (page = 1) => {
            isLoading.value = true

            try {
                const req = await request('get', '/api/tasks', { params: { page: page } })
                tasks.value = req.data.tasks.data
                laravelData.value = req.data.tasks
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
            isLoading.value = true
            errors.value = {}

            let data = {
                title: title.value,
                description: description.value,
            }

            if (dueDate.value)
                data.due_date = dueDate.value

            if (priorityLevel.value)
                data.priority_level = parseInt(priorityLevel.value)

            if (tagsLength.value)
                data.tags = tags.value

            try {
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
            isLoading.value = true
            errors.value = {}

            let data = {
                title: title.value,
                description: description.value,
            }

            if (dueDate.value)
                data.due_date = dueDate.value

            if (priorityLevel.value)
                data.priority_level = parseInt(priorityLevel.value)

            if (tagsLength.value)
                data.tags = tags.value

            try {
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

                loadTasksWithParams(currentPage.value)
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
            fileAttachments.value = val.task_files
        }

        const toggleTaskField = async (val, index, field) => {
            isLoading.value = true

            let data = {}
            data[field] = !val[field]

            try {
                const req = await request('put', `/api/tasks/${val.id}`, data)

                if (req.data.message) {
                    notify({
                        group: "generic",
                        title: "Success",
                        text: req.data.message
                    }, 4000)
                }

                loadTasksWithParams(currentPage.value)
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

        const uploadFile = async (files) => {
            let formData = new FormData();

            formData.append('file', files[0]);

            try {
                const req = await request('post', `/api/tasks/${taskId.value}/file-upload`, formData)

                if (req.data.taskFile)
                    fileAttachments.value.push(req.data.taskFile)

                if (req.data.message) {
                    notify({
                        group: "generic",
                        title: "Success",
                        text: req.data.message
                    }, 4000)
                }
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

        const downloadFile = async (imageId, filename) => {
            try {
                const req = await request('get', `/api/tasks/${taskId.value}/images/${imageId}`, { responseType: 'blob' })
                fileDownload(req.data, filename);
            } catch (e) {
                if (e.response.data && e.response.data.message) {
                    notify({
                        group: "error",
                        title: "Fail",
                        text: e.response.data.message
                    }, 4000)
                }
            }
        }

        const deleteFile = async (imageId, index) => {
            try {
                const req = await request('delete', `/api/tasks/${taskId.value}/images/${imageId}`)
                fileAttachments.value.splice(index, 1)
            } catch (e) {
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
        const loadTasksWithParams = async (page = 1) => {
            isLoading.value = true
            filterErrors.value = {}
            currentPage.value = page

            let data = {
                page: page,
                sort_by: filterSortBy.value,
                sort_direction: filterSortDirection.value,
                title: filterTitle.value,
                description: filterDescription.value,
                priority_level: filterPriorityLevel.value,
                due_date_from: filterDueDateFrom.value,
                due_date_to: filterDueDateTo.value,
                completed_date_from: filterCompletedDateFrom.value,
                completed_date_to: filterCompletedDateTo.value,
                archived_date_from: filterArchivedDateFrom.value,
                archived_date_to: filterArchivedDateTo.value,
                created_date_from: filterCreatedDateFrom.value,
                created_date_to: filterCreatedDateTo.value,
            }

            try {
                const req = await request('get', '/api/tasks', { params: data })
                tasks.value = req.data.tasks.data
                laravelData.value = req.data.tasks
                isFilterModalVisible.value = false
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
            filterSortBy.value = ''
            filterSortDirection.value = ''
            filterTitle.value = ''
            filterDescription.value = ''
            filterPriorityLevel.value = ''
            filterDueDateFrom.value = ''
            filterDueDateTo.value = ''
            filterCompletedDateFrom.value = ''
            filterCompletedDateTo.value = ''
            filterArchivedDateFrom.value = ''
            filterArchivedDateTo.value = ''
            filterCreatedDateFrom.value = ''
            filterCreatedDateTo.value = ''
        }

        return {
            task,
            tasks,
            user,
            isLoading,
            deleteTask,
            handleLogout,
            toggleTaskField,
            laravelData,
            loadTasks,
            downloadFile,
            deleteFile,

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
            uploadFile,

            // Filter modal variables
            isFilterModalVisible,
            filterSortByOptions,
            filterSortDirectionOptions,
            filterSortBy,
            filterSortDirection,
            filterTitle,
            filterDescription,
            filterPriorityLevel,
            filterDueDateFrom,
            filterDueDateTo,
            filterCompletedDateFrom,
            filterCompletedDateTo,
            filterArchivedDateFrom,
            filterArchivedDateTo,
            filterCreatedDateFrom,
            filterCreatedDateTo,
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
