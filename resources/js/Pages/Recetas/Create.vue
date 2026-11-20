<script setup>
import { route } from 'ziggy-js';
import PageHeader from '../../components/PageHeader.vue';
import RecetaForm from '../../components/forms/RecetaForm.vue';

defineProps({
    pacientes: { type: Array, default: () => [] },
    pacienteSeleccionado: { type: [Number, String], default: null },
});
</script>

<template>
    <div>
        <PageHeader
            title="Nueva receta"
            subtitle="Registra las medidas que debe tener cada luna."
            :back="route('recetas.index')"
        />
        <RecetaForm :pacientes="pacientes" :paciente-seleccionado="pacienteSeleccionado" />
    </div>
</template>
