<template>
    <div class="card mb-3">
        <div class="card-header">
            <div>Profile Details</div>
        </div>
        <form class="card-body" @submit.prevent="submitForm">
            <div class="row g-3">
                <div v-if="error != null" class="col-12 mb-3">
                    {{ error }}
                </div>

                <div class="col-md-3">
                    <Label for="inputFirstName" class="form-label" value="Legal Name (First Name)" />
                    <Input type="text" class="form-control" id="inputFirstName" v-model="editForm.first_name" />
                </div>
                <div class="col-md-3">
                    <Label for="inputLastName" class="form-label" value="Legal Name (Last Name)" />
                    <Input type="text" class="form-control" id="inputLastName" v-model="editForm.last_name" />
                </div>
                <div class="col-md-3">
                    <Label for="inputEmail" class="form-label" value="Email" />
                    <Input type="email" class="form-control" id="inputEmail" v-model="editForm.email" />
                </div>
                <div class="col-md-3">
                    <Label for="inputGender" class="form-label" value="Gender"/>
                    <Select class="form-select" id="inputGender" v-model="editForm.gender">
                        <option v-for="status in $attrs.utils['Gender']" :value="status.field_name">{{ status.field_name }}</option>
                    </Select>
                </div>

                <div class="col-md-2">
                    <Label for="inputSin" class="form-label" value="SIN" />
                    <Input type="number" min="100000000" max="999999999" class="form-control" id="inputSin" v-model="editForm.sin" />
                </div>

                <div class="col-md-2">
                    <Label for="inputDob" class="form-label" value="Birth Date" />
                    <Input type="date" min="1950-01-01" max="2024-12-31" placeholder="YYYY-MM-DD" class="form-control" id="inputDob" v-model="editForm.dob" />
                </div>
                <div class="col-md-2">
                    <Label for="inputCity" class="form-label" value="City" />
                    <Input type="text" class="form-control" id="inputCity" v-model="editForm.city" />
                </div>
                <div class="col-md-2">
                    <Label for="inputPostalCode" class="form-label" value="Postal Code" />
                    <Input type="text" maxlength="7" class="form-control" id="inputPostalCode" v-model="editForm.zip_code" />
                </div>
                <div class="col-md-2">
                    <Label for="inputCitizenship" class="form-label" value="Citizenship" />
                    <Select class="form-select" id="inputCitizenship" v-model="editForm.citizenship">
                        <option></option>
                        <option v-for="status in $attrs.utils['Citizenship']" :value="status.field_name">{{ status.field_name }}</option>
                    </Select>
                </div>
                <div class="col-md-2">
                    <Label for="inputG12" class="form-label" value="Grade 12 or Over 19y" />
                    <Select class="form-select" id="inputG12" v-model="editForm.grade12_or_over19">
                        <option></option>
                        <option v-for="status in $attrs.utils['Grade12/Over19y']" :value="status.field_name">{{ status.field_name }}</option>
                    </Select>
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <label for="flexCheckChecked1" class="form-check-label">
                            {{ $attrs.utils['BC_Resident Decl'][0].field_name }}
                        </label>
                        <input type="checkbox" class="form-check-input" id="flexCheckChecked1"
                               v-model="editForm.bc_resident" :checked="editForm.bc_resident" />
                    </div>
                    <div class="form-check">
                        <label for="flexCheckChecked2" class="form-check-label">
                            {{ $attrs.utils['Info Consent'][0].field_name }}
                        </label>
                        <input type="checkbox" class="form-check-input" id="flexCheckChecked2" v-model="editForm.info_consent" :checked="editForm.info_consent" />
                    </div>
                    <div class="form-check">
                        <label for="flexCheckChecked3" class="form-check-label">
                            {{ $attrs.utils['Duplicative Funding'][0].field_name }}
                        </label>
                        <input type="checkbox" class="form-check-input" id="flexCheckChecked3" v-model="editForm.duplicative_funding" :checked="editForm.duplicative_funding" />
                    </div>
                    <div class="form-check">
                        <label for="flexCheckChecked4" class="form-check-label">
                            {{ $attrs.utils['Tax Implications'][0].field_name }}
                        </label>
                        <input type="checkbox" class="form-check-input" id="flexCheckChecked4"
                               v-model="editForm.tax_implications" :checked="editForm.tax_implications" />
                    </div>
                    <div class="form-check">
                        <label for="flexCheckChecked5" class="form-check-label">
                            {{ $attrs.utils['Lifetime Max'][0].field_name }}
                        </label>
                        <input type="checkbox" class="form-check-input" id="flexCheckChecked5"
                               v-model="editForm.lifetime_max" :checked="editForm.lifetime_max" />
                    </div>
                    <div class="form-check">
                        <label for="flexCheckChecked6" class="form-check-label">
                            {{ $attrs.utils['Fed Prov Benefits'][0].field_name }}
                        </label>
                        <input type="checkbox" class="form-check-input" id="flexCheckChecked6"
                               v-model="editForm.fed_prov_benefits" :checked="editForm.fed_prov_benefits" />
                    </div>
                    <div class="form-check">
                        <label for="flexCheckChecked7" class="form-check-label">
                            {{ $attrs.utils['WorkBC Client'][0].field_name }}
                        </label>
                        <input type="checkbox" class="form-check-input" id="flexCheckChecked7"
                               v-model="editForm.workbc_client" :checked="editForm.workbc_client" />
                    </div>
                    <div class="form-check">
                        <label for="flexCheckChecked8" class="form-check-label">
                            {{ $attrs.utils['Additional Supports'][0].field_name }}
                        </label>
                        <input type="checkbox" class="form-check-input" id="flexCheckChecked8"
                               v-model="editForm.additional_supports" :checked="editForm.additional_supports" />
                    </div>
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
            <div class="card-footer mt-3">
                <button type="submit" class="btn me-2 btn-success float-end" :disabled="editForm.processing">Update Profile</button>
            </div>
            <FormSubmitAlert :form-state="editForm.formState"
                             :success-msg="'Student record was updated successfully.'"></FormSubmitAlert>
        </form>
    </div>

