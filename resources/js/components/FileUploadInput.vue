<template>
    <div class="mb-3">
        <label for="file" class="form-label">Upload File</label>
        <!-- Conditional rendering: show existing file info if available -->
        <div v-if="existingFileUrl" class="mb-2">
            <p>Current File:
                <a :href="existingFileUrl" target="_blank">{{ existingFileName }}</a>
            </p>
            <!-- Display a preview if the file is an image -->
            <img v-if="isImage(existingFileUrl)" :src="existingFileUrl" alt="Image Preview" class="img-thumbnail" style="max-width: 200px;">
        </div>

        <!-- File input for uploading a new file -->
        <div class="drag-drop-area" v-if="!existingFileUrl || allowReplaceFile" @dragover.prevent @drop.prevent="handleDrop">
            <input
                type="file"
                class="form-control"
                id="file"
                ref="fileInput"
                @change="handleFileChange"
            />
            <p v-if="fileName">{{ fileName }}</p>
            <p v-if="fileError" class="text-danger">{{ fileError }}</p>
        </div>

        <!-- Progress Bar -->
        <div v-if="uploadProgress > 0" class="progress mt-3">
            <div
                class="progress-bar"
                role="progressbar"
                :style="{ width: uploadProgress + '%' }"
                aria-valuenow="uploadProgress"
                aria-valuemin="0"
                aria-valuemax="100"
            >
                {{ uploadProgress }}%
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        modelValue: File, // Prop for v-model to bind file data
        existingFileUrl: String, // URL of the existing file
        existingFileName: String, // Name of the existing file
        allowReplaceFile: {
            type: Boolean,
            default: true, // Allow replacing the existing file
        }
    },
    data() {
        return {
            fileName: '',
            fileError: '',
            uploadProgress: 0, // Progress percentage for file upload
        };
    },
    methods: {
        validateFileSize(file) {
            const MAX_SIZE_MB = 8 * 1024 * 1024; // 8 MB in bytes
            if (file.size > MAX_SIZE_MB) {
                this.fileError = 'File size exceeds 8 MB';
                return false;
            }
            this.fileError = '';
            return true;
        },

        handleFileChange(event) {
            const file = event.target.files[0];
            if (file && this.validateFileSize(file)) {
                this.fileName = file.name;
                const category = this.getFileCategory(file);
                this.$emit('update:modelValue', { file, category }); // Emit file and category to parent
            }
        },

        handleDrop(event) {
            const file = event.dataTransfer.files[0];
            if (file && this.validateFileSize(file)) {
                this.fileName = file.name;
                const category = this.getFileCategory(file);
                this.$refs.fileInput.files = event.dataTransfer.files;
                this.$emit('update:modelValue', { file, category }); // Emit file and category to parent
            }
        },

        getFileCategory(file) {
            const mimeType = file.type;

            if (mimeType.startsWith('image/')) {
                return 'Image';
            } else if (mimeType === 'application/pdf') {
                return 'PDF';
            } else if (mimeType.startsWith('video/')) {
                return 'Video';
            } else if (mimeType.startsWith('audio/')) {
                return 'Audio';
            } else if (mimeType.startsWith('text/')) {
                return 'Text';
            } else {
                return 'Document'; // Default category for other file types
            }
        },

        isImage(url) {
            return /\.(jpeg|jpg|gif|png|webp|bmp)$/i.test(url); // Check if the URL is an image
        },
    },
};
</script>

<style scoped>
.drag-drop-area {
    border: 2px dashed #ccc;
    padding: 20px;
    text-align: center;
    cursor: pointer;
}

.progress {
    height: 25px;
}

.img-thumbnail {
    margin-top: 10px;
}
</style>
