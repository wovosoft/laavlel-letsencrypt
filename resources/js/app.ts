// import './bootstrap';
// import '../css/app.css';
import "bootstrap/scss/bootstrap.scss";
import "@wovosoft/wovoui/dist/style.css";

import {createApp, h, DefineComponent} from 'vue';
import {createInertiaApp} from '@inertiajs/vue3';
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import {ZiggyVue} from '../../vendor/tightenco/ziggy/dist/vue.m';
// @ts-ignore
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';
import i18n from "@/Lang/index";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        const page = resolvePageComponent(
            `./Pages/${name}.vue`, import.meta.glob<DefineComponent>('./Pages/**/*.vue')
        );
        // console.log(name)
        if (['Login'].includes(name) || name.startsWith('Auth')) {
            return page;
        }

        page.then((module: DefineComponent) => {
            module.default.layout = module.default.layout || AuthenticatedLayout;
        });
        return page;

    },
    setup({el, App, props, plugin}) {
        createApp({render: () => h(App, props)})
            .use(plugin)
            .use(i18n)
            .use(ZiggyVue, Ziggy)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
