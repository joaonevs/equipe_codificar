<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import MensagemFlash from '../Components/MensagemFlash.vue';

const pagina = usePage();
const urlAtual = computed(() => pagina.url);

const navegacao = [
    { rotulo: 'Chamados', href: '/chamados' },
    { rotulo: 'Painel', href: '/painel' },
];

const ativo = (href) => urlAtual.value.startsWith(href);
</script>

<template>
    <div class="min-h-full">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-lg font-semibold tracking-tight text-slate-900">Controle de Chamados</p>
                    <p class="text-sm text-slate-500">Suporte interno</p>
                </div>

                <nav class="flex gap-1" aria-label="Principal">
                    <Link
                        v-for="item in navegacao"
                        :key="item.href"
                        :href="item.href"
                        class="rounded-md px-3 py-2 text-sm font-medium transition-colors"
                        :class="ativo(item.href)
                            ? 'bg-indigo-50 text-indigo-700'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                    >
                        {{ item.rotulo }}
                    </Link>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8">
            <MensagemFlash />
            <slot />
        </main>
    </div>
</template>
