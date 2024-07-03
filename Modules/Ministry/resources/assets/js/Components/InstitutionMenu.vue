<template>

    <div class="list-group">
        <Link href="#" class="list-group-item list-group-item-action disabled list-group-item-secondary"><strong>{{ name }}</strong></Link>
        <Link :href="'/ministry/institutions/' + id" class="list-group-item list-group-item-action" :class="page === 'details' ? 'active' : ''">Information &amp; Contact Info.</Link>
        <Link :href="'/ministry/institutions/' + id + '/programs'" class="list-group-item list-group-item-action" :class="page === 'programs' ? 'active' : ''">Programs</Link>
        <Link :href="'/ministry/institutions/' + id + '/claims-by-course'" class="list-group-item list-group-item-action" :class="page === 'claims-by-course' ? 'active' : ''">Claims</Link>
<!--        <Link :href="'/ministry/institutions/' + id + '/claims-by-student'" class="list-group-item list-group-item-action" :class="page === 'claims-by-student' ? 'active' : ''">Claims by Student</Link>-->
        <Link :href="'/ministry/institutions/' + id + '/allocations'" class="list-group-item list-group-item-action" :class="page === 'allocations' ? 'active' : ''">Allocations</Link>
        <Link :href="'/ministry/institutions/' + id + '/staff'" class="list-group-item list-group-item-action" :class="page === 'staff' ? 'active' : ''">BCeID Staff List</Link>
    </div>
</template>
<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({
    page: String,
    id: Number,
    name: String
});

</script>
