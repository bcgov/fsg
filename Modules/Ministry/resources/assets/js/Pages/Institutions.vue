<style scoped>
[type='checkbox']:checked, [type='radio']:checked {
    background-size: initial;
}
</style>
<template>
    <Head title="Institutions" />

    <AuthenticatedLayout v-bind="$attrs">

            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="card">
                            <div class="card-header">
                                Institution Search
                            </div>
                            <div class="card-body">
                                <InstitutionSearchBox />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9 mb-3">
                        <div class="card">
                            <div class="card-header">
                                Institutions
<!--                                <button type="button" class="btn btn-success btn-sm float-end" data-bs-toggle="modal" data-bs-target="#newInstModal">New Institution</button>-->
                            </div>
                            <div class="card-body">
                                <div v-if="results != null && results.data.length > 0" class="table-responsive pb-3">
                                    <table class="table table-striped">
                                        <thead>
                                            <InstitutionsHeader></InstitutionsHeader>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(row, i) in results.data">
                                            <td><Link :href="'/ministry/institutions/' + row.id">{{ row.name }}</Link></td>
                                            <td>
                                                <span v-if="row.active_status" class="badge rounded-pill text-bg-success">Active</span>
                                                <span v-else class="badge rounded-pill text-bg-danger">Inactive</span>
                                            </td>
                                            <td>{{ row.size }}</td>
                                            <template v-if="row.active_allocation != null">
                                                <td>${{row.active_allocation.total_amount_formatted }}</td>
<!--                                                <td>{{row.active_allocation.on_hold_amount}}</td>-->
<!--                                                <td>{{row.active_allocation.total_amount - row.active_allocation.claimed_amount}}</td>-->
                                            </template>
                                            <template v-else>
                                                <td>$0</td>
<!--                                                <td></td>-->
<!--                                                <td></td>-->
                                            </template>
                                            <td>
                                                <span v-if="row.overallocation_flag" class="badge rounded-pill text-bg-danger">True</span>
                                                <span v-else class="badge rounded-pill text-bg-success">False</span>
                                            </td>

                                        </tr>
                                        </tbody>
                                    </table>
                                    <Pagination :links="results.links" :active-page="results.current_page" />
                                </div>
                                <h1 v-else class="lead">No results</h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <div class="modal modal-lg fade" id="newInstModal" tabindex="-1" aria-labelledby="newInstModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="newInstModalLabel">Add New Institution</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <InstitutionCreate v-bind="$attrs" :newInst="newInst" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

</template>
<script>
import AuthenticatedLayout from '../Layouts/Authenticated.vue';
import InstitutionSearchBox from '../Components/InstitutionSearch.vue';
import InstitutionsHeader from '../Components/InstitutionsHeader.vue';
import InstitutionCreate from '../Components/InstitutionCreate.vue';
import Pagination from "@/Components/Pagination";
import { Link, Head } from '@inertiajs/vue3';

export default {
    name: 'Institutions',
    components: {
        AuthenticatedLayout, InstitutionSearchBox, InstitutionsHeader, Head, Link, Pagination, InstitutionCreate
    },
    props: {
        results: Object,
        newInst: Object|null
    }
}
</script>
