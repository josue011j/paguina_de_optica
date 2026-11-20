<script setup>
import { computed } from 'vue';
import { money } from '../utils/format';

const props = defineProps({
    labels: { type: Array, default: () => [] },
    values: { type: Array, default: () => [] },
    format: { type: String, default: 'money' },
    height: { type: String, default: 'h-56' },
});

const max = computed(() => Math.max(...props.values.map((value) => Number(value || 0)), 1));

const bars = computed(() =>
    props.values.map((value, index) => ({
        label: props.labels[index] || '',
        value: Number(value || 0),
        height: Math.max((Number(value || 0) / max.value) * 100, Number(value || 0) > 0 ? 4 : 1.5),
    })),
);

const showEveryLabel = computed(() => (bars.value.length <= 14 ? 1 : Math.ceil(bars.value.length / 10)));

function formatValue(value) {
    return props.format === 'money' ? money(value) : Number(value || 0).toLocaleString('es-PE');
}
</script>

<template>
    <div>
        <div class="flex items-end gap-1.5 sm:gap-2" :class="height">
            <div v-for="(bar, index) in bars" :key="index" class="group relative flex h-full flex-1 flex-col justify-end">
                <div class="pointer-events-none absolute -top-1 left-1/2 z-10 -translate-x-1/2 whitespace-nowrap rounded-lg bg-slate-900 px-2 py-1 text-[10px] font-semibold text-white opacity-0 shadow-lg transition group-hover:opacity-100">
                    {{ formatValue(bar.value) }}
                </div>
                <div
                    class="w-full rounded-t-md bg-gradient-to-t from-brand-600 to-brand-400 transition group-hover:from-brand-700 group-hover:to-brand-500"
                    :style="{ height: `${bar.height}%` }"
                    :title="`${bar.label}: ${formatValue(bar.value)}`"
                />
            </div>
        </div>
        <div class="mt-2 flex gap-1.5 sm:gap-2">
            <div v-for="(bar, index) in bars" :key="index" class="flex-1 text-center">
                <span
                    v-if="index % showEveryLabel === 0 || index === bars.length - 1"
                    class="block truncate text-[10px] font-medium text-slate-400"
                >
                    {{ bar.label }}
                </span>
            </div>
        </div>
    </div>
</template>
