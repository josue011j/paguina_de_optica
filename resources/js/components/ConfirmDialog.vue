<script setup>
import { AlertTriangle, X } from 'lucide-vue-next';

defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: 'Confirmar acción' },
    message: { type: String, default: '' },
    confirmLabel: { type: String, default: 'Confirmar' },
    processing: { type: Boolean, default: false },
    danger: { type: Boolean, default: true },
});

const emit = defineEmits(['confirm', 'close']);
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="!processing && emit('close')" />

                <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                    <button
                        type="button"
                        class="absolute right-4 top-4 rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                        :disabled="processing"
                        @click="emit('close')"
                    >
                        <X class="h-4 w-4" />
                    </button>

                    <span
                        class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl"
                        :class="danger ? 'bg-rose-50 text-rose-600' : 'bg-brand-50 text-brand-700'"
                    >
                        <AlertTriangle class="h-6 w-6" />
                    </span>

                    <h3 class="text-lg font-semibold text-slate-900">{{ title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ message }}</p>

                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" class="btn btn-secondary" :disabled="processing" @click="emit('close')">
                            Cancelar
                        </button>
                        <button
                            type="button"
                            class="btn"
                            :class="danger ? 'btn-danger' : 'btn-primary'"
                            :disabled="processing"
                            @click="emit('confirm')"
                        >
                            {{ processing ? 'Procesando…' : confirmLabel }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
