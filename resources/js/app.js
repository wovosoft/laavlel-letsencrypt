import 'bootstrap/dist/css/bootstrap.css';
import "@wovosoft/wovoui/dist/style.css";
// import "./../css/custom.css";

import {createApp, h} from 'vue';
import {createInertiaApp, router} from '@inertiajs/vue3';
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import {ZiggyVue} from '../../vendor/tightenco/ziggy/dist/vue.m';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'BKB GoAML';
// @ts-ignore
import AppLayout from "@/Layouts/AuthenticatedLayout.vue";
import i18n from "@/Lang";
import {isLoading} from "@/Composables/useLoading";

router.on('start', () => isLoading.value = true);
router.on('finish', () => isLoading.value = false);

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        const page = resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        );
        if (['Login'].includes(name) || name.startsWith('Auth')) {
            return page;
        }
        page.then((module) => {
            module.default.layout = module.default.layout || AppLayout;
        });
        return page;
    },

    // @ts-ignore
    setup({el, App, props, plugin}) {
        return createApp({render: () => h(App, props)})
            .use(i18n)
            .use(plugin)
            .use(ZiggyVue, window['Ziggy'])
            .mount(el);
    },
    progress: {color: '#4B5563'}
});
