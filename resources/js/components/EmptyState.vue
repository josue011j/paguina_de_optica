<script setup>
import { Inbox } from 'lucide-vue-next';

defineProps({
    title: { type: String, default: 'Sin registros' },
    message: { type: String, default: 'Todavía no hay información para mostrar.' },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center px-6 py-14 text-center">
        <span class="mb-4 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
            <slot name="icon">
                <Inbox class="h-7 w-7" />
            </slot>
        </span>
        <h3 class="text-base font-semibold text-slate-800">{{ title }}</h3>
        <p class="mt-1 max-w-sm text-sm text-slate-500">{{ message }}</p>
        <div v-if="$slots.action" class="mt-5">
            <slot name="action" />
        </div>
    </div>
</template>
