<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ClipboardList, Save } from 'lucide-vue-next';
import FormField from '../FormField.vue';

const props = defineProps({
    receta: { type: Object, default: null },
    pacientes: { type: Array, default: () => [] },
    pacienteSeleccionado: { type: [Number, String], default: null },
});

const esEdicion = computed(() => Boolean(props.receta));

const today = new Date();
const localToday = new Date(today.getTime() - today.getTimezoneOffset() * 60000).toISOString().slice(0, 10);

const form = useForm({
    paciente_id: props.receta?.paciente_id || props.pacienteSeleccionado || '',
    fecha: props.receta?.fecha ? String(props.receta.fecha).slice(0, 10) : localToday,
    motivo_consulta: props.receta?.motivo_consulta || '',
    od_esfera: props.receta?.od_esfera ?? '',
    od_cilindro: props.receta?.od_cilindro ?? '',
    od_eje: props.receta?.od_eje ?? '',
    od_adicion: props.receta?.od_adicion ?? '',
    od_av: props.receta?.od_av || '',
    oi_esfera: props.receta?.oi_esfera ?? '',
    oi_cilindro: props.receta?.oi_cilindro ?? '',
    oi_eje: props.receta?.oi_eje ?? '',
    oi_adicion: props.receta?.oi_adicion ?? '',
    oi_av: props.receta?.oi_av || '',
    dip: props.receta?.dip ?? '',
    observaciones: props.receta?.observaciones || '',
});

const submit = () => {
    if (esEdicion.value) {
        form.put(route('recetas.update', props.receta.id));
        return;
    }

    form.post(route('recetas.store'));
};
</script>

