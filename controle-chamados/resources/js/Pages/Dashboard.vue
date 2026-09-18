<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    indicadores: { type: Object, required: true },
    carga: { type: Array, required: true },
});

const cartoes = computed(() => [
    { rotulo: 'Total', valor: props.indicadores.total },
    { rotulo: 'Abertos', valor: props.indicadores.aberto },
    { rotulo: 'Em andamento', valor: props.indicadores.em_andamento },
    { rotulo: 'Resolvidos', valor: props.indicadores.resolvido },
    { rotulo: 'Fechados', valor: props.indicadores.fechado },
]);

const maiorCarga = computed(() => Math.max(1, ...props.carga.map((item) => item.em_aberto)));
</script>

<template>
    <Head title="Painel" />

    <h1 class="text-xl font-semibold tracking-tight text-slate-900">Painel</h1>
    <p class="mt-1 text-sm text-slate-500">Chamados em aberto são os de status Aberto e Em andamento.</p>

    <div class="mt-6 grid gap-4 sm:grid-cols-3 lg:grid-cols-5">
        <div v-for="cartao in cartoes" :key="cartao.rotulo" class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm text-slate-500">{{ cartao.rotulo }}</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-slate-900">{{ cartao.valor }}</p>
        </div>
    </div>

    <div class="mt-8 rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h2 class="text-sm font-semibold text-slate-900">Distribuição por responsável</h2>
        <p class="mt-1 text-sm text-slate-500">A atribuição automática envia o próximo chamado para a menor barra.</p>

        <ul class="mt-4 space-y-4">
            <li v-for="responsavel in carga" :key="responsavel.id">
                <div class="flex items-baseline justify-between text-sm">
                    <span class="font-medium text-slate-800">{{ responsavel.nome }}</span>
                    <span class="tabular-nums text-slate-500">
                        {{ responsavel.em_aberto }} em aberto · {{ responsavel.total }} no total
                    </span>
                </div>
                <div class="mt-2 h-2 rounded-full bg-slate-100">
                    <div
                        class="h-2 rounded-full bg-indigo-500"
                        :style="{ width: `${(responsavel.em_aberto / maiorCarga) * 100}%` }"
                    />
                </div>
            </li>
        </ul>
    </div>

    <div class="mt-6">
        <Link href="/chamados" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Ver todos os chamados</Link>
    </div>
</template>
