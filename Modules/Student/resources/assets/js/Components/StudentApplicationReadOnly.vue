<template>
    <form v-if="newApplicationForm != null" class="card-body">
        <div class="modal-body">
            <div v-if="programs != null && programs.length > 0" class="row g-3">


                <div class="row col-12 g-3 mt-0">
                    <div class="col-12">
                        <Label for="inputInstGuid" class="form-label" value="Institution Name"/>
                        <Select @change="fetchPrograms($event)" v-if="institutions != null && institutions.length > 0" class="form-select" id="inputInstGuid" v-model="newApplicationForm.institution_guid" readonly="readonly" disabled>
                            <template  v-for="p in institutions">
                                <option v-if="p.active_status === true" :value="p.guid">{{ p.name }}</option>
                            </template>
                        </Select>
                    </div>
                    <div class="col-12">
                        <Label for="inputProgramGuid" class="form-label" value="Program Name"/>
                        <Select class="form-select" id="inputProgramGuid" v-model="newApplicationForm.program_guid"  readonly="readonly" disabled>
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
                                   v-model="newApplicationForm.agreement_confirmed" :checked="newApplicationForm.agreement_confirmed"  readonly="readonly" disabled/>
                        </div>
                        <div class="form-check">
                            <label for="flexCheckChecked2" class="form-check-label">
                                {{ $attrs.utils['Student Registration Confirmation'][0].field_name }}
                            </label>
                            <input type="checkbox" class="form-check-input" id="flexCheckChecked2"
                                   v-model="newApplicationForm.registration_confirmed" :checked="newApplicationForm.registration_confirmed"  readonly="readonly" disabled/>
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
            <div v-else class="row g-3">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Close</button>

        </div>
    </form>

</template>
<script>
import Select from '@/Components/Select.vue';
import Input from '@/Components/Input.vue';
import Label from '@/Components/Label.vue';
import FormSubmitAlert from '@/Components/FormSubmitAlert.vue';
import {Link, useForm} from '@inertiajs/vue3';

export default {
    name: 'StudentApplicationReadOnly',
    components: {
        Input, Label, Select, Link, useForm, FormSubmitAlert
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
        
        this.fetchPrograms(this.application.institution_guid);
        // this.newApplicationForm.institution_guid = this.results.guid;
    }
}
</script>
