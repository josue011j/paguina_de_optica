<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
    BarChart3,
    CalendarDays,
    CheckCircle2,
    ChevronDown,
    ClipboardList,
    Eye,
    Glasses,
    LayoutDashboard,
    LogOut,
    Menu,
    Plus,
    ReceiptText,
    ShieldCheck,
    TriangleAlert,
    UserCog,
    Users,
    X,
} from 'lucide-vue-next';
import { initials, longDate } from '../utils/format';

const page = usePage();

const user = computed(() => page.props.auth?.user);
const can = computed(() => page.props.permissions || {});
const appName = computed(() => page.props.app?.name || 'Óptica Visión Clara');
const roleLabel = computed(() => user.value?.role_label || '');
const currentUrl = computed(() => page.url.split('?')[0]);

const sidebarOpen = ref(false);
const userMenuOpen = ref(false);

const navigation = computed(() => {
    const sections = [
        {
            title: 'Operación',
            items: [
                { label: 'Dashboard', href: route('dashboard'), icon: LayoutDashboard },
                { label: 'Ventas', href: route('ventas.index'), icon: ReceiptText, permission: 'viewSales' },
                { label: 'Pacientes', href: route('pacientes.index'), icon: Users, permission: 'viewPatients' },
                { label: 'Recetas', href: route('recetas.index'), icon: ClipboardList, permission: 'viewRecetas' },
            ],
        },
        {
            title: 'Catálogo',
            items: [
                { label: 'Marcos', href: route('marcos.index'), icon: Glasses, permission: 'viewCatalog' },
                { label: 'Tipos de luna', href: route('lunas.index'), icon: Eye, permission: 'viewCatalog' },
                { label: 'Protecciones', href: route('protecciones.index'), icon: ShieldCheck, permission: 'viewCatalog' },
            ],
        },
        {
            title: 'Administración',
            items: [
                { label: 'Usuarios', href: route('usuarios.index'), icon: UserCog, permission: 'manageUsers' },
                { label: 'Reportes', href: route('reportes.index'), icon: BarChart3, permission: 'viewReports' },
            ],
        },
    ];

    return sections
        .map((section) => ({
            ...section,
            items: section.items.filter((item) => !item.permission || can.value[item.permission]),
        }))
        .filter((section) => section.items.length > 0);
});

function isActive(href) {
    if (href === route('dashboard')) {
        return currentUrl.value === '/dashboard';
    }

    return currentUrl.value.startsWith(href);
}

watch(
    () => page.url,
    () => {
        sidebarOpen.value = false;
        userMenuOpen.value = false;
    },
);

const toast = ref(null);
let toastTimer = null;

function showToast(type, message) {
    window.clearTimeout(toastTimer);
    toast.value = { type, message };
    toastTimer = window.setTimeout(() => {
        toast.value = null;
    }, 4500);
}

watch(
    () => [page.props.flash?.success, page.props.flash?.error],
    ([success, error]) => {
        if (success) {
            showToast('success', success);
        } else if (error) {
            showToast('error', error);
        }
    },
);

onBeforeUnmount(() => {
    window.clearTimeout(toastTimer);
});

function logout() {
    router.post(route('logout'));
}
</script>

