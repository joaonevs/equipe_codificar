<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: { type: Array, required: true },
});
</script>

<template>
    <nav v-if="links.length > 3" class="flex flex-wrap gap-1" aria-label="Paginação">
        <template v-for="(link, indice) in links" :key="indice">
            <span
                v-if="!link.url"
                class="rounded-md px-3 py-2 text-sm text-slate-400"
                v-html="link.label"
            />
            <Link
                v-else
                :href="link.url"
                preserve-scroll
                class="rounded-md px-3 py-2 text-sm transition-colors"
                :class="link.active
                    ? 'bg-indigo-600 text-white'
                    : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50'"
                v-html="link.label"
            />
        </template>
    </nav>
</template>
