<template>
    <div id="modal-backdrop" class="fixed inset-0 bg-opacity-40 bg-blue-900 flex justify-center items-center">
        <div id="modal" class="bg-purple-50 rounded-xl overflow-x-auto flex flex-col justify-between w-2/6 h-4/6">

            <!-- Modal Header -->
            <header id="modal-header" class="px-4 py-4 flex justify-between items-center bg-blue-100">
                <slot name="header">Modal Header</slot>
                <button type="button" class="w-8 p-2 bg-gray-300 rounded-full text-xs" @click="close">&#10006;</button>
            </header>

            <!-- Modal Body -->
            <section id="modal-body" class="px-4 py-6 overflow-y-auto">
                <slot name="body"></slot>
            </section>

            <!-- Modal Footer -->
            <footer id="modal-footer" class="px-4 py-6 border-t-2 border-white grid grid-flow-col justify-stretch gap-x-4">
                <slot name="footer"></slot>
            </footer>
        </div>
    </div>
</template>

<script>
export default {
    setup(props, context) {
        const name = "Modal"

        const close = () => {
            context.emit('close')
        }

        return {
            close,
        }
    }
};
</script>
