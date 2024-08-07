<template>
    <div v-if="claims != null && claims.data.length > 0" class="card">
    <div class="card-header">
            Institution Claims by Student
            <template>
                <span class="badge rounded-pill text-bg-primary me-1">Active Claim Total: {{ claims.data.length }}</span>
            </template>
            <button type="button" class="btn btn-success btn-sm float-end" @click="openNewForm">New Claim</button>
        </div>
        <div class="card-body">
            <div v-if="claims.data != null && claims.data.length > 0" class="table-responsive pb-3">
                <table class="table table-striped">
                    <thead>
                    <InstitutionClaimsByStudentHeader @update="updateClaims" :page="claims.current_page" :guid="results.guid"></InstitutionClaimsByStudentHeader>
                    </thead>
                    <tbody>
                    <template v-for="(row, i) in claims.data">
                        <tr v-if="row !== null">
                            <td><a href="#" @click="openEditForm(row)">{{ row.first_name }}</a></td>
                            <td>{{ row.last_name }}</td>
                            <td>{{ row.dob }}</td>
                            <td>${{ row.total_grant }}</td>
                            <td>{{ row.claims.length }}</td>
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <StudentClaimEdit v-bind="$attrs" @close="closeEditForm"
                                  :claim="editClaim"
                                  :page="'institutions'" :subPage="'claims-by-student'"
                                  :results="results" />
            </div>
        </div>
    </div>
</template>
<script>
import {Link} from '@inertiajs/vue3';
import StudentClaimCreate from "./StudentClaimCreate";
import StudentClaimEdit from "./StudentClaimEdit";
import Pagination from "@/Components/Pagination";
import InstitutionClaimsByStudentHeader from "./InstitutionClaimsByStudentHeader.vue";

export default {
    name: 'InstitutionClaimsByStudent',
    components: {
        Link, InstitutionClaimsByStudentHeader, Pagination,
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

        openEditForm: function (claim) {
            this.editClaim = claim;
            setTimeout(function () {
                $("#editClaimModal").modal('show');
            }, 10);
        },
        closeEditForm: function () {
            $("#editClaimModal").modal('hide');
            this.editClaim = '';
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

            axios.get('/ministry/api/fetch/institutions/claims-by-student?in=' + this.results.guid + '&page=' + page)
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
    }
}
</script>
