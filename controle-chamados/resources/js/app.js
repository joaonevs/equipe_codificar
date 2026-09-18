import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import AppLayout from './Layouts/AppLayout.vue';

const paginas = import.meta.glob('./Pages/**/*.vue');

createInertiaApp({
    title: (title) => (title ? `${title} · Controle de Chamados` : 'Controle de Chamados'),
    resolve: async (name) => {
        const pagina = (await paginas[`./Pages/${name}.vue`]()).default;
        pagina.layout = pagina.layout ?? AppLayout;

        return pagina;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4f46e5',
    },
});
