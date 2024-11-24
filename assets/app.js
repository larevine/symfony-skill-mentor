import './styles/app.scss';

import { createApp } from 'vue'
import App from './components/App.vue'

// Start the Mootools application
document.addEventListener('DOMContentLoaded', () => {
    const appElement = document.querySelector('app')
    if (appElement) {
        createApp(App).mount('app')
    }
});