<template>
    <form class="space-y-5" @submit.prevent="submit">
        <div class="card p-5 sm:p-6">
            <div class="mb-5">
                <h2 class="text-base font-semibold text-slate-900">Datos del examen</h2>
                <p class="mt-0.5 text-xs text-slate-500">Paciente, fecha y motivo de la evaluación visual.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <FormField label="Paciente" :error="form.errors.paciente_id" required>
                    <select v-model="form.paciente_id" class="input" :class="{ 'input-error': form.errors.paciente_id }">
                        <option value="">Selecciona un paciente</option>
                        <option v-for="paciente in pacientes" :key="paciente.id" :value="paciente.id">
                            {{ paciente.nombre_completo }} · {{ paciente.numero_documento }}
                        </option>
                    </select>
                </FormField>

                <FormField label="Fecha del examen" :error="form.errors.fecha" required>
                    <input v-model="form.fecha" type="date" class="input" :class="{ 'input-error': form.errors.fecha }" />
                </FormField>

                <div class="sm:col-span-2">
                    <FormField label="Motivo de consulta" :error="form.errors.motivo_consulta">
                        <input v-model="form.motivo_consulta" type="text" class="input" :class="{ 'input-error': form.errors.motivo_consulta }" placeholder="Ej. Control visual, visión borrosa, renovación de lentes" />
                    </FormField>
                </div>
            </div>
        </div>

        <div class="card p-5 sm:p-6">
            <div class="mb-5 flex items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                    <ClipboardList class="h-5 w-5" />
                </span>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Medidas de las lunas</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Registra la graduación de cada ojo. Puedes dejar en blanco lo que no aplique.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <div class="min-w-[640px]">
                    <div class="grid grid-cols-[150px_1fr_1fr] gap-3 border-b border-slate-100 pb-3">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Medida</span>
                        <span class="rounded-lg bg-sky-50 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-sky-700">OD · Ojo derecho</span>
                        <span class="rounded-lg bg-violet-50 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-violet-700">OI · Ojo izquierdo</span>
                    </div>

                    <div class="grid grid-cols-[150px_1fr_1fr] items-start gap-3 border-b border-slate-50 py-3">
                        <div>
                            <p class="text-sm font-medium text-slate-700">Esfera</p>
                            <p class="text-[11px] text-slate-400">Dioptrías (esf)</p>
                        </div>
                        <div>
                            <input v-model="form.od_esfera" type="number" step="0.25" class="input" :class="{ 'input-error': form.errors.od_esfera }" placeholder="-1.25" />
                            <p v-if="form.errors.od_esfera" class="mt-1 text-xs text-rose-600">{{ form.errors.od_esfera }}</p>
                        </div>
                        <div>
                            <input v-model="form.oi_esfera" type="number" step="0.25" class="input" :class="{ 'input-error': form.errors.oi_esfera }" placeholder="-1.00" />
                            <p v-if="form.errors.oi_esfera" class="mt-1 text-xs text-rose-600">{{ form.errors.oi_esfera }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-[150px_1fr_1fr] items-start gap-3 border-b border-slate-50 py-3">
                        <div>
                            <p class="text-sm font-medium text-slate-700">Cilindro</p>
                            <p class="text-[11px] text-slate-400">Dioptrías (cil)</p>
                        </div>
                        <div>
                            <input v-model="form.od_cilindro" type="number" step="0.25" class="input" :class="{ 'input-error': form.errors.od_cilindro }" placeholder="-0.50" />
                            <p v-if="form.errors.od_cilindro" class="mt-1 text-xs text-rose-600">{{ form.errors.od_cilindro }}</p>
                        </div>
                        <div>
                            <input v-model="form.oi_cilindro" type="number" step="0.25" class="input" :class="{ 'input-error': form.errors.oi_cilindro }" placeholder="-0.75" />
                            <p v-if="form.errors.oi_cilindro" class="mt-1 text-xs text-rose-600">{{ form.errors.oi_cilindro }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-[150px_1fr_1fr] items-start gap-3 border-b border-slate-50 py-3">
                        <div>
                            <p class="text-sm font-medium text-slate-700">Eje</p>
                            <p class="text-[11px] text-slate-400">Grados (0–180)</p>
                        </div>
                        <div>
                            <input v-model="form.od_eje" type="number" min="0" max="180" step="1" class="input" :class="{ 'input-error': form.errors.od_eje }" placeholder="90" />
                            <p v-if="form.errors.od_eje" class="mt-1 text-xs text-rose-600">{{ form.errors.od_eje }}</p>
                        </div>
                        <div>
                            <input v-model="form.oi_eje" type="number" min="0" max="180" step="1" class="input" :class="{ 'input-error': form.errors.oi_eje }" placeholder="85" />
                            <p v-if="form.errors.oi_eje" class="mt-1 text-xs text-rose-600">{{ form.errors.oi_eje }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-[150px_1fr_1fr] items-start gap-3 border-b border-slate-50 py-3">
                        <div>
                            <p class="text-sm font-medium text-slate-700">Adición</p>
                            <p class="text-[11px] text-slate-400">Para progresivos</p>
                        </div>
                        <div>
                            <input v-model="form.od_adicion" type="number" step="0.25" class="input" :class="{ 'input-error': form.errors.od_adicion }" placeholder="2.00" />
                            <p v-if="form.errors.od_adicion" class="mt-1 text-xs text-rose-600">{{ form.errors.od_adicion }}</p>
                        </div>
                        <div>
                            <input v-model="form.oi_adicion" type="number" step="0.25" class="input" :class="{ 'input-error': form.errors.oi_adicion }" placeholder="2.00" />
                            <p v-if="form.errors.oi_adicion" class="mt-1 text-xs text-rose-600">{{ form.errors.oi_adicion }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-[150px_1fr_1fr] items-start gap-3 py-3">
                        <div>
                            <p class="text-sm font-medium text-slate-700">Agudeza visual</p>
                            <p class="text-[11px] text-slate-400">AV</p>
                        </div>
                        <div>
                            <input v-model="form.od_av" type="text" class="input" :class="{ 'input-error': form.errors.od_av }" placeholder="20/20" />
                            <p v-if="form.errors.od_av" class="mt-1 text-xs text-rose-600">{{ form.errors.od_av }}</p>
                        </div>
                        <div>
                            <input v-model="form.oi_av" type="text" class="input" :class="{ 'input-error': form.errors.oi_av }" placeholder="20/20" />
                            <p v-if="form.errors.oi_av" class="mt-1 text-xs text-rose-600">{{ form.errors.oi_av }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <FormField label="DIP · Distancia interpupilar (mm)" :error="form.errors.dip">
                    <input v-model="form.dip" type="number" step="0.5" min="30" max="90" class="input" :class="{ 'input-error': form.errors.dip }" placeholder="62.5" />
                </FormField>
            </div>

            <div class="mt-4">
                <FormField label="Observaciones" :error="form.errors.observaciones">
                    <textarea v-model="form.observaciones" rows="3" class="input" :class="{ 'input-error': form.errors.observaciones }" placeholder="Indicaciones, diagnóstico o recomendaciones" />
                </FormField>
            </div>

            <div class="mt-4 rounded-xl bg-slate-50 px-4 py-3 text-xs leading-relaxed text-slate-500">
                <strong class="text-slate-700">Guía rápida:</strong> la esfera y el cilindro se expresan en dioptrías (por ejemplo, -1.25);
                el eje en grados (0 a 180) y la adición en dioptrías para lentes progresivos.
            </div>
        </div>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <Link :href="receta ? route('recetas.show', receta.id) : route('recetas.index')" class="btn btn-secondary">Cancelar</Link>
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                <Save class="h-4 w-4" />
                {{ form.processing ? 'Guardando…' : esEdicion ? 'Guardar cambios' : 'Registrar receta' }}
            </button>
        </div>
    </form>
</template>
