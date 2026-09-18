<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import ChamadoForm from '../../Components/ChamadoForm.vue';

defineProps({
    responsaveis: { type: Array, required: true },
    opcoes: { type: Object, required: true },
});

const form = useForm({
    titulo: '',
    descricao: '',
    prioridade: 'media',
    status: 'aberto',
    atribuicao_automatica: true,
    responsavel_id: null,
});

const enviar = () => form.post('/chamados', { preserveScroll: true });
</script>

<template>
    <Head title="Novo chamado" />

    <div class="max-w-3xl">
        <h1 class="text-xl font-semibold tracking-tight text-slate-900">Novo chamado</h1>
        <p class="mt-1 text-sm text-slate-500">A data e a hora de abertura são registradas automaticamente.</p>

        <div class="mt-6 rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <ChamadoForm
                :form="form"
                :responsaveis="responsaveis"
                :opcoes="opcoes"
                permitir-automatica
                texto-botao="Abrir chamado"
                @submit="enviar"
            />
        </div>
    </div>
</template>
