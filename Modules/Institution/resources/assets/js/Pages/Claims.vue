<template>
    <Head title="Claims" />

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
                            <a href="/institution/claims/export" target="_blank" class="btn btn-outline-success btn-sm float-end me-1" title="Export Claims"><i class="bi bi-filetype-csv"></i></a>
                        </div>
                        <div class="card-body">
                            <div v-if="results != null && results.data.length > 0" class="table-responsive pb-3">
                                <table class="table table-striped">
                                    <thead>
                                    <ClaimsHeader></ClaimsHeader>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(row, i) in claimList">
                                        <td><button type="button" @click="openEditForm(row)" class="btn btn-link p-0 text-start">{{ row.last_name }}</button></td>
                                        <td>{{ row.first_name }}</td>
                                        <td>{{ row.sin }}</td>
                                        <td><span v-if="row.program !== null">{{ row.program.program_name }}</span></td>
                                        <td>
                                            <div>
                                                <span v-if="row.claim_status === 'Submitted'" class="badge rounded-pill text-bg-success">Submitted</span>
                                                <span v-if="row.claim_status === 'Hold'" class="badge rounded-pill text-bg-warning">Hold</span>
                                                <span v-if="row.claim_status === 'Claimed'" class="badge rounded-pill text-bg-primary">Claimed</span>
                                                <span v-if="row.claim_status === 'Report Completed'" class="badge rounded-pill text-bg-info">Report Completed</span>
                                            </div>
                                        </td>
                                        <td>${{ row.estimated_hold_amount }}</td>
                                        <td>${{ row.total_claim_amount }}</td>
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

        <div v-if="showEditModal" class="modal modal-lg" id="editAtteModal" tabindex="0" aria-labelledby="editAtteModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title me-2" id="editAtteModalLabel">Edit Claim</h5>
                        <strong>Issued by: {{editRow.issued_by_name}}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <ClaimEdit :instCap="instCap" :error="error" v-bind="$attrs" :countries="countries" :institution="institution" :programs="programs" :claim="editRow" />
                    <div v-if="editRow.status === 'Issued'" class="modal-footer justify-content-between">
<!--                        <a :href="'/institution/claims/download/' + editRow.id" target="_blank" class="btn btn-success">-->
<!--                            Download <i class="bi bi-box-arrow-down"></i>-->
<!--                        </a>-->
<!--                        <button @click="duplicate" type="button" class="btn btn-secondary">Replicate &amp; Issue</button>-->
                    </div>
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
            editRow: '',
            showEditModal: false,
            capStat: ''
        }
    },

    methods: {
        duplicate: function (){
            let check = confirm('Are you sure you want to replicate and issue this claim? This will result in ' +
                'changing the status of the existing one to DECLINED and to use your available CAP to issue the new one if possible.');
            if(check){
                let duplicateForm = useForm({
                    formState: null,
                    formSuccessMsg: 'Form was submitted successfully.',
                    formFailMsg: 'There was an error submitting this form.',
                    old_guid: this.editRow.guid,
                })
                duplicateForm.formState = null;
                duplicateForm.post('/institution/duplicate_claims', {
                    onSuccess: (response) => {
                        $("#editAtteModal").modal('hide');

                        this.$inertia.visit('/institution/claims');
                    },
                    onError: () => {
                        duplicateForm.formState = false;
                    },
                    preserveState: true
                });
            }
        },
        formatDate: function (value) {
            if(value !== undefined && value !== ''){
                let date = value.split("T");
                let time = date[1].split(".");

                return date[0];
            }
            return value;
        },
        openEditForm: function (row){
            let vm = this;
            this.editRow = row;
            this.showEditModal = true;
            setTimeout(function(){
                $("#editAtteModal").modal('show')
                    .on('hidden.bs.modal', function () {
                        vm.showEditModal = false;
                        vm.editRow = '';
                    });
            }, 10);
        },
        fetchCapStats: function () {
            let vm = this;
            let data = {
                institution_guid: this.institution.guid,
            }
            axios.post('/institution/api/fetch/capStats', data)
                .then(function (response) {
                    vm.capStat = response.data.body;
                })
                .catch(function (error) {
                    // handle error
                    console.log(error);
                });
        }
    },
    mounted() {
        this.claimList = this.results.data;
        this.fetchCapStats();
    },
}
</script>
