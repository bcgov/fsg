<template>
    <div class="card">
        <div class="card-header">
            <div>Program Years
                <button @click="newPy" type="button" class="btn btn-success btn-sm float-end">New Program Year</button>
            </div>

        </div>

        <div class="card-body">
            <div v-if="results != null" class="table-responsive pb-3">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Start Date</th>
                            <th scope="col">End Date</th>
                            <th scope="col">Claim Percentage %</th>
                            <th scope="col">Status</th>
                            <th scope="col">Comment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(row, i) in results">
                            <td><a href="#" @click="editPy(row)">{{ row.start_date}}</a></td>
                            <td>{{ row.end_date }}</td>
                            <td>{{ row.claim_percent }}%</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group" aria-label="Toggle program year status">
                                    <input type="radio" class="btn-check" :name="'btnRadioStatus1'+i"
                                           :id="'btnRadioStatus1'+i" autocomplete="off" :checked="row.status === 'active'" :disabled="row.status === 'active'">
                                    <label @click.prevent="switchStatus(row,'active')" class="btn btn-outline-success" :for="'btnRadioStatus1'+i">Active</label>

                                    <input type="radio" class="btn-check" :name="'btnRadioStatus2'+i"
                                           :id="'btnRadioStatus2'+i" autocomplete="off" :checked="row.status !== 'active'" :disabled="row.status !== 'active'">
                                    <label @click.prevent="switchStatus(row,'inactive')" class="btn btn-outline-success" :for="'btnRadioStatus2'+i">Inactive</label>
                                </div>
                            </td>
                            <td>{{ row.comment }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <h1 v-else class="lead">No results</h1>
        </div>


        <div class="modal modal-lg fade" id="newPyModal" tabindex="-1" aria-labelledby="newPyModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="newPyModalLabel">New Program Year</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form v-if="newPyForm != null" @submit.prevent="submitNewPy">
                        <div class="modal-body">
                            <div class="card-body">
                                <div class="row g-3">

                                    <div class="col-md-4">
                                        <BreezeLabel for="newPyStartDate" class="form-label" value="Start Date" />
                                        <BreezeInput type="date" class="form-control" id="newPyStartDate" v-model="newPyForm.start_date" />
                                    </div>
                                    <div class="col-md-4">
                                        <BreezeLabel for="newEndDate" class="form-label" value="End Date" />
                                        <BreezeInput type="date" class="form-control" id="newEndDate" v-model="newPyForm.end_date" />
                                    </div>
                                    <div class="col-md-4">
                                        <BreezeLabel for="newClaimPercent" class="form-label" value="Claim Percentage" />
                                        <BreezeInput type="number" step=".01" class="form-control" id="newClaimPercent" v-model="newPyForm.claim_percent" />
                                    </div>
                                    <div class="col-12">
                                        <BreezeLabel for="newComment" class="form-label" value="Comments" />
                                        <textarea class="form-control" id="newComment"
                                                  v-model="newPyForm.comment"></textarea>
                                    </div>

                                </div>

                                <div v-if="newPyForm.errors != undefined" class="row">
                                    <div class="col-12">
                                        <div v-if="newPyForm.hasErrors == true" class="alert alert-danger mt-3">
                                            <ul>
                                                <li v-for="err in newPyForm.errors"><small>{{ err }}</small></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-sm btn-success" :disabled="newPyForm.processing">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div><!-- end new util -->
        <FormSubmitAlert :form-state="newPyForm.formState"></FormSubmitAlert>

        <div class="modal modal-lg fade" id="editPyModal" tabindex="-1" aria-labelledby="editPyModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editPyModalLabel">Edit Program Year</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form v-if="editPyForm != null" @submit.prevent="submitEditPy">
                        <div class="modal-body">
                            <div class="card-body">
                                <div class="row g-3">

                                    <div class="col-md-4">
                                        <BreezeLabel for="editPyStartDate" class="form-label" value="Start Date" />
                                        <BreezeInput type="date" class="form-control" id="editPyStartDate" v-model="editPyForm.start_date" />
                                    </div>
                                    <div class="col-md-4">
                                        <BreezeLabel for="editEndDate" class="form-label" value="End Date" />
                                        <BreezeInput type="date" class="form-control" id="editEndDate" v-model="editPyForm.end_date" />
                                    </div>
                                    <div class="col-md-4">
                                        <BreezeLabel for="editClaimPercent" class="form-label" value="Claim Percentage" />
                                        <BreezeInput type="number" step=".01" class="form-control" id="editClaimPercent" v-model="editPyForm.claim_percent" />
                                    </div>
                                    <div class="col-12">
                                        <BreezeLabel for="editComment" class="form-label" value="Comments" />
                                        <textarea class="form-control" id="editComment"
                                                  v-model="editPyForm.comment"></textarea>
                                    </div>


                                </div>

                                <div v-if="editPyForm.errors != undefined" class="row">
                                    <div class="col-12">
                                        <div v-if="editPyForm.hasErrors == true" class="alert alert-danger mt-3">
                                            <ul>
                                                <li v-for="err in editPyForm.errors"><small>{{ err }}</small></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-sm btn-success" :disabled="editPyForm.processing">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div><!-- end edit util -->
        <FormSubmitAlert :form-state="editPyForm.formState"></FormSubmitAlert>

    </div>

</template>
<script>
import {Link, useForm} from '@inertiajs/vue3';
import BreezeInput from '@/Components/Input.vue';
import BreezeSelect from "@/Components/Select.vue";
import BreezeLabel from "@/Components/Label.vue";
import FormSubmitAlert from "@/Components/FormSubmitAlert.vue";
import Input from "@/Components/Input.vue";

export default {
    name: 'MaintenanceProgramYears',
    components: {
        Input,
        FormSubmitAlert, BreezeLabel, BreezeSelect,
        BreezeInput, Link
    },
    props: {
        results: Object,
    },
    data() {
        return {
            editStatusForm: '',
            editPyForm: '',
            newPyForm: '',
            newPyFormData: {
                claim_percent: 0,
                comment: '',
                end_date: '',
                start_date: '',
                status: 'inactive'
            }
        }
    },
    methods: {

        switchStatus: function (programYear, status) {
            const statusMessage = status === 'inactive'
                ? 'You are about to switch this Program Year to INACTIVE. This will disable all institutions\' allocations associated to this program year.'
                : 'You are about to switch this Program Year to ACTIVE. This will disabled any Active program year and enable all institutions\' allocations associated to this program year.';

            alert(statusMessage);

            const confirmMessage = `Are you sure you want to switch this program year's Status to: ${status === 'inactive' ? 'Inactive' : 'Active'}?`;

            if (confirm(confirmMessage)) {
                this.editStatusForm = useForm(programYear);
                this.editStatusForm.status = status;
                this.submitSwitchForm();
            }
        },

        submitSwitchForm: function () {
            this.editStatusForm.formState = null;
            this.editStatusForm.put('/ministry/maintenance/program_years/' + this.editStatusForm.id, {
                onSuccess: () => {
                    this.editStatusForm.reset();
                    this.$inertia.visit('/ministry/maintenance/program_years');
                },
                onError: () => {
                    this.editStatusForm.formState = false;
                },
                preserveState: true
            });
        },
        newPy: function () {
            $("#newPyModal").modal('show');
            this.newPyForm = useForm(this.newPyFormData);
        },
        submitNewPy: function ()
        {
            this.newPyForm.formState = '';
            this.newPyForm.post('/ministry/maintenance/program_years', {
                onSuccess: (response) => {
                    $("#newPyModal").modal('hide');
                    this.newPyForm.reset(this.newPyFormData);
                    this.newPyForm.formState = true;

                },
                onError: () => {
                    this.newPyForm.formState = false;
                },
                preserveState: true,
                preserveScroll: true,
            });
        },

        editPy: function (programYear) {
            $("#editPyModal").modal('show');
            this.editPyForm = useForm(programYear);
        },
        submitEditPy: function ()
        {
            this.editPyForm.formState = '';
            this.editPyForm.put('/ministry/maintenance/program_years/' + this.editPyForm.id, {
                onSuccess: (response) => {
                    $("#editPyModal").modal('hide');
                    this.editPyForm.reset();
                    this.editPyForm.formState = true;

                },
                onError: () => {
                    this.editPyForm.formState = false;
                },
                preserveState: true,
                preserveScroll: true,
            });
        },

    }
}

</script>
