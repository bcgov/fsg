<template>
    <form v-if="newApplicationForm != null" class="card-body">
        <div class="modal-body">
            <div class="row g-3">


                <div class="col-md-4">
                    <Label for="inputFirstName" class="form-label" value="First Name" />
                    <Input type="text" class="form-control" id="inputFirstName" :value="results.first_name" readonly="readonly" disabled/>
                </div>
                <div class="col-md-4">
                    <Label for="inputLastName" class="form-label" value="Last Name" />
                    <Input type="text" class="form-control" id="inputLastName" :value="results.last_name" readonly="readonly" disabled/>
                </div>
                <div class="col-md-4">
                    <Label for="inputEmail" class="form-label" value="Email" />
                    <Input type="email" class="form-control" id="inputEmail" :value="results.email" readonly="readonly" disabled/>
                </div>

                <div class="col-md-3">
                    <Label for="inputSin" class="form-label" value="SIN" />
                    <Input type="number" min="100000000" max="999999999" class="form-control" id="inputSin" :value="results.sin" readonly="readonly" disabled/>
                </div>
                <div class="col-md-3">
                    <Label for="inputDob" class="form-label" value="Birth Date" />
                    <Input type="text" class="form-control" id="inputDob" :value="results.dob" readonly="readonly" disabled/>
                </div>
                <div class="col-md-3">
                    <Label for="inputCity" class="form-label" value="City" />
                    <Input type="text" class="form-control" id="inputCity" :value="results.city" readonly="readonly" disabled/>
                </div>
                <div class="col-md-3">
                    <Label for="inputPostalCode" class="form-label" value="Postal Code" />
                    <Input type="text" class="form-control" id="inputPostalCode" :value="results.zip_code" readonly="readonly" disabled/>
                </div>

                <hr/>

                <div class="col-12">
                    <Label for="inputInstGuid" class="form-label" value="Institution Name"/>
                    <Select @change="fetchPrograms($event)" v-if="institutions != null && institutions.length > 0" class="form-select" id="inputInstGuid" v-model="newApplicationForm.institution_guid">
                        <template  v-for="p in institutions">
                            <option v-if="p.active_status === true" :value="p.guid">{{ p.name }}</option>
                        </template>
                    </Select>
                </div>
                <div v-if="programs != null && programs.length > 0" class="col-12">
                    <Label for="inputProgramGuid" class="form-label" value="Program Name"/>
                    <Select class="form-select" id="inputProgramGuid" v-model="newApplicationForm.program_guid">
                        <option></option>
                        <template  v-for="p in programs">
                            <option v-if="p.active_status === true" :value="p.guid">{{ p.program_name }}</option>
                        </template>
                    </Select>
                </div>
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
import {Link, useForm} from '@inertiajs/vue3';

export default {
    name: 'StudentApplicationCreate',
    components: {
        Input, Label, Select, Link, useForm, FormSubmitAlert
    },
    props: {
        institutions: Object,
        results: Object
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

            // if(this.newInstitutionAllocationForm.fed_cap_id === '' || this.newInstitutionAllocationForm.total_attestations === ''){
            //     return false;
            // }

            this.newApplicationForm.formState = null;
            this.newApplicationForm.post('/applications', {
                onSuccess: (response) => {
                    $("#newApplicationModal").modal('hide');
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
            let vm = this;
            this.programs = null;
            this.newApplicationForm.program_guid = '';
            this.newApplicationForm.processing = true;
            axios.get('/student/api/fetch/institutions/' + e.target.value)
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
        this.newApplicationForm = useForm(this.newApplicationFormData);
        // this.fetchInstitutions();
        // this.newApplicationForm.institution_guid = this.results.guid;
    }
}
</script>
