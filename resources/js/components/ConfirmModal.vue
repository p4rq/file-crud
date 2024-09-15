<template>
    <div class="modal-backdrop show"></div>
    <div class="modal d-block">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ title }}</h5>
                    <button type="button" class="btn-close" @click="close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ message }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="close">Cancel</button>
                    <button type="button" class="btn btn-primary" @click="confirm">Confirm</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    title: String,
    message: String,
});

const emit = defineEmits(['confirm', 'close']);

const close = () => emit('close');

const form ={
    name: "",
    description: "",


}
const confirm = () => {
    axios.post('/files', form)
};
</script>

<style scoped>
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}
.modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}
</style>
