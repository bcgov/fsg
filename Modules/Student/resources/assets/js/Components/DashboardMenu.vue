<template>

    <div class="list-group">
        <Link href="/student/profile" class="list-group-item list-group-item-action" :class="page.split('profile').length > 1 ? 'active' : ''">Profile Details</Link>
        <Link v-if="results != null" href="/student/applications" class="list-group-item list-group-item-action" :class="page.split('applications').length > 1 ? 'active' : ''">Applications</Link>
<!--        <Link href="/ministry/maintenance/faqs" class="list-group-item list-group-item-action" :class="page.split('faqs').length > 1 ? 'active' : ''">FAQs</Link>-->
    </div>
</template>
<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    page: String,
    results: Object
});

</script>
