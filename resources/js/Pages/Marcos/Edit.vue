<script setup>
import { route } from 'ziggy-js';
import PageHeader from '../../components/PageHeader.vue';
import MarcoForm from '../../components/forms/MarcoForm.vue';

defineProps({
    marco: { type: Object, required: true },
});
</script>

<template>
    <div>
        <PageHeader
            title="Editar marco"
            :subtitle="`${marco.marca} ${marco.modelo} · ${marco.codigo}`"
            :back="route('marcos.index')"
        />
        <MarcoForm :marco="marco" />
    </div>
</template>
