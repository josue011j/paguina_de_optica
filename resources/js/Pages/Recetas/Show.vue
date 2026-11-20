<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ArrowLeft, ClipboardList, Pencil, Plus, Printer } from 'lucide-vue-next';

const props = defineProps({
    receta: { type: Object, required: true },
});

const page = usePage();
const can = computed(() => page.props.permissions || {});

const codigo = computed(() => `REC-${String(props.receta.id).padStart(6, '0')}`);

const edad = computed(() => {
    if (!props.receta.paciente?.fecha_nacimiento) return null;
    const birth = new Date(props.receta.paciente.fecha_nacimiento);
    const today = new Date();
    let years = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) years -= 1;
    return years;
});

function value(data) {
    return data === null || data === undefined || data === '' ? '—' : data;
}

function print() {
    window.print();
}
</script>

<template>
    <div>
        <div class="no-print mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <Link :href="route('pacientes.show', receta.paciente_id)" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-brand-700">
                <ArrowLeft class="h-4 w-4" />
                Volver a la ficha del paciente
            </Link>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" class="btn btn-secondary" @click="print">
                    <Printer class="h-4 w-4" />
                    Imprimir
                </button>
                <Link v-if="can.manageRecetas" :href="route('recetas.edit', receta.id)" class="btn btn-secondary">
                    <Pencil class="h-4 w-4" />
                    Editar
                </Link>
                <Link v-if="can.manageSales" :href="route('ventas.create', { paciente: receta.paciente_id })" class="btn btn-primary">
                    <Plus class="h-4 w-4" />
                    Nueva venta
                </Link>
            </div>
        </div>

        <div class="print-area mx-auto max-w-3xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
            <div class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-700 text-white">
                        <ClipboardList class="h-6 w-6" />
                    </span>
                    <div>
                        <p class="text-lg font-bold text-slate-900">Óptica Visión Clara</p>
                        <p class="text-xs font-medium uppercase tracking-wider text-brand-700">Receta óptica</p>
                    </div>
                </div>
                <div class="text-left sm:text-right">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ codigo }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ receta.fecha_label }}</p>
                </div>
            </div>

            <div class="mt-6 grid gap-4 text-sm sm:grid-cols-2">
                <div>
                    <p class="text-[11px] uppercase tracking-wider text-slate-400">Paciente</p>
                    <p class="mt-0.5 font-semibold text-slate-800">{{ receta.paciente?.nombre_completo }}</p>
                    <p class="text-xs text-slate-500">
                        {{ receta.paciente?.tipo_documento }} {{ receta.paciente?.numero_documento }}
                        <span v-if="edad !== null"> · {{ edad }} años</span>
                    </p>
                </div>
                <div class="sm:text-right">
                    <p class="text-[11px] uppercase tracking-wider text-slate-400">Optometrista</p>
                    <p class="mt-0.5 font-semibold text-slate-800">{{ receta.optometrista?.name }}</p>
                    <p class="text-xs text-slate-500">Evaluación visual</p>
                </div>
                <div v-if="receta.motivo_consulta" class="sm:col-span-2">
                    <p class="text-[11px] uppercase tracking-wider text-slate-400">Motivo de consulta</p>
                    <p class="mt-0.5 text-slate-700">{{ receta.motivo_consulta }}</p>
                </div>
            </div>

            <div class="mt-7 overflow-hidden rounded-xl border border-slate-200">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Medida</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-sky-700">OD · Ojo derecho</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-violet-700">OI · Ojo izquierdo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-600">Esfera</td>
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ value(receta.od_esfera) }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ value(receta.oi_esfera) }}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-600">Cilindro</td>
                            <td class="px-4 py-3 text-slate-800">{{ value(receta.od_cilindro) }}</td>
                            <td class="px-4 py-3 text-slate-800">{{ value(receta.oi_cilindro) }}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-600">Eje</td>
                            <td class="px-4 py-3 text-slate-800">{{ receta.od_eje !== null ? `${receta.od_eje}°` : '—' }}</td>
                            <td class="px-4 py-3 text-slate-800">{{ receta.oi_eje !== null ? `${receta.oi_eje}°` : '—' }}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-600">Adición</td>
                            <td class="px-4 py-3 text-slate-800">{{ value(receta.od_adicion) }}</td>
                            <td class="px-4 py-3 text-slate-800">{{ value(receta.oi_adicion) }}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-600">Agudeza visual</td>
                            <td class="px-4 py-3 text-slate-800">{{ value(receta.od_av) }}</td>
                            <td class="px-4 py-3 text-slate-800">{{ value(receta.oi_av) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-slate-50 px-4 py-3">
                    <p class="text-[11px] uppercase tracking-wider text-slate-400">DIP · Distancia interpupilar</p>
                    <p class="mt-0.5 text-lg font-bold text-slate-800">{{ receta.dip ? `${receta.dip} mm` : '—' }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 px-4 py-3">
                    <p class="text-[11px] uppercase tracking-wider text-slate-400">Tipo de luna sugerido</p>
                    <p class="mt-0.5 text-sm font-semibold text-slate-700">
                        {{ receta.od_adicion || receta.oi_adicion ? 'Progresivo / ocupacional' : 'Monofocal' }}
                    </p>
                </div>
            </div>

            <div v-if="receta.observaciones" class="mt-5 rounded-xl border border-slate-200 px-4 py-3">
                <p class="text-[11px] uppercase tracking-wider text-slate-400">Observaciones</p>
                <p class="mt-1 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ receta.observaciones }}</p>
            </div>

            <div class="mt-10 flex items-end justify-between gap-6 text-xs text-slate-400">
                <p>Documento generado por Óptica Visión Clara</p>
                <div class="text-center">
                    <div class="mb-1 w-44 border-t border-slate-300" />
                    Firma del optometrista
                </div>
            </div>
        </div>
    </div>
</template>
