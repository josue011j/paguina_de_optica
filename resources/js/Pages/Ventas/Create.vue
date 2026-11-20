<script setup>
import { computed, ref, watch } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
    AlertCircle,
    Check,
    ClipboardList,
    Glasses,
    Plus,
    ShieldCheck,
    ShoppingCart,
    UserRound,
} from 'lucide-vue-next';
import PageHeader from '../../components/PageHeader.vue';
import FormField from '../../components/FormField.vue';
import { money } from '../../utils/format';

const props = defineProps({
    pacientes: { type: Array, default: () => [] },
    marcos: { type: Array, default: () => [] },
    lunas: { type: Array, default: () => [] },
    protecciones: { type: Array, default: () => [] },
    metodosPago: { type: Object, default: () => ({}) },
    estados: { type: Object, default: () => ({}) },
    fechaHoy: { type: String, required: true },
    pacienteSeleccionado: { type: [Number, String], default: null },
});

const form = useForm({
    paciente_id: props.pacienteSeleccionado ? Number(props.pacienteSeleccionado) : '',
    receta_id: null,
    marco_id: '',
    luna_id: props.lunas[0]?.id || '',
    protecciones: [],
    cantidad: 1,
    descuento: 0,
    metodo_pago: Object.keys(props.metodosPago)[0] || 'efectivo',
    estado: Object.keys(props.estados)[0] || 'pendiente',
    fecha: props.fechaHoy,
    notas: '',
});

const receta = ref(null);
const cargandoReceta = ref(false);
const recetaError = ref(false);

const pacienteSeleccionadoData = computed(() =>
    props.pacientes.find((paciente) => paciente.id === Number(form.paciente_id)),
);
const marcoSeleccionado = computed(() => props.marcos.find((marco) => marco.id === Number(form.marco_id)));
const lunaSeleccionada = computed(() => props.lunas.find((luna) => luna.id === Number(form.luna_id)));
const proteccionesSeleccionadas = computed(() =>
    props.protecciones.filter((proteccion) => form.protecciones.includes(proteccion.id)),
);

const precioMarco = computed(() => Number(marcoSeleccionado.value?.precio || 0));
const precioLuna = computed(() => Number(lunaSeleccionada.value?.precio || 0));
const precioProtecciones = computed(() =>
    proteccionesSeleccionadas.value.reduce((total, proteccion) => total + Number(proteccion.precio || 0), 0),
);

const precioUnitario = computed(() => precioMarco.value + precioLuna.value + precioProtecciones.value);
const cantidad = computed(() => Math.max(1, Number(form.cantidad) || 1));
const subtotal = computed(() => precioUnitario.value * cantidad.value);
const descuento = computed(() => Math.max(0, Number(form.descuento) || 0));
const total = computed(() => Math.max(0, subtotal.value - descuento.value));

const stockInsuficiente = computed(
    () => Boolean(marcoSeleccionado.value) && cantidad.value > Number(marcoSeleccionado.value.stock),
);

const firstError = computed(() => Object.values(form.errors)[0] || '');

