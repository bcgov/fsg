<template>
    <div v-if="claims != null && claims.data.length > 0" class="card">
    <div class="card-header">
            Institution Claims by Course
            <template>
                <span class="badge rounded-pill text-bg-primary me-1">Active Claim Total: {{ claims.data.length }}</span>
            </template>
            <button type="button" class="btn btn-success btn-sm float-end" @click="openNewForm">New Claim</button>
        </div>
        <div class="card-body">
            <div v-if="claims.data != null && claims.data.length > 0" class="table-responsive pb-3">
                <table class="table table-striped">
                    <thead>
                    <InstitutionClaimsByCourseHeader @update="updateClaims" :page="claims.current_page"
                                                     :guid="results.guid"></InstitutionClaimsByCourseHeader>
                    </thead>
                    <tbody>
                    <template v-for="(row, i) in claims.data">
                        <tr v-if="row !== null">
                            <td><a href="#" @click="openEditForm(row)">{{ row.id }}</a></td>
                            <td>{{ row.first_name }}</td>
                            <td><Link :href="'/ministry/students/' + row.student.id">{{ row.last_name }}</Link></td>
                            <td>{{ row.program.program_name }}</td>
                            <td>${{ $amountPlusPyFee(row.estimated_hold_amount, row.py_admin_fee) }}</td>
                            <td>${{ totalPlusAdmin(row) }}
                                <span v-if="row.correction_amount > 0 || row.correction_amount < 0" style="color: red;">*</span>
                            </td>
<!--                            <td>${{ row.student.total_grant }}</td>-->
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
<!--                <Pagination :links="claims.links" :active-page="claims.current_page" />-->
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
    </div>

    <div v-if="editClaim == ''" class="modal modal-lg fade" id="newClaimModal" tabindex="-1"
         aria-labelledby="newClaimModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newClaimModalLabel">New Student Claim</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <StudentClaimCreate v-bind="$attrs" :results="results"/>
            </div>
        </div>
    </div>
    <div v-if="editClaim != ''" class="modal modal-lg fade" id="editClaimModal" tabindex="0"
         aria-labelledby="editClaimModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editClaimModalLabel">Edit Student Claim</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="clearEditForm"></button>
                </div>
                <StudentClaimEdit v-bind="$attrs" @close="closeEditForm"
                                  :claim="editClaim"
                                  :page="'institutions'" :subPage="'claims-by-course'"
                                  :results="results" />
            </div>
        </div>
    </div>
</template>
<script>
import {Link} from '@inertiajs/vue3';
import InstitutionClaimsByCourseHeader from "./InstitutionClaimsByCourseHeader";
import StudentClaimCreate from "./StudentClaimCreate";
import StudentClaimEdit from "./StudentClaimEdit";
import Pagination from "@/Components/Pagination";

export default {
    name: 'InstitutionClaimsByCourse',
    components: {
        Link, InstitutionClaimsByCourseHeader, Pagination,
        StudentClaimCreate, StudentClaimEdit
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
        openNewForm: function () {
            setTimeout(function () {
                $("#newClaimModal").modal('show');
            }, 10);
        },
        clearEditForm: function () {
            this.editClaim = '';
        },

        openEditForm: function (claim) {
            this.editClaim = claim;
            setTimeout(function () {
                $("#editClaimModal").modal('show');
            }, 10);
        },
        closeEditForm: function () {
            $("#editClaimModal").modal('hide');
            this.editClaim = '';
            this.$inertia.visit('/ministry/institutions/' + this.results.id + '/claims-by-course');

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

            axios.get('/ministry/api/fetch/institutions/claims-by-course?in=' + this.results.guid + '&page=' + page)
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
        },
        totalPlusAdmin(claim) {
            const programFee = parseFloat(claim.program_fee) || 0;
            const registrationFee = parseFloat(claim.registration_fee) || 0;
            const materialsFee = parseFloat(claim.materials_fee) || 0;
            const adminFeePercentage = parseInt(claim.allocation.py_admin_fee) || 0;
            const correction = parseInt(claim.correction_amount) || 0;

            const total = programFee + registrationFee + materialsFee + correction;
            const adminFee = total * (adminFeePercentage / 100);

            return total + adminFee;
        }
    },
    computed: {

    },
    mounted() {
        this.fetchData();
    }
}
</script>
