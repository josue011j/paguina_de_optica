<script setup>
import { reactive, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { Pencil, Plus, Search, ShieldCheck, Sparkles, Trash2 } from 'lucide-vue-next';
import PageHeader from '../../components/PageHeader.vue';
import StatCard from '../../components/StatCard.vue';
import Pagination from '../../components/Pagination.vue';
import EmptyState from '../../components/EmptyState.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import { money } from '../../utils/format';

const props = defineProps({
    protecciones: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({}) },
});

const page = usePage();
const can = page.props.permissions || {};

const filters = reactive({
    search: props.filters.search || '',
    estado: props.filters.estado || '',
});

function applyFilters() {
    router.get(
        route('protecciones.index'),
        { search: filters.search, estado: filters.estado },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function clearFilters() {
    filters.search = '';
    filters.estado = '';
    router.get(route('protecciones.index'), {}, { preserveState: true, preserveScroll: true, replace: true });
}

const deleteTarget = ref(null);
const deleting = ref(false);

function destroy() {
    if (!deleteTarget.value) return;

    deleting.value = true;
    router.delete(route('protecciones.destroy', deleteTarget.value.id), {
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
        <PageHeader title="Protecciones" subtitle="Tratamientos que el cliente puede agregar a sus lunas.">
            <template #actions>
                <Link v-if="can.manageCatalog" :href="route('protecciones.create')" class="btn btn-primary">
                    <Plus class="h-4 w-4" />
                    Nueva protección
                </Link>
            </template>
        </PageHeader>

        <div class="grid gap-4 sm:grid-cols-3">
            <StatCard label="Protecciones registradas" :value="stats.total" :icon="ShieldCheck" tone="teal" />
            <StatCard label="Disponibles" :value="stats.activas" :icon="Sparkles" tone="emerald" />
            <StatCard label="Precio promedio" :value="stats.precioPromedio" :icon="Sparkles" tone="violet" format="money" />
        </div>

        <div class="card mt-5">
            <div class="card-header">
                <form class="flex w-full flex-col gap-2 sm:flex-row sm:items-center" @submit.prevent="applyFilters">
                    <div class="relative flex-1">
                        <Search class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input v-model="filters.search" type="search" class="input pl-10" placeholder="Buscar protección…" />
                    </div>
                    <select v-model="filters.estado" class="input sm:w-36">
                        <option value="">Todas</option>
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
                            <th class="table-th">Protección</th>
                            <th class="table-th">Precio</th>
                            <th class="table-th">Usada en ventas</th>
                            <th class="table-th">Estado</th>
                            <th v-if="can.manageCatalog" class="table-th text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="proteccion in protecciones.data" :key="proteccion.id" class="transition hover:bg-slate-50/60">
                            <td class="table-td">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                                        <ShieldCheck class="h-4 w-4" />
                                    </span>
                                    <div class="max-w-[320px]">
                                        <p class="truncate font-semibold text-slate-800">{{ proteccion.nombre }}</p>
                                        <p class="truncate text-xs text-slate-400">{{ proteccion.descripcion || 'Sin descripción' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="table-td font-semibold text-slate-800">{{ money(proteccion.precio) }}</td>
                            <td class="table-td">
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ proteccion.ventas_count }}</span>
                            </td>
                            <td class="table-td">
                                <span class="chip" :class="proteccion.activo ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-100 text-slate-600 ring-slate-500/20'">
                                    {{ proteccion.activo ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td v-if="can.manageCatalog" class="table-td">
                                <div class="flex items-center justify-end gap-1">
                                    <Link :href="route('protecciones.edit', proteccion.id)" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-brand-700" title="Editar">
                                        <Pencil class="h-4 w-4" />
                                    </Link>
                                    <button type="button" class="rounded-lg p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600" title="Eliminar" @click="deleteTarget = proteccion">
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <EmptyState v-if="protecciones.data.length === 0" title="No hay protecciones" message="Agrega las protecciones que ofrece tu óptica.">
                    <template #icon><ShieldCheck class="h-7 w-7" /></template>
                    <template v-if="can.manageCatalog" #action>
                        <Link :href="route('protecciones.create')" class="btn btn-primary btn-sm">Nueva protección</Link>
                    </template>
                </EmptyState>
            </div>

            <Pagination :links="protecciones.links" :from="protecciones.from" :to="protecciones.to" :total="protecciones.total" />
        </div>

        <ConfirmDialog
            :show="Boolean(deleteTarget)"
            title="Eliminar protección"
            :message="`Se eliminará “${deleteTarget?.nombre}”. Si ya fue usada en ventas, el sistema no permitirá borrarla.`"
            confirm-label="Eliminar"
            :processing="deleting"
            @confirm="destroy"
            @close="deleteTarget = null"
        />
    </div>
</template>
