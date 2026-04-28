import { createApp } from 'vue';
import Vue3Toastify, { type ToastContainerOptions } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import './style.css';
import App from './App.vue';
import router from './router';
import 'iconify-icon';

const app = createApp(App);
app.use(router);
app.use(Vue3Toastify, {
    autoClose: 3000,
    theme: 'dark',
    position: 'bottom-right',
} as ToastContainerOptions);
app.mount('#app');
