<script setup>
import { route } from 'ziggy-js';
import PageHeader from '../../components/PageHeader.vue';
import UsuarioForm from '../../components/forms/UsuarioForm.vue';

defineProps({
    usuario: { type: Object, required: true },
    roles: { type: Object, default: () => ({}) },
    descripciones: { type: Object, default: () => ({}) },
});
</script>

<template>
    <div>
        <PageHeader
            title="Editar usuario"
            :subtitle="`Actualiza la cuenta de ${usuario.name}.`"
            :back="route('usuarios.index')"
        />
        <UsuarioForm :usuario="usuario" :roles="roles" :descripciones="descripciones" />
    </div>
</template>
