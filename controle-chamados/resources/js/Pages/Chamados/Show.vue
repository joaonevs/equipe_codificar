<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PrioridadeBadge from '../../Components/PrioridadeBadge.vue';
import StatusBadge from '../../Components/StatusBadge.vue';

const props = defineProps({
    chamado: { type: Object, required: true },
});

const confirmandoExclusao = ref(false);
const processando = ref(false);

const redistribuir = () => {
    processando.value = true;
    router.post(`/chamados/${props.chamado.id}/redistribuir`, {}, {
        preserveScroll: true,
        onFinish: () => (processando.value = false),
    });
};

const excluir = () => {
    processando.value = true;
    router.delete(`/chamados/${props.chamado.id}`, {
        onFinish: () => (processando.value = false),
    });
};
</script>

<template>
    <Head :title="`Chamado #${chamado.id}`" />

    <div class="max-w-3xl">
        <Link href="/chamados" class="text-sm font-medium text-slate-500 hover:text-slate-800">Voltar para a listagem</Link>

        <div class="mt-4 rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs text-slate-500">Chamado #{{ chamado.id }}</p>
                    <h1 class="mt-1 text-xl font-semibold tracking-tight text-slate-900">{{ chamado.titulo }}</h1>
                </div>
                <div class="flex gap-2">
                    <StatusBadge :status="chamado.status" :rotulo="chamado.status_rotulo" />
                    <PrioridadeBadge :prioridade="chamado.prioridade" :rotulo="chamado.prioridade_rotulo" />
                </div>
            </div>

            <dl class="mt-6 grid gap-4 border-y border-slate-100 py-4 text-sm sm:grid-cols-3">
                <div>
                    <dt class="text-slate-500">Responsável</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ chamado.responsavel?.nome ?? 'Sem responsável' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Aberto em</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ chamado.aberto_em }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Última atualização</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ chamado.atualizado_em }}</dd>
                </div>
            </dl>

            <div class="mt-6">
                <h2 class="text-sm font-medium text-slate-700">Descrição</h2>
                <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ chamado.descricao }}</p>
            </div>

            <div class="mt-8 flex flex-wrap items-center gap-3">
                <Link
                    :href="`/chamados/${chamado.id}/edit`"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-500"
                >
                    Editar
                </Link>
                <button
                    type="button"
                    :disabled="processando"
                    class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 transition-colors hover:bg-slate-50 disabled:opacity-60"
                    @click="redistribuir"
                >
                    Redistribuir automaticamente
                </button>
                <button
                    type="button"
                    class="ml-auto text-sm font-medium text-rose-600 hover:text-rose-500"
                    @click="confirmandoExclusao = true"
                >
                    Excluir
                </button>
            </div>
        </div>

        <div
            v-if="confirmandoExclusao"
            class="mt-4 rounded-md border border-rose-200 bg-rose-50 p-4"
            role="alertdialog"
            aria-label="Confirmar exclusão"
        >
            <p class="text-sm text-rose-800">Excluir o chamado #{{ chamado.id }}? Essa ação não pode ser desfeita.</p>
            <div class="mt-3 flex gap-3">
                <button
                    type="button"
                    :disabled="processando"
                    class="rounded-md bg-rose-600 px-3 py-2 text-sm font-semibold text-white transition-colors hover:bg-rose-500 disabled:opacity-60"
                    @click="excluir"
                >
                    Sim, excluir
                </button>
                <button type="button" class="text-sm font-medium text-slate-600" @click="confirmandoExclusao = false">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</template>
