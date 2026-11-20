<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
    AlertCircle,
    CalendarDays,
    ClipboardList,
    Eye,
    Mail,
    MapPin,
    Pencil,
    Phone,
    Plus,
    ReceiptText,
    Trash2,
} from 'lucide-vue-next';
import PageHeader from '../../components/PageHeader.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import EmptyState from '../../components/EmptyState.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import { initials, money, shortDate } from '../../utils/format';

const props = defineProps({
    paciente: { type: Object, required: true },
    puedeVerRecetas: { type: Boolean, default: false },
    puedeVerVentas: { type: Boolean, default: false },
});

const page = usePage();
const can = computed(() => page.props.permissions || {});

const edad = computed(() => {
    if (!props.paciente.fecha_nacimiento) return null;
    const birth = new Date(props.paciente.fecha_nacimiento);
    const today = new Date();
    let years = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        years -= 1;
    }
    return years;
});

const recetas = computed(() => props.paciente.recetas || []);
const ventas = computed(() => props.paciente.ventas || []);

const deleteOpen = ref(false);
const deleting = ref(false);

function destroy() {
    deleting.value = true;
    router.delete(route('pacientes.destroy', props.paciente.id), {
        onFinish: () => {
            deleting.value = false;
            deleteOpen.value = false;
        },
    });
}

function rx(esfera, cilindro, eje) {
    const parts = [];
    if (esfera !== null && esfera !== undefined && esfera !== '') parts.push(`Esf ${esfera}`);
    if (cilindro !== null && cilindro !== undefined && cilindro !== '') parts.push(`Cil ${cilindro}`);
    if (eje !== null && eje !== undefined && eje !== '') parts.push(`Eje ${eje}°`);
    return parts.length ? parts.join(' · ') : '—';
}
</script>

