<script setup>
import { reactive, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { Pencil, Plus, Search, ShieldCheck, Trash2, UserCheck, UserCog, Users } from 'lucide-vue-next';
import PageHeader from '../../components/PageHeader.vue';
import StatCard from '../../components/StatCard.vue';
import Pagination from '../../components/Pagination.vue';
import EmptyState from '../../components/EmptyState.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import { initials } from '../../utils/format';

const props = defineProps({
    usuarios: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    roles: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({}) },
});

const page = usePage();

const filters = reactive({
    search: props.filters.search || '',
    role: props.filters.role || '',
});

function applyFilters() {
    router.get(
        route('usuarios.index'),
        { search: filters.search, role: filters.role },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function clearFilters() {
    filters.search = '';
    filters.role = '';
    router.get(route('usuarios.index'), {}, { preserveState: true, preserveScroll: true, replace: true });
}

const roleTones = {
    programador: 'bg-violet-50 text-violet-700 ring-violet-600/20',
    dueno: 'bg-brand-50 text-brand-700 ring-brand-600/20',
    vendedor: 'bg-sky-50 text-sky-700 ring-sky-600/20',
    optometrista: 'bg-amber-50 text-amber-700 ring-amber-600/20',
};

const deleteTarget = ref(null);
const deleting = ref(false);

function destroy() {
    if (!deleteTarget.value) return;

    deleting.value = true;
    router.delete(route('usuarios.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            deleteTarget.value = null;
        },
    });
}
</script>

<template>
    <div>
        <PageHeader title="Usuarios" subtitle="Cuentas del equipo y sus permisos dentro del sistema.">
            <template #actions>
                <Link :href="route('usuarios.create')" class="btn btn-primary">
                    <Plus class="h-4 w-4" />
                    Nuevo usuario
                </Link>
            </template>
        </PageHeader>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <StatCard label="Usuarios registrados" :value="stats.total" :icon="Users" tone="teal" />
            <StatCard label="Cuentas activas" :value="stats.activos" :icon="UserCheck" tone="emerald" />
            <StatCard label="Vendedores" :value="stats.vendedor" :icon="UserCog" tone="sky" />
            <StatCard label="Optometristas" :value="stats.optometrista" :icon="ShieldCheck" tone="amber" />
        </div>

        <div class="card mt-5">
            <div class="card-header">
                <form class="flex w-full flex-col gap-2 sm:flex-row sm:items-center" @submit.prevent="applyFilters">
                    <div class="relative flex-1">
                        <Search class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input v-model="filters.search" type="search" class="input pl-10" placeholder="Buscar por nombre, correo o teléfono…" />
                    </div>
                    <select v-model="filters.role" class="input sm:w-44">
                        <option value="">Todos los roles</option>
                        <option v-for="(label, value) in roles" :key="value" :value="value">{{ label }}</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <button type="button" class="btn btn-ghost" @click="clearFilters">Limpiar</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/70">
                        <tr>
                            <th class="table-th">Usuario</th>
                            <th class="table-th">Rol</th>
                            <th class="table-th">Contacto</th>
                            <th class="table-th">Recetas</th>
                            <th class="table-th">Ventas</th>
                            <th class="table-th">Estado</th>
                            <th class="table-th text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="usuario in usuarios.data" :key="usuario.id" class="transition hover:bg-slate-50/60">
                            <td class="table-td">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-800">
                                        {{ initials(usuario.name) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-slate-800">
                                            {{ usuario.name }}
                                            <span v-if="usuario.id === $page.props.auth.user.id" class="ml-1 text-[11px] font-medium text-slate-400">(tú)</span>
                                        </p>
                                        <p class="truncate text-[11px] text-slate-400">{{ usuario.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="table-td">
                                <span class="chip" :class="roleTones[usuario.role] || 'bg-slate-100 text-slate-600 ring-slate-500/20'">
                                    {{ roles[usuario.role] || usuario.role }}
                                </span>
                            </td>
                            <td class="table-td text-slate-600">{{ usuario.telefono || '—' }}</td>
                            <td class="table-td">
                                <span class="rounded-full bg-violet-50 px-2 py-0.5 text-xs font-semibold text-violet-700">{{ usuario.recetas_count }}</span>
                            </td>
                            <td class="table-td">
                                <span class="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-semibold text-brand-700">{{ usuario.ventas_count }}</span>
                            </td>
                            <td class="table-td">
                                <span class="chip" :class="usuario.activo ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-100 text-slate-600 ring-slate-500/20'">
                                    {{ usuario.activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="table-td">
                                <div class="flex items-center justify-end gap-1">
                                    <Link :href="route('usuarios.edit', usuario.id)" class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-brand-700" title="Editar">
                                        <Pencil class="h-4 w-4" />
                                    </Link>
                                    <button
                                        v-if="usuario.id !== $page.props.auth.user.id"
                                        type="button"
                                        class="rounded-lg p-2 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600"
                                        title="Eliminar"
                                        @click="deleteTarget = usuario"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <EmptyState v-if="usuarios.data.length === 0" title="No se encontraron usuarios" message="Prueba con otro término de búsqueda o crea una cuenta nueva.">
                    <template #icon><Users class="h-7 w-7" /></template>
                    <template #action>
                        <Link :href="route('usuarios.create')" class="btn btn-primary btn-sm">
                            <Plus class="h-4 w-4" />
                            Nuevo usuario
                        </Link>
                    </template>
                </EmptyState>
            </div>

            <Pagination :links="usuarios.links" :from="usuarios.from" :to="usuarios.to" :total="usuarios.total" />
        </div>

        <ConfirmDialog
            :show="Boolean(deleteTarget)"
            title="Eliminar usuario"
            :message="`Se eliminará la cuenta de ${deleteTarget?.name}. Si tiene ventas o recetas registradas, desactívala en lugar de eliminarla.`"
            confirm-label="Eliminar usuario"
            :processing="deleting"
            @confirm="destroy"
            @close="deleteTarget = null"
        />
    </div>
</template>
