<script setup>
import { route } from 'ziggy-js';
import PageHeader from '../../components/PageHeader.vue';
import MarcoForm from '../../components/forms/MarcoForm.vue';
</script>

<template>
    <div>
        <PageHeader title="Nuevo marco" subtitle="Agrega un marco al inventario de la óptica." :back="route('marcos.index')" />
        <MarcoForm />
    </div>
</template>
