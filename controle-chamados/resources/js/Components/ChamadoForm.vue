<script setup>
import { Link } from '@inertiajs/vue3';
import CampoFormulario from './CampoFormulario.vue';

const props = defineProps({
    form: { type: Object, required: true },
    responsaveis: { type: Array, required: true },
    opcoes: { type: Object, required: true },
    // A opção de atribuição automática existe apenas na abertura do chamado.
    permitirAutomatica: { type: Boolean, default: false },
    textoBotao: { type: String, default: 'Salvar' },
    cancelarHref: { type: String, default: '/chamados' },
});

const emit = defineEmits(['submit']);

const campoClasse =
    'block w-full rounded-md border-0 px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600';
</script>

<template>
    <form class="space-y-6" @submit.prevent="emit('submit')">
        <CampoFormulario rotulo="Título" para="titulo" :erro="form.errors.titulo">
            <input
                id="titulo"
                v-model="form.titulo"
                type="text"
                maxlength="150"
                :class="campoClasse"
                placeholder="Ex.: Notebook não liga após atualização"
            />
        </CampoFormulario>

        <CampoFormulario rotulo="Descrição" para="descricao" :erro="form.errors.descricao">
            <textarea
                id="descricao"
                v-model="form.descricao"
                rows="5"
                :class="campoClasse"
                placeholder="Descreva o problema, o que já foi tentado e o impacto no trabalho."
            />
        </CampoFormulario>

        <div class="grid gap-6 sm:grid-cols-2">
            <CampoFormulario rotulo="Prioridade" para="prioridade" :erro="form.errors.prioridade">
                <select id="prioridade" v-model="form.prioridade" :class="campoClasse">
                    <option v-for="opcao in opcoes.prioridades" :key="opcao.value" :value="opcao.value">
                        {{ opcao.label }}
                    </option>
                </select>
            </CampoFormulario>

            <CampoFormulario rotulo="Status" para="status" :erro="form.errors.status">
                <select id="status" v-model="form.status" :class="campoClasse">
                    <option v-for="opcao in opcoes.status" :key="opcao.value" :value="opcao.value">
                        {{ opcao.label }}
                    </option>
                </select>
            </CampoFormulario>
        </div>

        <div class="space-y-4 rounded-md bg-slate-50 p-4 ring-1 ring-slate-200">
            <label v-if="permitirAutomatica" class="flex items-start gap-3">
                <input
                    v-model="form.atribuicao_automatica"
                    type="checkbox"
                    class="mt-1 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600"
                />
                <span class="text-sm">
                    <span class="font-medium text-slate-900">Distribuir automaticamente</span>
                    <span class="block text-slate-500">
                        O chamado vai para quem tiver menos chamados em aberto. Em caso de empate, para o cadastro mais antigo.
                    </span>
                </span>
            </label>

            <CampoFormulario
                v-if="!permitirAutomatica || !form.atribuicao_automatica"
                rotulo="Responsável"
                para="responsavel_id"
                :erro="form.errors.responsavel_id"
            >
                <select id="responsavel_id" v-model="form.responsavel_id" :class="campoClasse">
                    <option :value="null">Selecione um responsável</option>
                    <option v-for="responsavel in responsaveis" :key="responsavel.id" :value="responsavel.id">
                        {{ responsavel.nome }} ({{ responsavel.chamados_em_aberto }} em aberto)
                    </option>
                </select>
            </CampoFormulario>
        </div>

        <div class="flex items-center gap-3">
            <button
                type="submit"
                :disabled="form.processing"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-60"
            >
                {{ form.processing ? 'Salvando…' : textoBotao }}
            </button>
            <Link :href="cancelarHref" class="text-sm font-medium text-slate-600 hover:text-slate-900">Cancelar</Link>
        </div>
    </form>
</template>
