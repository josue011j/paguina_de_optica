<script setup>
import { reactive, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { Eye, Pencil, Plus, Search, Tag, Trash2, TrendingUp } from 'lucide-vue-next';
import PageHeader from '../../components/PageHeader.vue';
import StatCard from '../../components/StatCard.vue';
import Pagination from '../../components/Pagination.vue';
import EmptyState from '../../components/EmptyState.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import { money } from '../../utils/format';

const props = defineProps({
    lunas: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    tipos: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({}) },
});

const page = usePage();
const can = page.props.permissions || {};

const filters = reactive({
    search: props.filters.search || '',
    tipo: props.filters.tipo || '',
    estado: props.filters.estado || '',
});

function applyFilters() {
    router.get(
        route('lunas.index'),
        { search: filters.search, tipo: filters.tipo, estado: filters.estado },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function clearFilters() {
    filters.search = '';
    filters.tipo = '';
    filters.estado = '';
    router.get(route('lunas.index'), {}, { preserveState: true, preserveScroll: true, replace: true });
}

const deleteTarget = ref(null);
const deleting = ref(false);

function destroy() {
    if (!deleteTarget.value) return;

    deleting.value = true;
    router.delete(route('lunas.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            deleteTarget.value = null;
        },
    });
}

const tipoTones = {
    monofocal: 'sky',
    bifocal: 'violet',
    progresivo: 'teal',
    ocupacional: 'amber',
};

const chipClasses = {
    sky: 'bg-sky-50 text-sky-700 ring-sky-600/20',
    violet: 'bg-violet-50 text-violet-700 ring-violet-600/20',
    teal: 'bg-brand-50 text-brand-700 ring-brand-600/20',
    amber: 'bg-amber-50 text-amber-700 ring-amber-600/20',
};
</script>

<template>
    <div>
        <PageHeader title="Tipos de luna" subtitle="Lentes base que se combinan con las protecciones elegidas por el cliente.">
            <template #actions>
                <Link v-if="can.manageCatalog" :href="route('lunas.create')" class="btn btn-primary">
                    <Plus class="h-4 w-4" />
                    Nuevo tipo de luna
                </Link>
            </template>
        </PageHeader>

        <div class="grid gap-4 sm:grid-cols-3">
            <StatCard label="Tipos registrados" :value="stats.total" :icon="Eye" tone="teal" />
            <StatCard label="Disponibles para venta" :value="stats.activas" :icon="Tag" tone="emerald" />
            <StatCard label="Precio promedio" :value="stats.precioPromedio" :icon="TrendingUp" tone="violet" format="money" />
        </div>

        <div class="card mt-5">
            <div class="card-header">
                <form class="flex w-full flex-col gap-2 sm:flex-row sm:items-center" @submit.prevent="applyFilters">
                    <div class="relative flex-1">
                        <Search class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input v-model="filters.search" type="search" class="input pl-10" placeholder="Buscar luna…" />
                    </div>
                    <select v-model="filters.tipo" class="input sm:w-44">
                        <option value="">Todos los tipos</option>
                        <option v-for="(label, value) in tipos" :key="value" :value="value">{{ label }}</option>
                    </select>
                    <select v-model="filters.estado" class="input sm:w-36">
                        <option value="">Todos</option>
                        <option value="activo">Activas</option>
                        <option value="inactivo">Inactivas</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <button type="button" class="btn btn-ghost" @click="clearFilters">Limpiar</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/70">
                        <tr>
                            <th class="table-th">Luna</th>
                            <th class="table-th">Tipo</th>
                            <th class="table-th">Precio</th>
                            <th class="table-th">Ventas</th>
                            <th class="table-th">Estado</th>
                            <th v-if="can.manageCatalog" class="table-th text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="luna in lunas.data" :key="luna.id" class="transition hover:bg-slate-50/60">
                            <td class="table-td">
                                <div class="max-w-[280px]">
                                    <p class="truncate font-semibold text-slate-800">{{ luna.nombre }}</p>
                                    <p class="truncate text-xs text-slate-400">{{ luna.descripcion || 'Sin descripción' }}</p>
                                </div>
                            </td>
                            <td class="table-td">
                                <span class="chip" :class="chipClasses[tipoTones[luna.tipo]] || chipClasses.teal">{{ luna.tipo_label }}</span>
                            </td>
                            <td class="table-td font-semibold text-slate-800">{{ money(luna.precio) }}</td>
                            <td class="table-td">
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ luna.ventas_count }}</span>
                            </td>
                            <td class="table-td">
                                <span class="chip" :class="luna.activo ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-100 text-slate-600 ring-slate-500/20'">
                                    {{ luna.activo ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td v-if="can.manageCatalog" class="table-td">
                                <div class="flex items-center justify-end gap-1">
                                    <Link :href="route('lunas.edit', luna.id)" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-brand-700" title="Editar">
                                        <Pencil class="h-4 w-4" />
                                    </Link>
                                    <button type="button" class="rounded-lg p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600" title="Eliminar" @click="deleteTarget = luna">
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <EmptyState v-if="lunas.data.length === 0" title="No hay tipos de luna" message="Agrega el primer tipo de luna para usarlo en las ventas.">
                    <template #icon><Eye class="h-7 w-7" /></template>
                    <template v-if="can.manageCatalog" #action>
                        <Link :href="route('lunas.create')" class="btn btn-primary btn-sm">Nuevo tipo de luna</Link>
                    </template>
                </EmptyState>
            </div>

            <Pagination :links="lunas.links" :from="lunas.from" :to="lunas.to" :total="lunas.total" />
        </div>

        <ConfirmDialog
            :show="Boolean(deleteTarget)"
            title="Eliminar tipo de luna"
            :message="`Se eliminará “${deleteTarget?.nombre}”. Si tiene ventas asociadas, el sistema no permitirá borrarlo.`"
            confirm-label="Eliminar"
            :processing="deleting"
            @confirm="destroy"
            @close="deleteTarget = null"
        />
    </div>
</template>
