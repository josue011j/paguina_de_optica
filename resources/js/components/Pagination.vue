<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    links: { type: Array, default: () => [] },
    from: { type: Number, default: 0 },
    to: { type: Number, default: 0 },
    total: { type: Number, default: 0 },
});

const labels = {
    '&laquo; Previous': '‹ Anterior',
    'Next &raquo;': 'Siguiente ›',
};

const items = computed(() =>
    props.links.map((link) => ({
        ...link,
        shortLabel: labels[link.label] || link.label,
    })),
);
</script>

<template>
    <div v-if="total > 0" class="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-slate-500">
            Mostrando <span class="font-semibold text-slate-700">{{ from }}</span> a
            <span class="font-semibold text-slate-700">{{ to }}</span> de
            <span class="font-semibold text-slate-700">{{ total }}</span> registros
        </p>
        <div v-if="items.length > 3" class="flex flex-wrap items-center gap-1">
            <template v-for="(link, index) in items" :key="index">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-2.5 text-sm font-medium transition"
                    :class="link.active ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'"
                    v-html="link.shortLabel"
                />
                <span
                    v-else
                    class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-2.5 text-sm font-medium text-slate-300"
                    v-html="link.shortLabel"
                />
            </template>
        </div>
    </div>
</template>
