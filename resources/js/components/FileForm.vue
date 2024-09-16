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
                        v-model="fileDetails"
                        @update:modelValue="handleFileDetailsUpdate"
                        @file-validation-error="handleFileValidationError"
                    />
                </div>
            </div>

            <!-- Right side (Additional fields and actions) -->
            <div class="col-md-4">
                <div class="card p-4 shadow-sm">
                    <!-- Show file category (automatically set) -->
                    <div class="mb-3">
                        <label for="fileCategory" class="form-label">File Category</label>
                        <p>{{ fileCategory }}</p>
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
const fileDetails = ref({ file: null, category: '' });
const fileCategory = ref(''); // Store category from child component
const uploadProgress = ref(null);
const isEditMode = ref(false);
const fileId = ref(null);
const existingFileUrl = ref(null);

const route = useRoute();
const router = useRouter();

onMounted(() => {
    if (route.params.id) {
        isEditMode.value = true;
        fileId.value = route.params.id;
        loadFile(fileId.value);
    } else {
        isEditMode.value = false;
    }
});

const loadFile = async (id) => {
    try {
        const response = await axios.get(`/api/files/${id}`);
        const fileData = response.data;
        fileName.value = fileData.name;
        fileCategory.value = fileData.category;
        existingFileUrl.value = fileData.file_url;
    } catch (error) {
        swal('Error!', 'Could not load file data', 'error');
    }
};

// Update file details when new file is selected
const handleFileDetailsUpdate = (details) => {
    fileDetails.value = details;
    fileCategory.value = details.category; // Automatically update category
};

// Save or update file
const handleSave = () => {
    if (!fileDetails.value.file && !existingFileUrl.value) {
        handleFileValidationError('File is required');
        return;
    }

    const formData = new FormData();
    formData.append('name', fileName.value || (fileDetails.value.file ? fileDetails.value.file.name : ''));
    if (fileDetails.value.file) {
        formData.append('file', fileDetails.value.file);
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
            router.push('/');
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
            router.push('/');
        })
        .catch(() => {
            swal('Error!', 'There was an error deleting the file', 'error');
        });
};

// Handle save or delete confirmation
const openConfirmation = (action) => {
    swal({
        title: `Are you sure you want to ${action} this file?`,
        icon: 'warning',
        buttons: true,
        dangerMode: true,
    }).then((willAct) => {
        if (willAct) {
            if (action === 'save') {
                handleSave();
            } else if (action === 'delete') {
                deleteFile();
            }
        }
    });
};

// Handle file validation errors
const handleFileValidationError = (error) => {
    swal('Error!', error, 'error');
};
</script>
