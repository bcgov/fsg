<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout v-bind="$attrs">

        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            Dashboard Menu
                        </div>
                        <div class="card-body">
                            <DashboardMenu :page="page" :results="results" />
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <DashboardProfile v-bind="$attrs" v-if="page === 'profile'" :results="results"></DashboardProfile>
                    <DashboardApplications v-bind="$attrs" v-if="page === 'applications' && results != null" :results="results"></DashboardApplications>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>

</template>
<script>
import AuthenticatedLayout from '../Layouts/Authenticated.vue';
import { Link, Head } from '@inertiajs/vue3';
import DashboardProfile from "../Components/DashboardProfile.vue";
import DashboardApplications from "../Components/DashboardApplications.vue";
import DashboardMenu from "../Components/DashboardMenu.vue";

export default {
    name: 'Dashboard',
    components: {
        DashboardMenu, DashboardProfile, DashboardApplications,
        AuthenticatedLayout, Head, Link
    },
    props: {
        results: Object,
        page: String|null
    }
}
</script>
