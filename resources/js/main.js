import { createApp } from 'vue';
import { createPinia } from 'pinia';
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';
import Tooltip from 'primevue/tooltip';
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';

import 'primevue/resources/themes/lara-dark-blue/theme.css';
import 'primevue/resources/primevue.css';
import 'primeicons/primeicons.css';

import App from './App.vue';
import router from './router';
import '../css/light-mode.css';

import './bootstrap';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);
app.use(PrimeVue, {
    ripple: true,
    inputStyle: 'filled',
});
app.use(ToastService);
app.use(ConfirmationService);
app.use(Toast, {
    position: 'top-right',
    timeout: 4000,
    closeOnClick: true,
    pauseOnHover: true,
    draggable: true,
    hideProgressBar: false,
    toastClassName: 'cofund-toast',
    bodyClassName: 'cofund-toast-body',
});
app.directive('tooltip', Tooltip);

app.mount('#app');

