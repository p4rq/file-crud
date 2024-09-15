import { createRouter, createWebHistory } from 'vue-router';
import FileList from '../components/FileList.vue';
import FileForm from '../components/FileForm.vue';
import NotFound from '../components/NotFound.vue';

const routes = [
    { path: '/', name: 'files.index', component: FileList },
    { path: '/files/create', name: 'files.create', component: FileForm },
    { path: '/files/edit/:id', name: 'files.edit', component: FileForm, props: true },
    { path: '/:pathMatch(.*)*', name: 'notfound', component: NotFound }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
