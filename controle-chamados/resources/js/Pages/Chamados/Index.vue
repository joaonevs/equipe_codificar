<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';
import { debounce } from '../../utils/debounce';
import EstadoVazio from '../../Components/EstadoVazio.vue';
import Paginacao from '../../Components/Paginacao.vue';
import PrioridadeBadge from '../../Components/PrioridadeBadge.vue';
import StatusBadge from '../../Components/StatusBadge.vue';

const props = defineProps({
    chamados: { type: Object, required: true },
    responsaveis: { type: Array, required: true },
    filtros: { type: Object, required: true },
    opcoes: { type: Object, required: true },
});

const filtros = reactive({
    busca: props.filtros.busca ?? '',
    status: props.filtros.status ?? '',
    prioridade: props.filtros.prioridade ?? '',
    responsavel_id: props.filtros.responsavel_id ?? '',
    ordenar_por: props.filtros.ordenar_por ?? 'aberto_em',
    direcao: props.filtros.direcao ?? 'desc',
});

const aplicar = debounce(() => {
    const parametros = Object.fromEntries(
        Object.entries(filtros).filter(([, valor]) => valor !== '' && valor !== null),
    );

    router.get('/chamados', parametros, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 300);

watch(filtros, aplicar);

const limpar = () => {
    Object.assign(filtros, {
        busca: '',
        status: '',
        prioridade: '',
        responsavel_id: '',
        ordenar_por: 'aberto_em',
        direcao: 'desc',
    });
};

const campoClasse =
    'block w-full rounded-md border-0 px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600';
</script>

<template>
    <Head title="Chamados" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-slate-900">Chamados</h1>
            <p class="mt-1 text-sm text-slate-500">{{ chamados.meta.total }} chamado(s) no total.</p>
        </div>

        <Link
            href="/chamados/create"
            class="inline-flex justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-500"
        >
            Novo chamado
        </Link>
    </div>

    <div class="mt-6 grid gap-3 rounded-lg bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:grid-cols-2 lg:grid-cols-5">
        <div class="sm:col-span-2 lg:col-span-1">
            <label for="busca" class="sr-only">Buscar</label>
            <input id="busca" v-model="filtros.busca" type="search" placeholder="Buscar por título ou descrição" :class="campoClasse" />
        </div>

        <div>
            <label for="filtro-status" class="sr-only">Status</label>
            <select id="filtro-status" v-model="filtros.status" :class="campoClasse">
                <option value="">Todos os status</option>
                <option v-for="opcao in opcoes.status" :key="opcao.value" :value="opcao.value">{{ opcao.label }}</option>
            </select>
        </div>

        <div>
            <label for="filtro-prioridade" class="sr-only">Prioridade</label>
            <select id="filtro-prioridade" v-model="filtros.prioridade" :class="campoClasse">
                <option value="">Todas as prioridades</option>
                <option v-for="opcao in opcoes.prioridades" :key="opcao.value" :value="opcao.value">{{ opcao.label }}</option>
            </select>
        </div>

        <div>
            <label for="filtro-responsavel" class="sr-only">Responsável</label>
            <select id="filtro-responsavel" v-model="filtros.responsavel_id" :class="campoClasse">
                <option value="">Todos os responsáveis</option>
                <option v-for="responsavel in responsaveis" :key="responsavel.id" :value="responsavel.id">
                    {{ responsavel.nome }}
                </option>
            </select>
        </div>

        <div class="flex gap-2">
            <select v-model="filtros.ordenar_por" :class="campoClasse" aria-label="Ordenar por">
                <option value="aberto_em">Abertura</option>
                <option value="prioridade">Prioridade</option>
                <option value="status">Status</option>
                <option value="titulo">Título</option>
            </select>
            <select v-model="filtros.direcao" :class="campoClasse" aria-label="Direção da ordenação">
                <option value="desc">↓</option>
                <option value="asc">↑</option>
            </select>
        </div>
    </div>

    <div class="mt-6">
        <EstadoVazio
            v-if="chamados.data.length === 0"
            titulo="Nenhum chamado encontrado"
            descricao="Ajuste os filtros ou abra o primeiro chamado da equipe."
        >
            <div class="flex justify-center gap-3">
                <button type="button" class="text-sm font-medium text-indigo-600 hover:text-indigo-500" @click="limpar">
                    Limpar filtros
                </button>
                <Link href="/chamados/create" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    Abrir chamado
                </Link>
            </div>
        </EstadoVazio>

        <div v-else class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold text-slate-600">
                        <th scope="col" class="px-4 py-3">Chamado</th>
                        <th scope="col" class="hidden px-4 py-3 sm:table-cell">Prioridade</th>
                        <th scope="col" class="px-4 py-3">Status</th>
                        <th scope="col" class="hidden px-4 py-3 md:table-cell">Responsável</th>
                        <th scope="col" class="hidden px-4 py-3 lg:table-cell">Aberto em</th>
                        <th scope="col" class="px-4 py-3"><span class="sr-only">Ações</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="chamado in chamados.data" :key="chamado.id" class="text-sm hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <Link :href="`/chamados/${chamado.id}`" class="font-medium text-slate-900 hover:text-indigo-600">
                                {{ chamado.titulo }}
                            </Link>
                            <p class="text-xs text-slate-500">#{{ chamado.id }}</p>
                        </td>
                        <td class="hidden px-4 py-3 sm:table-cell">
                            <PrioridadeBadge :prioridade="chamado.prioridade" :rotulo="chamado.prioridade_rotulo" />
                        </td>
                        <td class="px-4 py-3">
                            <StatusBadge :status="chamado.status" :rotulo="chamado.status_rotulo" />
                        </td>
                        <td class="hidden px-4 py-3 text-slate-600 md:table-cell">
                            {{ chamado.responsavel?.nome ?? 'Sem responsável' }}
                        </td>
                        <td class="hidden px-4 py-3 text-slate-500 lg:table-cell">{{ chamado.aberto_em }}</td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="`/chamados/${chamado.id}/edit`" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                                Editar
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <Paginacao :links="chamados.meta.links" />
        </div>
    </div>
</template>
