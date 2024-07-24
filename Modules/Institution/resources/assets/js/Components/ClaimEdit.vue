<template>
    <div>
        <div v-if="editStudentClaimForm == null" class="text-center m-5">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <form v-if="editStudentClaimForm != null && programs.length > 0" class="card-body">
            <div class="modal-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <Label for="inputSd" class="form-label" value="Program Name"/>
                        <p v-if="editStudentClaimForm.program !== null">{{ editStudentClaimForm.program.program_name }}</p>
                        <p v-else> - </p>
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



                    <template v-if="claim.claim_status === 'Submitted'">
                        <div class="col-md-4">
                            <Label for="inputExpiryDate" class="form-label" value="Hold Amount Expiry Date" />
                            <Input type="date" min="2024-01-01" max="2034-12-31" placeholder="YYYY-MM-DD" class="form-control" id="inputExpiryDate" v-model="editStudentClaimForm.expiry_date" />
                        </div>
                        <div class="col-md-4">
                            <Label for="inputEstimatedHoldAmount" class="form-label" value="Est. Hold Amount (Max.)" />
                            <Input type="number" step=".01" max="3500" class="form-control" id="inputEstimatedHoldAmount" v-model="editStudentClaimForm.estimated_hold_amount" />
                        </div>
                        <div class="col-md-4">
                            <Label for="inputExpStableEnrolDate" class="form-label" value="Expected Stable Enrol. Date" />
                            <Input type="date" min="2024-01-01" max="2034-12-31" placeholder="YYYY-MM-DD" class="form-control" id="inputExpStableEnrolDate" v-model="editStudentClaimForm.expected_stable_enrolment_date" />
                        </div>

                    </template>
                    <template v-if="claim.claim_status === 'Hold'">
                        <div class="col-md-4">
                            <Label for="inputExpiryDate" class="form-label" value="Hold Amount Expiry Date" />
                            {{ editStudentClaimForm.expiry_date }}
                        </div>
                        <div class="col-md-4">
                            <Label for="inputEstimatedHoldAmount" class="form-label" value="Estimated Hold Amount" />
                            ${{ editStudentClaimForm.estimated_hold_amount }}
                        </div>
                        <div class="col-md-4">
                            <Label for="inputExpStableEnrolDate" class="form-label" value="Expected Stable Enrol. Date" />
                            <Input type="date" min="2024-01-01" max="2034-12-31" placeholder="YYYY-MM-DD" class="form-control" id="inputExpStableEnrolDate" v-model="editStudentClaimForm.expected_stable_enrolment_date" />
                        </div>

                        <div class="col-md-4">
                            <Label for="inputProgramFee" class="form-label" value="Program/Tuition Fee" />
                            <Input type="number" step=".01" class="form-control" id="inputProgramFee" v-model="editStudentClaimForm.program_fee" />
                        </div>
                        <div class="col-md-4">
                            <Label for="inputRegistrationFee" class="form-label" value="Registration Fee" />
                            <Input type="number" step=".01" class="form-control" id="inputRegistrationFee" v-model="editStudentClaimForm.registration_fee" />
                        </div>
                        <div class="col-md-4">
                            <Label for="inputMaterialsFee" class="form-label" value="Materials Fee" />
                            <Input type="number" step=".01" class="form-control" id="inputMaterialsFee" v-model="editStudentClaimForm.materials_fee" />
                        </div>

                    </template>
                    <template v-if="claim.claim_status === 'Claimed' || claim.claim_status === 'Expired' || claim.claim_status === 'Cancelled'">
                        <div class="col-md-4">
                            <Label for="inputExpiryDate" class="form-label" value="Hold Amount Expiry Date" />
                            {{ editStudentClaimForm.expiry_date }}
                        </div>
                        <div class="col-md-4">
                            <Label for="inputEstimatedHoldAmount" class="form-label" value="Estimated Hold Amount" />
                            ${{ editStudentClaimForm.estimated_hold_amount }}
                        </div>
                        <div class="col-md-4">
                            <Label for="inputExpStableEnrolDate" class="form-label" value="Expected Stable Enrol. Date" />
                            {{ editStudentClaimForm.expected_stable_enrolment_date }}
                        </div>

                        <div class="col-md-4">
                            <Label for="inputProgramFee" class="form-label" value="Tuition/Program Fee" />
                            ${{ editStudentClaimForm.program_fee }}
                        </div>
                        <div class="col-md-4">
                            <Label for="inputRegistrationFee" class="form-label" value="Registration Fee" />
                            ${{ editStudentClaimForm.registration_fee }}
                        </div>
                        <div class="col-md-4">
                            <Label for="inputMaterialsFee" class="form-label" value="Materials Fee" />
                            ${{ editStudentClaimForm.materials_fee }}
                        </div>
                    </template>

                    <div class="col-md-4">
                        <Label for="inputExpCompletionDate" class="form-label" value="Expected Completion Date" />
                        <Input type="date" min="2024-01-01" max="2034-12-31" placeholder="YYYY-MM-DD" class="form-control" id="inputExpCompletionDate" v-model="editStudentClaimForm.expected_completion_date" />
                    </div>

                    <template v-if="claim.claim_status === 'Claimed'">
                        <div class="col-md-4">
                            <Label for="inputOutcomeDate" class="form-label" value="Outcome Effective Date" />
                            <Input type="date" min="2024-01-01" max="2034-12-31" placeholder="YYYY-MM-DD" class="form-control" id="inputOutcomeDate" v-model="editStudentClaimForm.outcome_effective_date" />
                        </div>
                        <div class="col-md-4">
                            <Label for="inputOutcomeStatus" class="form-label" value="Outcome Status"/>
                            <Select class="form-select" id="inputOutcomeStatus" v-model="editStudentClaimForm.outcome_status">
                                <option v-for="status in $attrs.utils['Outcome Status']" :value="status.field_name">{{ status.field_name }}</option>
                            </Select>
                        </div>
                    </template>
                    <template v-else>
                        <div class="col-md-4">
                            <Label for="inputOutcomeDate" class="form-label" value="Outcome Effective Date" />
                            -
                        </div>
                        <div class="col-md-4">
                            <Label for="inputOutcomeStatus" class="form-label" value="Outcome Status"/>
                            -
                        </div>
                    </template>

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
                <button @click="submitForm('Cancelled')" v-if="claim.claim_status === 'Submitted' || claim.claim_status === 'Hold'" type="button" class="btn btn-sm btn-danger" :disabled="editStudentClaimForm.processing">
                    Cancel Request
                </button>
                <button @click="submitForm('Hold')" v-if="claim.claim_status === 'Submitted'" type="button" class="ms-3 btn btn-sm btn-success" :disabled="editStudentClaimForm.processing">
                    Put on Hold
                </button>
                <button @click="submitForm('Claimed')" v-if="claim.claim_status === 'Hold'" type="button" class="ms-3 btn btn-sm btn-success" :disabled="editStudentClaimForm.processing">
                    Claim Request
                </button>
                <button @click="submitForm('Claimed')" v-if="claim.claim_status === 'Claimed'" type="button" class="ms-3 btn btn-sm btn-success" :disabled="editStudentClaimForm.processing">
                    Update Request
                </button>
            </div>
            <FormSubmitAlert :form-state="editStudentClaimForm.formState" :success-msg="editStudentClaimForm.formSuccessMsg"
                             :fail-msg="editStudentClaimForm.formFailMsg"></FormSubmitAlert>
        </form>
    </div>


