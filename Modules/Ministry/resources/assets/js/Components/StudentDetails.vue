<template>
    <div v-if="editForm != null" class="card mb-3">
        <div class="card-header">
            <div>Student Details</div>
        </div>

        <form class="card-body" @submit.prevent="submitForm">
            <div class="row g-3">

                <div class="col-md-4">
                    <Label for="inputFirstName" class="form-label" value="Legal Name (First Name)" />
                    <Input type="text" class="form-control" id="inputFirstName" v-model="editForm.first_name" />
                </div>
                <div class="col-md-4">
                    <Label for="inputLastName" class="form-label" value="Legal Name (Last Name)" />
                    <Input type="text" class="form-control" id="inputLastName" v-model="editForm.last_name" />
                </div>
                <div class="col-md-4">
                    <Label for="inputEmail" class="form-label" value="Email" />
                    <Input type="email" class="form-control" id="inputEmail" v-model="editForm.email" />
                </div>

                <div class="col-md-2">
                    <Label for="inputSing" class="form-label" value="SIN" />
                    <Input type="number" min="100000000" max="999999999" class="form-control" id="inputSing" v-model="editForm.sin" />
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
                               v-model="editForm.additional_support" :checked="editForm.additional_support" />
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
                <button type="button" class="btn me-2 btn-secondary" @click="back">Back</button>
                <button type="submit" class="btn me-2 btn-success float-end" :disabled="editForm.processing">Update Student</button>
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
    name: 'StudentDetails',
    components: {
        Input, Label, Select, Link, useForm, FormSubmitAlert
    },
    props: {
        results: Object,
    },
    data() {
        return {
            editForm: '',
        }
    },
    methods: {
        back: function()
        {
            window.history.back();
        },
        submitForm: function ()
        {
            this.editForm.formState = null;
            this.editForm.put('/ministry/students', {
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
        this.editForm = useForm(this.results);
    }
}
</script>
