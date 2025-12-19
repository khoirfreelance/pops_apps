import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'

import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'

// Pakai custom bootstrap yang sudah di-override
import './assets/custom.scss'

//createApp(App).use(router).mount('#app')

const app = createApp(App)

app.use(createPinia())
app.use(router)

app.mount('#app')
