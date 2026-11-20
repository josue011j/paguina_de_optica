<script setup>
import { reactive } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { CheckCircle2, Clock3, Eye, Plus, ReceiptText, Search, Wallet } from 'lucide-vue-next';
import PageHeader from '../../components/PageHeader.vue';
import StatCard from '../../components/StatCard.vue';
import Pagination from '../../components/Pagination.vue';
import EmptyState from '../../components/EmptyState.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import { money } from '../../utils/format';

const props = defineProps({
    ventas: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    estados: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({}) },
});

const page = usePage();
const can = page.props.permissions || {};

const filters = reactive({
    search: props.filters.search || '',
    estado: props.filters.estado || '',
    desde: props.filters.desde || '',
    hasta: props.filters.hasta || '',
});

function applyFilters() {
    router.get(
        route('ventas.index'),
        {
            search: filters.search,
            estado: filters.estado,
            desde: filters.desde,
            hasta: filters.hasta,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function clearFilters() {
    filters.search = '';
    filters.estado = '';
    filters.desde = '';
    filters.hasta = '';
    router.get(route('ventas.index'), {}, { preserveState: true, preserveScroll: true, replace: true });
}
</script>

<template>
    <div>
        <PageHeader title="Ventas" subtitle="Pedidos de lentes, protecciones y estado de entrega.">
            <template #actions>
                <Link v-if="can.manageSales" :href="route('ventas.create')" class="btn btn-primary">
                    <Plus class="h-4 w-4" />
                    Nueva venta
                </Link>
            </template>
        </PageHeader>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <StatCard label="Ventas en el periodo" :value="stats.total" :icon="ReceiptText" tone="teal" />
            <StatCard label="Ingresos del periodo" :value="stats.ingresos" :icon="Wallet" tone="emerald" format="money" />
            <StatCard label="Pendientes de entrega" :value="stats.pendientes" :icon="Clock3" tone="amber" />
            <StatCard label="Entregadas" :value="stats.entregadas" :icon="CheckCircle2" tone="sky" />
        </div>

        <div class="card mt-5">
            <div class="card-header">
                <form class="flex w-full flex-col gap-2 lg:flex-row lg:items-center" @submit.prevent="applyFilters">
                    <div class="relative flex-1">
                        <Search class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input v-model="filters.search" type="search" class="input pl-10" placeholder="Buscar por código, paciente o documento…" />
                    </div>
                    <select v-model="filters.estado" class="input lg:w-40">
                        <option value="">Todos los estados</option>
                        <option v-for="(label, value) in estados" :key="value" :value="value">{{ label }}</option>
                    </select>
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
                            <th class="table-th">Venta</th>
                            <th class="table-th">Paciente</th>
                            <th class="table-th">Producto</th>
                            <th class="table-th">Vendedor</th>
                            <th class="table-th">Fecha</th>
                            <th class="table-th">Total</th>
                            <th class="table-th">Estado</th>
                            <th class="table-th text-right">Ver</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="venta in ventas.data" :key="venta.id" class="transition hover:bg-slate-50/60">
                            <td class="table-td">
                                <Link :href="route('ventas.show', venta.id)" class="font-semibold text-brand-700 hover:underline">
                                    {{ venta.codigo }}
                                </Link>
                                <span class="block text-[11px] text-slate-400">{{ venta.metodo_pago_label }}</span>
                            </td>
                            <td class="table-td">
                                <span class="block font-medium text-slate-800">
                                    {{ venta.paciente?.nombres }} {{ venta.paciente?.apellidos }}
                                </span>
                                <span class="block text-[11px] text-slate-400">{{ venta.paciente?.numero_documento }}</span>
                            </td>
                            <td class="table-td">
                                <span class="block max-w-[220px] truncate text-sm">
                                    {{ venta.marco ? `${venta.marco.marca} ${venta.marco.modelo}` : 'Solo lunas' }}
                                </span>
                                <span class="block max-w-[220px] truncate text-[11px] text-slate-400">
                                    {{ venta.luna?.nombre }} · {{ venta.cantidad }} par(es)
                                </span>
                            </td>
                            <td class="table-td text-slate-500">{{ venta.vendedor?.name }}</td>
                            <td class="table-td text-slate-500">{{ venta.fecha_label }}</td>
                            <td class="table-td font-semibold text-slate-800">{{ money(venta.total) }}</td>
                            <td class="table-td">
                                <StatusBadge :label="venta.estado_label" :tone="venta.estado_color" />
                            </td>
                            <td class="table-td">
                                <div class="flex justify-end">
                                    <Link :href="route('ventas.show', venta.id)" class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-brand-700" title="Ver venta">
                                        <Eye class="h-4 w-4" />
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <EmptyState
                    v-if="ventas.data.length === 0"
                    title="No hay ventas en este periodo"
                    message="Prueba con otro rango de fechas o registra la primera venta."
                >
                    <template #icon><ReceiptText class="h-7 w-7" /></template>
                    <template v-if="can.manageSales" #action>
                        <Link :href="route('ventas.create')" class="btn btn-primary btn-sm">
                            <Plus class="h-4 w-4" />
                            Nueva venta
                        </Link>
                    </template>
                </EmptyState>
            </div>

            <Pagination :links="ventas.links" :from="ventas.from" :to="ventas.to" :total="ventas.total" />
        </div>
    </div>
</template>
