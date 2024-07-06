<template>
    <form v-if="editStudentApplicationForm != null && programs.length > 0" class="card-body" @submit.prevent="submitForm">
        <div class="modal-body">
            <div class="row g-3">

                <div class="col-md-6">
                    <Label for="inputSd" class="form-label" value="Program Name"/>
                    <Select class="form-select" id="inputSd" v-model="editStudentApplicationForm.program_guid">
                        <option></option>
                        <template  v-for="p in programs">
                            <option v-if="p.active_status === true" :value="p.guid">{{ p.program_name }}</option>
                        </template>
                    </Select>
                </div>
                <div class="col-md-3">
                    <Label for="inputAgreeConfirmed" class="form-label" value="Consent Confirmed?" />
                    <span v-if="editStudentApplicationForm.agreement_confirmed == true" class="badge rounded-pill text-bg-success">True</span>
                    <span v-else class="badge rounded-pill text-bg-danger">False</span>
                </div>
                <div class="col-md-3">
                    <Label for="inputRegisterConfirmed" class="form-label" value="Registration Confirmed?" />
                    <span v-if="editStudentApplicationForm.registration_confirmed == true" class="badge rounded-pill text-bg-success">True</span>
                    <span v-else class="badge rounded-pill text-bg-danger">False</span>
                </div>

                <div class="col-md-4">
                    <Label for="inputFirstName" class="form-label" value="First Name" />
                    <Input type="text" class="form-control" id="inputFirstName" :value="editStudentApplicationForm.student.first_name" readonly="readonly" disabled/>
                </div>
                <div class="col-md-4">
                    <Label for="inputLastName" class="form-label" value="Last Name" />
                    <Input type="text" class="form-control" id="inputLastName" :value="editStudentApplicationForm.student.last_name" readonly="readonly" disabled/>
                </div>
                <div class="col-md-4">
                    <Label for="inputEmail" class="form-label" value="Email" />
                    <Input type="email" class="form-control" id="inputEmail" :value="editStudentApplicationForm.student.email" readonly="readonly" disabled/>
                </div>

                <div class="col-md-3">
                    <Label for="inputSin" class="form-label" value="SIN" />
                    <Input type="number" min="100000000" max="999999999" class="form-control" id="inputSin" :value="editStudentApplicationForm.student.sin" readonly="readonly" disabled/>
                </div>
                <div class="col-md-3">
                    <Label for="inputDob" class="form-label" value="Birth Date" />
                    <Input type="text" class="form-control" id="inputDob" :value="editStudentApplicationForm.student.dob" readonly="readonly" disabled/>
                </div>
                <div class="col-md-3">
                    <Label for="inputCity" class="form-label" value="City" />
                    <Input type="text" class="form-control" id="inputCity" :value="editStudentApplicationForm.student.city" readonly="readonly" disabled/>
                </div>
                <div class="col-md-3">
                    <Label for="inputPostalCode" class="form-label" value="Postal Code" />
                    <Input type="text" class="form-control" id="inputPostalCode" :value="editStudentApplicationForm.student.zip_code" readonly="readonly" disabled/>
                </div>

                <hr/>

                <div class="col-md-4">
                    <Label for="input52Week" class="form-label" value="52 Week Affirm."/>
                    <Select class="form-select" id="input52Week" v-model="editStudentApplicationForm.fifty_two_week_affirmation">
                        <option value="true">Yes</option>
                        <option value="false">No</option>
                    </Select>
                </div>
                <div class="col-md-4">
                    <Label for="inputExpiryDate" class="form-label" value="Expiry Date" />
                    <Input type="date" min="2020-01-01" max="2034-12-31" placeholder="YYYY-MM-DD" class="form-control" id="inputExpiryDate" v-model="editStudentApplicationForm.expiry_date" />
                </div>
                <div class="col-md-4">
                    <Label for="inputStatus" class="form-label" value="Application Status"/>
                    <Select class="form-select" id="inputStatus" v-model="editStudentApplicationForm.claim_status">
                        <option v-for="status in $attrs.utils['Claim Status']" :value="status.field_name">{{ status.field_name }}</option>
                    </Select>
                </div>


                <div v-if="claim.claim_status === 'Submitted'" class="col-md-12">
                    <Label for="inputEstimatedHoldAmount" class="form-label" value="Estimated Hold Amount" />
                    <Input type="number" step=".01" max="3500" class="form-control" id="inputEstimatedHoldAmount" v-model="editStudentApplicationForm.estimated_hold_amount" />
                </div>
                <template v-if="claim.claim_status === 'Hold'">
                    <div class="col-md-3">
                        <Label for="inputEstimatedHoldAmount" class="form-label" value="Estimated Hold Amount" />
                        <Input type="number" step=".01" max="3500" class="form-control" id="inputEstimatedHoldAmount" v-model="editStudentApplicationForm.estimated_hold_amount" />
                    </div>
                    <div class="col-md-3">
                        <Label for="inputRegistrationFee" class="form-label" value="Registration Fee" />
                        <Input type="number" step=".01" class="form-control" id="inputRegistrationFee" v-model="editStudentApplicationForm.registration_fee" />
                    </div>
                    <div class="col-md-3">
                        <Label for="inputMaterialsFee" class="form-label" value="Materials Fee" />
                        <Input type="number" step=".01" class="form-control" id="inputMaterialsFee" v-model="editStudentApplicationForm.materials_fee" />
                    </div>
                    <div class="col-md-3">
                        <Label for="inputProgramFee" class="form-label" value="Program Fee" />
                        <Input type="number" step=".01" class="form-control" id="inputProgramFee" v-model="editStudentApplicationForm.program_fee" />
                    </div>

                </template>

                <div class="col-md-4">
                    <Label for="inputStableDate" class="form-label" value="Stable Enrol. Date" />
                    <Input type="date" min="2020-01-01" max="2034-12-31" placeholder="YYYY-MM-DD" class="form-control" id="inputStableDate" v-model="editStudentApplicationForm.stable_enrolment_date" />
                </div>
                <div class="col-md-4">
                    <Label for="inputPsiDate" class="form-label" value="PSI Application Request Date" />
                    {{ editStudentApplicationForm.psi_claim_request_date == null ? '-' : editStudentApplicationForm.psi_claim_request_date }}
                </div>
                <div class="col-md-4">
                    <Label for="inputCompleteDate" class="form-label" value="Reporting Complete Date" />
                    {{ editStudentApplicationForm.reporting_completed_date == null ? '-' : editStudentApplicationForm.reporting_completed_date }}
                </div>


                <div v-if="editStudentApplicationForm.process_feedback != null" class="row">
                    <div class="col-12">
                        <div class="alert alert-warning mt-3 mb-3">
                            {{ editStudentApplicationForm.process_feedback }}
                        </div>
                    </div>
                </div>

                <div v-if="editStudentApplicationForm.errors != undefined" class="row">
                    <div class="col-12">
                        <div v-if="editStudentApplicationForm.hasErrors == true" class="alert alert-danger mt-3">
                            <ul>
                                <li v-for="err in editStudentApplicationForm.errors">{{ err }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-sm btn-success" :disabled="editStudentApplicationForm.processing">
                Update Student Application
            </button>
        </div>
        <FormSubmitAlert :form-state="editStudentApplicationForm.formState" :success-msg="editStudentApplicationForm.formSuccessMsg"
                         :fail-msg="editStudentApplicationForm.formFailMsg"></FormSubmitAlert>
    </form>

</template>
<script>
import Select from '@/Components/Select.vue';
import Input from '@/Components/Input.vue';
import Label from '@/Components/Label.vue';
import FormSubmitAlert from '@/Components/FormSubmitAlert.vue';
import {Link, useForm} from '@inertiajs/vue3';

export default {
    name: 'StudentApplicationEdit',
    components: {
        Input, Label, Select, Link, useForm, FormSubmitAlert
    },
    props: {
        programYears: Object,
        results: Object,
        claim: Object,
        page: String,
        subPage: String
    },
    data() {
        return {
            editStudentApplicationForm: null,
            editStudentApplicationFormData: {
                formState: true,
                formSuccessMsg: 'Form was submitted successfully.',
                formFailMsg: 'There was an error submitting this form.',
                institution_guid: "",
                total_amount: "",
                program_year_guid: "",
                status: "",
            },
            programs: [],
            // editApplication: ''
        }
    },
    methods: {

        submitForm: function () {
            let vm = this;
            this.editStudentApplicationForm.formState = null;
            this.editStudentApplicationForm.put('/applications', {
                onSuccess: (response) => {
                    this.editStudentApplicationForm.formState = true;
                    setTimeout(function () {
                    $("#editApplicationModal").modal('hide')
                        .on('hidden.bs.modal', function () {
                            vm.editStudentApplicationForm.reset();
                            vm.$emit('close');
                        });
                    }, 2500);
                },
                onError: () => {
                    this.editStudentApplicationForm.formState = false;
                },
                preserveState: true
            });
        },
        fetchData: function () {
            let vm = this;
            let data = {
                institution_guid: this.results.guid,
            }
            axios.get('/student/api/fetch/applications/' + this.claim.guid)
                .then(function (response) {
                    vm.programs = response.data.programs;
                    vm.editStudentApplicationFormData = response.data.claim;
                    vm.editStudentApplicationFormData.formState = null;
                    vm.editStudentApplicationFormData.page = vm.page;
                    vm.editStudentApplicationFormData.subpage = vm.subPage;
                    vm.editStudentApplicationForm = useForm(vm.editStudentApplicationFormData);


                })
                .catch(function (error) {
                    // handle error
                    console.log(error);
                });
        },
    },
    mounted() {
        this.fetchData();
    },
    watch: {
        claim: function (newVal, oldVal) {
            if (newVal != null) {
                this.fetchData();
            }
        },
    },
}
</script>
