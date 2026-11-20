<script setup>
import { computed, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { Eye, EyeOff, Glasses, LockKeyhole, Mail, ShieldCheck, Sparkles, UserRound } from 'lucide-vue-next';

defineOptions({ layout: null });

const props = defineProps({
    demoAccounts: { type: Array, default: () => [] },
});

const showPassword = ref(false);
const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login.store'), {
        onFinish: () => form.reset('password'),
    });
};

const fillDemo = (account) => {
    form.email = account.email;
    form.password = 'password';
};

const hasError = computed(() => Object.keys(form.errors).length > 0);
</script>

<template>
    <div class="min-h-screen bg-slate-50 lg:grid lg:grid-cols-2">
        <Head title="Iniciar sesión" />

        <section class="relative hidden overflow-hidden bg-brand-900 lg:flex lg:flex-col lg:justify-between lg:p-12">
            <div class="absolute -left-24 -top-24 h-80 w-80 rounded-full bg-brand-500/20 blur-3xl" />
            <div class="absolute -bottom-32 -right-16 h-96 w-96 rounded-full bg-teal-400/10 blur-3xl" />

            <div class="relative flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 text-white ring-1 ring-white/20 backdrop-blur">
                    <Glasses class="h-6 w-6" />
                </span>
                <div>
                    <p class="text-sm font-bold text-white">Óptica Visión Clara</p>
                    <p class="text-[11px] font-medium uppercase tracking-[0.2em] text-brand-200">Salud visual</p>
                </div>
            </div>

            <div class="relative max-w-lg">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-xs font-medium text-brand-100 backdrop-blur">
                    <Sparkles class="h-3.5 w-3.5" />
                    Gestión integral para tu óptica
                </span>
                <h1 class="mt-5 text-4xl font-bold leading-tight tracking-tight text-white">
                    Marcos, lunas y recetas en un solo lugar.
                </h1>
                <p class="mt-4 text-base leading-relaxed text-brand-100/80">
                    Controla tu inventario, registra las medidas de cada paciente y cierra la venta con las
                    protecciones que eligió el cliente.
                </p>

                <div class="mt-8 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                        <ShieldCheck class="h-5 w-5 text-brand-300" />
                        <p class="mt-3 text-sm font-semibold text-white">Roles con permisos</p>
                        <p class="mt-1 text-xs leading-relaxed text-brand-100/70">
                            Dueño, programador, vendedor y optometrista.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                        <Eye class="h-5 w-5 text-brand-300" />
                        <p class="mt-3 text-sm font-semibold text-white">Receta y venta conectadas</p>
                        <p class="mt-1 text-xs leading-relaxed text-brand-100/70">
                            El vendedor ve la medida indicada por el optometrista.
                        </p>
                    </div>
                </div>
            </div>

            <p class="relative text-xs text-brand-200/60">© {{ new Date().getFullYear() }} Óptica Visión Clara</p>
        </section>

        <section class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-8">
            <div class="w-full max-w-md">
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-700 text-white">
                        <Glasses class="h-6 w-6" />
                    </span>
                    <div>
                        <p class="text-base font-bold text-slate-900">Óptica Visión Clara</p>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-brand-700">Salud visual</p>
                    </div>
                </div>

                <div class="card p-6 sm:p-8">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900">Bienvenido de nuevo</h2>
                        <p class="mt-1 text-sm text-slate-500">Ingresa con tu cuenta para continuar.</p>
                    </div>

                    <div v-if="hasError" class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ form.errors.email || form.errors.password || 'Revisa los datos ingresados.' }}
                    </div>

                    <form class="space-y-4" @submit.prevent="submit">
                        <div>
                            <label for="email" class="label">Correo electrónico</label>
                            <div class="relative">
                                <Mail class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    name="email"
                                    autocomplete="username"
                                    class="input pl-10"
                                    :class="{ 'input-error': form.errors.email }"
                                    placeholder="tu@optica.com"
                                    required
                                />
                            </div>
                        </div>

                        <div>
                            <label for="password" class="label">Contraseña</label>
                            <div class="relative">
                                <LockKeyhole class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                <input
                                    id="password"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    name="password"
                                    autocomplete="current-password"
                                    class="input pl-10 pr-11"
                                    :class="{ 'input-error': form.errors.password }"
                                    placeholder="••••••••"
                                    required
                                />
                                <button
                                    type="button"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1 text-slate-400 hover:text-slate-600"
                                    :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                    @click="showPassword = !showPassword"
                                >
                                    <EyeOff v-if="showPassword" class="h-4 w-4" />
                                    <Eye v-else class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input v-model="form.remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
                            Mantener mi sesión iniciada
                        </label>

                        <button type="submit" class="btn btn-primary w-full py-3" :disabled="form.processing">
                            <span v-if="form.processing" class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white" />
                            {{ form.processing ? 'Ingresando…' : 'Iniciar sesión' }}
                        </button>
                    </form>
                </div>

                <div v-if="props.demoAccounts.length" class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-white/60 p-4">
                    <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <UserRound class="h-4 w-4" />
                        Cuentas de demostración
                    </p>
                    <p class="mt-1 text-xs text-slate-500">Haz clic en un rol para completar el acceso. Contraseña: <strong>password</strong></p>
                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                        <button
                            v-for="account in props.demoAccounts"
                            :key="account.email"
                            type="button"
                            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-left transition hover:border-brand-300 hover:bg-brand-50/50"
                            @click="fillDemo(account)"
                        >
                            <span class="block text-xs font-semibold text-slate-800">{{ account.role }}</span>
                            <span class="block truncate text-[11px] text-slate-500">{{ account.email }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
