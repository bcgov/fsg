<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout v-bind="$attrs">

        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-header">
                            Dashboard Menu
                        </div>
                        <div class="card-body">
                            <DashboardMenu :page="page" :results="results" />
                        </div>
                    </div>
                </div>
                <div class="col-md-9 mb-3">
                    <DashboardApplications v-bind="$attrs" v-if="page === 'applications' && results != null" :results="results"></DashboardApplications>
                    <DashboardProfile v-bind="$attrs" v-if="page === 'profile' && results != null && individual_data == null" :error="error" :results="results" :provider-user="providerUser"></DashboardProfile>
                    <DashboardProfileWithPdex v-bind="$attrs" v-if="page === 'profile' && results != null && individual_data != null" :error="error" :results="results" :provider-user="providerUser"></DashboardProfileWithPdex>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>

</template>
<script>
import AuthenticatedLayout from '../Layouts/Authenticated.vue';
import { Link, Head } from '@inertiajs/vue3';
import DashboardProfile from "../Components/DashboardProfile.vue";
import DashboardProfileWithPdex from "../Components/DashboardProfileWithPdex.vue";
import DashboardApplications from "../Components/DashboardApplications.vue";
import DashboardMenu from "../Components/DashboardMenu.vue";

export default {
    name: 'Dashboard',
    components: {
        DashboardMenu, DashboardProfile, DashboardProfileWithPdex, DashboardApplications,
        AuthenticatedLayout, Head, Link
    },
    props: {
        results: Object,
        providerUser: Object|null,
        page: String|null,
        error: String|null
    }
}
</script>
