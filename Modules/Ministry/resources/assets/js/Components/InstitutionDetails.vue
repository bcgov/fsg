<template>
    <div v-if="editForm != null" class="card mb-3">
        <div class="card-header">
            <div>
                Institution Details <small class="text-muted">{{ editForm.bceid_business_guid }}</small>
            </div>

        </div>

        <form class="card-body" @submit.prevent="submitForm">
            <div class="row g-3">

                <div class="col-md-6">
                    <Label for="inputName" class="form-label" value="Name" />
                    <Input type="text" class="form-control" id="inputName" v-model="editForm.name" />
                </div>
                <div class="col-md-6">
                    <Label for="inputLegalName" class="form-label" value="Legal Name" />
                    <Input type="text" class="form-control" id="inputLegalName" v-model="editForm.legal_name" />
                </div>
                <div class="col-md-4">
                    <Label for="inputAddress1" class="form-label" value="Address 1" />
                    <Input type="text" class="form-control" id="inputAddress1" v-model="editForm.address1" />
                </div>
                <div class="col-md-4">
                    <Label for="inputAddress1" class="form-label" value="Address 2" />
                    <Input type="text" class="form-control" id="inputAddress1" v-model="editForm.address2" />
                </div>
                <div class="col-md-4">
                    <Label for="inputCategory" class="form-label" value="Category" />
                    <Select class="form-select" id="inputCategory" v-model="editForm.category">
                        <option v-for="cat in $attrs.utils['Institution Category']" :value="cat.field_name">{{ cat.field_name }}</option>
                    </Select>
                </div>

                <div class="col-md-3">
                    <Label for="inputPrimaryContact" class="form-label" value="Primary Contact Name" />
                    <Input type="text" class="form-control" id="inputPrimaryContact" v-model="editForm.primary_contact" />
                </div>
                <div class="col-md-3">
                    <Label for="inputPrimaryEmail" class="form-label" value="Primary Contact Email" />
                    <Input type="email" class="form-control" id="inputPrimaryEmail" v-model="editForm.primary_email" />
                </div>
                <div class="col-md-3">
                    <Label for="inputCity" class="form-label" value="City" />
                    <Input type="text" class="form-control" id="inputCity" v-model="editForm.city" />
                </div>
                <div class="col-md-3">
                    <Label for="inputPostalCode" class="form-label" value="Postal Code" />
                    <Input type="text" class="form-control" id="inputPostalCode" v-model="editForm.postal_code" />
                </div>

                <div class="col-md-3">
                    <Label for="inputProvince" class="form-label" value="Province" />
                    <Select class="form-select" id="inputProvince" v-model="editForm.province">
                        <option v-for="prov in $attrs.utils['Provinces']" :value="prov.field_name">{{ prov.field_name }}</option>
                    </Select>
                </div>
                <div class="col-md-3">
                    <Label for="inputEconomicRegion" class="form-label" value="Economic Region" />
                    <Select class="form-select" id="inputEconomicRegion" v-model="editForm.economic_region">
                        <option v-for="prov in $attrs.utils['Economic Region']" :value="prov.field_name">{{ prov.field_name }}</option>
                    </Select>
                </div>
                <div class="col-md-3">
                    <Label for="inputSize" class="form-label" value="Size" />
                    <Select class="form-select" id="inputSize" v-model="editForm.size">
                        <option v-for="cat in $attrs.utils['Institution Size']" :value="cat.field_name">{{ cat.field_name }}</option>
                    </Select>
                </div>
                <div class="col-md-3">
                    <Label for="inputActiveStatus" class="form-label" value="Is Active?" />
                    <Select class="form-select" id="inputActiveStatus" v-model="editForm.active_status">
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
            <div class="card-footer mt-3">
                <button type="button" class="btn me-2 btn-secondary" @click="back">Back</button>
                <button type="submit" class="btn me-2 btn-success float-end" :disabled="editForm.processing">Update Institution</button>
            </div>
            <FormSubmitAlert :form-state="editForm.formState"
                             :success-msg="'Institution record was updated successfully.'"></FormSubmitAlert>
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
    name: 'InstitutionDetails',
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
            this.editForm.put('/ministry/institutions', {
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
