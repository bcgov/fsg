<template>

    <div class="list-group">
        <Link href="#" class="list-group-item list-group-item-action disabled list-group-item-secondary"><strong>{{ first_name }} {{ last_name }}</strong></Link>
        <Link :href="'/ministry/students/' + id" class="list-group-item list-group-item-action" :class="page === 'details' ? 'active' : ''">Information &amp; Contact Info.</Link>
        <Link :href="'/ministry/students/' + id + '/claims'" class="list-group-item list-group-item-action" :class="page === 'claims' ? 'active' : ''">Claims</Link>
    </div>
</template>
<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({
    page: String,
    id: Number,
    first_name: String,
    last_name: String
});

</script>
