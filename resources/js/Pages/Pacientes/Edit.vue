<script setup>
import { route } from 'ziggy-js';
import PageHeader from '../../components/PageHeader.vue';
import PacienteForm from '../../components/forms/PacienteForm.vue';

defineProps({
    paciente: { type: Object, required: true },
});
</script>

<template>
    <div>
        <PageHeader
            title="Editar paciente"
            :subtitle="`Actualiza los datos de ${paciente.nombres} ${paciente.apellidos}.`"
            :back="route('pacientes.show', paciente.id)"
        />

        <PacienteForm :paciente="paciente" />
    </div>
</template>