</template>
<script>
import Select from '@/Components/Select.vue';
import Input from '@/Components/Input.vue';
import Label from '@/Components/Label.vue';
import FormSubmitAlert from '@/Components/FormSubmitAlert.vue';
import { Link, useForm } from '@inertiajs/vue3';

export default {
    name: 'DashboardProfile',
    components: {
        Input, Label, Select, Link, useForm, FormSubmitAlert
    },
    props: {
        results: Object,
        providerUser: Object|null,
        error: String|null
    },
    data() {
        return {
            editForm: '',
            editFormData: {
                formState: null,
                first_name: "",
                last_name: "",
                email: "",
                gender: "",
                sin: "",
                dob: "",
                city: "",
                zip_code: "",
                citizenship: null,
                grade12_or_over19: null,
                bc_resident: false,
                info_consent: false,
                duplicative_funding: false,
                tax_implications: false,
                lifetime_max: false,
                fed_prov_benefits: false,
                workbc_client: false,
                additional_supports: false
            }
        }
    },
    methods: {
        validateForm: function() {
            const errors = {};
            const errors2 = {};

            // Check for empty fields
            // const requiredFields = [
            //     'first_name', 'last_name', 'email', 'sin', 'dob', 'city', 'zip_code', 'citizenship', 'grade12_or_over19'
            // ];
            // requiredFields.forEach(field => {
            //     if (!this.editForm[field]) {
            //         errors[field] = `${field.replace(/_/g, ' ')} is required.`;
            //     }
            // });

            // Check for false checkbox fields
            let trueFieldsValid = true;
            const requiredTrueFields = [
                'bc_resident', 'info_consent', 'duplicative_funding', 'tax_implications', 'lifetime_max', 'fed_prov_benefits', 'workbc_client', 'additional_supports'
            ];
            requiredTrueFields.forEach(field => {
                if (!this.editForm[field]) {
                    errors2[field] = `${field.replace(/_/g, ' ')} is required.`;
                    trueFieldsValid = false;
                }
            });
            if(!trueFieldsValid) {
                errors['bc_resident'] = `Please make sure you agree to all of the above fields.`;
            }
            console.log(errors2);

            this.editForm.errors = errors;
            return Object.keys(errors).length === 0;
        },
        submitForm: function () {
            this.editForm.hasErrors = false;
            this.editForm.errors = {};

            if (!this.validateForm()) {
                // Handle validation errors
                this.editForm.hasErrors = true;
                return false;
            }

            if(this.results == null){
                this.createForm();
            }else{
                this.updateForm()
            }
        },
        updateForm: function ()
        {
            this.editForm.formState = null;
            this.editForm.put('/profile', {
                onSuccess: () => {
                    this.editForm.formState = true;
                },
                onError: () => {
                    this.editForm.formState = false;
                },
                preserveState: true
            });
        },
        createForm: function ()
        {
            this.editForm.formState = null;
            this.editForm.post('/profile', {
                onSuccess: () => {
                    this.editForm.formState = true;
                },
                onError: () => {
                    this.editForm.formState = false;
                },
                preserveState: true
            });
        },
    },

    mounted() {
        if(this.results == null){
            this.editForm = useForm(this.editFormData);
            this.editForm.first_name = this.providerUser.given_name;
            this.editForm.last_name = this.providerUser.family_name;
            this.editForm.gender = this.providerUser.gender === 'male' ? 'Male' : 'Female';
            this.editForm.dob = this.providerUser.birthdate;
        }else{
            this.editForm = useForm(this.results);
        }
    }
}
</script>
