<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { Save, ShieldCheck } from 'lucide-vue-next';
import FormField from '../FormField.vue';

const props = defineProps({
    proteccion: { type: Object, default: null },
});

const esEdicion = computed(() => Boolean(props.proteccion));

const form = useForm({
    nombre: props.proteccion?.nombre || '',
    precio: props.proteccion?.precio ?? '',
    descripcion: props.proteccion?.descripcion || '',
    activo: props.proteccion?.activo ?? true,
});

const submit = () => {
    if (esEdicion.value) {
        form.put(route('protecciones.update', props.proteccion.id));
        return;
    }

    form.post(route('protecciones.store'));
};
</script>

<template>
    <form class="space-y-5" @submit.prevent="submit">
        <div class="card p-5 sm:p-6">
            <div class="mb-5 flex items-center gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                    <ShieldCheck class="h-5 w-5" />
                </span>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Protección para lunas</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Se ofrecerá al vendedor al momento de la venta.</p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <FormField label="Nombre" :error="form.errors.nombre" required hint="Ej. Antirreflejo, Filtro luz azul">
                    <input v-model="form.nombre" type="text" class="input" :class="{ 'input-error': form.errors.nombre }" placeholder="Antirreflejo" />
                </FormField>

                <FormField label="Precio adicional (S/)" :error="form.errors.precio" required>
                    <input v-model="form.precio" type="number" min="0" step="0.01" class="input" :class="{ 'input-error': form.errors.precio }" placeholder="80.00" />
                </FormField>

                <div class="sm:col-span-2">
                    <FormField label="Descripción" :error="form.errors.descripcion">
                        <textarea v-model="form.descripcion" rows="3" class="input" :class="{ 'input-error': form.errors.descripcion }" placeholder="Beneficio que aporta al cliente" />
                    </FormField>
                </div>
            </div>

            <label class="mt-4 flex w-fit items-center gap-2 text-sm text-slate-600">
                <input v-model="form.activo" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
                Disponible para la venta
            </label>
        </div>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <Link :href="route('protecciones.index')" class="btn btn-secondary">Cancelar</Link>
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                <Save class="h-4 w-4" />
                {{ form.processing ? 'Guardando…' : esEdicion ? 'Guardar cambios' : 'Registrar protección' }}
            </button>
        </div>
    </form>
</template>
