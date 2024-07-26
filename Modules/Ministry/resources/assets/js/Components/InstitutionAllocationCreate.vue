<template>
    <form v-if="newInstitutionAllocationForm != null" class="card-body">
        <div class="modal-body">
            <div class="row g-3">

                <div class="col-12 mb-3">
                    <div class="alert alert-info">Creating a new allocation will deactivate all active allocations permanently. This new allocation will be the only active allocation.</div>
                </div>
                <div class="col-md-6">
                    <Label for="inputSd" class="form-label" value="Program Year"/>
                    <Select class="form-select" id="inputSd" v-model="newInstitutionAllocationForm.program_year_guid">
                        <option></option>
                        <option v-for="f in programYears" :value="f.guid">{{ f.start_date }} - {{ f.end_date}}</option>
                    </Select>
                </div>

                <div class="col-md-6">
                    <Label for="inputTotalAtte" class="form-label" value="Total Allowed"/>
                    <div class="input-group mb-3">
                        <Input type="number" class="form-control" id="inputTotalAllowed" aria-describedby="basic-inputTotalAtte" v-model="newInstitutionAllocationForm.total_amount"/>
                    </div>
                </div>


                <div v-if="newInstitutionAllocationForm.errors != undefined" class="row">
                    <div class="col-12">
                        <div v-if="newInstitutionAllocationForm.hasErrors == true" class="alert alert-danger mt-3">
                            <ul>
                                <li v-for="err in newInstitutionAllocationForm.errors">{{ err }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="modal-footer">
            <button @click="submitForm" type="button" class="btn btn-sm btn-success" :disabled="newInstitutionAllocationForm.processing">
                Create Institution Allocation
            </button>
        </div>
        <FormSubmitAlert :form-state="newInstitutionAllocationForm.formState" :success-msg="newInstitutionAllocationForm.formSuccessMsg"
                         :fail-msg="newInstitutionAllocationForm.formFailMsg"></FormSubmitAlert>
    </form>

</template>
<script>
import Select from '@/Components/Select.vue';
import Input from '@/Components/Input.vue';
import Label from '@/Components/Label.vue';
import FormSubmitAlert from '@/Components/FormSubmitAlert.vue';
import {Link, useForm} from '@inertiajs/vue3';

export default {
    name: 'InstitutionAllocationCreate',
    components: {
        Input, Label, Select, Link, useForm, FormSubmitAlert
    },
    props: {
        programYears: Object,
        results: Object,
        activeInstCap: Object|null
    },
    data() {
        return {
            newInstitutionAllocationForm: null,
            newInstitutionAllocationFormData: {
                formState: true,
                formSuccessMsg: 'Form was submitted successfully.',
                formFailMsg: 'There was an error submitting this form.',
                institution_guid: "",
                allocation_guid: "",
                program_year_guid: "",
                total_amount: "",
            },
        }
    },
    methods: {

        submitForm: function () {
            // let check = confirm('You are about to create a new Institution Allocation. The new amount will override the previous cap and disable the old active Institution Cap. Are you sure you want to continue?');
            // if(!check){
            //     return false;
            // }
            // if(this.newInstitutionAllocationForm.fed_cap_id === '' || this.newInstitutionAllocationForm.total_attestations === ''){
            //     return false;
            // }

            this.newInstitutionAllocationForm.formState = null;
            this.newInstitutionAllocationForm.post('/ministry/allocations', {
                onSuccess: (response) => {
                    $("#newInstAllocationModal").modal('hide');
                    this.newInstitutionAllocationForm.reset(this.newInstitutionAllocationFormData);

                    this.$inertia.visit('/ministry/institutions/' + this.results.id + '/allocations');
                    // console.log(response.props.institution)
                },
                onError: () => {
                    this.newInstitutionAllocationForm.formState = false;
                },
                preserveState: true
            });
        }
    },

    mounted() {
        this.newInstitutionAllocationForm = useForm(this.newInstitutionAllocationFormData);
        this.newInstitutionAllocationForm.institution_guid = this.results.guid;
    }
}
</script>
