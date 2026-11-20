<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { BarChart3, Glasses, Printer, ReceiptText, ShieldCheck, TrendingUp, TriangleAlert, Users, Wallet } from 'lucide-vue-next';
import PageHeader from '../../components/PageHeader.vue';
import StatCard from '../../components/StatCard.vue';
import BarChart from '../../components/BarChart.vue';
import EmptyState from '../../components/EmptyState.vue';
import { money } from '../../utils/format';

const props = defineProps({
    filters: { type: Object, default: () => ({}) },
    summary: { type: Object, default: () => ({}) },
    chart: { type: Object, default: () => ({ title: '', labels: [], values: [] }) },
    topProtecciones: { type: Array, default: () => [] },
    topMarcos: { type: Array, default: () => [] },
    topLunas: { type: Array, default: () => [] },
    vendedores: { type: Array, default: () => [] },
    lowStock: { type: Array, default: () => [] },
});

const filters = reactive({
    desde: props.filters.desde || '',
    hasta: props.filters.hasta || '',
});

function applyFilters() {
    router.get(
        route('reportes.index'),
        { desde: filters.desde, hasta: filters.hasta },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function print() {
    window.print();
}
</script>

<template>
    <div>
        <PageHeader title="Reportes" subtitle="Rendimiento de ventas, productos más pedidos e inventario crítico.">
            <template #actions>
                <button type="button" class="btn btn-secondary no-print" @click="print">
                    <Printer class="h-4 w-4" />
                    Imprimir
                </button>
            </template>
        </PageHeader>

        <div class="card no-print mb-5 p-4">
            <form class="flex flex-col gap-2 sm:flex-row sm:items-end" @submit.prevent="applyFilters">
                <div class="flex-1">
                    <label class="label" for="desde">Desde</label>
                    <input id="desde" v-model="filters.desde" type="date" class="input" />
                </div>
                <div class="flex-1">
                    <label class="label" for="hasta">Hasta</label>
                    <input id="hasta" v-model="filters.hasta" type="date" class="input" />
                </div>
                <button type="submit" class="btn btn-primary">Aplicar periodo</button>
            </form>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <StatCard label="Ingresos del periodo" :value="summary.ingresos" :icon="Wallet" tone="emerald" format="money" />
            <StatCard label="Ventas registradas" :value="summary.ventas" :icon="ReceiptText" tone="teal" />
            <StatCard label="Ticket promedio" :value="summary.ticketPromedio" :icon="TrendingUp" tone="violet" format="money" />
            <StatCard label="Ventas canceladas" :value="summary.canceladas" :icon="TriangleAlert" tone="rose" />
        </div>

        <div class="mt-5 grid gap-5 xl:grid-cols-3">
            <div class="card print-area p-5 xl:col-span-2">
                <div class="mb-5 flex items-start justify-between gap-3">
                    <div>
                        <h2 class="flex items-center gap-2 text-base font-semibold text-slate-900">
                            <BarChart3 class="h-4 w-4 text-brand-700" />
                            {{ chart.title }}
                        </h2>
                        <p class="mt-0.5 text-xs text-slate-500">
                            Del {{ filters.desde }} al {{ filters.hasta }}
                        </p>
                    </div>
                </div>
                <BarChart :labels="chart.labels" :values="chart.values" format="money" height="h-64" />
            </div>

            <div class="card p-5">
                <h2 class="flex items-center gap-2 text-base font-semibold text-slate-900">
                    <Users class="h-4 w-4 text-brand-700" />
                    Ventas por vendedor
                </h2>
                <p class="mt-0.5 text-xs text-slate-500">Ingresos generados en el periodo.</p>

                <ul v-if="vendedores.length" class="mt-4 space-y-3">
                    <li v-for="(vendedor, index) in vendedores" :key="vendedor.nombre" class="flex items-center gap-3">
                        <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-[11px] font-bold text-slate-500">
                            {{ index + 1 }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-700">{{ vendedor.nombre }}</p>
                            <p class="text-[11px] text-slate-400">{{ vendedor.ventas }} venta(s)</p>
                        </div>
                        <span class="shrink-0 text-sm font-semibold text-slate-800">{{ money(vendedor.ingresos) }}</span>
                    </li>
                </ul>
                <EmptyState v-else title="Sin ventas en el periodo" message="Cambia el rango de fechas para ver resultados." />
            </div>
        </div>

        <div class="mt-5 grid gap-5 lg:grid-cols-3">
            <div class="card p-5">
                <h2 class="flex items-center gap-2 text-base font-semibold text-slate-900">
                    <ShieldCheck class="h-4 w-4 text-brand-700" />
                    Protecciones más vendidas
                </h2>
                <ul v-if="topProtecciones.length" class="mt-4 space-y-3">
                    <li v-for="item in topProtecciones" :key="item.nombre" class="flex items-center justify-between gap-3 border-b border-slate-100 pb-2.5 last:border-0 last:pb-0">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-700">{{ item.nombre }}</p>
                            <p class="text-[11px] text-slate-400">{{ item.cantidad }} unidad(es)</p>
                        </div>
                        <span class="shrink-0 text-sm font-semibold text-slate-800">{{ money(item.ingresos) }}</span>
                    </li>
                </ul>
                <p v-else class="mt-4 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-500">
                    No se registraron protecciones en este periodo.
                </p>
            </div>

            <div class="card p-5">
                <h2 class="flex items-center gap-2 text-base font-semibold text-slate-900">
                    <Glasses class="h-4 w-4 text-brand-700" />
                    Marcos más vendidos
                </h2>
                <ul v-if="topMarcos.length" class="mt-4 space-y-3">
                    <li v-for="item in topMarcos" :key="item.nombre" class="flex items-center justify-between gap-3 border-b border-slate-100 pb-2.5 last:border-0 last:pb-0">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-700">{{ item.nombre }}</p>
                            <p class="text-[11px] text-slate-400">{{ item.cantidad }} unidad(es)</p>
                        </div>
                        <span class="shrink-0 text-sm font-semibold text-slate-800">{{ money(item.ingresos) }}</span>
                    </li>
                </ul>
                <p v-else class="mt-4 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-500">
                    No se vendieron marcos en este periodo.
                </p>
            </div>

            <div class="card p-5">
                <h2 class="flex items-center gap-2 text-base font-semibold text-slate-900">
                    <TrendingUp class="h-4 w-4 text-brand-700" />
                    Tipos de luna más vendidos
                </h2>
                <ul v-if="topLunas.length" class="mt-4 space-y-3">
                    <li v-for="item in topLunas" :key="item.nombre" class="flex items-center justify-between gap-3 border-b border-slate-100 pb-2.5 last:border-0 last:pb-0">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-700">{{ item.nombre }}</p>
                            <p class="text-[11px] text-slate-400">{{ item.cantidad }} unidad(es)</p>
                        </div>
                        <span class="shrink-0 text-sm font-semibold text-slate-800">{{ money(item.ingresos) }}</span>
                    </li>
                </ul>
                <p v-else class="mt-4 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-500">
                    No se vendieron lunas en este periodo.
                </p>
            </div>
        </div>

        <div class="card mt-5">
            <div class="card-header">
                <div>
                    <h2 class="flex items-center gap-2 text-base font-semibold text-slate-900">
                        <TriangleAlert class="h-4 w-4 text-amber-600" />
                        Marcos por reponer
                    </h2>
                    <p class="mt-0.5 text-xs text-slate-500">Inventario en o por debajo del stock mínimo.</p>
                </div>
                <Link :href="route('marcos.index')" class="text-xs font-semibold text-brand-700 hover:underline">
                    Ver inventario
                </Link>
            </div>

            <div v-if="lowStock.length" class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/70">
                        <tr>
                            <th class="table-th">Marco</th>
                            <th class="table-th">Código</th>
                            <th class="table-th">Stock</th>
                            <th class="table-th">Mínimo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="marco in lowStock" :key="marco.id" class="transition hover:bg-slate-50/60">
                            <td class="table-td font-medium text-slate-800">{{ marco.nombre }}</td>
                            <td class="table-td text-slate-500">{{ marco.codigo }}</td>
                            <td class="table-td">
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="marco.stock === 0 ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700'">
                                    {{ marco.stock }} u.
                                </span>
                            </td>
                            <td class="table-td text-slate-500">{{ marco.stock_minimo }} u.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-else class="px-5 py-8 text-center text-sm text-slate-500">
                Todo el inventario está por encima del stock mínimo.
            </p>
        </div>
    </div>
</template>
