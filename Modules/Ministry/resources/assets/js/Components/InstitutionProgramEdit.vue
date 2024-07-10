<template>
    <form v-if="editForm != null" class="card-body" @submit.prevent="submitForm">
        <div class="modal-body">
            <div class="row g-3">

                <div class="col-12">
                    <Label for="inputProgramName" class="form-label" value="Program Name" />
                    <Input type="text" class="form-control" id="inputProgramName" v-model="editForm.program_name" />
                </div>
                <div class="col-md-4">
                    <Label for="inputDeliveryMethod" class="form-label" value="Delivery Method" />
                    <Select class="form-select" id="inputDeliveryMethod" v-model="editForm.delivery_method">
                        <option></option>
                        <option v-for="stat in $attrs.utils['Delivery Method']" :value="stat.field_name">{{ stat.field_name }}</option>
                    </Select>
                </div>
                <div class="col-md-4">
                    <Label for="inputOnlineDeliveryType" class="form-label" value="Online Delivery Type" />
                    <Select class="form-select" id="inputOnlineDeliveryType" v-model="editForm.online_delivery_type">
                        <option></option>
                        <option v-for="stat in $attrs.utils['Online Delivery Type']" :value="stat.field_name">{{ stat.field_name }}</option>
                    </Select>
                </div>
                <div class="col-md-4">
                    <Label for="inputCredentialType" class="form-label" value="Credential Type"/>
                    <Select class="form-select" id="inputCredentialType" v-model="editForm.credential_type">
                        <option></option>
                        <option v-for="stat in $attrs.utils['Credential Type']" :value="stat.field_name">{{ stat.field_name }}</option>
                    </Select>
                </div>
                <div class="col-md-4">
                    <Label for="inputPriority" class="form-label" value="High Priority Industry"/>
                    <Select class="form-select" id="inputPriority" v-model="editForm.high_priority_industry">
                        <option></option>
                        <option v-for="stat in $attrs.utils['High Priority Industry']" :value="stat.field_name">{{ stat.field_name }}</option>
                    </Select>
                </div>
                <div class="col-md-4">
                    <Label for="inputIndiLearning" class="form-label" value="Indigenous Related Learning ?"/>
                    <Select class="form-select" id="inputIndiLearning" v-model="editForm.indigenous_related_learning">
                        <option value=""></option>
                        <option value="true">Yes</option>
                        <option value="false">No</option>
                    </Select>
                </div>
                <div class="col-md-4">
                    <Label for="inputDiversInc" class="form-label" value="Diversity Inclusion Related Learning?"/>
                    <Select class="form-select" id="inputDiversInc" v-model="editForm.diversity_inclusion_related_learning">
                        <option value=""></option>
                        <option value="true">Yes</option>
                        <option value="false">No</option>
                    </Select>
                </div>

                <div class="col-md-3">
                    <Label for="inputMicroCred" class="form-label" value="Prov. Funded Micro cred ?"/>
                    <Select class="form-select" id="inputMicroCred" v-model="editForm.prov_funded_micro_cred">
                        <option value=""></option>
                        <option value="true">Yes</option>
                        <option value="false">No</option>
                    </Select>
                </div>
                <div class="col-md-3">
                    <Label for="inputCreditable" class="form-label" value="Creditable ?"/>
                    <Select class="form-select" id="inputCreditable" v-model="editForm.creditable">
                        <option value=""></option>
                        <option value="true">Yes</option>
                        <option value="false">No</option>
                    </Select>
                </div>
                <div class="col-md-3">
                    <Label for="inputFullTime" class="form-label" value="Full-time ?"/>
                    <Select class="form-select" id="inputFullTime" v-model="editForm.full_time">
                        <option value=""></option>
                        <option value="true">Yes</option>
                        <option value="false">No</option>
                    </Select>
                </div>
                <div class="col-md-3">
                    <Label for="inputActive" class="form-label" value="Active?" />
                    <Select class="form-select" id="inputActive" v-model="editForm.active_status">
                        <option value=""></option>
                        <option value="true">Yes</option>
                        <option value="false">No</option>
                    </Select>
                </div>


                <div v-if="editForm.errors != undefined" class="row">
                    <div class="col-12">
                        <div v-if="editForm.hasErrors == true" class="alert alert-danger mt-3">
                            <ul>
                                <li v-for="err in editForm.errors">{{ err }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-sm btn-success" :disabled="editForm.processing">
                Update Program
            </button>
        </div>
        <FormSubmitAlert :form-state="editForm.formState" :success-msg="editForm.formSuccessMsg"
                         :fail-msg="editForm.formFailMsg"></FormSubmitAlert>
    </form>

</template>
<script>
import Select from '@/Components/Select.vue';
import Input from '@/Components/Input.vue';
import Label from '@/Components/Label.vue';
import FormSubmitAlert from '@/Components/FormSubmitAlert.vue';
import {Link, useForm} from '@inertiajs/vue3';

export default {
    name: 'InstitutionProgramEdit',
    components: {
        Input, Label, Select, Link, useForm, FormSubmitAlert
    },
    props: {
        results: Object,
        program: Object
    },
    data() {
        return {
            editForm: null,
            editFormData: {
                formState: true,
                formSuccessMsg: 'Form was submitted successfully.',
                formFailMsg: 'There was an error submitting this form.'
            },
        }
    },
    methods: {

        submitForm: function () {
            let vm = this;
            this.editForm.formState = null;
            this.editForm.put('/ministry/programs', {
                onSuccess: (response) => {
                    $("#editInstProgramModal").modal('hide')
                        .on('hidden.bs.modal', function () {
                            vm.editForm.reset(vm.editForm);
                            vm.$inertia.visit('/ministry/institutions/' + vm.results.id + '/programs');
                            vm.$emit('close');
                        });
                },
                onError: () => {
                    this.editForm.formState = false;
                },
                preserveState: true
            });
        }
    },

    mounted() {
        this.editForm = useForm(this.program);
    }
}
</script>
