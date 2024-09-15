<template>
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>{{ isEditMode ? 'Edit File' : 'Upload New File' }}</h1>
            <button class="btn btn-primary" @click="openConfirmation('save')">Save</button>
        </div>

        <div class="row">
            <!-- Left side (Forms) -->
            <div class="col-md-8">
                <div class="card p-4 shadow-sm mb-4">
                    <!-- File name -->
                    <file-name-input
                        v-model="fileName"
                        placeholder="Enter file name (optional)"
                        label="File Name (optional)"
                    />

                    <!-- File upload field -->
                    <file-upload
                        v-model="file"
                        :existing-file-url="existingFileUrl"
                        @file-validation-error="handleFileValidationError"
                    />
                </div>
            </div>

            <!-- Right side (Additional fields and actions) -->
            <div class="col-md-4">
                <div class="card p-4 shadow-sm">
                    <!-- File category -->
                    <div class="mb-3">
                        <label for="fileCategory" class="form-label">File Category</label>
                        <select class="form-control" v-model="fileCategory">
                            <option value="" disabled>Select category</option>
                            <option v-for="category in categories" :key="category">{{ category }}</option>
                        </select>
                    </div>

                    <!-- Upload progress -->
                    <div v-if="uploadProgress !== null" class="progress mb-3">
                        <div
                            class="progress-bar progress-bar-striped progress-bar-animated"
                            :style="{ width: uploadProgress + '%' }">
                            {{ uploadProgress }}%
                        </div>
                    </div>

                    <hr>

                    <!-- Action buttons -->
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-outline-danger" @click="openConfirmation('delete')" v-if="isEditMode">Delete</button>
                        <button class="btn btn-outline-secondary" @click="openConfirmation('save')">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import swal from 'sweetalert';
import { useRoute, useRouter } from 'vue-router';
import FileNameInput from './FileNameInput.vue';
import FileUpload from './FileUploadInput.vue';

const fileName = ref('');
const file = ref(null);
const fileCategory = ref('');
const uploadProgress = ref(null);
const isEditMode = ref(false);
const fileId = ref(null);
const existingFileUrl = ref(null); // Новый URL для существующего файла

const categories = ['Documents', 'Images', 'Videos', 'Other'];

const route = useRoute();
const router = useRouter();

// Check if we're in edit mode
onMounted(() => {
    if (route.params.id) {
        isEditMode.value = true;
        fileId.value = route.params.id;
        loadFile(fileId.value); // Load the file details for editing
    } else {
        isEditMode.value = false;
    }
});

// Load file details for editing
const loadFile = async (id) => {
    try {
        const response = await axios.get(`/api/files/${id}`);
        const fileData = response.data;
        fileName.value = fileData.name;
        fileCategory.value = fileData.category;
        existingFileUrl.value = fileData.file_url; // Загрузка существующего файла
    } catch (error) {
        swal('Error!', 'Could not load file data', 'error');
    }
};

// Open modal with swal for confirmation
const openConfirmation = (type) => {
    swal({
        title: type === 'save' ? 'Save File?' : 'Delete File?',
        text: type === 'save' ? 'Are you sure you want to save this file?' : 'Are you sure you want to delete this file?',
        icon: type === 'save' ? 'info' : 'warning',
        buttons: true,
        dangerMode: type === 'delete',
    }).then((willProceed) => {
        if (willProceed) {
            if (type === 'save') {
                handleSave();
            } else if (type === 'delete') {
                deleteFile();
            }
        }
    });
};

// Save or update file
const handleSave = () => {
    if (!file.value && !existingFileUrl.value) {
        handleFileValidationError('File is required');
        return;
    }

    if (!fileCategory.value) {
        handleFileValidationError('Category is required');
        return;
    }

    const formData = new FormData();
    formData.append('name', fileName.value || (file.value ? file.value.name : '')); // File name is optional
    if (file.value) {
        formData.append('file', file.value);
    }
    formData.append('category', fileCategory.value);

    const method = isEditMode.value ? 'put' : 'post';
    const url = isEditMode.value ? `/api/files/${fileId.value}` : '/api/files';

    axios({
        method: method,
        url: url,
        data: formData,
        headers: { 'Content-Type': 'multipart/form-data' },
        onUploadProgress: (progressEvent) => {
            uploadProgress.value = Math.round((progressEvent.loaded / progressEvent.total) * 100);
        },
    })
        .then(() => {
            swal('Success!', 'File saved successfully', 'success');
            router.push('/'); // Redirect after saving
        })
        .catch((error) => {
            swal('Error!', 'There was an error saving the file', 'error');
        });
};

// Delete file
const deleteFile = () => {
    axios.delete(`/api/files/${fileId.value}`)
        .then(() => {
            swal('Deleted!', 'File deleted successfully', 'success');
            router.push('/'); // Redirect after deletion
        })
        .catch(() => {
            swal('Error!', 'There was an error deleting the file', 'error');
        });
};

// Handle file validation error
const handleFileValidationError = (error) => {
    swal('Error!', error, 'error');
};
</script>
