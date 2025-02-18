<template>
    <div>
        <div v-if="claims != null && claims.data.length > 0" class="card mb-3">
        <div class="card-header">
                Student Claims
                <template>
                    <span class="badge rounded-pill text-bg-primary me-1">Active Claim Total: {{ claims.data.length }}</span>
                </template>
    <!--            <button type="button" class="btn btn-success btn-sm float-end" @click="openNewForm">New Claim</button>-->
            </div>
            <div class="card-body">
                <div v-if="claims.data != null && claims.data.length > 0" class="table-responsive pb-3">
                    <table class="table table-striped">
                        <thead>
                        <StudentClaimsHeader @update="updateClaims" :page="claims.data.current_page"
                                                         :guid="results.guid"></StudentClaimsHeader>
                        </thead>
                        <tbody>
                        <template v-for="(row, i) in claims.data">
                            <tr v-if="row !== null">
                                <td><a href="#" @click="openEditForm(row)">{{ row.program.program_name }}</a></td>
                                <td>${{ row.estimated_hold_amount }}</td>
                                <td>${{ row.registration_fee }}</td>
                                <td>${{ row.materials_fee }}</td>
                                <td>${{ row.program_fee }}</td>
                                <td>
                                    <span v-if="row.claim_status === 'Draft'" class="badge rounded-pill text-bg-info">Draft</span>
                                    <span v-else-if="row.claim_status === 'Submitted'" class="badge rounded-pill text-bg-primary">Submitted</span>
                                    <span v-else-if="row.claim_status === 'Hold'" class="badge rounded-pill text-bg-warning">Hold</span>
                                    <span v-else-if="row.claim_status === 'Claimed'" class="badge rounded-pill text-bg-success">Claimed</span>
                                    <span v-else class="badge rounded-pill text-bg-secondary">{{ row.claim_status }}</span>

                                    <span v-if="row.process_feedback != null" class="badge rounded-pill text-bg-danger ms-1">!</span>
                                </td>
                                <td>{{ $formatDate(row.created_at) }}</td>
                            </tr>
                        </template>
                        </tbody>
                        <tfoot>
                        <tr>
                            <th></th>
                            <th>${{ countTotals('hold') }}</th>
                            <th>${{ countTotals('registration_fee') }}</th>
                            <th>${{ countTotals('materials_fee') }}</th>
                            <th>${{ countTotals('program_fee') }}</th>
                            <th></th>
                            <th></th>
                        </tr>

                        </tfoot>
                    </table>
                    <div v-if="claims.links.length > 3">
                        <div class="d-flex flex-row justify-content-center -mb-1">
                            <template v-for="(link, key) in claims.links">
                                <div v-if="link.url === null" :key="key" class="btn btn-light border m-1" v-html="link.label" />
                                <span v-else :key="`link-${key}`" class="btn btn-light btn-link border m-1" :class="{ 'disabled': (link.label == claims.current_page) }" @click="fetchData(link.url)" v-html="link.label" />
                            </template>
                        </div>
                    </div>

                </div>
                <h1 v-else class="lead">No results</h1>
            </div>
            <div v-if="editClaim != ''" class="modal modal-lg fade" id="editClaimModal" tabindex="0"
                 aria-labelledby="editClaimModalLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editClaimModalLabel">Edit Student Claim</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <ClaimEdit v-bind="$attrs" @close="closeEditForm"
                                   :claim="editClaim"
                                   :page="'students'" :subPage="'claims'"
                                   :results="results" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import {Link} from '@inertiajs/vue3';
import StudentClaimsHeader from "./StudentClaimsHeader";
import ClaimEdit from "./ClaimEdit";
import Pagination from "@/Components/Pagination";

export default {
    name: 'StudentClaims',
    components: {
        Link, StudentClaimsHeader, Pagination,
        ClaimEdit
    },
    props: {
        results: Object,
    },
    data() {
        return {
            editClaim: '',
            claims: null
        }
    },
    methods: {
        countTotals: function (type) {
            if(this.claims.data == null || this.claims.data.length === 0) return 0;
            let total = 0;
            if(type === 'hold'){
                this.claims.data.forEach(item => {
                    total += parseFloat(item.estimated_hold_amount);
                });
            }
            if(type === 'program_fee'){
                this.claims.data.forEach(item => {
                    total += parseFloat(item.program_fee);
                });
            }
            if(type === 'registration_fee'){
                this.claims.data.forEach(item => {
                    total += parseFloat(item.registration_fee);
                });
            }
            if(type === 'materials_fee'){
                this.claims.data.forEach(item => {
                    total += parseFloat(item.materials_fee);
                });
            }
            if(type === 'correction'){
                this.claims.data.forEach(item => {
                    total += parseFloat(item.correction);
                });
            }

            return total;
        },

        openEditForm: function (claim) {
            this.editClaim = claim;
            setTimeout(function () {
                let vm = this;
                $("#editClaimModal").modal('show').on('hidden.bs.modal', function () {
                    vm.editClaim = '';
                });

            }, 10);
        },
        closeEditForm: function () {
            $("#editClaimModal").modal('hide');
            this.editClaim = '';
            this.$inertia.visit('/institution/students/' + this.results.id + '/claims');

        },
        formatDate: function (value) {
            if (value !== undefined && value !== '') {
                let date = value.split("T");

                return date[0];
            }
            return value;
        },
        fetchData: function (page = 1) {
            let vm = this;

            axios.get('/institution/api/fetch/students/claims-by-student?in=' + this.results.guid + '&page=' + page)
                .then(function (response) {
                    vm.claims = response.data.body;
                })
                .catch(function (error) {
                    // handle error
                    console.log(error);
                });
        },
        updateClaims: function (e) {
            this.claims = e;
        }
    },
    mounted() {
        this.fetchData();
        // this.claims = this.results.claims;
    }
}
</script>
