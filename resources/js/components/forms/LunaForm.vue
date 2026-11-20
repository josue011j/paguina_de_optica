<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { Save } from 'lucide-vue-next';
import FormField from '../FormField.vue';

const props = defineProps({
    luna: { type: Object, default: null },
    tipos: { type: Object, default: () => ({}) },
});

const esEdicion = computed(() => Boolean(props.luna));

const form = useForm({
    nombre: props.luna?.nombre || '',
    tipo: props.luna?.tipo || 'monofocal',
    precio: props.luna?.precio ?? '',
    descripcion: props.luna?.descripcion || '',
    activo: props.luna?.activo ?? true,
});

const submit = () => {
    if (esEdicion.value) {
        form.put(route('lunas.update', props.luna.id));
        return;
    }

    form.post(route('lunas.store'));
};
</script>

<template>
    <form class="space-y-5" @submit.prevent="submit">
        <div class="card p-5 sm:p-6">
            <div class="mb-5">
                <h2 class="text-base font-semibold text-slate-900">Tipo de luna</h2>
                <p class="mt-0.5 text-xs text-slate-500">El vendedor elegirá uno de estos tipos al cerrar la venta.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <FormField label="Nombre" :error="form.errors.nombre" required hint="Ej. Monofocal estándar">
                    <input v-model="form.nombre" type="text" class="input" :class="{ 'input-error': form.errors.nombre }" placeholder="Monofocal estándar" />
                </FormField>

                <FormField label="Tipo" :error="form.errors.tipo" required>
                    <select v-model="form.tipo" class="input">
                        <option v-for="(label, value) in tipos" :key="value" :value="value">{{ label }}</option>
                    </select>
                </FormField>

                <FormField label="Precio de venta (S/)" :error="form.errors.precio" required>
                    <input v-model="form.precio" type="number" min="0" step="0.01" class="input" :class="{ 'input-error': form.errors.precio }" placeholder="150.00" />
                </FormField>

                <div class="sm:col-span-2">
                    <FormField label="Descripción" :error="form.errors.descripcion">
                        <textarea v-model="form.descripcion" rows="3" class="input" :class="{ 'input-error': form.errors.descripcion }" placeholder="Características del lente" />
                    </FormField>
                </div>
            </div>

            <label class="mt-4 flex w-fit items-center gap-2 text-sm text-slate-600">
                <input v-model="form.activo" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
                Disponible para la venta
            </label>
        </div>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <Link :href="route('lunas.index')" class="btn btn-secondary">Cancelar</Link>
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                <Save class="h-4 w-4" />
                {{ form.processing ? 'Guardando…' : esEdicion ? 'Guardar cambios' : 'Registrar tipo de luna' }}
            </button>
        </div>
    </form>
</template>
