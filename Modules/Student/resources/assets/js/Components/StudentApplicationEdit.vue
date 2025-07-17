<template>
    <form v-if="newApplicationForm != null" class="card-body">
        <div class="modal-body">
            <div class="row g-3">

                <div v-if="programs != null && programs.length > 0" class="row col-12 g-3 mt-0">
                    <div class="col-12">
                        <Label for="inputInstGuid" class="form-label" value="Institution Name"/>
                        <Select @change="fetchPrograms($event)" v-if="institutions != null && institutions.length > 0" class="form-select" id="inputInstGuid" v-model="newApplicationForm.institution_guid">
                            <template  v-for="p in institutions">
                                <option v-if="p.active_status === true" :value="p.guid">{{ p.name }}</option>
                            </template>
                        </Select>
                    </div>
                    <div class="col-12">
                        <Label for="inputProgramGuid" class="form-label" value="Program Name"/>
                        <Select class="form-select" id="inputProgramGuid" v-model="newApplicationForm.program_guid">
                            <option></option>
                            <template  v-for="p in programs">
                                <option v-if="p.active_status === true" :value="p.guid">{{ p.program_name }}</option>
                            </template>
                        </Select>
                    </div>
                    
                    <!-- Demographics Section -->
                    <StudentDemographics 
                        v-if="$attrs.demographics"
                        :demographics="$attrs.demographics"
                        :existing-demographics="$attrs.existingDemographics || {}"
                        v-model="newApplicationForm.demographics"
                    />
                    
                    <div v-if="newApplicationForm.program_guid != ''" class="col-12">
                        <div class="form-check">
                            <label for="flexCheckChecked1" class="form-check-label">
                                {{ $attrs.utils['Student Agreement'][0].field_name }}
                            </label>
                            <input type="checkbox" class="form-check-input" id="flexCheckChecked1"
                                   v-model="newApplicationForm.agreement_confirmed" :checked="newApplicationForm.agreement_confirmed" />
                        </div>
                        <div class="form-check">
                            <label for="flexCheckChecked2" class="form-check-label">
                                {{ $attrs.utils['Student Registration Confirmation'][0].field_name }}
                            </label>
                            <input type="checkbox" class="form-check-input" id="flexCheckChecked2"
                                   v-model="newApplicationForm.registration_confirmed" :checked="newApplicationForm.registration_confirmed" />
                        </div>
                    </div>

                    <div v-if="newApplicationForm.processing" class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <div v-if="newApplicationForm.errors != undefined" class="row">
                        <div class="col-12">
                            <div v-if="newApplicationForm.hasErrors == true" class="alert alert-danger mt-3">
                                <ul>
                                    <li v-for="err in newApplicationForm.errors">{{ err }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="row g-3">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="modal-footer">
            <button @click="save" type="button" class="btn me-2 btn-primary" :disabled="newApplicationForm.processing ||
            newApplicationForm.institution_guid == '' || newApplicationForm.program_guid == ''">Save Draft</button>
            <button @click="submitForm" type="button" class="btn btn-success" :disabled="newApplicationForm.processing ||
            newApplicationForm.institution_guid == '' || newApplicationForm.program_guid == ''">
                Submit Application
            </button>
        </div>
        <FormSubmitAlert :form-state="newApplicationForm.formState" :success-msg="newApplicationForm.formSuccessMsg"
                         :fail-msg="newApplicationForm.formFailMsg"></FormSubmitAlert>
    </form>

</template>
<script>
import Select from '@/Components/Select.vue';
import Input from '@/Components/Input.vue';
import Label from '@/Components/Label.vue';
import FormSubmitAlert from '@/Components/FormSubmitAlert.vue';
import StudentDemographics from './StudentDemographics.vue';
import {Link, useForm} from '@inertiajs/vue3';

export default {
    name: 'StudentApplicationEdit',
    components: {
        Input, Label, Select, Link, useForm, FormSubmitAlert, StudentDemographics
    },
    props: {
        results: Object,
        application: Object,
        institutions: Object
    },
    data() {
        return {

            programs: null,
            newApplicationForm: null,
            newApplicationFormData: {
                formState: true,
                formSuccessMsg: 'Form was submitted successfully.',
                formFailMsg: 'There was an error submitting this form.',
                institution_guid: "",
                program_guid: "",
                agreement_confirmed: false,
                registration_confirmed: false,
                claim_status: "Submitted"
            },
        }
    },
    methods: {
        save: function () {
            this.newApplicationForm.claim_status = 'Draft';
            this.submitForm();
        },

        submitForm: function () {
            if(this.newApplicationForm.claim_status === 'Submitted'){
                let check = confirm('You are about to submit this application. You are not going to be able to edit this application anymore. Proceed?');
                if(!check){
                    return false;
                }
            }
            this.newApplicationForm.formState = null;
            this.newApplicationForm.put('/applications', {
                onSuccess: (response) => {
                    $("#editApplicationModal").modal('hide');
                    this.newApplicationForm.reset(this.newApplicationFormData);

                    this.$inertia.visit('/applications');
                    // console.log(response.props.institution)
                },
                onError: () => {
                    this.newApplicationForm.formState = false;
                },
                preserveState: true
            });
        },
        fetchPrograms: function (e) {
            let guid = e;
            if(e.target != undefined){
                guid = e.target.value;
                this.newApplicationForm.program_guid = '';
            }
            let vm = this;
            this.programs = null;
            this.newApplicationForm.processing = true;
            axios.get('/student/api/fetch/institutions/' + guid)
                .then(function (response) {
                    vm.programs = response.data.institution.active_programs;
                    vm.newApplicationForm.processing = false;

                })
                .catch(function (error) {
                    // handle error
                    vm.newApplicationForm.processing = false;
                    console.log(error);
                });
        },
    },

    mounted() {
        this.newApplicationForm = useForm(this.application);
        this.newApplicationForm.claim_status = 'Submitted';
        
        // Initialize demographics if not present
        if (!this.newApplicationForm.demographics) {
            this.newApplicationForm.demographics = {};
        }
        
        this.fetchPrograms(this.application.institution_guid);
        // this.newApplicationForm.institution_guid = this.results.guid;
    }
}
</script>
