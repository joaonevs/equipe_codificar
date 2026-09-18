<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import ChamadoForm from '../../Components/ChamadoForm.vue';

const props = defineProps({
    chamado: { type: Object, required: true },
    responsaveis: { type: Array, required: true },
    opcoes: { type: Object, required: true },
});

const form = useForm({
    titulo: props.chamado.titulo,
    descricao: props.chamado.descricao,
    prioridade: props.chamado.prioridade,
    status: props.chamado.status,
    responsavel_id: props.chamado.responsavel_id,
});

const enviar = () => form.put(`/chamados/${props.chamado.id}`, { preserveScroll: true });
</script>

<template>
    <Head :title="`Editar chamado #${chamado.id}`" />

    <div class="max-w-3xl">
        <h1 class="text-xl font-semibold tracking-tight text-slate-900">Editar chamado #{{ chamado.id }}</h1>
        <p class="mt-1 text-sm text-slate-500">Aberto em {{ chamado.aberto_em }}.</p>

        <div class="mt-6 rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <ChamadoForm
                :form="form"
                :responsaveis="responsaveis"
                :opcoes="opcoes"
                texto-botao="Salvar alterações"
                :cancelar-href="`/chamados/${chamado.id}`"
                @submit="enviar"
            />
        </div>
    </div>
</template>
