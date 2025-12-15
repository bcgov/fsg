<template>
    <Head title="Reports" />

    <BreezeAuthenticatedLayout v-bind="$attrs">

            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="card">
                            <div class="card-header">
                                Reports Menu
                            </div>
                            <div class="card-body">
                                <ReportsMenu :page="page" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9 mb-3">
                        <ReportsSummary v-if="page === 'summary'" :py="py" :programYears="programYears"></ReportsSummary>
                        <ReportSources v-if="page === 'sources'" :py="py"></ReportSources>
                    </div>
                </div>
            </div>
    </BreezeAuthenticatedLayout>

</template>
<script>
import BreezeAuthenticatedLayout from '../Layouts/Authenticated.vue';
import { Head, Link } from '@inertiajs/vue3';
import ReportsMenu from "../Components/ReportsMenu";
import ReportsSummary from "../Components/ReportsSummary";
import ReportSources from "../Components/ReportSources";

export default {
    name: 'Reports',
    components: {
        ReportsSummary,
        ReportSources,
        BreezeAuthenticatedLayout, Head, Link, ReportsMenu
    },
    props: {
        results: Object,
        py: Object|null,
        page: String,
        programYears: Array,
    },
    data() {
        return {
        }
    },
}
</script>
