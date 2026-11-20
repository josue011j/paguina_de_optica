<script setup>
import { computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { Save } from 'lucide-vue-next';
import FormField from '../FormField.vue';

const props = defineProps({
    paciente: { type: Object, default: null },
});

const page = usePage();
const can = computed(() => page.props.permissions || {});
const esEdicion = computed(() => Boolean(props.paciente));

const form = useForm({
    nombres: props.paciente?.nombres || '',
    apellidos: props.paciente?.apellidos || '',
    tipo_documento: props.paciente?.tipo_documento || 'DNI',
    numero_documento: props.paciente?.numero_documento || '',
    fecha_nacimiento: props.paciente?.fecha_nacimiento ? String(props.paciente.fecha_nacimiento).slice(0, 10) : '',
    sexo: props.paciente?.sexo || '',
    telefono: props.paciente?.telefono || '',
    email: props.paciente?.email || '',
    direccion: props.paciente?.direccion || '',
    alergias: props.paciente?.alergias || '',
    observaciones: props.paciente?.observaciones || '',
    activo: props.paciente?.activo ?? true,
});

const submit = () => {
    if (esEdicion.value) {
        form.put(route('pacientes.update', props.paciente.id));
        return;
    }

    form.post(route('pacientes.store'));
};
</script>

<template>
    <form class="space-y-5" @submit.prevent="submit">
        <div class="card p-5 sm:p-6">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Datos personales</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Información básica para identificar al paciente.</p>
                </div>
                <span class="hidden rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500 sm:inline-flex">Paso 1</span>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <FormField label="Nombres" :error="form.errors.nombres" required>
                    <input v-model="form.nombres" type="text" class="input" :class="{ 'input-error': form.errors.nombres }" placeholder="Ej. Lucía" />
                </FormField>

                <FormField label="Apellidos" :error="form.errors.apellidos" required>
                    <input v-model="form.apellidos" type="text" class="input" :class="{ 'input-error': form.errors.apellidos }" placeholder="Ej. Gonzales Ríos" />
                </FormField>

                <FormField label="Tipo de documento" :error="form.errors.tipo_documento" required>
                    <select v-model="form.tipo_documento" class="input">
                        <option value="DNI">DNI</option>
                        <option value="CE">Carné de extranjería</option>
                        <option value="Pasaporte">Pasaporte</option>
                    </select>
                </FormField>

                <FormField label="Número de documento" :error="form.errors.numero_documento" required>
                    <input v-model="form.numero_documento" type="text" class="input" :class="{ 'input-error': form.errors.numero_documento }" placeholder="Ej. 45871236" />
                </FormField>

                <FormField label="Fecha de nacimiento" :error="form.errors.fecha_nacimiento">
                    <input v-model="form.fecha_nacimiento" type="date" class="input" :class="{ 'input-error': form.errors.fecha_nacimiento }" />
                </FormField>

                <FormField label="Sexo" :error="form.errors.sexo">
                    <select v-model="form.sexo" class="input">
                        <option value="">Sin especificar</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Femenino">Femenino</option>
                        <option value="Otro">Otro</option>
                    </select>
                </FormField>
            </div>
        </div>

        <div class="card p-5 sm:p-6">
            <div class="mb-5">
                <h2 class="text-base font-semibold text-slate-900">Datos de contacto</h2>
                <p class="mt-0.5 text-xs text-slate-500">Para avisos de entrega y seguimiento.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <FormField label="Teléfono" :error="form.errors.telefono">
                    <input v-model="form.telefono" type="tel" class="input" :class="{ 'input-error': form.errors.telefono }" placeholder="Ej. 987654321" />
                </FormField>

                <FormField label="Correo electrónico" :error="form.errors.email">
                    <input v-model="form.email" type="email" class="input" :class="{ 'input-error': form.errors.email }" placeholder="paciente@correo.com" />
                </FormField>

                <div class="sm:col-span-2">
                    <FormField label="Dirección" :error="form.errors.direccion">
                        <input v-model="form.direccion" type="text" class="input" :class="{ 'input-error': form.errors.direccion }" placeholder="Av. Los Álamos 245, Lima" />
                    </FormField>
                </div>
            </div>
        </div>

        <div v-if="can.editPatients" class="card p-5 sm:p-6">
            <div class="mb-5">
                <h2 class="text-base font-semibold text-slate-900">Información clínica</h2>
                <p class="mt-0.5 text-xs text-slate-500">Datos relevantes antes de la evaluación visual.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <FormField label="Alergias" :error="form.errors.alergias" hint="Medicamentos, materiales o sustancias.">
                    <textarea v-model="form.alergias" rows="3" class="input" :class="{ 'input-error': form.errors.alergias }" placeholder="Sin alergias conocidas" />
                </FormField>

                <FormField label="Observaciones" :error="form.errors.observaciones">
                    <textarea v-model="form.observaciones" rows="3" class="input" :class="{ 'input-error': form.errors.observaciones }" placeholder="Notas generales del paciente" />
                </FormField>
            </div>

            <label class="mt-4 flex w-fit items-center gap-2 text-sm text-slate-600">
                <input v-model="form.activo" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
                Paciente activo
            </label>
        </div>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <Link :href="paciente ? route('pacientes.show', paciente.id) : route('pacientes.index')" class="btn btn-secondary">
                Cancelar
            </Link>
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                <Save class="h-4 w-4" />
                {{ form.processing ? 'Guardando…' : esEdicion ? 'Guardar cambios' : 'Registrar paciente' }}
            </button>
        </div>
    </form>
</template>