</template>
<script>
import Select from '@/Components/Select.vue';
import Input from '@/Components/Input.vue';
import Label from '@/Components/Label.vue';
import FormSubmitAlert from '@/Components/FormSubmitAlert.vue';
import {Link, useForm} from '@inertiajs/vue3';

export default {
    name: 'ClaimEdit',
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
            programs: []
        }
    },
    methods: {

        submitForm: function (status) {
            // Show confirm only if the user is switching the status from Submitted to Hold
            if(this.claim.claim_status === 'Submitted' && status === 'Hold'){
                if(!confirm("You are about to switch the status of this claim to Hold. The field Estimated Hold Amount is going to be locked. Proceed?")){
                    return false;
                }
                this.editStudentClaimForm.claim_status = 'Hold';
            }

            // Show confirm only if the user is switching the status from Hold to Claimed
            if(this.claim.claim_status === 'Hold' && status === 'Claimed'){
                if(!confirm("You are about to switch the status of this claim to Claimed. " +
                    "The fields Registration, Materials, and Program Fee are going to be locked. Proceed?")){
                    return false;
                }
                this.editStudentClaimForm.claim_status = 'Claimed';
            }

            if(status === 'Cancelled'){
                if(!confirm("You are about to Cancel this claim. This action is permanent. Proceed?")){
                    return false;
                }
                this.editStudentClaimForm.claim_status = 'Cancelled';
            }


            let vm = this;
            this.editStudentClaimForm.formState = null;
            this.editStudentClaimForm.put('/institution/claims', {
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
            axios.get('/institution/api/fetch/claims/' + this.claim.guid)
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
