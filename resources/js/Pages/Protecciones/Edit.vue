<script setup>
import { route } from 'ziggy-js';
import PageHeader from '../../components/PageHeader.vue';
import ProteccionForm from '../../components/forms/ProteccionForm.vue';

defineProps({
    proteccion: { type: Object, required: true },
});
</script>

<template>
    <div>
        <PageHeader :title="`Editar ${proteccion.nombre}`" subtitle="Actualiza el precio o la disponibilidad." :back="route('protecciones.index')" />
        <ProteccionForm :proteccion="proteccion" />
    </div>
</template>
