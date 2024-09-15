<!-- resources/js/components/FileNameInput.vue -->
<template>
    <div class="mb-3">
        <label for="fileName" class="form-label">File Name (optional)</label>
        <input
            type="text"
            class="form-control"
            id="fileName"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
        />
    </div>
</template>

<script>
export default {
    props: {
        modelValue: String,
    },
};
</script>

<style scoped>
/* Add custom styles if needed */
</style>
