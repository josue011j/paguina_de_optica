<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ClipboardList, Eye, Pencil, Plus, Search, UserCheck, UserPlus, Users } from 'lucide-vue-next';
import PageHeader from '../../components/PageHeader.vue';
import StatCard from '../../components/StatCard.vue';
import Pagination from '../../components/Pagination.vue';
import EmptyState from '../../components/EmptyState.vue';
import { initials, shortDate } from '../../utils/format';

const props = defineProps({
    pacientes: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({}) },
});

const filters = reactive({
    search: props.filters.search || '',
    estado: props.filters.estado || '',
});

function applyFilters() {
    router.get(
        route('pacientes.index'),
        { search: filters.search, estado: filters.estado },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function clearFilters() {
    filters.search = '';
    filters.estado = '';
    router.get(route('pacientes.index'), {}, { preserveState: true, preserveScroll: true, replace: true });
}
</script>

<template>
    <div>
        <PageHeader
            title="Pacientes"
            subtitle="Datos de contacto, recetas e historial de compras."
        >
            <template #actions>
                <Link :href="route('pacientes.create')" class="btn btn-primary">
                    <Plus class="h-4 w-4" />
                    Nuevo paciente
                </Link>
            </template>
        </PageHeader>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <StatCard label="Pacientes registrados" :value="stats.total" :icon="Users" tone="teal" />
            <StatCard label="Pacientes activos" :value="stats.activos" :icon="UserCheck" tone="emerald" />
            <StatCard label="Con receta registrada" :value="stats.conRecetas" :icon="ClipboardList" tone="violet" />
            <StatCard label="Nuevos este mes" :value="stats.nuevosMes" :icon="UserPlus" tone="sky" />
        </div>

        <div class="card mt-5">
            <div class="card-header">
                <form class="flex w-full flex-col gap-2 sm:flex-row sm:items-center" @submit.prevent="applyFilters">
                    <div class="relative flex-1">
                        <Search class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="filters.search"
                            type="search"
                            class="input pl-10"
                            placeholder="Buscar por nombre, documento o teléfono…"
                        />
                    </div>
                    <select v-model="filters.estado" class="input sm:w-40">
                        <option value="">Todos</option>
                        <option value="activo">Activos</option>
                        <option value="inactivo">Inactivos</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <button type="button" class="btn btn-ghost" @click="clearFilters">Limpiar</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/70">
                        <tr>
                            <th class="table-th">Paciente</th>
                            <th class="table-th">Documento</th>
                            <th class="table-th">Contacto</th>
                            <th class="table-th">Recetas</th>
                            <th class="table-th">Compras</th>
                            <th class="table-th">Estado</th>
                            <th class="table-th text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="paciente in pacientes.data" :key="paciente.id" class="transition hover:bg-slate-50/60">
                            <td class="table-td">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-500">
                                        {{ initials(`${paciente.nombres} ${paciente.apellidos}`) }}
                                    </span>
                                    <div class="min-w-0">
                                        <Link :href="route('pacientes.show', paciente.id)" class="block truncate font-semibold text-slate-800 hover:text-brand-700">
                                            {{ paciente.nombres }} {{ paciente.apellidos }}
                                        </Link>
                                        <span class="block text-[11px] text-slate-400">
                                            Nac. {{ paciente.fecha_nacimiento ? shortDate(paciente.fecha_nacimiento) : 'no registrada' }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="table-td">
                                <span class="block text-xs font-medium text-slate-400">{{ paciente.tipo_documento }}</span>
                                <span class="block text-sm text-slate-700">{{ paciente.numero_documento }}</span>
                            </td>
                            <td class="table-td">
                                <span class="block text-sm text-slate-700">{{ paciente.telefono || '—' }}</span>
                                <span class="block max-w-[180px] truncate text-xs text-slate-400">{{ paciente.email || 'Sin correo' }}</span>
                            </td>
                            <td class="table-td">
                                <span class="rounded-full bg-violet-50 px-2 py-0.5 text-xs font-semibold text-violet-700">{{ paciente.recetas_count }}</span>
                            </td>
                            <td class="table-td">
                                <span class="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-semibold text-brand-700">{{ paciente.ventas_count }}</span>
                            </td>
                            <td class="table-td">
                                <span
                                    class="chip"
                                    :class="paciente.activo ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-100 text-slate-600 ring-slate-500/20'"
                                >
                                    {{ paciente.activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="table-td">
                                <div class="flex items-center justify-end gap-1">
                                    <Link :href="route('pacientes.show', paciente.id)" class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-brand-700" title="Ver ficha">
                                        <Eye class="h-4 w-4" />
                                    </Link>
                                    <Link
                                        v-if="$page.props.permissions.editPatients"
                                        :href="route('pacientes.edit', paciente.id)"
                                        class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-brand-700"
                                        title="Editar"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <EmptyState
                    v-if="pacientes.data.length === 0"
                    title="No se encontraron pacientes"
                    message="Prueba con otro término de búsqueda o registra un paciente nuevo."
                >
                    <template #action>
                        <Link :href="route('pacientes.create')" class="btn btn-primary btn-sm">
                            <Plus class="h-4 w-4" />
                            Nuevo paciente
                        </Link>
                    </template>
                </EmptyState>
            </div>

            <Pagination :links="pacientes.links" :from="pacientes.from" :to="pacientes.to" :total="pacientes.total" />
        </div>
    </div>
</template>
