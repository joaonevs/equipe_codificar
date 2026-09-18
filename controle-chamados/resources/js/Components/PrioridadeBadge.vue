<script setup>
import { computed } from 'vue';

const props = defineProps({
    prioridade: { type: String, required: true },
    rotulo: { type: String, required: true },
});

const estilo = computed(() => ({
    alta: 'bg-rose-50 text-rose-700 ring-rose-600/20',
    media: 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
    baixa: 'bg-slate-100 text-slate-600 ring-slate-500/20',
}[props.prioridade] ?? 'bg-slate-100 text-slate-600 ring-slate-500/20'));
</script>

<template>
    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset" :class="estilo">
        {{ rotulo }}
    </span>
</template>
