<template>
    <Head title="Claims"/>

    <AuthenticatedLayout v-bind="$attrs">

        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            Claims Search
                        </div>
                        <div class="card-body">
                            <ClaimSearchBox v-bind="$attrs" />
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="card mb-3">
                        <div class="card-header">
                            Student Claims
<!--                            <a href="/ministry/claims/export" target="_blank" class="btn btn-outline-success btn-sm float-end" title="Export Claims"><i class="bi bi-filetype-csv"></i></a>-->
                        </div>
                        <div class="card-body">
                            <div v-if="claimList != '' && results.data.length > 0" class="table-responsive pb-3">
                                <table class="table table-striped">
                                    <thead>
                                    <ClaimsHeader :page="results.current_page" @update="refreshList"></ClaimsHeader>
                                    </thead>
                                    <tbody>
                                    <template v-for="(row, i) in claimList">
                                        <tr v-if="row !== null">
                                            <td><a href="#" @click="openEditForm(row)">{{ row.sin ?? 0 }}</a></td>
                                            <td>{{ row.first_name }}</td>
                                            <td><Link :href="'/ministry/students/' + row.student.id">{{ row.last_name }}</Link></td>
                                            <td><Link :href="'/ministry/institutions/' + row.institution.id">{{ row.institution.name }}</Link></td>
                                            <td>{{ row.program.program_name }}</td>
                                            <td>{{ row.allocation.py.start_date }} {{ row.allocation.py.end_date }}</td>
                                            <td>${{ $amountPlusPyFee(row.estimated_hold_amount, row.py_admin_fee) }}</td>
                                            <td>${{ $amountPlusPyFee(parseFloat(row.registration_fee) + parseFloat(row.materials_fee) + parseFloat(row.program_fee) + parseFloat(row.correction_amount), row.py_admin_fee) }}</td>
<!--                                            <td>${{ row.student.total_grant }}</td>-->
                                            <td>
                                                <p v-if="row.claim_status === 'Draft'" class="badge rounded-pill text-bg-info">Draft<span v-if="row.process_feedback != null" class="badge rounded-pill text-bg-danger ms-1">!</span></p>
                                                <p v-else-if="row.claim_status === 'Submitted'" class="badge rounded-pill text-bg-primary">Submitted<span v-if="row.process_feedback != null" class="badge rounded-pill text-bg-danger ms-1">!</span></p>
                                                <p v-else-if="row.claim_status === 'Hold'" class="badge rounded-pill text-bg-warning">Hold<span v-if="row.process_feedback != null" class="badge rounded-pill text-bg-danger ms-1">!</span></p>
                                                <p v-else-if="row.claim_status === 'Claimed'" class="badge rounded-pill text-bg-success">Claimed<span v-if="row.process_feedback != null" class="badge rounded-pill text-bg-danger ms-1">!</span></p>
                                                <p v-else class="badge rounded-pill text-bg-secondary">{{ row.claim_status }}<span v-if="row.process_feedback != null" class="badge rounded-pill text-bg-danger ms-1">!</span></p>
                                            </td>
                                            <td>
                                                <span v-if="row.outcome_status != null" class="badge rounded-pill text-bg-success ms-1">
                                                    <i class="bi bi-check-lg"></i>
                                                </span>
                                                <span v-else class="badge rounded-pill text-bg-secondary ms-1">
                                                    <i class="bi bi-dash"></i>
                                                </span>
                                            </td>
                                        </tr>
                                    </template>
                                    </tbody>

                                </table>
                                <small v-if="claimList != ''" class="text-danger">* Includes {{ claimList[0].py_admin_fee }}% Administration Fee</small>
                                <Pagination :links="results.links" :active-page="results.current_page" :sort-by="sortBy" :sort-dir="sortDir" />
                            </div>
                            <h1 v-else class="lead">No results</h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="editClaim != ''" class="modal modal-lg" id="editClaimModal" tabindex="0" aria-labelledby="editClaimModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title me-2" id="editClaimModalLabel">Edit Claim
                            <span v-if="editClaim.claim_status === 'Draft'" class="badge rounded-pill text-bg-info">Draft</span>
                            <span v-else-if="editClaim.claim_status === 'Submitted'" class="badge rounded-pill text-bg-primary">Submitted</span>
                            <span v-else-if="editClaim.claim_status === 'Hold'" class="badge rounded-pill text-bg-warning">Hold</span>
                            <span v-else-if="editClaim.claim_status === 'Claimed'" class="badge rounded-pill text-bg-success">Claimed</span>
                            <span v-else class="badge rounded-pill text-bg-secondary">{{ editClaim.claim_status }}</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <ClaimEdit v-bind="$attrs" @close="closeEditForm"
                                      :claim="editClaim"
                                      :page="'claims'"
                                      :results="results" />
                </div>
            </div>
        </div>

    </AuthenticatedLayout>

</template>
<script>
import AuthenticatedLayout from '../Layouts/Authenticated.vue';
import ClaimSearchBox from '../Components/ClaimSearch.vue';
import ClaimsHeader from '../Components/ClaimsHeader.vue';
import ClaimEdit from '../Components/StudentClaimEdit.vue';
import Pagination from "@/Components/Pagination";
import {Link, Head, useForm} from '@inertiajs/vue3';

export default {
    name: 'Claims',
    components: {
        AuthenticatedLayout, ClaimSearchBox, ClaimsHeader, Head, Link, ClaimEdit, Pagination
    },
    props: {
        results: Object,
        institution: Object,
        allocation: Object,
        countries: Object,
        error: String|null,
    },
    data() {
        return {
            claimList: '',
            editClaim: '',
            showEditModal: false,
            sortDir: '',
            sortBy: '',
        }
    },

    methods: {

        formatDate: function (value) {
            if(value !== undefined && value !== ''){
                let date = value.split("T");
                let time = date[1].split(".");

                return date[0];
            }
            return value;
        },
        openEditForm: function (claim) {
            let vm = this;
            this.editClaim = claim;
            setTimeout(function () {
                $("#editClaimModal").modal('show')
                    .on('hidden.bs.modal', function () {
                        vm.editClaim = '';
                    });
            }, 10);
        },
        closeEditForm: function () {
            $("#editClaimModal").modal('hide');
            this.editClaim = '';
            // this.$inertia.visit('/institution/claims');
            window.location.reload();
        },
        refreshList: function (e) {
            this.claimList = e.data;
            this.sortDir = e.sortDir;
            this.sortBy = e.sortBy;
            //console.log(e);
        }

    },
    mounted() {
        this.claimList = this.results.data;
    },
    computed:{

    }
}
</script>
