<script setup>
defineProps({
    titulo: { type: String, required: true },
    descricao: { type: String, default: null },
});
</script>

<template>
    <div class="rounded-md border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
        <p class="text-sm font-medium text-slate-900">{{ titulo }}</p>
        <p v-if="descricao" class="mt-1 text-sm text-slate-500">{{ descricao }}</p>
        <div class="mt-4">
            <slot />
        </div>
    </div>
</template>
