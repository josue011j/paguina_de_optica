<script setup>
import { computed } from 'vue';
import { money } from '../utils/format';

const props = defineProps({
    label: { type: String, required: true },
    value: { type: [Number, String], default: 0 },
    icon: { type: [Object, Function], default: null },
    tone: { type: String, default: 'teal' },
    format: { type: String, default: 'number' },
    hint: { type: String, default: '' },
});

const tones = {
    teal: 'bg-brand-50 text-brand-700 ring-brand-600/10',
    sky: 'bg-sky-50 text-sky-700 ring-sky-600/10',
    violet: 'bg-violet-50 text-violet-700 ring-violet-600/10',
    amber: 'bg-amber-50 text-amber-700 ring-amber-600/10',
    emerald: 'bg-emerald-50 text-emerald-700 ring-emerald-600/10',
    rose: 'bg-rose-50 text-rose-700 ring-rose-600/10',
};

const display = computed(() => (props.format === 'money' ? money(props.value) : Number(props.value || 0).toLocaleString('es-PE')));
</script>

<template>
    <div class="card p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-slate-500">{{ label }}</p>
                <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ display }}</p>
                <p v-if="hint" class="mt-1 text-xs text-slate-400">{{ hint }}</p>
            </div>
            <span v-if="icon" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ring-1 ring-inset" :class="tones[tone] || tones.teal">
                <component :is="icon" class="h-5 w-5" />
            </span>
        </div>
    </div>
</template>
