<template>
    <form v-if="editStudentClaimForm != null && programs.length > 0" class="card-body" @submit.prevent="submitForm">
        <div class="modal-body">
            <div class="row g-3">
<div class="col-12">PY: {{ editStudentClaimForm.allocation.py.start_date }} to {{ editStudentClaimForm.allocation.py.end_date }}</div>
                <div class="col-md-6">
                    <label for="inputSd" class="block font-medium text-sm text-gray-700 form-label">{{ getInactiveProgramName() }}</label>
                    <Select class="form-select" id="inputSd" v-model="editStudentClaimForm.program_guid">
                        <template  v-for="p in programs">
                            <option :disabled="p.active_status === false" :value="p.guid">{{ p.program_name }}</option>
                        </template>
                    </Select>
                </div>
                <div class="col-md-3">
                    <Label for="inputAgreeConfirmed" class="form-label" value="Consent Confirmed?" />
                    <Select class="form-select" id="inputAgreeConfirmed" v-model="editStudentClaimForm.agreement_confirmed">
                        <option value="true">Yes</option>
                        <option value="false">No</option>
                    </Select>
                </div>
                <div class="col-md-3">
                    <Label for="inputRegisterConfirmed" class="form-label" value="Registration Confirmed?" />
                    <Select class="form-select" id="inputRegisterConfirmed" v-model="editStudentClaimForm.registration_confirmed">
                        <option value="true">Yes</option>
                        <option value="false">No</option>
                    </Select>
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

                <div class="col-md-12">
                    <Label for="inputStatus" class="form-label" value="Claim Status"/>
                    <Select class="form-select" id="inputStatus" v-model="editStudentClaimForm.claim_status">
                        <option v-for="status in $attrs.utils['Claim Status']" :value="status.field_name">{{ status.field_name }}</option>
                    </Select>
                </div>

                <hr/>

                <template v-if="claim.claim_status === 'Submitted'">
                    <div class="col-md-3">
                        <Label for="inputExpiryDate" class="form-label" value="Hold Amount Expiry Date" />
                        <Input type="date" min="2024-01-01" max="2034-12-31" placeholder="YYYY-MM-DD" class="form-control" id="inputExpiryDate" v-model="editStudentClaimForm.expiry_date" />
                    </div>
                    <div class="col-md-3">
                        <Label for="inputEstimatedHoldAmount" class="form-label" value="Est. Hold Amount (Max.)" />
                        <Input type="number" step=".01" max="3500" class="form-control" id="inputEstimatedHoldAmount" v-model="editStudentClaimForm.estimated_hold_amount" />
                    </div>
                    <div class="col-md-3">
                        <Label for="inputExpStableEnrolDate" class="form-label" value="Expected Stable Enrol. Date" />
                        <Input type="date" min="2024-01-01" max="2034-12-31" placeholder="YYYY-MM-DD" class="form-control" id="inputExpStableEnrolDate" v-model="editStudentClaimForm.expected_stable_enrolment_date" />
                    </div>
                    <div class="col-md-3">
                        <Label for="inputExpCompletionDate" class="form-label" value="Expected Completion Date" />
                        <Input type="date" min="2024-01-01" max="2034-12-31" placeholder="YYYY-MM-DD" class="form-control" id="inputExpCompletionDate" v-model="editStudentClaimForm.expected_completion_date" />
                    </div>

                </template>
                <template v-if="claim.claim_status === 'Hold'">
                    <div class="col-md-3">
                        <Label for="inputExpiryDate" class="form-label" value="Hold Amount Expiry Date" />
                        {{ editStudentClaimForm.expiry_date }}
                    </div>

                    <div class="col-md-3">
                        <Label for="inputEstimatedHoldAmount" class="form-label" value="Estimated Hold Amount" />
                        <Input type="number" step=".01" max="3500" class="form-control" id="inputEstimatedHoldAmount" v-model="editStudentClaimForm.estimated_hold_amount" />
