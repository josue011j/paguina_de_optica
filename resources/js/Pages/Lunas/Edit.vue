<script setup>
import { route } from 'ziggy-js';
import PageHeader from '../../components/PageHeader.vue';
import LunaForm from '../../components/forms/LunaForm.vue';

defineProps({
    luna: { type: Object, required: true },
    tipos: { type: Object, default: () => ({}) },
});
</script>

<template>
    <div>
        <PageHeader :title="`Editar ${luna.nombre}`" subtitle="Actualiza el precio o la disponibilidad del lente." :back="route('lunas.index')" />
        <LunaForm :luna="luna" :tipos="tipos" />
    </div>
</template>
