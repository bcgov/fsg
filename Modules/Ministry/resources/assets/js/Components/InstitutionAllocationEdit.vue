<template>
    <form v-if="editInstitutionAllocationForm != null" class="card-body" @submit.prevent="submitForm">
        <div class="modal-body">
            <div class="row g-3">

                <div v-if="newAllocation" class="col-12 mb-3">
                    <div class="alert alert-info">Activating this allocation will deactivate all active allocations permanently. This new allocation will be the only active allocation.</div>
                </div>
                <div class="col-md-3">
                    <Label for="inputSd" class="form-label" value="Program Year"/>
                    <Select class="form-select" id="inputSd" v-model="editInstitutionAllocationForm.program_year_guid">
                        <option></option>
                        <option v-for="f in programYears" :value="f.guid">{{ f.start_date }} - {{ f.end_date}}</option>
                    </Select>
                </div>
                <div class="col-md-3">
                    <Label for="inputStatus" class="form-label" value="Status"/>
                    <Select class="form-select" id="inputStatus" v-model="editInstitutionAllocationForm.status" @change="switchStatus">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </Select>
                </div>

                <div class="col-md-3">
                    <Label for="inputTotalAllowed" class="form-label" value="Total Allowed"/>
                    <div class="input-group mb-3">
                        <Input type="text" class="form-control" id="inputTotalAllowed"
                               v-model="editInstitutionAllocationForm.total_amount"/>
                    </div>
                </div>

                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <Label class="form-label mb-0" value="Funding Types"/>
                        <button type="button" class="btn btn-sm btn-outline-success" @click="addFundingType">Add Funding Type</button>
                    </div>

                    <p v-if="editInstitutionAllocationForm.funding_types.length === 0" class="text-muted small mb-0">
                        No funding types added. Click "Add Funding Type" to add one.
                    </p>

                    <div v-for="(row, index) in editInstitutionAllocationForm.funding_types" :key="index" class="row g-2 align-items-end mb-2">
                        <div class="col-md-6">
                            <Label :for="'inputFundingType' + index" class="form-label" value="Funding Type"/>
                            <Select class="form-select" :id="'inputFundingType' + index" v-model="row.funding_type">
                                <option value=""></option>
                                <option v-for="ft in fundingTypeOptions" :key="ft.field_name" :value="ft.field_name">{{ ft.field_name }}</option>
                            </Select>
                        </div>
                        <div class="col-md-5">
                            <Label :for="'inputFundingAmount' + index" class="form-label" value="Amount"/>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <Input type="number" class="form-control" :id="'inputFundingAmount' + index" min="0" v-model.number="row.amount"/>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-sm btn-outline-danger" @click="removeFundingType(index)" aria-label="Remove">&times;</button>
                        </div>
                    </div>
                </div>


                <div v-if="editInstitutionAllocationForm.errors != undefined" class="row">
                    <div class="col-12">
                        <div v-if="editInstitutionAllocationForm.hasErrors == true" class="alert alert-danger mt-3">
                            <ul>
                                <li v-for="err in editInstitutionAllocationForm.errors">{{ err }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-sm btn-success" :disabled="editInstitutionAllocationForm.processing">
                Update Institution Allocation
            </button>
        </div>
        <FormSubmitAlert :form-state="editInstitutionAllocationForm.formState" :success-msg="editInstitutionAllocationForm.formSuccessMsg"
                         :fail-msg="editInstitutionAllocationForm.formFailMsg"></FormSubmitAlert>
    </form>

</template>
<script>
import Select from '@/Components/Select.vue';
import Input from '@/Components/Input.vue';
import Label from '@/Components/Label.vue';
import FormSubmitAlert from '@/Components/FormSubmitAlert.vue';
import {Link, useForm} from '@inertiajs/vue3';

export default {
    name: 'InstitutionAllocationEdit',
    components: {
        Input, Label, Select, Link, useForm, FormSubmitAlert
    },
    props: {
        programYears: Object,
        results: Object,
        allocation: Object,
    },
    data() {
        return {
            editInstitutionAllocationForm: null,
            editInstitutionAllocationFormData: {
                formState: true,
                formSuccessMsg: 'Form was submitted successfully.',
                formFailMsg: 'There was an error submitting this form.',
                institution_guid: "",
                total_amount: "",
                program_year_guid: "",
                status: "",
                funding_types: [],
            },
            selectedFedCap: '',
            allowProgramCap: false,
            newAllocation: false
        }
    },
    computed: {
        fundingTypeOptions() {
            const utils = this.$attrs.utils || {};
            return utils['Funding Type'] || [];
        }
    },
    methods: {
        addFundingType: function () {
            this.editInstitutionAllocationForm.funding_types.push({ funding_type: "", amount: "" });
        },
        removeFundingType: function (index) {
            this.editInstitutionAllocationForm.funding_types.splice(index, 1);
        },
        switchStatus: function () {
            if(this.editInstitutionAllocationForm.status === 'active'){
                this.newAllocation = true;
            }
        },
        submitForm: function () {
            if(this.editInstitutionAllocationForm.status === 'active'){
                if(!confirm("By switching this allocation to Active all others are going to be switched to Inactive. Proceed?")){
                    return false;
                }
            }

            let vm = this;
            this.editInstitutionAllocationForm.formState = null;
            this.editInstitutionAllocationForm.put('/ministry/allocations', {
                onSuccess: (response) => {
                    $("#editInstAllocationModal").modal('hide')
                        .on('hidden.bs.modal', function () {
                            vm.editInstitutionAllocationForm.reset(vm.editInstitutionAllocationFormData);
                            vm.$inertia.visit('/ministry/institutions/' + vm.results.id + '/allocations');
                            vm.$emit('close');
                        });
                },
                onError: () => {
                    this.editInstitutionAllocationForm.formState = false;
                },
                preserveState: true
            });
        }
    },

    mounted() {
        this.editInstitutionAllocationFormData = this.allocation;
        this.editInstitutionAllocationFormData.total_amount = this.editInstitutionAllocationFormData.total_amount_formatted;
        this.editInstitutionAllocationFormData.funding_types = (this.allocation.funding_types || []).map(ft => ({
            funding_type: ft.funding_type,
            amount: ft.amount,
        }));
        this.editInstitutionAllocationForm = useForm(this.editInstitutionAllocationFormData);
    }
}
</script>