<!--                        -->
<!--                        ${{ editStudentClaimForm.estimated_hold_amount }}-->
                    </div>
                    <div class="col-md-3">
                        <Label for="inputExpStableEnrolDate" class="form-label" value="Expected Stable Enrol. Date" />
                        <Input type="date" min="2024-01-01" max="2034-12-31" placeholder="YYYY-MM-DD" class="form-control" id="inputExpStableEnrolDate" v-model="editStudentClaimForm.expected_stable_enrolment_date" />
                    </div>
                    <div class="col-md-3">
                        <Label for="inputExpCompletionDate" class="form-label" value="Expected Completion Date" />
                        <Input type="date" min="2024-01-01" max="2034-12-31" placeholder="YYYY-MM-DD" class="form-control" id="inputExpCompletionDate" v-model="editStudentClaimForm.expected_completion_date" />
                    </div>

                    <hr/>

                    <div class="col-md-3">
                        <Label for="inputProgramFee" class="form-label" value="Program/Tuition Fee" />
                        <Input type="number" step=".01" class="form-control" id="inputProgramFee" v-model="editStudentClaimForm.program_fee" />
                    </div>
                    <div class="col-md-3">
                        <Label for="inputRegistrationFee" class="form-label" value="Registration Fee" />
                        <Input type="number" step=".01" class="form-control" id="inputRegistrationFee" v-model="editStudentClaimForm.registration_fee" />
                    </div>
                    <div class="col-md-3">
                        <Label for="inputMaterialsFee" class="form-label" value="Materials Fee" />
                        <Input type="number" step=".01" class="form-control" id="inputMaterialsFee" v-model="editStudentClaimForm.materials_fee" />
                    </div>
                    <div class="col-md-3">
                        <Label for="inputCorrection" class="form-label" value="Correction" />
                        <Input type="number" step=".01" class="form-control" id="inputCorrection" v-model="editStudentClaimForm.correction_amount" />
                    </div>

                    <div class="col-md-3">
                        <Label for="inputStableDate" class="form-label" value="Admin Fee" />
                        {{ editStudentClaimForm.allocation.py_admin_fee }}%
                    </div>
                    <div class="col-md-3">
                        <Label for="inputStableDate" class="form-label" value="Total + Admin" />
                        ${{ totalPlusAdmin }}
                    </div>
                    <div class="col-md-3">
                        <Label for="inputStableDate" class="form-label" value="Actual Stable Enrol. Date" />
                        <Input type="date" min="2024-01-01" max="2034-12-31" placeholder="YYYY-MM-DD" class="form-control" id="inputStableDate" v-model="editStudentClaimForm.stable_enrolment_date" />
                    </div>
                    <div class="col-md-3">
                        <Label for="inputStableDate" class="form-label" value="Expected Stable Enrol. Date" />
                        <Input type="date" min="2024-01-01" max="2034-12-31" placeholder="YYYY-MM-DD" class="form-control" id="inputStableDate" v-model="editStudentClaimForm.expected_stable_enrolment_date" />
                    </div>

                </template>

                <template v-if="claim.claim_status === 'Claimed' || claim.claim_status === 'Expired' || claim.claim_status === 'Cancelled'">
                    <div class="col-md-3">
                        <Label for="inputExpiryDate" class="form-label" value="Hold Amount Expiry Date" />
                        {{ editStudentClaimForm.expiry_date }}
                    </div>
                    <div class="col-md-3">
                        <Label for="inputEstimatedHoldAmount" class="form-label" value="Estimated Hold Amount" />
                        ${{ editStudentClaimForm.estimated_hold_amount }}
                    </div>
                    <div class="col-md-3">
                        <Label for="inputExpStableEnrolDate" class="form-label" value="Expected Stable Enrol. Date" />
                        {{ editStudentClaimForm.expected_stable_enrolment_date }}
                    </div>
                    <div class="col-md-3">
                        <Label for="inputExpCompletionDate" class="form-label" value="Expected Completion Date" />
                        {{ editStudentClaimForm.expected_completion_date }}
                    </div>

                    <hr v-if="claim.claim_status !== 'Draft'"/>

                    <div class="col-md-3">
                        <Label for="inputProgramFee" class="form-label" value="Tuition/Program Fee" />
                        ${{ editStudentClaimForm.program_fee }}
                    </div>
                    <div class="col-md-3">
                        <Label for="inputRegistrationFee" class="form-label" value="Registration Fee" />
                        ${{ editStudentClaimForm.registration_fee }}
                    </div>
                    <div class="col-md-3">
                        <Label for="inputMaterialsFee" class="form-label" value="Materials Fee" />
                        ${{ editStudentClaimForm.materials_fee }}
                    </div>
                    <div class="col-md-3">
                        <Label for="inputCorrection" class="form-label" value="Correction" />
                        <Input type="number" step=".01" class="form-control" id="inputCorrection" v-model="editStudentClaimForm.correction_amount" />
                    </div>
                    <div v-if="editStudentClaimForm.correction_amount != null && editStudentClaimForm.correction_amount != 0" class="col-12">
                        <Label for="inputCorrectionCmnt" class="form-label" value="Correction Comment" />
                        <textarea class="form-control" id="inputCorrectionCmnt" v-model="editStudentClaimForm.correction_comment" />
                    </div>

                    <div class="col-md-4">
                        <Label for="inputStableDate" class="form-label" value="Admin Fee" />
                        {{ editStudentClaimForm.allocation.py_admin_fee }}%
                    </div>
                    <div class="col-md-4">
                        <Label for="inputStableDate" class="form-label" value="Total + Admin" />
                        ${{ totalPlusAdmin }}
                    </div>
                    <div class="col-md-4">
                        <Label for="inputStableDate" class="form-label" value="Actual Stable Enrol. Date" />
                        {{ editStudentClaimForm.expected_stable_enrolment_date }}
                    </div>

                </template>

                <hr v-if="claim.claim_status !== 'Draft'"/>

                <template v-if="claim.claim_status === 'Claimed'">
                    <div class="col-md-6">
                        <Label for="inputOutcomeDate" class="form-label" value="Outcome Effective Date" />
                        <Input type="date" min="2024-01-01" max="2034-12-31" placeholder="YYYY-MM-DD" class="form-control" id="inputOutcomeDate" v-model="editStudentClaimForm.outcome_effective_date" />
                    </div>
                    <div class="col-md-6">
                        <Label for="inputOutcomeStatus" class="form-label" value="Outcome Status"/>
                        <Select class="form-select" id="inputOutcomeStatus" v-model="editStudentClaimForm.outcome_status">
                            <option v-for="status in $attrs.utils['Outcome Status']" :value="status.field_name">{{ status.field_name }}</option>
                        </Select>
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
        <div class="modal-footer" :class="claim.claim_status === 'Claimed' && claim.outcome_effective_date != null ? 'd-flex justify-content-between' : ''">
            <button @click="clearOutcome" v-if="claim.claim_status === 'Claimed' && claim.outcome_effective_date != null" type="button" class="btn btn-sm btn-warning" :disabled="editStudentClaimForm.processing">
                Clear Claim Outcome
            </button>
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
                program_year_guid: "",
                status: "",
            },
            programs: [],
            editStudentClaimOutcomeForm: useForm({
                id: null,
            }),
        }
    },
    methods: {
        clearOutcome: function () {
            if(!confirm("You are about to clear Outcome Effective Date and Status. Proceed?")){
                return false;
            }

            console.log('clear outcomes');
            let vm = this;
            this.editStudentClaimOutcomeForm.id = this.editStudentClaimFormData.id;
            this.editStudentClaimOutcomeForm.post('/ministry/clear-claim-outcome', {
                onSuccess: () => {
                    $("#editClaimModal").modal('hide')
                        .on('hidden.bs.modal', function () {
                            vm.$emit('close');
                        });
                },
                onError: () => {
                    $("#editClaimModal").modal('hide')
                        .on('hidden.bs.modal', function () {
                            vm.$emit('close');
                        });
                },
                preserveState: true
            });
        },
        getInactiveProgramName: function () {
            let txt = 'Program Name';
            if(this.editStudentClaimForm.program.active_status === false) {
                txt += ' (' + this.editStudentClaimForm.program.program_name + ')';
            }
            return txt;
        },
        submitForm: function () {
            // Show confirm only if the user is switching the status from Submitted to Hold
            if(this.claim.claim_status === 'Submitted' && this.editStudentClaimForm.claim_status === 'Hold'){
                if(!confirm("You are about to switch the status of this claim to Hold. The field Estimated Hold Amount is going to be locked. Proceed?")){
                    return false;
                }
            }

            // Show confirm only if the user is switching the status from Hold to Claimed
            if(this.claim.claim_status === 'Hold' && this.editStudentClaimForm.claim_status === 'Claimed'){
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
                    }, 1500);
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
    computed: {
        totalPlusAdmin() {
            const programFee = parseFloat(this.editStudentClaimForm.program_fee) || 0;
            const registrationFee = parseFloat(this.editStudentClaimForm.registration_fee) || 0;
            const materialsFee = parseFloat(this.editStudentClaimForm.materials_fee) || 0;
            const adminFeePercentage = parseInt(this.editStudentClaimForm.allocation.py_admin_fee) || 0;
            const correction = parseInt(this.editStudentClaimForm.correction_amount) || 0;

            const total = programFee + registrationFee + materialsFee + correction;
            const adminFee = total * (adminFeePercentage / 100);

            return total + adminFee;
        }
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
