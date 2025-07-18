<template>

    <div class="list-group">
        <Link href="/ministry/maintenance/staff" class="list-group-item list-group-item-action" :class="page.split('staff').length > 1 ? 'active' : ''">Staff</Link>
        <Link href="/ministry/maintenance/program_years" class="list-group-item list-group-item-action" :class="page.split('program_years').length > 1 ? 'active' : ''">Program Years</Link>
        <Link href="/ministry/maintenance/utils" class="list-group-item list-group-item-action" :class="page.split('utils').length > 1 ? 'active' : ''">Utils</Link>
        <Link href="/ministry/maintenance/faqs" class="list-group-item list-group-item-action" :class="page.split('faqs').length > 1 ? 'active' : ''">FAQs</Link>
        <Link href="/ministry/maintenance/demographics" class="list-group-item list-group-item-action" :class="page.split('demographics').length > 1 ? 'active' : ''">Demographics</Link>
        <!-- <Link href="/ministry/maintenance/shareable_entities" class="list-group-item list-group-item-action" :class="page.split('shareable_entities').length > 1 ? 'active' : ''">Shareable Entities</Link> -->
    </div>
</template>
<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    page: String,
});

</script>