<template>
    <div>
        <PageHeader
            :title="`${paciente.nombres} ${paciente.apellidos}`"
            :subtitle="`${paciente.tipo_documento} ${paciente.numero_documento}`"
            :back="route('pacientes.index')"
        >
            <template #actions>
                <button v-if="can.destroyPatients" type="button" class="btn btn-secondary text-rose-600" @click="deleteOpen = true">
                    <Trash2 class="h-4 w-4" />
                    Eliminar
                </button>
                <Link v-if="can.editPatients" :href="route('pacientes.edit', paciente.id)" class="btn btn-secondary">
                    <Pencil class="h-4 w-4" />
                    Editar
                </Link>
                <Link v-if="can.manageRecetas" :href="route('recetas.create', { paciente: paciente.id })" class="btn btn-secondary">
                    <ClipboardList class="h-4 w-4" />
                    Nueva receta
                </Link>
                <Link v-if="can.manageSales" :href="route('ventas.create', { paciente: paciente.id })" class="btn btn-primary">
                    <Plus class="h-4 w-4" />
                    Nueva venta
                </Link>
            </template>
        </PageHeader>

        <div class="grid gap-5 xl:grid-cols-3">
            <div class="space-y-5 xl:col-span-2">
                <div class="card overflow-hidden">
                    <div class="h-20 bg-gradient-to-r from-brand-700 via-brand-600 to-teal-500" />
                    <div class="px-5 pb-5">
                        <div class="-mt-8 flex items-end gap-4">
                            <span class="inline-flex h-16 w-16 items-center justify-center rounded-2xl border-4 border-white bg-brand-100 text-xl font-bold text-brand-800 shadow-sm">
                                {{ initials(`${paciente.nombres} ${paciente.apellidos}`) }}
                            </span>
                            <div class="pb-1">
                                <span class="chip" :class="paciente.activo ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-100 text-slate-600 ring-slate-500/20'">
                                    {{ paciente.activo ? 'Paciente activo' : 'Paciente inactivo' }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <div class="flex items-center gap-3 rounded-xl bg-slate-50 px-3 py-2.5">
                                <CalendarDays class="h-4 w-4 text-slate-400" />
                                <div>
                                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Nacimiento</p>
                                    <p class="text-sm font-medium text-slate-700">
                                        {{ paciente.fecha_nacimiento ? shortDate(paciente.fecha_nacimiento) : 'No registrado' }}
                                        <span v-if="edad !== null" class="text-slate-400">· {{ edad }} años</span>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 rounded-xl bg-slate-50 px-3 py-2.5">
                                <Phone class="h-4 w-4 text-slate-400" />
                                <div>
                                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Teléfono</p>
                                    <p class="text-sm font-medium text-slate-700">{{ paciente.telefono || 'No registrado' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 rounded-xl bg-slate-50 px-3 py-2.5">
                                <Mail class="h-4 w-4 text-slate-400" />
                                <div class="min-w-0">
                                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Correo</p>
                                    <p class="truncate text-sm font-medium text-slate-700">{{ paciente.email || 'No registrado' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 rounded-xl bg-slate-50 px-3 py-2.5">
                                <MapPin class="h-4 w-4 text-slate-400" />
                                <div class="min-w-0">
                                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Dirección</p>
                                    <p class="truncate text-sm font-medium text-slate-700">{{ paciente.direccion || 'No registrada' }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="paciente.alergias || paciente.observaciones" class="mt-4 rounded-xl border border-amber-200 bg-amber-50/60 p-4">
                            <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-amber-700">
                                <AlertCircle class="h-4 w-4" />
                                Información clínica
                            </p>
                            <p v-if="paciente.alergias" class="mt-2 text-sm text-amber-900"><strong>Alergias:</strong> {{ paciente.alergias }}</p>
                            <p v-if="paciente.observaciones" class="mt-1 text-sm text-amber-900"><strong>Observaciones:</strong> {{ paciente.observaciones }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="puedeVerRecetas" class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="text-base font-semibold text-slate-900">Recetas</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Medidas registradas por el optometrista.</p>
                        </div>
                        <Link v-if="can.manageRecetas" :href="route('recetas.create', { paciente: paciente.id })" class="text-xs font-semibold text-brand-700 hover:underline">
                            Añadir receta
                        </Link>
                    </div>

                    <div v-if="recetas.length" class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50/70">
                                <tr>
                                    <th class="table-th">Fecha</th>
                                    <th class="table-th">Ojo derecho (OD)</th>
                                    <th class="table-th">Ojo izquierdo (OI)</th>
                                    <th class="table-th">DIP</th>
                                    <th class="table-th text-right">Ver</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="receta in recetas" :key="receta.id" class="transition hover:bg-slate-50/60">
                                    <td class="table-td">
                                        <span class="block font-medium text-slate-700">{{ receta.fecha_label }}</span>
                                        <span class="block text-[11px] text-slate-400">{{ receta.optometrista?.name }}</span>
                                    </td>
                                    <td class="table-td text-xs">{{ rx(receta.od_esfera, receta.od_cilindro, receta.od_eje) }}</td>
                                    <td class="table-td text-xs">{{ rx(receta.oi_esfera, receta.oi_cilindro, receta.oi_eje) }}</td>
                                    <td class="table-td">{{ receta.dip ? `${receta.dip} mm` : '—' }}</td>
                                    <td class="table-td text-right">
                                        <Link :href="route('recetas.show', receta.id)" class="inline-flex rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-brand-700">
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <EmptyState v-else title="Sin recetas registradas" message="El optometrista aún no registró las medidas de este paciente.">
                        <template #icon><ClipboardList class="h-7 w-7" /></template>
                    </EmptyState>
                </div>

                <div v-if="puedeVerVentas" class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="text-base font-semibold text-slate-900">Historial de compras</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Últimas ventas asociadas al paciente.</p>
                        </div>
                        <Link v-if="can.manageSales" :href="route('ventas.create', { paciente: paciente.id })" class="text-xs font-semibold text-brand-700 hover:underline">
                            Nueva venta
                        </Link>
                    </div>

                    <div v-if="ventas.length" class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50/70">
                                <tr>
                                    <th class="table-th">Venta</th>
                                    <th class="table-th">Fecha</th>
                                    <th class="table-th">Total</th>
                                    <th class="table-th">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="venta in ventas" :key="venta.id" class="transition hover:bg-slate-50/60">
                                    <td class="table-td">
                                        <Link :href="route('ventas.show', venta.id)" class="font-semibold text-brand-700 hover:underline">{{ venta.codigo }}</Link>
                                    </td>
                                    <td class="table-td text-slate-500">{{ venta.fecha_label }}</td>
                                    <td class="table-td font-semibold text-slate-800">{{ money(venta.total) }}</td>
                                    <td class="table-td"><StatusBadge :label="venta.estado_label" :tone="venta.estado_color" /></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <EmptyState v-else title="Sin compras registradas" message="Cuando se registre una venta, aparecerá en esta sección.">
                        <template #icon><ReceiptText class="h-7 w-7" /></template>
                    </EmptyState>
                </div>
            </div>

            <div class="space-y-5">
                <div class="card p-5">
                    <h2 class="text-base font-semibold text-slate-900">Resumen del paciente</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Recetas</dt>
                            <dd class="font-semibold text-slate-800">{{ recetas.length }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Compras</dt>
                            <dd class="font-semibold text-slate-800">{{ ventas.length }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Sexo</dt>
                            <dd class="font-semibold text-slate-800">{{ paciente.sexo || '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Registrado</dt>
                            <dd class="font-semibold text-slate-800">{{ shortDate(paciente.created_at) }}</dd>
                        </div>
                    </dl>
                </div>

                <div v-if="can.manageRecetas" class="rounded-2xl border border-brand-200 bg-brand-50 p-5">
                    <p class="flex items-center gap-2 text-sm font-semibold text-brand-900">
                        <ClipboardList class="h-4 w-4" />
                        Siguiente paso
                    </p>
                    <p class="mt-2 text-sm leading-relaxed text-brand-800/80">
                        Registra una nueva receta antes de vender los lentes para que el vendedor conozca las
                        medidas exactas.
                    </p>
                    <Link :href="route('recetas.create', { paciente: paciente.id })" class="btn btn-primary btn-sm mt-3">
                        Registrar receta
                    </Link>
                </div>
            </div>
        </div>

        <ConfirmDialog
            :show="deleteOpen"
            title="Eliminar paciente"
            :message="`Se eliminará a ${paciente.nombres} ${paciente.apellidos}. Su historial de ventas se conservará, pero dejará de aparecer en la lista.`"
            confirm-label="Sí, eliminar"
            :processing="deleting"
            @confirm="destroy"
            @close="deleteOpen = false"
        />
    </div>
</template>
