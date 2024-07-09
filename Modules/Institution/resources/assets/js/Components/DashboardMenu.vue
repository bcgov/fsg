<template>

    <div class="list-group">
        <Link v-if="results != null" href="/applications" class="list-group-item list-group-item-action" :class="page.split('applications').length > 1 ? 'active' : ''">Applications</Link>
        <Link href="/profile" class="list-group-item list-group-item-action" :class="page.split('profile').length > 1 ? 'active' : ''">Profile Details</Link>
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
