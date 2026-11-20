<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { Save, ShieldCheck } from 'lucide-vue-next';
import FormField from '../FormField.vue';

const props = defineProps({
    usuario: { type: Object, default: null },
    roles: { type: Object, default: () => ({}) },
    descripciones: { type: Object, default: () => ({}) },
});

const esEdicion = computed(() => Boolean(props.usuario));

const form = useForm({
    name: props.usuario?.name || '',
    email: props.usuario?.email || '',
    telefono: props.usuario?.telefono || '',
    role: props.usuario?.role || Object.keys(props.roles)[0] || 'vendedor',
    password: '',
    password_confirmation: '',
    activo: props.usuario?.activo ?? true,
});

const submit = () => {
    if (esEdicion.value) {
        form.put(route('usuarios.update', props.usuario.id));
        return;
    }

    form.post(route('usuarios.store'));
};
</script>

<template>
    <form class="space-y-5" @submit.prevent="submit">
        <div class="card p-5 sm:p-6">
            <div class="mb-5">
                <h2 class="text-base font-semibold text-slate-900">Datos de la cuenta</h2>
                <p class="mt-0.5 text-xs text-slate-500">Información de acceso del trabajador.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <FormField label="Nombre completo" :error="form.errors.name" required>
                    <input v-model="form.name" type="text" class="input" :class="{ 'input-error': form.errors.name }" placeholder="Ej. Lucía Ramírez" />
                </FormField>

                <FormField label="Correo electrónico" :error="form.errors.email" required>
                    <input v-model="form.email" type="email" class="input" :class="{ 'input-error': form.errors.email }" placeholder="usuario@optica.com" />
                </FormField>

                <FormField label="Teléfono" :error="form.errors.telefono">
                    <input v-model="form.telefono" type="tel" class="input" :class="{ 'input-error': form.errors.telefono }" placeholder="987654321" />
                </FormField>

                <div class="flex items-end">
                    <label class="flex w-fit items-center gap-2 pb-2.5 text-sm text-slate-600">
                        <input v-model="form.activo" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
                        Usuario activo
                    </label>
                </div>
            </div>
        </div>

        <div class="card p-5 sm:p-6">
            <div class="mb-5">
                <h2 class="text-base font-semibold text-slate-900">Rol y permisos</h2>
                <p class="mt-0.5 text-xs text-slate-500">Cada rol define lo que el usuario puede hacer en el sistema.</p>
            </div>

            <div class="grid gap-2 sm:grid-cols-2">
                <label
                    v-for="(label, value) in roles"
                    :key="value"
                    class="cursor-pointer rounded-xl border p-3.5 transition"
                    :class="form.role === value ? 'border-brand-500 bg-brand-50/60 ring-2 ring-brand-500/20' : 'border-slate-200 hover:border-slate-300'"
                >
                    <input v-model="form.role" type="radio" :value="value" class="sr-only" />
                    <span class="flex items-center gap-2">
                        <span
                            class="inline-flex h-7 w-7 items-center justify-center rounded-lg"
                            :class="form.role === value ? 'bg-brand-100 text-brand-700' : 'bg-slate-100 text-slate-500'"
                        >
                            <ShieldCheck class="h-3.5 w-3.5" />
                        </span>
                        <span class="text-sm font-semibold text-slate-800">{{ label }}</span>
                    </span>
                    <span class="mt-1.5 block text-[11px] leading-snug text-slate-500">{{ descripciones[value] }}</span>
                </label>
            </div>
            <p v-if="form.errors.role" class="mt-1.5 text-xs font-medium text-rose-600">{{ form.errors.role }}</p>
        </div>

        <div class="card p-5 sm:p-6">
            <div class="mb-5">
                <h2 class="text-base font-semibold text-slate-900">
                    {{ esEdicion ? 'Cambiar contraseña' : 'Contraseña' }}
                </h2>
                <p class="mt-0.5 text-xs text-slate-500">
                    {{ esEdicion ? 'Déjala en blanco si no deseas cambiarla.' : 'Mínimo 8 caracteres.' }}
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <FormField label="Contraseña" :error="form.errors.password" :required="!esEdicion">
                    <input v-model="form.password" type="password" class="input" :class="{ 'input-error': form.errors.password }" autocomplete="new-password" placeholder="••••••••" />
                </FormField>

                <FormField label="Confirmar contraseña" :error="form.errors.password_confirmation" :required="!esEdicion">
                    <input v-model="form.password_confirmation" type="password" class="input" autocomplete="new-password" placeholder="••••••••" />
                </FormField>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <Link :href="route('usuarios.index')" class="btn btn-secondary">Cancelar</Link>
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                <Save class="h-4 w-4" />
                {{ form.processing ? 'Guardando…' : esEdicion ? 'Guardar cambios' : 'Crear usuario' }}
            </button>
        </div>
    </form>
</template>