<template>
    <div class="min-h-full">
        <aside
            class="no-print fixed inset-y-0 left-0 z-40 flex w-72 flex-col border-r border-slate-200 bg-white transition-transform duration-300 lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-16 items-center justify-between gap-3 border-b border-slate-100 px-5">
                <Link :href="route('dashboard')" class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-brand-700 text-white shadow-sm shadow-brand-700/30">
                        <Glasses class="h-5 w-5" />
                    </span>
                    <span>
                        <span class="block text-sm font-bold leading-tight text-slate-900">{{ appName }}</span>
                        <span class="block text-[11px] font-medium uppercase tracking-wider text-brand-700">Salud visual</span>
                    </span>
                </Link>
                <button type="button" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 lg:hidden" @click="sidebarOpen = false">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5">
                <div v-for="section in navigation" :key="section.title">
                    <p class="section-title px-3 pb-2">{{ section.title }}</p>
                    <div class="space-y-1">
                        <Link
                            v-for="item in section.items"
                            :key="item.href"
                            :href="item.href"
                            class="sidebar-link"
                            :class="{ 'sidebar-link-active': isActive(item.href) }"
                        >
                            <component :is="item.icon" class="h-[18px] w-[18px] shrink-0" />
                            {{ item.label }}
                        </Link>
                    </div>
                </div>
            </nav>

            <div class="border-t border-slate-100 p-4">
                <div class="rounded-xl bg-gradient-to-br from-brand-700 to-brand-900 p-4 text-white">
                    <p class="text-xs font-semibold uppercase tracking-wider text-brand-200">Sesión activa</p>
                    <p class="mt-1 truncate text-sm font-semibold">{{ user?.name }}</p>
                    <p class="mt-0.5 text-xs text-brand-100/80">{{ roleLabel }}</p>
                </div>
            </div>
        </aside>

        <div v-if="sidebarOpen" class="no-print fixed inset-0 z-30 bg-slate-900/40 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false" />

        <div class="lg:pl-72">
            <header class="no-print sticky top-0 z-20 flex h-16 items-center justify-between gap-3 border-b border-slate-200 bg-white/85 px-4 backdrop-blur sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden" @click="sidebarOpen = true">
                        <Menu class="h-5 w-5" />
                    </button>
                    <div class="hidden items-center gap-2 text-sm text-slate-500 sm:flex">
                        <CalendarDays class="h-4 w-4 text-slate-400" />
                        <span>{{ longDate() }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Link v-if="can.manageSales" :href="route('ventas.create')" class="btn btn-primary btn-sm">
                        <Plus class="h-4 w-4" />
                        Nueva venta
                    </Link>

                    <div class="relative">
                        <button
                            type="button"
                            class="flex items-center gap-2 rounded-xl border border-transparent p-1.5 pr-2 transition hover:border-slate-200 hover:bg-slate-50"
                            @click="userMenuOpen = !userMenuOpen"
                        >
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-brand-100 text-sm font-bold text-brand-800">
                                {{ initials(user?.name) }}
                            </span>
                            <span class="hidden text-left sm:block">
                                <span class="block max-w-[150px] truncate text-sm font-semibold leading-tight text-slate-800">{{ user?.name }}</span>
                                <span class="block text-[11px] text-slate-500">{{ roleLabel }}</span>
                            </span>
                            <ChevronDown class="hidden h-4 w-4 text-slate-400 sm:block" />
                        </button>

                        <div v-if="userMenuOpen" class="fixed inset-0 z-10" @click="userMenuOpen = false" />

                        <Transition
                            enter-active-class="transition duration-150 ease-out"
                            enter-from-class="scale-95 opacity-0"
                            enter-to-class="scale-100 opacity-100"
                            leave-active-class="transition duration-100 ease-in"
                            leave-from-class="scale-100 opacity-100"
                            leave-to-class="scale-95 opacity-0"
                        >
                            <div v-if="userMenuOpen" class="absolute right-0 z-20 mt-2 w-64 origin-top-right rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                                <div class="border-b border-slate-100 px-3 py-2.5">
                                    <p class="truncate text-sm font-semibold text-slate-800">{{ user?.name }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ user?.email }}</p>
                                    <span class="mt-2 inline-flex items-center rounded-full bg-brand-50 px-2 py-0.5 text-[11px] font-semibold text-brand-700">
                                        {{ roleLabel }}
                                    </span>
                                </div>
                                <button
                                    type="button"
                                    class="mt-1 flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-medium text-slate-600 transition hover:bg-rose-50 hover:text-rose-600"
                                    @click="logout"
                                >
                                    <LogOut class="h-4 w-4" />
                                    Cerrar sesión
                                </button>
                            </div>
                        </Transition>
                    </div>
                </div>
            </header>

            <main class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <slot />
            </main>
        </div>

        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="translate-y-2 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="toast"
                    class="no-print fixed right-4 top-20 z-50 flex w-full max-w-sm items-start gap-3 rounded-2xl border bg-white p-4 shadow-xl"
                    :class="toast.type === 'success' ? 'border-emerald-200' : 'border-rose-200'"
                >
                    <span
                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
                        :class="toast.type === 'success' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'"
                    >
                        <CheckCircle2 v-if="toast.type === 'success'" class="h-5 w-5" />
                        <TriangleAlert v-else class="h-5 w-5" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-slate-800">
                            {{ toast.type === 'success' ? 'Listo' : 'Atención' }}
                        </p>
                        <p class="mt-0.5 text-sm leading-snug text-slate-500">{{ toast.message }}</p>
                    </div>
                    <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100" @click="toast = null">
                        <X class="h-4 w-4" />
                    </button>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
