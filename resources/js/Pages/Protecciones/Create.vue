<script setup>
import { route } from 'ziggy-js';
import PageHeader from '../../components/PageHeader.vue';
import ProteccionForm from '../../components/forms/ProteccionForm.vue';
</script>

<template>
    <div>
        <PageHeader title="Nueva protección" subtitle="Agrega un tratamiento o protección para las lunas." :back="route('protecciones.index')" />
        <ProteccionForm />
    </div>
</template>
