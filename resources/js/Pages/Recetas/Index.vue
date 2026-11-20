<script setup>
import { reactive, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { CalendarCheck2, ClipboardList, Eye, Pencil, Plus, Search, Trash2, Users } from 'lucide-vue-next';
import PageHeader from '../../components/PageHeader.vue';
import StatCard from '../../components/StatCard.vue';
import Pagination from '../../components/Pagination.vue';
import EmptyState from '../../components/EmptyState.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';

const props = defineProps({
    recetas: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({}) },
});

const page = usePage();
const can = page.props.permissions || {};

const filters = reactive({
    search: props.filters.search || '',
    desde: props.filters.desde || '',
    hasta: props.filters.hasta || '',
});

function applyFilters() {
    router.get(
        route('recetas.index'),
        { search: filters.search, desde: filters.desde, hasta: filters.hasta },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function clearFilters() {
    filters.search = '';
    filters.desde = '';
    filters.hasta = '';
    router.get(route('recetas.index'), {}, { preserveState: true, preserveScroll: true, replace: true });
}

const deleteTarget = ref(null);
const deleting = ref(false);

function destroy() {
    if (!deleteTarget.value) return;

    deleting.value = true;
    router.delete(route('recetas.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            deleteTarget.value = null;
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
        <PageHeader title="Recetas" subtitle="Medidas registradas por el optometrista para cada paciente.">
            <template #actions>
                <Link v-if="can.manageRecetas" :href="route('recetas.create')" class="btn btn-primary">
                    <Plus class="h-4 w-4" />
                    Nueva receta
                </Link>
            </template>
        </PageHeader>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <StatCard label="Recetas registradas" :value="stats.total" :icon="ClipboardList" tone="teal" />
            <StatCard label="Recetas de hoy" :value="stats.hoy" :icon="CalendarCheck2" tone="sky" />
            <StatCard label="Recetas del mes" :value="stats.mes" :icon="Eye" tone="violet" />
            <StatCard label="Pacientes evaluados" :value="stats.pacientes" :icon="Users" tone="emerald" />
        </div>

        <div class="card mt-5">
            <div class="card-header">
                <form class="flex w-full flex-col gap-2 lg:flex-row lg:items-center" @submit.prevent="applyFilters">
                    <div class="relative flex-1">
                        <Search class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input v-model="filters.search" type="search" class="input pl-10" placeholder="Buscar por paciente o documento…" />
                    </div>
                    <div class="flex items-center gap-2">
                        <input v-model="filters.desde" type="date" class="input" title="Desde" />
                        <span class="text-xs text-slate-400">al</span>
                        <input v-model="filters.hasta" type="date" class="input" title="Hasta" />
                    </div>
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <button type="button" class="btn btn-ghost" @click="clearFilters">Limpiar</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/70">
                        <tr>
                            <th class="table-th">Fecha</th>
                            <th class="table-th">Paciente</th>
                            <th class="table-th">Optometrista</th>
                            <th class="table-th">OD</th>
                            <th class="table-th">OI</th>
                            <th class="table-th">DIP</th>
                            <th class="table-th text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="receta in recetas.data" :key="receta.id" class="transition hover:bg-slate-50/60">
                            <td class="table-td font-medium text-slate-700">{{ receta.fecha_label }}</td>
                            <td class="table-td">
                                <Link :href="route('pacientes.show', receta.paciente_id)" class="font-semibold text-slate-800 hover:text-brand-700">
                                    {{ receta.paciente?.nombre_completo }}
                                </Link>
                                <span class="block text-[11px] text-slate-400">{{ receta.paciente?.numero_documento }}</span>
                            </td>
                            <td class="table-td text-slate-500">{{ receta.optometrista?.name }}</td>
                            <td class="table-td text-xs">{{ rx(receta.od_esfera, receta.od_cilindro, receta.od_eje) }}</td>
                            <td class="table-td text-xs">{{ rx(receta.oi_esfera, receta.oi_cilindro, receta.oi_eje) }}</td>
                            <td class="table-td">{{ receta.dip ? `${receta.dip} mm` : '—' }}</td>
                            <td class="table-td">
                                <div class="flex items-center justify-end gap-1">
                                    <Link :href="route('recetas.show', receta.id)" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-brand-700" title="Ver receta">
                                        <Eye class="h-4 w-4" />
                                    </Link>
                                    <Link :href="route('recetas.edit', receta.id)" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-brand-700" title="Editar">
                                        <Pencil class="h-4 w-4" />
                                    </Link>
                                    <button type="button" class="rounded-lg p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600" title="Eliminar" @click="deleteTarget = receta">
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <EmptyState v-if="recetas.data.length === 0" title="No hay recetas registradas" message="Registra la primera receta para vincular las medidas con una venta.">
                    <template #icon><ClipboardList class="h-7 w-7" /></template>
                    <template #action>
                        <Link :href="route('recetas.create')" class="btn btn-primary btn-sm">Nueva receta</Link>
                    </template>
                </EmptyState>
            </div>

            <Pagination :links="recetas.links" :from="recetas.from" :to="recetas.to" :total="recetas.total" />
        </div>

        <ConfirmDialog
            :show="Boolean(deleteTarget)"
            title="Eliminar receta"
            :message="`Se eliminará la receta de ${deleteTarget?.paciente?.nombre_completo || 'este paciente'}. Si está asociada a una venta no se podrá borrar.`"
            confirm-label="Eliminar receta"
            :processing="deleting"
            @confirm="destroy"
            @close="deleteTarget = null"
        />
    </div>
</template>
