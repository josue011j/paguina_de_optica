<script setup>
import { computed, ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
    ArrowLeft,
    CalendarDays,
    ClipboardList,
    CreditCard,
    Glasses,
    Printer,
    ReceiptText,
    ShieldCheck,
    Trash2,
    UserRound,
} from 'lucide-vue-next';
import StatusBadge from '../../components/StatusBadge.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import { money } from '../../utils/format';

const props = defineProps({
    venta: { type: Object, required: true },
    estados: { type: Object, default: () => ({}) },
    puedeEditarEstado: { type: Boolean, default: false },
    puedeEliminar: { type: Boolean, default: false },
});

const estadoForm = useForm({
    estado: props.venta.estado,
});

const estadoCambiado = computed(() => estadoForm.estado !== props.venta.estado);

const changingState = ref(false);

function updateEstado() {
    if (!estadoCambiado.value) return;

    changingState.value = true;
    estadoForm.patch(route('ventas.estado', props.venta.id), {
        preserveScroll: true,
        onFinish: () => {
            changingState.value = false;
        },
    });
}

const deleteOpen = ref(false);
const deleting = ref(false);

function destroy() {
    deleting.value = true;
    router.delete(route('ventas.destroy', props.venta.id), {
        onFinish: () => {
            deleting.value = false;
            deleteOpen.value = false;
        },
    });
}

const protecciones = computed(() => props.venta.protecciones || []);
const proteccionesTotal = computed(() =>
    protecciones.value.reduce((total, proteccion) => total + Number(proteccion.pivot?.precio || proteccion.precio || 0), 0),
);

function print() {
    window.print();
}
</script>

