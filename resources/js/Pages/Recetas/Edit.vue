<script setup>
import { route } from 'ziggy-js';
import PageHeader from '../../components/PageHeader.vue';
import RecetaForm from '../../components/forms/RecetaForm.vue';

defineProps({
    receta: { type: Object, required: true },
    pacientes: { type: Array, default: () => [] },
});
</script>

<template>
    <div>
        <PageHeader
            title="Editar receta"
            :subtitle="`${receta.paciente?.nombre_completo || 'Paciente'} · ${receta.fecha_label}`"
            :back="route('recetas.show', receta.id)"
        />
        <RecetaForm :receta="receta" :pacientes="pacientes" />
    </div>
</template>
