<script setup>
defineProps({
    rotulo: { type: String, required: true },
    para: { type: String, required: true },
    erro: { type: String, default: null },
    ajuda: { type: String, default: null },
});
</script>

<template>
    <div>
        <label :for="para" class="block text-sm font-medium text-slate-700">{{ rotulo }}</label>
        <div class="mt-1">
            <slot />
        </div>
        <p v-if="ajuda && !erro" class="mt-1 text-xs text-slate-500">{{ ajuda }}</p>
        <p v-if="erro" class="mt-1 text-sm text-rose-600">{{ erro }}</p>
    </div>
</template>
