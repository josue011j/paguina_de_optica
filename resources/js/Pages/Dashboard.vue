<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
    CheckCircle2,
    ClipboardList,
    Clock,
    Eye,
    Glasses,
    Package,
    Plus,
    ReceiptText,
    ShieldCheck,
    TrendingUp,
    UserPlus,
    Users,
    Wallet,
} from 'lucide-vue-next';
import StatCard from '../components/StatCard.vue';
import BarChart from '../components/BarChart.vue';
import StatusBadge from '../components/StatusBadge.vue';
import EmptyState from '../components/EmptyState.vue';
import { money } from '../utils/format';

const props = defineProps({
    role: { type: String, default: 'dueno' },
    stats: { type: Array, default: () => [] },
    chart: { type: Object, default: () => ({ labels: [], values: [], title: '', format: 'money' }) },
    recentSales: { type: Array, default: () => [] },
    recentRecetas: { type: Array, default: () => [] },
    lowStock: { type: Array, default: () => [] },
    topProtecciones: { type: Array, default: () => [] },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const can = computed(() => page.props.permissions || {});
const isOptometrista = computed(() => props.role === 'optometrista');

const icons = {
    wallet: Wallet,
    receipt: ReceiptText,
    users: Users,
    package: Package,
    clock: Clock,
    check: CheckCircle2,
    clipboard: ClipboardList,
    eye: Eye,
    userPlus: UserPlus,
};

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Buenos días';
    if (hour < 19) return 'Buenas tardes';
    return 'Buenas noches';
});

const firstName = computed(() => (user.value?.name || '').split(' ')[0]);

const roleCopy = computed(() => {
    if (props.role === 'vendedor') {
        return 'Registra las ventas y elige las protecciones que pidió el cliente.';
    }
    if (props.role === 'optometrista') {
        return 'Gestiona a los pacientes y registra las medidas de sus lunas.';
    }
    return 'Tienes una visión completa de la operación de la óptica.';
});
</script>

