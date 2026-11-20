<script setup>
defineProps({
    label: { type: String, default: '' },
    error: { type: String, default: '' },
    hint: { type: String, default: '' },
    required: { type: Boolean, default: false },
});
</script>

<template>
    <div>
        <label v-if="label" class="label">
            {{ label }}
            <span v-if="required" class="text-rose-500">*</span>
        </label>
        <slot />
        <p v-if="error" class="mt-1.5 text-xs font-medium text-rose-600">{{ error }}</p>
        <p v-else-if="hint" class="mt-1.5 text-xs text-slate-500">{{ hint }}</p>
    </div>
</template>
