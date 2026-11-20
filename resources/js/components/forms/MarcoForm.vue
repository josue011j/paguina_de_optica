<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { Glasses, ImagePlus, Save, Trash2, Upload } from 'lucide-vue-next';
import FormField from '../FormField.vue';

const props = defineProps({
    marco: { type: Object, default: null },
});

const esEdicion = computed(() => Boolean(props.marco));

const form = useForm({
    codigo: props.marco?.codigo || '',
    marca: props.marco?.marca || '',
    modelo: props.marco?.modelo || '',
    color: props.marco?.color || '',
    material: props.marco?.material || '',
    forma: props.marco?.forma || '',
    talla: props.marco?.talla || 'Adulto',
    precio: props.marco?.precio ?? '',
    stock: props.marco?.stock ?? 0,
    stock_minimo: props.marco?.stock_minimo ?? 2,
    descripcion: props.marco?.descripcion || '',
    foto: null,
    eliminar_foto: false,
    activo: props.marco?.activo ?? true,
});

const fotoInput = ref(null);
const fotoPreview = ref(props.marco?.foto_url || null);
const fotoLocalUrl = ref(null);

function liberarUrlLocal() {
    if (fotoLocalUrl.value) {
        URL.revokeObjectURL(fotoLocalUrl.value);
        fotoLocalUrl.value = null;
    }
}

function seleccionarFoto(event) {
    const archivo = event.target.files?.[0];

    if (!archivo) {
        return;
    }

    liberarUrlLocal();
    fotoLocalUrl.value = URL.createObjectURL(archivo);
    fotoPreview.value = fotoLocalUrl.value;
    form.foto = archivo;
    form.eliminar_foto = false;
    form.clearErrors('foto');
}

function quitarFoto() {
    liberarUrlLocal();
    fotoPreview.value = null;
    form.foto = null;
    form.eliminar_foto = Boolean(props.marco?.foto_url);

    if (fotoInput.value) {
        fotoInput.value.value = '';
    }
}

onBeforeUnmount(liberarUrlLocal);

const submit = () => {
    const options = { forceFormData: true };

    if (esEdicion.value) {
        form.put(route('marcos.update', props.marco.id), options);
        return;
    }

    form.post(route('marcos.store'), options);
};
</script>

