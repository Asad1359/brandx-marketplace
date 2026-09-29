import './echo';           // creates window.Echo (must be first)
import './bootstrap';      // axios only — no Echo
import '../css/app.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';

import App from './App.vue';
import router from './router';

const app = createApp(App);

app.use(createPinia());
app.use(router);

app.mount('#app');