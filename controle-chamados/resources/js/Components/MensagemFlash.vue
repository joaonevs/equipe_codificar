<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const pagina = usePage();
const visivel = ref(true);

const sucesso = computed(() => pagina.props.flash?.sucesso);
const erro = computed(() => pagina.props.flash?.erro);

watch([sucesso, erro], () => {
    visivel.value = true;
});
</script>

<template>
    <div v-if="visivel && (sucesso || erro)" class="mb-6">
        <div
            class="flex items-start justify-between gap-4 rounded-md border px-4 py-3 text-sm"
            :class="sucesso
                ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                : 'border-rose-200 bg-rose-50 text-rose-800'"
            role="status"
        >
            <p>{{ sucesso || erro }}</p>
            <button type="button" class="font-medium underline" @click="visivel = false">Fechar</button>
        </div>
    </div>
</template>
