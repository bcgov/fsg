import './bootstrap';
// import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { globalMixins } from './globalMixins'; // Import the global mixins file

// @ts-ignore
const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'FSG';

createInertiaApp({
    id: 'app',
    title: (title) => `${title} - ${appName}`,
    // resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    resolve: (name) => {
        let parts = name.split('::')
        let type = false;
        if (parts.length > 1) type = parts[0]
        if(type) return require(`../../Modules/${type}/resources/assets/js/Pages/${parts[1]}.vue`).default
        return require(`./Pages/${name}.vue`).default
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mixin(globalMixins);
        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
