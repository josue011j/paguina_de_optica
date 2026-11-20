<script setup>
import { route } from 'ziggy-js';
import PageHeader from '../../components/PageHeader.vue';
import UsuarioForm from '../../components/forms/UsuarioForm.vue';

defineProps({
    roles: { type: Object, default: () => ({}) },
    descripciones: { type: Object, default: () => ({}) },
});
</script>

<template>
    <div>
        <PageHeader
            title="Nuevo usuario"
            subtitle="Crea una cuenta y asigna el rol que tendrá dentro de la óptica."
            :back="route('usuarios.index')"
        />
        <UsuarioForm :roles="roles" :descripciones="descripciones" />
    </div>
</template>
