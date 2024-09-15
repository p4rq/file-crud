import { createApp } from 'vue';
import index from './router/index.js'; // Настроенный роутер
import 'bootstrap/dist/css/bootstrap.min.css';
import app from "./components/App.vue";
import router from "./router/index.js";
createApp(app).use(router).mount("#app")
