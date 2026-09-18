<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: { type: String, required: true },
    rotulo: { type: String, required: true },
});

const estilo = computed(() => ({
    aberto: 'bg-sky-50 text-sky-700 ring-sky-600/20',
    em_andamento: 'bg-amber-50 text-amber-800 ring-amber-600/20',
    resolvido: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    fechado: 'bg-slate-100 text-slate-600 ring-slate-500/20',
}[props.status] ?? 'bg-slate-100 text-slate-600 ring-slate-500/20'));
</script>

<template>
    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset" :class="estilo">
        {{ rotulo }}
    </span>
</template>
