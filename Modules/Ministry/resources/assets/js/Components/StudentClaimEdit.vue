<template>
    <form v-if="editStudentClaimForm != null && programs.length > 0" class="card-body" @submit.prevent="submitForm">
        <div class="modal-body">
            <div class="row g-3">

                <div class="col-md-6">
                    <Label for="inputSd" class="form-label" value="Program Name"/>
                    <Select class="form-select" id="inputSd" v-model="editStudentClaimForm.program_guid">
                        <option></option>
                        <template  v-for="p in programs">
                            <option v-if="p.active_status === true" :value="p.guid">{{ p.program_name }}</option>
                        </template>
                    </Select>
                </div>
                <div class="col-md-3">
                    <Label for="inputAgreeConfirmed" class="form-label" value="Consent Confirmed?" />
                    <span v-if="editStudentClaimForm.agreement_confirmed == true" class="badge rounded-pill text-bg-success">True</span>
                    <span v-else class="badge rounded-pill text-bg-danger">False</span>
                </div>
                <div class="col-md-3">
                    <Label for="inputRegisterConfirmed" class="form-label" value="Registration Confirmed?" />
                    <span v-if="editStudentClaimForm.registration_confirmed == true" class="badge rounded-pill text-bg-success">True</span>
                    <span v-else class="badge rounded-pill text-bg-danger">False</span>
                </div>

                <div class="col-md-4">
                    <Label for="inputFirstName" class="form-label" value="First Name" />
                    <Input type="text" class="form-control" id="inputFirstName" :value="editStudentClaimForm.student.first_name" readonly="readonly" disabled/>
                </div>
                <div class="col-md-4">
                    <Label for="inputLastName" class="form-label" value="Last Name" />
                    <Input type="text" class="form-control" id="inputLastName" :value="editStudentClaimForm.student.last_name" readonly="readonly" disabled/>
                </div>
                <div class="col-md-4">
                    <Label for="inputEmail" class="form-label" value="Email" />
                    <Input type="email" class="form-control" id="inputEmail" :value="editStudentClaimForm.student.email" readonly="readonly" disabled/>
                </div>

                <div class="col-md-3">
                    <Label for="inputSin" class="form-label" value="SIN" />
                    <Input type="number" min="100000000" max="999999999" class="form-control" id="inputSin" :value="editStudentClaimForm.student.sin" readonly="readonly" disabled/>
                </div>
                <div class="col-md-3">
                    <Label for="inputDob" class="form-label" value="Birth Date" />
                    <Input type="text" class="form-control" id="inputDob" :value="editStudentClaimForm.student.dob" readonly="readonly" disabled/>
                </div>
                <div class="col-md-3">
                    <Label for="inputCity" class="form-label" value="City" />
                    <Input type="text" class="form-control" id="inputCity" :value="editStudentClaimForm.student.city" readonly="readonly" disabled/>
                </div>
                <div class="col-md-3">
                    <Label for="inputPostalCode" class="form-label" value="Postal Code" />
                    <Input type="text" class="form-control" id="inputPostalCode" :value="editStudentClaimForm.student.zip_code" readonly="readonly" disabled/>
                </div>

                <hr/>

                <div class="col-md-4">
                    <Label for="input52Week" class="form-label" value="52 Week Affirm."/>
                    <Select class="form-select" id="input52Week" v-model="editStudentClaimForm.fifty_two_week_affirmation">
                        <option value="true">Yes</option>
                        <option value="false">No</option>
                    </Select>
                </div>
                <div class="col-md-4">
                    <Label for="inputExpiryDate" class="form-label" value="Expiry Date" />
                    <Input type="date" min="2020-01-01" max="2034-12-31" placeholder="YYYY-MM-DD" class="form-control" id="inputExpiryDate" v-model="editStudentClaimForm.expiry_date" />
                </div>
                <div class="col-md-4">
                    <Label for="inputStatus" class="form-label" value="Claim Status"/>
                    <Select class="form-select" id="inputStatus" v-model="editStudentClaimForm.claim_status">
                        <option v-for="status in $attrs.utils['Claim Status']" :value="status.field_name">{{ status.field_name }}</option>
                    </Select>
                </div>


                <div v-if="claim.claim_status === 'Submitted'" class="col-md-12">
                    <Label for="inputEstimatedHoldAmount" class="form-label" value="Estimated Hold Amount" />
                    <Input type="number" step=".01" max="3500" class="form-control" id="inputEstimatedHoldAmount" v-model="editStudentClaimForm.estimated_hold_amount" />
                </div>
                <template v-if="claim.claim_status === 'Hold'">
                    <div class="col-md-12">
                        <Label for="inputEstimatedHoldAmount" class="form-label" value="Estimated Hold Amount" />
                        {{ editStudentClaimForm.estimated_hold_amount }}
                    </div>
                    <div class="col-md-4">
                        <Label for="inputRegistrationFee" class="form-label" value="Registration Fee" />
                        <Input type="number" step=".01" class="form-control" id="inputRegistrationFee" v-model="editStudentClaimForm.registration_fee" />
                    </div>
                    <div class="col-md-4">
                        <Label for="inputMaterialsFee" class="form-label" value="Materials Fee" />
                        <Input type="number" step=".01" class="form-control" id="inputMaterialsFee" v-model="editStudentClaimForm.materials_fee" />
                    </div>
                    <div class="col-md-4">
                        <Label for="inputProgramFee" class="form-label" value="Program Fee" />
                        <Input type="number" step=".01" class="form-control" id="inputProgramFee" v-model="editStudentClaimForm.program_fee" />
                    </div>

                </template>
                <template v-if="claim.claim_status === 'Claimed' || claim.claim_status === 'Expired' || claim.claim_status === 'Cancelled'">
                    <div class="col-md-12">
                        <Label for="inputEstimatedHoldAmount" class="form-label" value="Estimated Hold Amount" />
                        {{ editStudentClaimForm.estimated_hold_amount }}
                    </div>
                    <div class="col-md-4">
                        <Label for="inputRegistrationFee" class="form-label" value="Registration Fee" />
                        {{ editStudentClaimForm.registration_fee }}
                    </div>
                    <div class="col-md-4">
                        <Label for="inputMaterialsFee" class="form-label" value="Materials Fee" />
                        {{ editStudentClaimForm.materials_fee }}
                    </div>
                    <div class="col-md-4">
                        <Label for="inputProgramFee" class="form-label" value="Program Fee" />
                        {{ editStudentClaimForm.program_fee }}
                    </div>
                </template>

                <div class="col-md-4">
                    <Label for="inputStableDate" class="form-label" value="Stable Enrol. Date" />
                    <Input type="date" min="2020-01-01" max="2034-12-31" placeholder="YYYY-MM-DD" class="form-control" id="inputStableDate" v-model="editStudentClaimForm.stable_enrolment_date" />
                </div>
                <div class="col-md-4">
                    <Label for="inputPsiDate" class="form-label" value="PSI Claim Request Date" />
                    {{ editStudentClaimForm.psi_claim_request_date == null ? '-' : editStudentClaimForm.psi_claim_request_date }}
                </div>
                <div class="col-md-4">
                    <Label for="inputCompleteDate" class="form-label" value="Reporting Complete Date" />
                    {{ editStudentClaimForm.reporting_completed_date == null ? '-' : editStudentClaimForm.reporting_completed_date }}
                </div>


                <div v-if="editStudentClaimForm.process_feedback != null" class="row">
                    <div class="col-12">
                        <div class="alert alert-warning mt-3 mb-3">
                            {{ editStudentClaimForm.process_feedback }}
                        </div>
                    </div>
                </div>

                <div v-if="editStudentClaimForm.errors != undefined" class="row">
                    <div class="col-12">
                        <div v-if="editStudentClaimForm.hasErrors == true" class="alert alert-danger mt-3">
                            <ul>
                                <li v-for="err in editStudentClaimForm.errors">{{ err }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-sm btn-success" :disabled="editStudentClaimForm.processing">
                Update Student Claim
            </button>
        </div>
        <FormSubmitAlert :form-state="editStudentClaimForm.formState" :success-msg="editStudentClaimForm.formSuccessMsg"
                         :fail-msg="editStudentClaimForm.formFailMsg"></FormSubmitAlert>
    </form>

</template>
<script>
import Select from '@/Components/Select.vue';
import Input from '@/Components/Input.vue';
import Label from '@/Components/Label.vue';
import FormSubmitAlert from '@/Components/FormSubmitAlert.vue';
import {Link, useForm} from '@inertiajs/vue3';

export default {
    name: 'StudentClaimEdit',
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
            editStudentClaimForm: null,
            editStudentClaimFormData: {
                formState: true,
                formSuccessMsg: 'Form was submitted successfully.',
                formFailMsg: 'There was an error submitting this form.',
                institution_guid: "",
                total_amount: "",
                program_year_guid: "",
                status: "",
            },
            programs: [],
            // editClaim: ''
        }
    },
    methods: {

        submitForm: function () {
            if(this.editStudentClaimForm.claim_status === 'Hold'){
                if(!confirm("You are about to switch the status of this claim to Hold. The field Estimated Hold Amount is going to be locked. Proceed?")){
                    return false;
                }
            }
            if(this.editStudentClaimForm.claim_status === 'Claimed'){
                if(!confirm("You are about to switch the status of this claim to Claimed. " +
                    "The fields Registration, Materials, and Program Fee are going to be locked. Proceed?")){
                    return false;
                }
            }

            let vm = this;
            this.editStudentClaimForm.formState = null;
            this.editStudentClaimForm.put('/ministry/claims', {
                onSuccess: (response) => {
                    this.editStudentClaimForm.formState = true;
                    setTimeout(function () {
                    $("#editClaimModal").modal('hide')
                        .on('hidden.bs.modal', function () {
                            vm.editStudentClaimForm.reset();
                            vm.$emit('close');
                        });
                    }, 2500);
                },
                onError: () => {
                    this.editStudentClaimForm.formState = false;
                },
                preserveState: true
            });
        },
        fetchData: function () {
            let vm = this;
            let data = {
                institution_guid: this.results.guid,
            }
            axios.get('/ministry/api/fetch/claims/' + this.claim.guid)
                .then(function (response) {
                    vm.programs = response.data.programs;
                    vm.editStudentClaimFormData = response.data.claim;
                    vm.editStudentClaimFormData.formState = null;
                    vm.editStudentClaimFormData.page = vm.page;
                    vm.editStudentClaimFormData.subpage = vm.subPage;
                    vm.editStudentClaimForm = useForm(vm.editStudentClaimFormData);


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
