<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout v-bind="$attrs">

            <div class="container-fluid">
                <div class="row">

                    <div class="col-md-9">
                        Hi
                    </div>
                </div>
            </div>

    </AuthenticatedLayout>

</template>
<script>
import AuthenticatedLayout from '../Layouts/Authenticated.vue';
import { Link, Head } from '@inertiajs/vue3';

export default {
    name: 'Dashboard',
    components: {
        AuthenticatedLayout, Head, Link
    },
    props: {
        results: Object,
    }
}
</script>