<template>
    <div>
        <div class="no-print mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <Link :href="route('ventas.index')" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-brand-700">
                <ArrowLeft class="h-4 w-4" />
                Volver a ventas
            </Link>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" class="btn btn-secondary" @click="print">
                    <Printer class="h-4 w-4" />
                    Imprimir
                </button>
                <button v-if="puedeEliminar" type="button" class="btn btn-secondary text-rose-600" @click="deleteOpen = true">
                    <Trash2 class="h-4 w-4" />
                    Eliminar
                </button>
                <Link v-if="venta.paciente" :href="route('pacientes.show', venta.paciente_id)" class="btn btn-secondary">
                    <UserRound class="h-4 w-4" />
                    Ver paciente
                </Link>
            </div>
        </div>

        <div class="grid gap-5 xl:grid-cols-3">
            <div class="space-y-5 xl:col-span-2">
                <div class="card print-area overflow-hidden">
                    <div class="flex flex-col gap-4 border-b border-slate-100 bg-gradient-to-r from-brand-700 to-brand-900 px-5 py-6 text-white sm:flex-row sm:items-center sm:justify-between sm:px-7">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/20">
                                <Glasses class="h-6 w-6" />
                            </span>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-200">Comprobante de venta</p>
                                <p class="text-xl font-bold">{{ venta.codigo }}</p>
                            </div>
                        </div>
                        <div class="sm:text-right">
                            <StatusBadge :label="venta.estado_label" :tone="venta.estado_color" class="!bg-white/10 !text-white !ring-white/30" />
                            <p class="mt-2 text-xs text-brand-100/80">{{ venta.fecha_label }}</p>
                        </div>
                    </div>

                    <div class="grid gap-4 px-5 py-5 sm:grid-cols-2 sm:px-7">
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <p class="text-[11px] uppercase tracking-wider text-slate-400">Paciente</p>
                            <p class="mt-0.5 font-semibold text-slate-800">
                                {{ venta.paciente?.nombres }} {{ venta.paciente?.apellidos }}
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ venta.paciente?.tipo_documento }} {{ venta.paciente?.numero_documento }}
                            </p>
                            <p v-if="venta.paciente?.telefono" class="text-xs text-slate-500">{{ venta.paciente.telefono }}</p>
                        </div>

                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <p class="text-[11px] uppercase tracking-wider text-slate-400">Registro</p>
                            <p class="mt-0.5 font-semibold text-slate-800">{{ venta.vendedor?.name }}</p>
                            <p class="text-xs text-slate-500">{{ venta.metodo_pago_label }}</p>
                            <p class="flex items-center gap-1 text-xs text-slate-500">
                                <CalendarDays class="h-3 w-3" />
                                {{ venta.fecha_label }}
                            </p>
                        </div>
                    </div>

                    <div class="px-5 pb-5 sm:px-7">
                        <div class="overflow-hidden rounded-xl border border-slate-200">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Descripción</th>
                                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Cantidad</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Precio</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-if="venta.marco">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-slate-100 text-slate-400 ring-1 ring-slate-200">
                                                    <img
                                                        v-if="venta.marco.foto_url"
                                                        :src="venta.marco.foto_url"
                                                        :alt="`${venta.marco.marca} ${venta.marco.modelo}`"
                                                        class="h-full w-full object-cover"
                                                    />
                                                    <Glasses v-else class="h-4 w-4" />
                                                </span>
                                                <div class="min-w-0">
                                                    <p class="font-medium text-slate-800">Marco {{ venta.marco.marca }} {{ venta.marco.modelo }}</p>
                                                    <p class="text-xs text-slate-400">{{ venta.marco.codigo }} · {{ venta.marco.color || 'Sin color' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">{{ venta.cantidad }}</td>
                                        <td class="px-4 py-3 text-right font-medium text-slate-800">{{ money(venta.marco.precio) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3">
                                            <p class="font-medium text-slate-800">{{ venta.luna?.nombre }}</p>
                                            <p class="text-xs capitalize text-slate-400">Luna {{ venta.luna?.tipo }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">{{ venta.cantidad }}</td>
                                        <td class="px-4 py-3 text-right font-medium text-slate-800">{{ money(venta.luna?.precio) }}</td>
                                    </tr>
                                    <tr v-for="proteccion in protecciones" :key="proteccion.id">
                                        <td class="px-4 py-3">
                                            <p class="flex items-center gap-1.5 font-medium text-slate-800">
                                                <ShieldCheck class="h-3.5 w-3.5 text-brand-600" />
                                                {{ proteccion.nombre }}
                                            </p>
                                            <p class="text-xs text-slate-400">Protección para lunas</p>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">{{ venta.cantidad }}</td>
                                        <td class="px-4 py-3 text-right font-medium text-slate-800">{{ money(proteccion.pivot?.precio ?? proteccion.precio) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 flex flex-col items-end gap-2 text-sm">
                            <div class="flex w-full max-w-xs items-center justify-between">
                                <span class="text-slate-500">Subtotal</span>
                                <span class="font-medium text-slate-800">{{ money(venta.subtotal) }}</span>
                            </div>
                            <div v-if="Number(venta.descuento) > 0" class="flex w-full max-w-xs items-center justify-between text-emerald-700">
                                <span>Descuento</span>
                                <span class="font-medium">− {{ money(venta.descuento) }}</span>
                            </div>
                            <div class="flex w-full max-w-xs items-center justify-between border-t border-slate-200 pt-2">
                                <span class="font-semibold text-slate-600">Total pagado</span>
                                <span class="text-xl font-bold text-brand-700">{{ money(venta.total) }}</span>
                            </div>
                        </div>

                        <div v-if="venta.notas" class="mt-5 rounded-xl border border-slate-200 px-4 py-3">
                            <p class="text-[11px] uppercase tracking-wider text-slate-400">Notas</p>
                            <p class="mt-1 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ venta.notas }}</p>
                        </div>

                        <p class="mt-6 text-center text-[11px] text-slate-400">
                            Gracias por confiar en Óptica Visión Clara · Documento generado por el sistema
                        </p>
                    </div>
                </div>

                <div v-if="venta.receta" class="card no-print p-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="flex items-center gap-2 text-base font-semibold text-slate-900">
                                <ClipboardList class="h-4 w-4 text-brand-700" />
                                Receta asociada
                            </h2>
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ venta.receta.fecha_label }} · {{ venta.receta.optometrista?.name }}
                            </p>
                        </div>
                        <Link :href="route('recetas.show', venta.receta.id)" class="text-xs font-semibold text-brand-700 hover:underline">
                            Ver completa
                        </Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-3 py-2 font-semibold text-slate-500">Ojo</th>
                                    <th class="px-3 py-2 font-semibold text-slate-500">Esf</th>
                                    <th class="px-3 py-2 font-semibold text-slate-500">Cil</th>
                                    <th class="px-3 py-2 font-semibold text-slate-500">Eje</th>
                                    <th class="px-3 py-2 font-semibold text-slate-500">Adc</th>
                                    <th class="px-3 py-2 font-semibold text-slate-500">AV</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                <tr>
                                    <td class="px-3 py-2 font-semibold text-sky-700">OD</td>
                                    <td class="px-3 py-2">{{ venta.receta.od_esfera ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ venta.receta.od_cilindro ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ venta.receta.od_eje ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ venta.receta.od_adicion ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ venta.receta.od_av ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2 font-semibold text-violet-700">OI</td>
                                    <td class="px-3 py-2">{{ venta.receta.oi_esfera ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ venta.receta.oi_cilindro ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ venta.receta.oi_eje ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ venta.receta.oi_adicion ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ venta.receta.oi_av ?? '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-if="venta.receta.dip" class="mt-3 text-xs text-slate-500">
                        DIP: <strong class="text-slate-700">{{ venta.receta.dip }} mm</strong>
                    </p>
                </div>
            </div>

            <div class="no-print space-y-5">
                <div v-if="puedeEditarEstado" class="card p-5">
                    <h2 class="text-base font-semibold text-slate-900">Estado del pedido</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Actualiza el avance de la venta.</p>

                    <div class="mt-4">
                        <label class="label" for="estado">Estado</label>
                        <select id="estado" v-model="estadoForm.estado" class="input">
                            <option v-for="(label, value) in estados" :key="value" :value="value">{{ label }}</option>
                        </select>
                        <p v-if="estadoForm.errors.estado" class="mt-1.5 text-xs font-medium text-rose-600">{{ estadoForm.errors.estado }}</p>
                    </div>

                    <button
                        type="button"
                        class="btn btn-primary mt-4 w-full"
                        :disabled="!estadoCambiado || changingState"
                        @click="updateEstado"
                    >
                        {{ changingState ? 'Actualizando…' : 'Actualizar estado' }}
                    </button>
                    <p class="mt-2 text-center text-[11px] text-slate-400">
                        Cancelar una venta devuelve el stock del marco.
                    </p>
                </div>

                <div class="card p-5">
                    <h2 class="flex items-center gap-2 text-base font-semibold text-slate-900">
                        <ReceiptText class="h-4 w-4 text-brand-700" />
                        Resumen
                    </h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Productos</dt>
                            <dd class="font-semibold text-slate-800">
                                {{ (venta.marco ? 1 : 0) + 1 + protecciones.length }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Pares</dt>
                            <dd class="font-semibold text-slate-800">{{ venta.cantidad }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Protecciones</dt>
                            <dd class="font-semibold text-slate-800">{{ money(proteccionesTotal) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Pago</dt>
                            <dd class="flex items-center gap-1 font-semibold text-slate-800">
                                <CreditCard class="h-3.5 w-3.5 text-slate-400" />
                                {{ venta.metodo_pago_label }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-3">
                            <dt class="font-semibold text-slate-600">Total</dt>
                            <dd class="text-lg font-bold text-brand-700">{{ money(venta.total) }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-2xl border border-brand-200 bg-brand-50 p-4 text-xs leading-relaxed text-brand-900">
                    <p class="font-semibold">Entrega de lentes</p>
                    <p class="mt-1 text-brand-800/80">
                        Verifica las medidas de la receta antes de entregar el pedido y confirma con el cliente las
                        protecciones seleccionadas.
                    </p>
                </div>
            </div>
        </div>

        <ConfirmDialog
            :show="deleteOpen"
            title="Eliminar venta"
            :message="`Se eliminará la venta ${venta.codigo}. El stock de los marcos será restaurado automáticamente.`"
            confirm-label="Eliminar venta"
            :processing="deleting"
            @confirm="destroy"
            @close="deleteOpen = false"
        />
    </div>
</template>