watch(
    () => form.paciente_id,
    async (pacienteId) => {
        receta.value = null;
        recetaError.value = false;
        form.receta_id = null;

        if (!pacienteId) return;

        cargandoReceta.value = true;

        try {
            const response = await fetch(route('pacientes.ultima-receta', pacienteId), {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                recetaError.value = true;
                return;
            }

            const data = await response.json();
            receta.value = data.receta;
            form.receta_id = data.receta?.id ?? null;
        } catch {
            recetaError.value = true;
        } finally {
            cargandoReceta.value = false;
        }
    },
    { immediate: true },
);

const submit = () => {
    form.transform((data) => ({
        ...data,
        paciente_id: Number(data.paciente_id),
        luna_id: Number(data.luna_id),
        marco_id: data.marco_id ? Number(data.marco_id) : null,
        receta_id: receta.value?.id ?? null,
        cantidad: Number(data.cantidad),
        descuento: Number(data.descuento || 0),
    })).post(route('ventas.store'));
};
</script>

<template>
    <div>
        <PageHeader
            title="Nueva venta"
            subtitle="Selecciona el marco, las lunas y las protecciones elegidas por el cliente."
            :back="route('ventas.index')"
        />

        <form class="grid gap-5 xl:grid-cols-3" @submit.prevent="submit">
            <div class="space-y-5 xl:col-span-2">
                <div class="card p-5 sm:p-6">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                            <UserRound class="h-5 w-5" />
                        </span>
                        <div>
                            <h2 class="text-base font-semibold text-slate-900">Paciente</h2>
                            <p class="mt-0.5 text-xs text-slate-500">¿Para quién es la venta?</p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <FormField label="Paciente" :error="form.errors.paciente_id" required>
                            <select v-model="form.paciente_id" class="input" :class="{ 'input-error': form.errors.paciente_id }">
                                <option value="">Selecciona un paciente</option>
                                <option v-for="paciente in pacientes" :key="paciente.id" :value="paciente.id">
                                    {{ paciente.nombre_completo }} · {{ paciente.numero_documento }}
                                </option>
                            </select>
                        </FormField>

                        <div class="flex items-end">
                            <Link :href="route('pacientes.create')" class="btn btn-secondary w-full">
                                <Plus class="h-4 w-4" />
                                Registrar nuevo paciente
                            </Link>
                        </div>
                    </div>

                    <div v-if="pacienteSeleccionadoData" class="mt-4 rounded-xl bg-slate-50 px-4 py-3 text-sm">
                        <p class="font-semibold text-slate-800">{{ pacienteSeleccionadoData.nombre_completo }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">
                            {{ pacienteSeleccionadoData.numero_documento }} · {{ pacienteSeleccionadoData.telefono || 'Sin teléfono' }}
                        </p>
                    </div>

                    <div v-if="form.paciente_id" class="mt-4 rounded-xl border border-slate-200">
                        <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-4 py-3">
                            <p class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                <ClipboardList class="h-4 w-4 text-brand-700" />
                                Última receta
                            </p>
                            <span v-if="receta" class="chip bg-emerald-50 text-emerald-700 ring-emerald-600/20">
                                <Check class="h-3 w-3" />
                                Asociada
                            </span>
                        </div>

                        <div v-if="cargandoReceta" class="px-4 py-5 text-sm text-slate-400">Cargando receta…</div>

                        <div v-else-if="receta" class="p-4">
                            <div class="mb-2 flex items-center justify-between text-xs text-slate-500">
                                <span>{{ receta.fecha }} · {{ receta.optometrista }}</span>
                                <Link :href="route('recetas.show', receta.id)" class="font-semibold text-brand-700 hover:underline">Ver completa</Link>
                            </div>
                            <div class="overflow-hidden rounded-lg border border-slate-200">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-2.5 py-2 font-semibold text-slate-500">Ojo</th>
                                            <th class="px-2.5 py-2 font-semibold text-slate-500">Esf</th>
                                            <th class="px-2.5 py-2 font-semibold text-slate-500">Cil</th>
                                            <th class="px-2.5 py-2 font-semibold text-slate-500">Eje</th>
                                            <th class="px-2.5 py-2 font-semibold text-slate-500">Adc</th>
                                            <th class="px-2.5 py-2 font-semibold text-slate-500">AV</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-slate-700">
                                        <tr>
                                            <td class="px-2.5 py-2 font-semibold text-sky-700">OD</td>
                                            <td class="px-2.5 py-2">{{ receta.od_esfera ?? '—' }}</td>
                                            <td class="px-2.5 py-2">{{ receta.od_cilindro ?? '—' }}</td>
                                            <td class="px-2.5 py-2">{{ receta.od_eje ?? '—' }}</td>
                                            <td class="px-2.5 py-2">{{ receta.od_adicion ?? '—' }}</td>
                                            <td class="px-2.5 py-2">{{ receta.od_av ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-2.5 py-2 font-semibold text-violet-700">OI</td>
                                            <td class="px-2.5 py-2">{{ receta.oi_esfera ?? '—' }}</td>
                                            <td class="px-2.5 py-2">{{ receta.oi_cilindro ?? '—' }}</td>
                                            <td class="px-2.5 py-2">{{ receta.oi_eje ?? '—' }}</td>
                                            <td class="px-2.5 py-2">{{ receta.oi_adicion ?? '—' }}</td>
                                            <td class="px-2.5 py-2">{{ receta.oi_av ?? '—' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p v-if="receta.dip" class="mt-2 text-xs text-slate-500">DIP: <strong>{{ receta.dip }} mm</strong></p>
                        </div>

                        <div v-else-if="recetaError" class="px-4 py-4 text-sm text-rose-600">
                            No se pudo cargar la receta del paciente.
                        </div>

                        <div v-else class="px-4 py-4 text-sm text-slate-500">
                            Este paciente no tiene recetas registradas. La venta se puede realizar sin receta.
                        </div>
                    </div>
                </div>

                <div class="card p-5 sm:p-6">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-700">
                            <Glasses class="h-5 w-5" />
                        </span>
                        <div>
                            <h2 class="text-base font-semibold text-slate-900">Marco y lunas</h2>
                            <p class="mt-0.5 text-xs text-slate-500">El marco es opcional si el cliente solo lleva lunas.</p>
                        </div>
                    </div>

                    <FormField label="Marco" :error="form.errors.marco_id">
                        <select v-model="form.marco_id" class="input">
                            <option value="">Sin marco · solo lunas</option>
                            <option v-for="marco in marcos" :key="marco.id" :value="marco.id">
                                {{ marco.codigo }} · {{ marco.marca }} {{ marco.modelo }} — {{ money(marco.precio) }} (stock: {{ marco.stock }})
                            </option>
                        </select>
                    </FormField>

                    <div v-if="marcoSeleccionado" class="mt-3 flex items-center gap-3 rounded-xl bg-slate-50 px-4 py-3">
                        <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white text-slate-400 ring-1 ring-slate-200">
                            <img
                                v-if="marcoSeleccionado.foto_url"
                                :src="marcoSeleccionado.foto_url"
                                :alt="`${marcoSeleccionado.marca} ${marcoSeleccionado.modelo}`"
                                class="h-full w-full object-cover"
                            />
                            <Glasses v-else class="h-5 w-5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ marcoSeleccionado.marca }} {{ marcoSeleccionado.modelo }}</p>
                            <p class="text-xs text-slate-500">{{ marcoSeleccionado.color || 'Sin color' }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
                            Stock: {{ marcoSeleccionado.stock }}
                        </span>
                    </div>

                    <div class="mt-5">
                        <p class="label">Tipo de luna <span class="text-rose-500">*</span></p>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <label
                                v-for="luna in lunas"
                                :key="luna.id"
                                class="cursor-pointer rounded-xl border p-3 transition"
                                :class="Number(form.luna_id) === luna.id ? 'border-brand-500 bg-brand-50/60 ring-2 ring-brand-500/20' : 'border-slate-200 hover:border-slate-300'"
                            >
                                <input v-model="form.luna_id" type="radio" :value="luna.id" class="sr-only" />
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800">{{ luna.nombre }}</p>
                                        <p class="text-[11px] capitalize text-slate-400">{{ luna.tipo }}</p>
                                    </div>
                                    <span class="text-sm font-bold text-brand-700">{{ money(luna.precio) }}</span>
                                </div>
                                <p v-if="luna.descripcion" class="mt-1.5 line-clamp-2 text-[11px] text-slate-500">{{ luna.descripcion }}</p>
                            </label>
                        </div>
                        <p v-if="form.errors.luna_id" class="mt-1.5 text-xs font-medium text-rose-600">{{ form.errors.luna_id }}</p>
                    </div>
                </div>

                <div class="card p-5 sm:p-6">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-700">
                            <ShieldCheck class="h-5 w-5" />
                        </span>
                        <div>
                            <h2 class="text-base font-semibold text-slate-900">Protecciones</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Marca las que el cliente eligió.</p>
                        </div>
                    </div>

                    <div class="grid gap-2 sm:grid-cols-2">
                        <label
                            v-for="proteccion in protecciones"
                            :key="proteccion.id"
                            class="flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition"
                            :class="form.protecciones.includes(proteccion.id) ? 'border-brand-500 bg-brand-50/60 ring-2 ring-brand-500/20' : 'border-slate-200 hover:border-slate-300'"
                        >
                            <input v-model="form.protecciones" type="checkbox" :value="proteccion.id" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
                            <span class="min-w-0 flex-1">
                                <span class="flex items-start justify-between gap-2">
                                    <span class="text-sm font-semibold text-slate-800">{{ proteccion.nombre }}</span>
                                    <span class="shrink-0 text-sm font-bold text-brand-700">{{ money(proteccion.precio) }}</span>
                                </span>
                                <span v-if="proteccion.descripcion" class="mt-1 block text-[11px] leading-snug text-slate-500">{{ proteccion.descripcion }}</span>
                            </span>
                        </label>
                    </div>
                    <p v-if="form.errors.protecciones" class="mt-1.5 text-xs font-medium text-rose-600">{{ form.errors.protecciones }}</p>
                </div>

                <div class="card p-5 sm:p-6">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                            <ShoppingCart class="h-5 w-5" />
                        </span>
                        <div>
                            <h2 class="text-base font-semibold text-slate-900">Datos de la venta</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Cantidad, pago y estado del pedido.</p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <FormField label="Cantidad (pares)" :error="form.errors.cantidad" required>
                            <input v-model="form.cantidad" type="number" min="1" max="10" step="1" class="input" />
                        </FormField>

                        <FormField label="Fecha" :error="form.errors.fecha" required>
                            <input v-model="form.fecha" type="date" class="input" :max="fechaHoy" />
                        </FormField>

                        <FormField label="Método de pago" :error="form.errors.metodo_pago" required>
                            <select v-model="form.metodo_pago" class="input">
                                <option v-for="(label, value) in metodosPago" :key="value" :value="value">{{ label }}</option>
                            </select>
                        </FormField>

                        <FormField label="Estado" :error="form.errors.estado" required>
                            <select v-model="form.estado" class="input">
                                <option v-for="(label, value) in estados" :key="value" :value="value">{{ label }}</option>
                            </select>
                        </FormField>

                        <FormField label="Descuento (S/)" :error="form.errors.descuento">
                            <input v-model="form.descuento" type="number" min="0" step="0.10" class="input" placeholder="0.00" />
                        </FormField>

                        <div class="sm:col-span-2 lg:col-span-3">
                            <FormField label="Notas" :error="form.errors.notas">
                                <textarea v-model="form.notas" rows="2" class="input" placeholder="Indicaciones de entrega, observaciones del cliente…" />
                            </FormField>
                        </div>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-1">
                <div class="sticky top-24 space-y-4">
                    <div class="card p-5">
                        <h2 class="text-base font-semibold text-slate-900">Resumen de la venta</h2>

                        <dl class="mt-4 space-y-2.5 text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-slate-500">Marco</dt>
                                <dd class="font-medium text-slate-800">{{ money(precioMarco) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-slate-500">{{ lunaSeleccionada?.nombre || 'Luna' }}</dt>
                                <dd class="font-medium text-slate-800">{{ money(precioLuna) }}</dd>
                            </div>
                            <div v-if="proteccionesSeleccionadas.length" class="flex items-start justify-between gap-3">
                                <dt class="text-slate-500">
                                    Protecciones
                                    <span class="mt-1 block text-[11px] text-slate-400">
                                        {{ proteccionesSeleccionadas.map((p) => p.nombre).join(', ') }}
                                    </span>
                                </dt>
                                <dd class="font-medium text-slate-800">{{ money(precioProtecciones) }}</dd>
                            </div>
                        </dl>

                        <div class="mt-4 space-y-2 border-t border-slate-100 pt-4 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Precio unitario</span>
                                <span class="font-medium text-slate-800">{{ money(precioUnitario) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Cantidad</span>
                                <span class="font-medium text-slate-800">× {{ cantidad }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Subtotal</span>
                                <span class="font-medium text-slate-800">{{ money(subtotal) }}</span>
                            </div>
                            <div v-if="descuento > 0" class="flex items-center justify-between text-emerald-700">
                                <span>Descuento</span>
                                <span class="font-medium">− {{ money(descuento) }}</span>
                            </div>
                        </div>

                        <div class="mt-4 flex items-end justify-between border-t border-slate-100 pt-4">
                            <span class="text-sm font-semibold text-slate-600">Total a pagar</span>
                            <span class="text-2xl font-bold tracking-tight text-brand-700">{{ money(total) }}</span>
                        </div>

                        <div v-if="stockInsuficiente" class="mt-4 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs text-amber-800">
                            <AlertCircle class="mt-0.5 h-4 w-4 shrink-0" />
                            Solo hay {{ marcoSeleccionado?.stock }} unidades de este marco.
                        </div>

                        <div v-if="firstError" class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 text-xs text-rose-700">
                            {{ firstError }}
                        </div>

                        <button type="submit" class="btn btn-primary mt-4 w-full py-3" :disabled="form.processing || stockInsuficiente">
                            <span v-if="form.processing" class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white" />
                            {{ form.processing ? 'Registrando venta…' : 'Registrar venta' }}
                        </button>
                        <p class="mt-2 text-center text-[11px] text-slate-400">
                            Al guardar se descuenta el stock del marco.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-brand-200 bg-brand-50 p-4 text-xs leading-relaxed text-brand-900">
                        <p class="font-semibold">Consejo de venta</p>
                        <p class="mt-1 text-brand-800/80">
                            Confirma con el cliente cada protección antes de cerrar la venta. El detalle quedará
                            guardado en su historial.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>
