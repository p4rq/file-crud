<template>
    <div class="container mt-5">
        <!-- File list header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Files</h1>
            <div>
                <input
                    type="text"
                    class="form-control"
                    v-model="searchQuery"
                    placeholder="Search files..."
                    @input="updateQuery"
                    style="width: 300px; display: inline-block;"
                />
                <button class="btn btn-secondary ms-2" @click="newFile">Add File</button>
            </div>
        </div>

        <!-- File list table -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-light">
                <tr>
                    <th scope="col">Image</th>
                    <th scope="col">Name</th>
                    <th scope="col">Category</th>
                    <th scope="col">Size (MB)</th>
                    <th scope="col">Path</th>
                    <th scope="col">Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="file in files" :key="file.id">
                    <td>
                        <img
                            :src="getImagePath(file)"
                            alt="File Image"
                            class="img-fluid"
                            style="height: 100px; width: 100px; object-fit: cover;"
                        />
                    </td>
                    <td>{{ file.name }}</td>
                    <td>{{ file.category }}</td>
                    <td>{{ (file.size / 1024 / 1024).toFixed(2) }}</td>
                    <td>{{ file.path }}</td>
                    <td>
                        <a :href="'/storage/' + file.path" class="btn btn-primary btn-sm me-1" download>Download</a>
                        <button class="btn btn-success btn-sm me-1" @click="editFile(file.id)">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" @click="confirmDelete(file.id)">
                            <i class="far fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center my-3">
            <div>Showing {{ files.length }} of {{ totalFiles }} files</div>
            <nav>
                <ul class="pagination">
                    <li class="page-item" :class="{ disabled: currentPage === 1 }">
                        <button class="page-link" @click="prevPage">Previous</button>
                    </li>
                    <li class="page-item" v-for="page in totalPages" :key="page"
                        :class="{ active: page === currentPage }">
                        <button class="page-link" @click="changePage(page)">{{ page }}</button>
                    </li>
                    <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                        <button class="page-link" @click="nextPage">Next</button>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import swal from 'sweetalert'; // Importing the original SweetAlert
import { useRouter } from 'vue-router';

const files = ref([]);
const totalFiles = ref(0);
const currentPage = ref(1);
const perPage = 50; // Number of items per page
const searchQuery = ref('');

const router = useRouter();

// Function to get the image path
const getImagePath = (file) => {
    // Определите, что такое изображение в вашем контексте
    const imageCategories = ['Image']; // Добавьте сюда все категории, которые считаете изображениями

    // Проверьте, является ли категория изображения
    const isImage = imageCategories.includes(file.category);

    // Возвращаем путь в зависимости от проверки
    return isImage ? `/storage/${file.path}` : '/storage/no-image.jpg';
};




// Fetch files
const fetchFiles = async () => {
    const response = await axios.get('/api/files', {
        params: { page: currentPage.value, search: searchQuery.value },
    });
    files.value = response.data.data; // Paginated data
    totalFiles.value = response.data.total; // Total number of files
};

// Total number of pages
const totalPages = computed(() => {
    return Math.ceil(totalFiles.value / perPage);
});

const updateQuery = () => {
    router.push({
        path: '/',
        query: { search: searchQuery.value },
    });

    fetchFiles(); // Re-fetch files with the updated search query
};

// Pagination methods
const changePage = (page) => {
    if (page !== currentPage.value) {
        currentPage.value = page;
        fetchFiles();
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
        fetchFiles();
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
        fetchFiles();
    }
};

// File actions
const newFile = () => {
    router.push('/files/create');
};

const editFile = (id) => {
    router.push(`/files/edit/${id}`);
};

// Confirmation and deletion using SweetAlert (original)
const confirmDelete = (id) => {
    swal({
        title: "Are you sure?",
        text: "Once deleted, you will not be able to recover this file!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            deleteFile(id);
        }
    });
};

// Delete file function
const deleteFile = async (id) => {
    try {
        await axios.delete(`/api/files/${id}`);
        swal("Poof! Your file has been deleted!", {
            icon: "success",
        });
        fetchFiles(); // Refresh file list after deletion
    } catch (error) {
        swal("Error! Something went wrong while deleting the file.", {
            icon: "error",
        });
    }
};

// Fetch files on component mount
onMounted(fetchFiles);
</script>
