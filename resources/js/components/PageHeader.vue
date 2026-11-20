<script setup>
import { Head } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
    back: { type: String, default: '' },
});
</script>

<template>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <Head :title="title" />
            <Link
                v-if="back"
                :href="back"
                class="mb-2 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-brand-700"
            >
                <ArrowLeft class="h-4 w-4" />
                Volver
            </Link>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ title }}</h1>
            <p v-if="subtitle" class="mt-1 max-w-2xl text-sm text-slate-500">{{ subtitle }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>