<template>
    <form class="space-y-5" @submit.prevent="submit">
        <div class="card p-5 sm:p-6">
            <div class="mb-5 flex items-center gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                    <ImagePlus class="h-5 w-5" />
                </span>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Foto del marco</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Se mostrará en el inventario y al momento de vender.</p>
                </div>
            </div>

            <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                <div class="relative flex h-44 w-full shrink-0 items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 sm:w-60">
                    <img v-if="fotoPreview" :src="fotoPreview" alt="Foto del marco" class="h-full w-full object-cover" />
                    <div v-else class="flex flex-col items-center gap-2 text-slate-400">
                        <Glasses class="h-8 w-8" />
                        <span class="text-xs font-medium">Sin foto</span>
                    </div>
                    <button
                        v-if="fotoPreview"
                        type="button"
                        class="absolute right-2 top-2 inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white/90 text-rose-600 shadow-sm ring-1 ring-slate-200 transition hover:bg-white"
                        title="Quitar foto"
                        @click="quitarFoto"
                    >
                        <Trash2 class="h-4 w-4" />
                    </button>
                </div>

                <div class="flex-1 space-y-2.5">
                    <input
                        ref="fotoInput"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        class="hidden"
                        @change="seleccionarFoto"
                    />
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="btn btn-secondary" @click="fotoInput?.click()">
                            <Upload class="h-4 w-4" />
                            {{ fotoPreview ? 'Cambiar foto' : 'Subir foto' }}
                        </button>
                        <button v-if="fotoPreview" type="button" class="btn btn-ghost text-rose-600" @click="quitarFoto">
                            <Trash2 class="h-4 w-4" />
                            Quitar
                        </button>
                    </div>
                    <p class="text-xs text-slate-500">Formatos JPG, PNG o WebP. Tamaño máximo 4 MB.</p>
                    <p v-if="form.progress" class="text-xs font-medium text-brand-700">
                        Subiendo foto… {{ form.progress.percentage }}%
                    </p>
                    <div v-if="form.progress" class="h-1.5 w-full max-w-xs overflow-hidden rounded-full bg-slate-200">
                        <div class="h-full rounded-full bg-brand-600 transition-all" :style="{ width: `${form.progress.percentage}%` }" />
                    </div>
                    <p v-if="form.errors.foto" class="text-xs font-medium text-rose-600">{{ form.errors.foto }}</p>
                </div>
            </div>
        </div>

        <div class="card p-5 sm:p-6">
            <div class="mb-5">
                <h2 class="text-base font-semibold text-slate-900">Identificación del marco</h2>
                <p class="mt-0.5 text-xs text-slate-500">Código interno, marca y modelo para encontrarlo rápido.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <FormField label="Código" :error="form.errors.codigo" required hint="Ej. M-1001">
                    <input v-model="form.codigo" type="text" class="input" :class="{ 'input-error': form.errors.codigo }" placeholder="M-1001" />
                </FormField>

                <FormField label="Marca" :error="form.errors.marca" required>
                    <input v-model="form.marca" type="text" class="input" :class="{ 'input-error': form.errors.marca }" placeholder="Ray-Ban" />
                </FormField>

                <FormField label="Modelo" :error="form.errors.modelo" required>
                    <input v-model="form.modelo" type="text" class="input" :class="{ 'input-error': form.errors.modelo }" placeholder="RB2140 Wayfarer" />
                </FormField>

                <FormField label="Color" :error="form.errors.color">
                    <input v-model="form.color" type="text" class="input" :class="{ 'input-error': form.errors.color }" placeholder="Negro" />
                </FormField>

                <FormField label="Material" :error="form.errors.material">
                    <input v-model="form.material" list="materiales" type="text" class="input" :class="{ 'input-error': form.errors.material }" placeholder="Acetato" />
                    <datalist id="materiales">
                        <option value="Acetato" />
                        <option value="Metal" />
                        <option value="Titanio" />
                        <option value="TR90" />
                        <option value="O-Matter" />
                    </datalist>
                </FormField>

                <FormField label="Forma" :error="form.errors.forma">
                    <input v-model="form.forma" list="formas" type="text" class="input" :class="{ 'input-error': form.errors.forma }" placeholder="Rectangular" />
                    <datalist id="formas">
                        <option value="Rectangular" />
                        <option value="Redonda" />
                        <option value="Cuadrada" />
                        <option value="Aviador" />
                        <option value="Deportiva" />
                    </datalist>
                </FormField>

                <FormField label="Talla" :error="form.errors.talla">
                    <select v-model="form.talla" class="input">
                        <option value="Adulto">Adulto</option>
                        <option value="Niño">Niño</option>
                        <option value="Unisex">Unisex</option>
                    </select>
                </FormField>
            </div>
        </div>

        <div class="card p-5 sm:p-6">
            <div class="mb-5">
                <h2 class="text-base font-semibold text-slate-900">Precio e inventario</h2>
                <p class="mt-0.5 text-xs text-slate-500">El stock se descuenta automáticamente al registrar una venta.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <FormField label="Precio de venta (S/)" :error="form.errors.precio" required>
                    <input v-model="form.precio" type="number" min="0" step="0.01" class="input" :class="{ 'input-error': form.errors.precio }" placeholder="499.00" />
                </FormField>

                <FormField label="Stock actual" :error="form.errors.stock" required>
                    <input v-model="form.stock" type="number" min="0" step="1" class="input" :class="{ 'input-error': form.errors.stock }" />
                </FormField>

                <FormField label="Stock mínimo" :error="form.errors.stock_minimo" required hint="Alerta cuando el stock llegue a este valor.">
                    <input v-model="form.stock_minimo" type="number" min="0" step="1" class="input" :class="{ 'input-error': form.errors.stock_minimo }" />
                </FormField>

                <div class="sm:col-span-3">
                    <FormField label="Descripción" :error="form.errors.descripcion">
                        <textarea v-model="form.descripcion" rows="2" class="input" :class="{ 'input-error': form.errors.descripcion }" placeholder="Detalles adicionales del marco" />
                    </FormField>
                </div>
            </div>

            <label class="mt-4 flex w-fit items-center gap-2 text-sm text-slate-600">
                <input v-model="form.activo" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
                Marco disponible para la venta
            </label>
        </div>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <Link :href="route('marcos.index')" class="btn btn-secondary">Cancelar</Link>
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                <Save class="h-4 w-4" />
                {{ form.processing ? 'Guardando…' : esEdicion ? 'Guardar cambios' : 'Agregar marco' }}
            </button>
        </div>
    </form>
</template>
