<script setup>
const props = defineProps({
    label: { type: String, required: true },
    tone: { type: String, default: 'slate' },
});

const tones = {
    amber: 'bg-amber-50 text-amber-700 ring-amber-600/20',
    sky: 'bg-sky-50 text-sky-700 ring-sky-600/20',
    emerald: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    rose: 'bg-rose-50 text-rose-700 ring-rose-600/20',
    slate: 'bg-slate-100 text-slate-600 ring-slate-500/20',
    violet: 'bg-violet-50 text-violet-700 ring-violet-600/20',
};

const dots = {
    amber: 'bg-amber-500',
    sky: 'bg-sky-500',
    emerald: 'bg-emerald-500',
    rose: 'bg-rose-500',
    slate: 'bg-slate-400',
    violet: 'bg-violet-500',
};
</script>

<template>
    <span class="chip" :class="tones[props.tone] || tones.slate">
        <span class="h-1.5 w-1.5 rounded-full" :class="dots[props.tone] || dots.slate" />
        {{ label }}
    </span>
</template>
