<script setup>
import { route } from 'ziggy-js';
import PageHeader from '../../components/PageHeader.vue';
import LunaForm from '../../components/forms/LunaForm.vue';

defineProps({
    tipos: { type: Object, default: () => ({}) },
});
</script>

<template>
    <div>
        <PageHeader title="Nuevo tipo de luna" subtitle="Define el lente base y su precio de venta." :back="route('lunas.index')" />
        <LunaForm :tipos="tipos" />
    </div>
</template>
