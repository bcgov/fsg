<template>
    <div class="card mb-3">
        <div class="card-header">
            Applications
<!--                <template>-->
<!--                    <span class="badge rounded-pill text-bg-primary me-1">Active Application Total: {{ claims.data.length }}</span>-->
<!--                </template>-->
            <button v-if="institutions != '' && institutions.length > 0" type="button" class="btn btn-success btn-sm float-end" @click="openNewForm">New Application</button>
        </div>
        <div class="card-body">
            <div v-if="claims != null && claims.data != null && claims.data.length > 0" class="table-responsive pb-3">
                <table class="table table-striped">
                    <thead>
                    <StudentApplicationsHeader @update="updateClaims" :page="claims.current_page"
                                                     :guid="results.guid"></StudentApplicationsHeader>
                    </thead>
                    <tbody>
                    <template v-for="(row, i) in claims.data">
                        <tr v-if="row !== null">
                            <td><a href="#" @click="openEditForm(row)">{{ row.program.program_name }}</a></td>
                            <td>{{ row.institution.name }}</td>
                            <td>${{ row.total_claim_amount }}
                                <span v-if="row.correction_amount > 0 || row.correction_amount < 0" style="color: red;">*</span>
                            </td>

                            <td>${{ row.student.total_grant }}</td>
                            <td>
                                <span v-if="row.claim_status === 'Draft'" class="badge rounded-pill text-bg-info">Draft</span>
                                <span v-else-if="row.claim_status === 'Submitted'" class="badge rounded-pill text-bg-primary">Submitted</span>
                                <span v-else-if="row.claim_status === 'Hold'" class="badge rounded-pill text-bg-warning">Hold</span>
                                <span v-else-if="row.claim_status === 'Claimed'" class="badge rounded-pill text-bg-success">Claimed</span>
                                <span v-else class="badge rounded-pill text-bg-secondary">{{ row.claim_status }}</span>
                            </td>
                            <td>{{ formatDate(row.created_at) }}</td>
                        </tr>
                    </template>
                    </tbody>
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
            <h1 v-else class="lead">No applications</h1>
        </div>


            <div v-if="editApplication == ''" class="modal modal-lg fade" id="newApplicationModal" tabindex="-1"
                 aria-labelledby="newApplicationModalLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="newApplicationModalLabel">New Student Application</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <StudentApplicationCreate v-bind="$attrs" :results="results" :institutions="institutions" />
                    </div>
                </div>
            </div>
            <div v-if="editApplication != ''" class="modal modal-lg fade" id="editApplicationModal" tabindex="0"
                 aria-labelledby="editApplicationModalLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editApplicationModalLabel">Edit Application</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <StudentApplicationEdit v-if="editApplication.claim_status === 'Draft'" v-bind="$attrs" @close="closeEditForm"
                                                :application="editApplication"
                                                :institutions="institutions"
                                                :results="results" />
                        <StudentApplicationReadOnly v-else v-bind="$attrs" @close="closeEditForm"
                                                :application="editApplication"
                                                :institutions="institutions"
                                                :results="results" />
                    </div>
                </div>
            </div>
    </div>

</template>
<script>
import {Link} from '@inertiajs/vue3';
// import InstitutionClaimsByCourseHeader from "./InstitutionClaimsByCourseHeader";
import StudentApplicationCreate from "./StudentApplicationCreate";
import StudentApplicationEdit from "./StudentApplicationEdit";
import StudentApplicationReadOnly from "./StudentApplicationReadOnly";
import Pagination from "@/Components/Pagination";
import StudentApplicationsHeader from "./StudentApplicationsHeader.vue";

export default {
    name: 'DashboardApplications',
    components: {
        Link, Pagination, StudentApplicationsHeader,
        StudentApplicationCreate, StudentApplicationEdit, StudentApplicationReadOnly
    },
    props: {
        results: Object
    },
    data() {
        return {
            institutions: '',
            editApplication: '',
            claims: null
        }
    },
    methods: {
        openNewForm: function () {
            setTimeout(function () {
                $("#newApplicationModal").modal('show');
            }, 10);
        },

        openEditForm: function (claim) {
            let vm = this;
            this.editApplication = claim;
            setTimeout(function () {
                $("#editApplicationModal").modal('show').on('hidden.bs.modal', function () {
                    vm.editApplication = '';
                });
            }, 10);
        },
        closeEditForm: function () {
            $("#editApplicationModal").modal('hide');
            this.editApplication = '';
            this.$inertia.visit('/applications');
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

            axios.get('/student/api/fetch/students/applications?page=' + page)
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
        fetchInstitutions: function () {
            let vm = this;
            axios.get('/student/api/fetch/institutions')
                .then(function (response) {
                    vm.institutions = response.data.institutions;
                })
                .catch(function (error) {
                    // handle error
                    console.log(error);
                });
        },
    },
    mounted() {
        this.fetchData();
        this.fetchInstitutions();
    }
}
</script>
