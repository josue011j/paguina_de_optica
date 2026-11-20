<script setup>
import { route } from 'ziggy-js';
import PageHeader from '../../components/PageHeader.vue';
import PacienteForm from '../../components/forms/PacienteForm.vue';
</script>

<template>
    <div>
        <PageHeader
            title="Nuevo paciente"
            subtitle="Registra los datos del paciente para su historial y futuras ventas."
            :back="route('pacientes.index')"
        />

        <PacienteForm />
    </div>
</template>
