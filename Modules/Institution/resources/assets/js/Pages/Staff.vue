<template>
    <Head title="Staff" />

    <AuthenticatedLayout v-bind="$attrs">

        <div class="container">
            <div class="row">
                <div class="col-12">
                    <StaffList :results="results"></StaffList>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

</template>
<script>
import AuthenticatedLayout from '../Layouts/Authenticated.vue';
import { Head, Link } from '@inertiajs/vue3';
import StaffList from "../Components/StaffList";

export default {
    name: 'Staff',
    components: {
        AuthenticatedLayout, Head, Link, StaffList
    },
    props: {
        results: Object,
        categories: Object|null,
        page: String,
    },
    data() {
        return {
        }
    },
}
</script>
