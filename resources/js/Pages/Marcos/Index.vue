<script setup>
import { reactive, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { Boxes, Glasses, PackageCheck, Pencil, Plus, Search, Trash2, TriangleAlert } from 'lucide-vue-next';
import PageHeader from '../../components/PageHeader.vue';
import StatCard from '../../components/StatCard.vue';
import Pagination from '../../components/Pagination.vue';
import EmptyState from '../../components/EmptyState.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import { money } from '../../utils/format';

const props = defineProps({
    marcos: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    marcas: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
});

const page = usePage();
const can = page.props.permissions || {};

const filters = reactive({
    search: props.filters.search || '',
    marca: props.filters.marca || '',
    estado: props.filters.estado || '',
});

function applyFilters() {
    router.get(
        route('marcos.index'),
        { search: filters.search, marca: filters.marca, estado: filters.estado },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function clearFilters() {
    filters.search = '';
    filters.marca = '';
    filters.estado = '';
    router.get(route('marcos.index'), {}, { preserveState: true, preserveScroll: true, replace: true });
}

const deleteTarget = ref(null);
const deleting = ref(false);

function destroy() {
    if (!deleteTarget.value) return;

    deleting.value = true;
    router.delete(route('marcos.destroy', deleteTarget.value.id), {
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
        <PageHeader title="Marcos" subtitle="Inventario de monturas disponibles para la venta.">
            <template #actions>
                <Link v-if="can.manageCatalog" :href="route('marcos.create')" class="btn btn-primary">
                    <Plus class="h-4 w-4" />
                    Nuevo marco
                </Link>
            </template>
        </PageHeader>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <StatCard label="Marcos registrados" :value="stats.total" :icon="Glasses" tone="teal" />
            <StatCard label="Marcos activos" :value="stats.activos" :icon="PackageCheck" tone="emerald" />
            <StatCard label="Unidades en stock" :value="stats.stock" :icon="Boxes" tone="sky" />
            <StatCard label="Stock bajo" :value="stats.bajos" :icon="TriangleAlert" tone="amber" />
        </div>

        <div v-if="!can.manageCatalog" class="mt-4 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
            Estás viendo el catálogo en modo consulta. Solo el dueño o el programador pueden modificar el inventario.
        </div>

        <div class="card mt-5">
            <div class="card-header">
                <form class="flex w-full flex-col gap-2 lg:flex-row lg:items-center" @submit.prevent="applyFilters">
                    <div class="relative flex-1">
                        <Search class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input v-model="filters.search" type="search" class="input pl-10" placeholder="Buscar por código, marca, modelo o color…" />
                    </div>
                    <select v-model="filters.marca" class="input lg:w-44">
                        <option value="">Todas las marcas</option>
                        <option v-for="marca in marcas" :key="marca" :value="marca">{{ marca }}</option>
                    </select>
                    <select v-model="filters.estado" class="input lg:w-40">
                        <option value="">Todos</option>
                        <option value="activo">Activos</option>
                        <option value="inactivo">Inactivos</option>
                        <option value="bajo">Stock bajo</option>
                        <option value="agotado">Agotados</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <button type="button" class="btn btn-ghost" @click="clearFilters">Limpiar</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/70">
                        <tr>
                            <th class="table-th">Marco</th>
                            <th class="table-th">Detalles</th>
                            <th class="table-th">Precio</th>
                            <th class="table-th">Stock</th>
                            <th class="table-th">Estado</th>
                            <th v-if="can.manageCatalog" class="table-th text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="marco in marcos.data" :key="marco.id" class="transition hover:bg-slate-50/60">
                            <td class="table-td">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-100 text-slate-400 ring-1 ring-slate-200">
                                        <img
                                            v-if="marco.foto_url"
                                            :src="marco.foto_url"
                                            :alt="`${marco.marca} ${marco.modelo}`"
                                            class="h-full w-full object-cover"
                                            loading="lazy"
                                        />
                                        <Glasses v-else class="h-5 w-5" />
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-slate-800">{{ marco.marca }} {{ marco.modelo }}</p>
                                        <p class="text-[11px] text-slate-400">{{ marco.codigo }} · {{ marco.color || 'Sin color' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="table-td">
                                <span class="block text-sm">{{ marco.material || '—' }} · {{ marco.forma || '—' }}</span>
                                <span class="block text-[11px] text-slate-400">{{ marco.talla || '—' }}</span>
                            </td>
                            <td class="table-td font-semibold text-slate-800">{{ money(marco.precio) }}</td>
                            <td class="table-td">
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="marco.stock === 0 ? 'bg-rose-50 text-rose-700' : marco.stock_bajo ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700'"
                                >
                                    {{ marco.stock }} u.
                                </span>
                                <span class="ml-1 text-[11px] text-slate-400">mín. {{ marco.stock_minimo }}</span>
                            </td>
                            <td class="table-td">
                                <span class="chip" :class="marco.activo ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-100 text-slate-600 ring-slate-500/20'">
                                    {{ marco.activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td v-if="can.manageCatalog" class="table-td">
                                <div class="flex items-center justify-end gap-1">
                                    <Link :href="route('marcos.edit', marco.id)" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-brand-700" title="Editar">
                                        <Pencil class="h-4 w-4" />
                                    </Link>
                                    <button type="button" class="rounded-lg p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600" title="Eliminar" @click="deleteTarget = marco">
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <EmptyState
                    v-if="marcos.data.length === 0"
                    title="No hay marcos con esos filtros"
                    message="Cambia la búsqueda o agrega un nuevo marco al inventario."
                >
                    <template #icon><Glasses class="h-7 w-7" /></template>
                    <template v-if="can.manageCatalog" #action>
                        <Link :href="route('marcos.create')" class="btn btn-primary btn-sm">
                            <Plus class="h-4 w-4" />
                            Nuevo marco
                        </Link>
                    </template>
                </EmptyState>
            </div>

            <Pagination :links="marcos.links" :from="marcos.from" :to="marcos.to" :total="marcos.total" />
        </div>

        <ConfirmDialog
            :show="Boolean(deleteTarget)"
            title="Eliminar marco"
            :message="`Se eliminará ${deleteTarget?.marca} ${deleteTarget?.modelo} del inventario. Si tiene ventas registradas, el sistema no permitirá borrarlo.`"
            confirm-label="Eliminar marco"
            :processing="deleting"
            @confirm="destroy"
            @close="deleteTarget = null"
        />
    </div>
</template>