<template>
    <div>
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-medium text-brand-700">{{ greeting }}, {{ firstName }}</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Panel de control</h1>
                <p class="mt-1 max-w-2xl text-sm text-slate-500">{{ roleCopy }}</p>
            </div>
            <span class="inline-flex w-fit items-center gap-2 rounded-full border border-brand-200 bg-brand-50 px-3 py-1.5 text-xs font-semibold text-brand-700">
                <ShieldCheck class="h-3.5 w-3.5" />
                {{ user?.role_label }}
            </span>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <StatCard
                v-for="stat in stats"
                :key="stat.label"
                :label="stat.label"
                :value="stat.value"
                :icon="icons[stat.icon]"
                :tone="stat.tone"
                :format="stat.format"
            />
        </div>

        <div class="mt-5 grid gap-5 xl:grid-cols-3">
            <div class="card p-5 xl:col-span-2">
                <div class="mb-5 flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">{{ chart.title }}</h2>
                        <p class="mt-0.5 text-xs text-slate-500">
                            {{ chart.format === 'money' ? 'Importe registrado por día' : 'Recetas registradas por día' }}
                        </p>
                    </div>
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                        <TrendingUp class="h-4 w-4" />
                    </span>
                </div>
                <BarChart :labels="chart.labels" :values="chart.values" :format="chart.format" />
            </div>

            <div class="card p-5">
                <h2 class="text-base font-semibold text-slate-900">Acciones rápidas</h2>
                <p class="mt-0.5 text-xs text-slate-500">Atajos según tu rol.</p>

                <div class="mt-4 space-y-2">
                    <Link v-if="can.manageSales" :href="route('ventas.create')" class="flex items-center gap-3 rounded-xl border border-slate-200 p-3 transition hover:border-brand-300 hover:bg-brand-50/40">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-700"><Plus class="h-4 w-4" /></span>
                        <span>
                            <span class="block text-sm font-semibold text-slate-800">Nueva venta</span>
                            <span class="block text-xs text-slate-500">Marco, lunas y protecciones</span>
                        </span>
                    </Link>
                    <Link v-if="can.createPatients" :href="route('pacientes.create')" class="flex items-center gap-3 rounded-xl border border-slate-200 p-3 transition hover:border-brand-300 hover:bg-brand-50/40">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-sky-50 text-sky-700"><UserPlus class="h-4 w-4" /></span>
                        <span>
                            <span class="block text-sm font-semibold text-slate-800">Registrar paciente</span>
                            <span class="block text-xs text-slate-500">Datos de contacto e historia</span>
                        </span>
                    </Link>
                    <Link v-if="can.manageRecetas" :href="route('recetas.create')" class="flex items-center gap-3 rounded-xl border border-slate-200 p-3 transition hover:border-brand-300 hover:bg-brand-50/40">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-violet-50 text-violet-700"><ClipboardList class="h-4 w-4" /></span>
                        <span>
                            <span class="block text-sm font-semibold text-slate-800">Nueva receta</span>
                            <span class="block text-xs text-slate-500">Medidas OD y OI</span>
                        </span>
                    </Link>
                    <Link v-if="can.manageCatalog" :href="route('marcos.create')" class="flex items-center gap-3 rounded-xl border border-slate-200 p-3 transition hover:border-brand-300 hover:bg-brand-50/40">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-700"><Glasses class="h-4 w-4" /></span>
                        <span>
                            <span class="block text-sm font-semibold text-slate-800">Agregar marco</span>
                            <span class="block text-xs text-slate-500">Inventario y precios</span>
                        </span>
                    </Link>
                </div>

                <div v-if="topProtecciones.length" class="mt-5 border-t border-slate-100 pt-4">
                    <p class="section-title">Protecciones más vendidas</p>
                    <ul class="mt-3 space-y-2.5">
                        <li v-for="(item, index) in topProtecciones" :key="item.nombre" class="flex items-center gap-2 text-sm">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-slate-100 text-[11px] font-bold text-slate-500">{{ index + 1 }}</span>
                            <span class="flex-1 truncate text-slate-600">{{ item.nombre }}</span>
                            <span class="font-semibold text-slate-800">{{ item.total }}</span>
                        </li>
                    </ul>
                </div>

                <div v-if="!isOptometrista && lowStock.length" class="mt-5 border-t border-slate-100 pt-4">
                    <p class="section-title">Stock bajo</p>
                    <ul class="mt-3 space-y-2">
                        <li v-for="marco in lowStock.slice(0, 3)" :key="marco.id" class="flex items-center justify-between gap-2 text-sm">
                            <span class="truncate text-slate-600">{{ marco.nombre }}</span>
                            <span class="shrink-0 rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700">{{ marco.stock }} u.</span>
                        </li>
                    </ul>
                    <Link :href="route('marcos.index')" class="mt-3 inline-flex text-xs font-semibold text-brand-700 hover:underline">Ver inventario</Link>
                </div>
            </div>
        </div>

        <div class="mt-5 grid gap-5 xl:grid-cols-3">
            <div class="card xl:col-span-2">
                <div class="card-header">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">{{ isOptometrista ? 'Recetas recientes' : 'Ventas recientes' }}</h2>
                        <p class="mt-0.5 text-xs text-slate-500">
                            {{ isOptometrista ? 'Últimos pacientes evaluados' : 'Movimientos más recientes de la óptica' }}
                        </p>
                    </div>
                    <Link v-if="isOptometrista" :href="route('recetas.index')" class="text-xs font-semibold text-brand-700 hover:underline">Ver todas</Link>
                    <Link v-else-if="can.viewSales" :href="route('ventas.index')" class="text-xs font-semibold text-brand-700 hover:underline">Ver todas</Link>
                </div>

                <div v-if="isOptometrista" class="divide-y divide-slate-100">
                    <Link
                        v-for="receta in recentRecetas"
                        :key="receta.id"
                        :href="route('recetas.show', receta.id)"
                        class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-slate-50"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ receta.paciente }}</p>
                            <p class="mt-0.5 truncate text-xs text-slate-500">{{ receta.motivo || 'Evaluación visual' }}</p>
                        </div>
                        <span class="shrink-0 text-xs text-slate-400">{{ receta.fecha }}</span>
                    </Link>
                    <EmptyState v-if="recentRecetas.length === 0" title="Aún no registraste recetas" message="Cuando evalúes a un paciente, aparecerá aquí.">
                        <template #action>
                            <Link :href="route('recetas.create')" class="btn btn-primary btn-sm">Nueva receta</Link>
                        </template>
                    </EmptyState>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50/70">
                            <tr>
                                <th class="table-th">Venta</th>
                                <th class="table-th">Paciente</th>
                                <th class="table-th">Fecha</th>
                                <th class="table-th">Total</th>
                                <th class="table-th">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="venta in recentSales" :key="venta.id" class="transition hover:bg-slate-50/60">
                                <td class="table-td">
                                    <Link :href="route('ventas.show', venta.id)" class="font-semibold text-brand-700 hover:underline">{{ venta.codigo }}</Link>
                                </td>
                                <td class="table-td">{{ venta.paciente }}</td>
                                <td class="table-td text-slate-500">{{ venta.fecha }}</td>
                                <td class="table-td font-semibold text-slate-800">{{ money(venta.total) }}</td>
                                <td class="table-td"><StatusBadge :label="venta.estado" :tone="venta.estado_color" /></td>
                            </tr>
                        </tbody>
                    </table>
                    <EmptyState v-if="recentSales.length === 0" title="Todavía no hay ventas" message="Registra la primera venta para ver el resumen aquí.">
                        <template #action>
                            <Link v-if="can.manageSales" :href="route('ventas.create')" class="btn btn-primary btn-sm">Nueva venta</Link>
                        </template>
                    </EmptyState>
                </div>
            </div>

            <div class="card p-5">
                <h2 class="text-base font-semibold text-slate-900">
                    {{ isOptometrista ? 'Consejo del día' : 'Marcos por reponer' }}
                </h2>
                <p class="mt-0.5 text-xs text-slate-500">
                    {{ isOptometrista ? 'Recuerda registrar la agudeza visual y la DIP.' : 'Productos en o por debajo del mínimo.' }}
                </p>

                <template v-if="isOptometrista">
                    <div class="mt-4 rounded-xl bg-brand-50 p-4 text-sm leading-relaxed text-brand-900">
                        <p class="font-semibold">Una receta completa evita errores de venta.</p>
                        <p class="mt-1 text-brand-800/80">
                            Registra esfera, cilindro, eje, adición y distancia interpupilar para cada ojo. El
                            vendedor verá esta información al cerrar la venta.
                        </p>
                    </div>
                    <Link :href="route('pacientes.index')" class="mt-4 flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-brand-300 hover:bg-brand-50/40">
                        Ver pacientes
                        <Users class="h-4 w-4 text-slate-400" />
                    </Link>
                </template>

                <template v-else>
                    <ul v-if="lowStock.length" class="mt-4 space-y-3">
                        <li v-for="marco in lowStock" :key="marco.id" class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 px-3 py-2.5">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-slate-700">{{ marco.nombre }}</p>
                                <p class="text-[11px] text-slate-400">{{ marco.codigo }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-bold" :class="marco.stock === 0 ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700'">
                                {{ marco.stock }} / {{ marco.stock_minimo }}
                            </span>
                        </li>
                    </ul>
                    <p v-else class="mt-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        Todo el inventario está por encima del stock mínimo.
                    </p>
                    <Link v-if="can.viewCatalog" :href="route('marcos.index')" class="mt-3 inline-flex text-xs font-semibold text-brand-700 hover:underline">
                        Revisar inventario
                    </Link>
                </template>
            </div>
        </div>
    </div>
</template>
