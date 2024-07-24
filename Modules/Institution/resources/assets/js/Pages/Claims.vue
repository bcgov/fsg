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
                            <ClaimSearchBox />
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="card mb-3">
                        <div class="card-header">
                            Student Claims
<!--                            <template v-if="capStat != '' && capStat.instCap != null">-->
<!--                                <span class="badge rounded-pill text-bg-primary me-1">Active Cap Total: {{ capStat.instCap.total_claims}}</span>-->
<!--                                <span class="badge rounded-pill text-bg-primary me-1">Issued PALs: {{ capStat.issued }}</span>-->
<!--                                <span class="badge rounded-pill text-bg-primary me-1">Remaining PALs: {{ capStat.instCap.total_claims - capStat.issued }}</span>-->
<!--                            </template>-->
<!--                            <button type="button" class="btn btn-success btn-sm float-end" @click="openNewForm">New Claim</button>-->
<!--                            <a href="/institution/claims/export" target="_blank" class="btn btn-outline-success btn-sm float-end me-1" title="Export Claims"><i class="bi bi-filetype-csv"></i></a>-->
                        </div>
                        <div class="card-body">
                            <div v-if="results != null && results.data.length > 0" class="table-responsive pb-3">
                                <table class="table table-striped">
                                    <thead>
                                    <ClaimsHeader></ClaimsHeader>
                                    </thead>
                                    <tbody>
                                    <template v-for="(row, i) in results.data">
                                        <tr v-if="row !== null">
                                            <td><a href="#" @click="openEditForm(row)">{{ row.sin ?? 0 }}</a></td>
                                            <td>{{ row.first_name }}</td>
                                            <td><Link :href="'/institution/students/' + row.student.id">{{ row.last_name }}</Link></td>
                                            <td>{{ row.program.program_name }}</td>
                                            <td>${{ $amountPlusPyFee(row.estimated_hold_amount, row.py_admin_fee) }}</td>
                                            <td>${{ parseFloat(row.registration_fee) + parseFloat(row.materials_fee) + parseFloat(row.program_fee) }}</td>
<!--                                            <td>${{ row.student.total_grant }}</td>-->
                                            <td>
                                                <span v-if="row.claim_status === 'Draft'" class="badge rounded-pill text-bg-info">Draft</span>
                                                <span v-else-if="row.claim_status === 'Submitted'" class="badge rounded-pill text-bg-primary">Submitted</span>
                                                <span v-else-if="row.claim_status === 'Hold'" class="badge rounded-pill text-bg-warning">Hold</span>
                                                <span v-else-if="row.claim_status === 'Claimed'" class="badge rounded-pill text-bg-success">Claimed</span>
                                                <span v-else class="badge rounded-pill text-bg-secondary">{{ row.claim_status }}</span>
                                                <span v-if="row.process_feedback != null" class="badge rounded-pill text-bg-danger ms-1">!</span>

                                            </td>
                                            <td>{{ formatDate(row.created_at) }}</td>
                                        </tr>
                                    </template>
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

        <div v-if="editClaim != ''" class="modal modal-lg" id="editClaimModal" tabindex="0" aria-labelledby="editClaimModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title me-2" id="editClaimModalLabel">Edit Claim</h5>
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
import ClaimEdit from '../Components/ClaimEdit.vue';
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
            this.$inertia.visit('/institution/claims');

        },

    },
    mounted() {
        this.claimList = this.results.data;
    },
    computed:{

    }
}
</script>
