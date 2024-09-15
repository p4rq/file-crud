<template>
    <div class="mb-3">
        <label for="file" class="form-label">Upload File</label>
        <div
            class="drag-drop-area"
            @dragover.prevent
            @drop.prevent="handleDrop"
        >
            <input
                type="file"
                class="form-control"
                id="file"
                ref="fileInput"
                @change="handleFileChange"
                required
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
import axios from 'axios';

export default {
    props: {
        modelValue: File, // Prop for v-model to bind file data
    },
    data() {
        return {
            fileName: '',
            fileError: '',
            uploadProgress: 0, // Progress percentage for file upload
        };
    },
    methods: {
        // Check if the file size is less than 8MB (8 * 1024 * 1024 bytes)
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
                this.$emit('update:modelValue', file); // Emit file to parent
                this.uploadFile(file); // Start file upload when file is selected
            }
        },

        handleDrop(event) {
            const file = event.dataTransfer.files[0];
            if (file && this.validateFileSize(file)) {
                this.fileName = file.name;
                this.$refs.fileInput.files = event.dataTransfer.files;
                this.$emit('update:modelValue', file); // Emit file to parent
                this.uploadFile(file); // Start file upload when file is dropped
            }
        },

        // Automatically determine the file category based on the MIME type
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

        async uploadFile(file) {
            const formData = new FormData();
            const fileCategory = this.getFileCategory(file); // Determine category

            formData.append('file', file);
            formData.append('category', fileCategory); // Automatically set the category

            try {
                const response = await axios.post('/api/files', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                    onUploadProgress: (progressEvent) => {
                        if (progressEvent.lengthComputable) {
                            this.uploadProgress = Math.round((progressEvent.loaded / progressEvent.total) * 100);
                        }
                    },
                });

                if (response.status === 201) {
                    console.log('File uploaded successfully', response.data);
                    // Handle success, e.g., reset the file input or redirect
                }
            } catch (error) {
                console.error('Upload failed', error.response.data);
                if (error.response.status === 422) {
                    this.fileError = error.response.data.errors.file || 'Invalid file upload';
                }
                this.uploadProgress = 0; // Reset progress bar on error
            }
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
</style>
