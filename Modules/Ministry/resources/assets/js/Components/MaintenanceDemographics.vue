<template>
    <div>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Demographics Management</h3>
            <button @click="newDemographic" type="button" class="btn btn-success">
                <i class="fas fa-plus"></i> New Demographic
            </button>
        </div>

        <div v-if="results.length === 0" class="alert alert-info">
            <i class="fas fa-info-circle"></i> No demographics found. Create your first demographic to get started.
        </div>

        <div v-else class="accordion" id="demographicsAccordion">
            <div v-for="(demographic, index) in results" :key="demographic.id" class="accordion-item">
                <h2 class="accordion-header" :id="'heading-' + demographic.id">
                    <button class="accordion-button collapsed" type="button" 
                            data-bs-toggle="collapse" 
                            :data-bs-target="'#collapse-' + demographic.id"
                            :aria-expanded="false" 
                            :aria-controls="'collapse-' + demographic.id">
                        <div class="d-flex justify-content-between align-items-center w-100 me-3">
                            <div>
                                <strong>{{ demographic.question }}</strong>
                                <span class="badge rounded-pill ms-2" :class="demographic.active ? 'text-bg-success' : 'text-bg-danger'">
                                    {{ demographic.active ? 'Active' : 'Inactive' }}
                                </span>
                                <span class="badge rounded-pill text-bg-info ms-1">{{ demographic.type }}</span>
                                <span v-if="demographic.required" class="badge rounded-pill text-bg-warning ms-1">Required</span>
                            </div>
                            <div>
                                <span class="text-muted">{{ demographic.options.length }} options</span>
                            </div>
                        </div>
                    </button>
                </h2>
                <div :id="'collapse-' + demographic.id" class="accordion-collapse collapse" 
                     :aria-labelledby="'heading-' + demographic.id" 
                     data-bs-parent="#demographicsAccordion">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Options</h6>
                                        <button @click="newOption(demographic)" type="button" class="btn btn-sm btn-success">
                                            <i class="fas fa-plus"></i> Add Option
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div v-if="demographic.options.length === 0" class="text-muted">
                                            No options available for this demographic.
                                        </div>
                                        <div v-else class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Order</th>
                                                        <th>Label</th>
                                                        <th>Value</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="option in demographic.options" :key="option.id">
                                                        <td>{{ option.order }}</td>
                                                        <td>{{ option.label }}</td>
                                                        <td>{{ option.value || '-' }}</td>
                                                        <td>
                                                            <button @click="editOption(option)" type="button" 
                                                                    class="btn btn-sm btn-outline-primary me-1">Edit
                                                            </button>
                                                            <button @click="deleteOption(option)" type="button" 
                                                                    class="btn btn-sm btn-outline-danger">Delete
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Demographic Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Question:</strong> {{ demographic.question }}</p>
                                        <p><strong>Type:</strong> {{ demographic.type }}</p>
                                        <p><strong>Required:</strong> {{ demographic.required ? 'Yes' : 'No' }}</p>
                                        <p><strong>Active:</strong> {{ demographic.active ? 'Yes' : 'No' }}</p>
                                        <p><strong>Order:</strong> {{ demographic.order || 0 }}</p>
                                        <p v-if="demographic.description"><strong>Description:</strong> {{ demographic.description }}</p>
                                        <div class="mt-3">
                                            <button @click="editDemographic(demographic)" type="button" 
                                                    class="btn btn-sm btn-outline-primary me-2">Edit
                                            </button>
                                            <button @click="deleteDemographic(demographic)" type="button" 
                                                    class="btn btn-sm btn-outline-danger">Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Demographic Modal -->
        <div class="modal modal-lg fade" id="newDemographicModal" tabindex="-1" aria-labelledby="newDemographicModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="newDemographicModalLabel">New Demographic</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form v-if="newDemographicForm != null" @submit.prevent="submitNewDemographic">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <BreezeLabel for="newDemographicQuestion" class="form-label" value="Question" />
                                    <BreezeInput type="text" class="form-control" id="newDemographicQuestion" v-model="newDemographicForm.question" />
                                </div>
                                <div class="col-md-3">
                                    <BreezeLabel for="newDemographicType" class="form-label" value="Type" />
                                    <BreezeSelect class="form-select" id="newDemographicType" v-model="newDemographicForm.type">
                                        <option value="">Select Type</option>
                                        <option value="text">Text</option>
                                        <option value="select">Select</option>
                                        <option value="multi-select">Multi-Select</option>
                                        <option value="radio">Radio</option>
                                        <option value="checkbox">Checkbox</option>
                                    </BreezeSelect>
                                </div>
                                <div class="col-md-3">
                                    <BreezeLabel for="newDemographicRequired" class="form-label" value="Required" />
                                    <BreezeSelect class="form-select" id="newDemographicRequired" v-model="newDemographicForm.required">
                                        <option value="false">No</option>
                                        <option value="true">Yes</option>
                                    </BreezeSelect>
                                </div>
                                <div class="col-md-3">
                                    <BreezeLabel for="newDemographicActive" class="form-label" value="Active" />
                                    <BreezeSelect class="form-select" id="newDemographicActive" v-model="newDemographicForm.active">
                                        <option value="false">No</option>
                                        <option value="true">Yes</option>
                                    </BreezeSelect>
                                </div>
                                <div class="col-md-3">
                                    <BreezeLabel for="newDemographicOrder" class="form-label" value="Order" />
                                    <BreezeInput type="number" class="form-control" id="newDemographicOrder" v-model="newDemographicForm.order" min="0" />
                                </div>
                                <div class="col-12">
                                    <BreezeLabel for="newDemographicDescription" class="form-label" value="Description" />
                                    <textarea class="form-control" id="newDemographicDescription" rows="3" v-model="newDemographicForm.description"></textarea>
                                </div>
                            </div>

                            <div v-if="newDemographicForm.errors != undefined" class="row">
                                <div class="col-12">
                                    <div v-if="newDemographicForm.hasErrors == true" class="alert alert-danger mt-3">
                                        <ul>
                                            <li v-for="err in newDemographicForm.errors"><small>{{ err }}</small></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success" :disabled="newDemographicForm.processing">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Demographic Modal -->
        <div class="modal modal-lg fade" id="editDemographicModal" tabindex="-1" aria-labelledby="editDemographicModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editDemographicModalLabel">Edit Demographic</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form v-if="editDemographicForm != null" @submit.prevent="submitEditDemographic">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <BreezeLabel for="editDemographicQuestion" class="form-label" value="Question" />
                                    <BreezeInput type="text" class="form-control" id="editDemographicQuestion" v-model="editDemographicForm.question" />
                                </div>
                                <div class="col-md-3">
                                    <BreezeLabel for="editDemographicType" class="form-label" value="Type" />
                                    <BreezeSelect class="form-select" id="editDemographicType" v-model="editDemographicForm.type">
                                        <option value="">Select Type</option>
                                        <option value="text">Text</option>
                                        <option value="select">Select</option>
                                        <option value="multi-select">Multi-Select</option>
                                        <option value="radio">Radio</option>
                                        <option value="checkbox">Checkbox</option>
                                    </BreezeSelect>
                                </div>
                                <div class="col-md-3">
                                    <BreezeLabel for="editDemographicRequired" class="form-label" value="Required" />
                                    <BreezeSelect class="form-select" id="editDemographicRequired" v-model="editDemographicForm.required">
                                        <option value="false">No</option>
                                        <option value="true">Yes</option>
                                    </BreezeSelect>
                                </div>
                                <div class="col-md-3">
                                    <BreezeLabel for="editDemographicActive" class="form-label" value="Active" />
                                    <BreezeSelect class="form-select" id="editDemographicActive" v-model="editDemographicForm.active">
                                        <option value="false">No</option>
                                        <option value="true">Yes</option>
                                    </BreezeSelect>
                                </div>
                                <div class="col-md-3">
                                    <BreezeLabel for="editDemographicOrder" class="form-label" value="Order" />
                                    <BreezeInput type="number" class="form-control" id="editDemographicOrder" v-model="editDemographicForm.order" min="0" />
                                </div>
                                <div class="col-12">
                                    <BreezeLabel for="editDemographicDescription" class="form-label" value="Description" />
                                    <textarea class="form-control" id="editDemographicDescription" rows="3" v-model="editDemographicForm.description"></textarea>
                                </div>
                            </div>

                            <div v-if="editDemographicForm.errors != undefined" class="row">
                                <div class="col-12">
                                    <div v-if="editDemographicForm.hasErrors == true" class="alert alert-danger mt-3">
                                        <ul>
                                            <li v-for="err in editDemographicForm.errors"><small>{{ err }}</small></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success" :disabled="editDemographicForm.processing">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- New Option Modal -->
        <div class="modal fade" id="newOptionModal" tabindex="-1" aria-labelledby="newOptionModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="newOptionModalLabel">New Option</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form v-if="newOptionForm != null" @submit.prevent="submitNewOption">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <BreezeLabel for="newOptionLabel" class="form-label" value="Label" />
                                    <BreezeInput type="text" class="form-control" id="newOptionLabel" v-model="newOptionForm.label" />
                                </div>
                                <div class="col-md-4">
                                    <BreezeLabel for="newOptionOrder" class="form-label" value="Order" />
                                    <BreezeInput type="number" class="form-control" id="newOptionOrder" v-model="newOptionForm.order" min="0" />
                                </div>
                                <div class="col-12">
                                    <BreezeLabel for="newOptionValue" class="form-label" value="Value (optional)" />
                                    <BreezeInput type="text" class="form-control" id="newOptionValue" v-model="newOptionForm.value" />
                                </div>
                            </div>

                            <div v-if="newOptionForm.errors != undefined" class="row">
                                <div class="col-12">
                                    <div v-if="newOptionForm.hasErrors == true" class="alert alert-danger mt-3">
                                        <ul>
                                            <li v-for="err in newOptionForm.errors"><small>{{ err }}</small></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success" :disabled="newOptionForm.processing">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Option Modal -->
        <div class="modal fade" id="editOptionModal" tabindex="-1" aria-labelledby="editOptionModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editOptionModalLabel">Edit Option</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form v-if="editOptionForm != null" @submit.prevent="submitEditOption">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <BreezeLabel for="editOptionLabel" class="form-label" value="Label" />
                                    <BreezeInput type="text" class="form-control" id="editOptionLabel" v-model="editOptionForm.label" />
                                </div>
                                <div class="col-md-4">
                                    <BreezeLabel for="editOptionOrder" class="form-label" value="Order" />
                                    <BreezeInput type="number" class="form-control" id="editOptionOrder" v-model="editOptionForm.order" min="0" />
                                </div>
                                <div class="col-12">
                                    <BreezeLabel for="editOptionValue" class="form-label" value="Value (optional)" />
                                    <BreezeInput type="text" class="form-control" id="editOptionValue" v-model="editOptionForm.value" />
                                </div>
                            </div>

                            <div v-if="editOptionForm.errors != undefined" class="row">
                                <div class="col-12">
                                    <div v-if="editOptionForm.hasErrors == true" class="alert alert-danger mt-3">
                                        <ul>
                                            <li v-for="err in editOptionForm.errors"><small>{{ err }}</small></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success" :disabled="editOptionForm.processing">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <FormSubmitAlert :form-state="formState"></FormSubmitAlert>
    </div>
</template>

<script>
import { Link, useForm } from '@inertiajs/vue3';
import BreezeInput from '@/Components/Input.vue';
import BreezeSelect from '@/Components/Select.vue';
import BreezeLabel from '@/Components/Label.vue';
import FormSubmitAlert from '@/Components/FormSubmitAlert.vue';

export default {
    name: 'MaintenanceDemographics',
    components: {
        BreezeInput,
        BreezeSelect,
        BreezeLabel,
        FormSubmitAlert,
        Link
    },
    props: {
        results: Array
    },
    data() {
        return {
            formState: '',
            newDemographicForm: null,
            editDemographicForm: null,
            newOptionForm: null,
            editOptionForm: null,
            newDemographicFormData: {
                question: '',
                description: '',
                type: '',
                required: false,
                active: true,
                order: 0
            },
            newOptionFormData: {
                demographic_id: '',
                label: '',
                value: '',
                order: 0
            }
        }
    },
    methods: {
        newDemographic() {
            $("#newDemographicModal").modal('show');
            this.newDemographicForm = useForm(this.newDemographicFormData);
        },

        submitNewDemographic() {
            this.formState = '';
            this.newDemographicForm.post('/ministry/maintenance/demographics', {
                onSuccess: () => {
                    $("#newDemographicModal").modal('hide');
                    this.newDemographicForm.reset(this.newDemographicFormData);
                    this.formState = true;
                },
                onError: () => {
                    this.formState = false;
                },
                preserveState: true,
                preserveScroll: true
            });
        },

        editDemographic(demographic) {
            $("#editDemographicModal").modal('show');
            this.editDemographicForm = useForm(demographic);
            this.editDemographicForm.formState = '';
        },

        submitEditDemographic() {
            this.formState = '';
            this.editDemographicForm.put('/ministry/maintenance/demographics/' + this.editDemographicForm.id, {
                onSuccess: () => {
                    $("#editDemographicModal").modal('hide');
                    this.formState = true;
                },
                onError: () => {
                    this.formState = false;
                },
                preserveState: true,
                preserveScroll: true
            });
        },

        deleteDemographic(demographic) {
            if (confirm('Are you sure you want to delete this demographic? This action cannot be undone.')) {
                this.formState = '';
                this.$inertia.delete('/ministry/maintenance/demographics/' + demographic.id, {
                    onSuccess: () => {
                        this.formState = true;
                    },
                    onError: () => {
                        this.formState = false;
                    },
                    preserveState: true,
                    preserveScroll: true
                });
            }
        },

        newOption(demographic) {
            $("#newOptionModal").modal('show');
            let formData = { ...this.newOptionFormData };
            formData.demographic_id = demographic.id;
            formData.order = demographic.options.length;
            this.newOptionForm = useForm(formData);
        },

        submitNewOption() {
            this.formState = '';
            this.newOptionForm.post('/ministry/maintenance/demographics/' + this.newOptionForm.demographic_id + '/options', {
                onSuccess: () => {
                    $("#newOptionModal").modal('hide');
                    this.newOptionForm.reset(this.newOptionFormData);
                    this.formState = true;
                },
                onError: () => {
                    this.formState = false;
                },
                preserveState: true,
                preserveScroll: true
            });
        },

        editOption(option) {
            $("#editOptionModal").modal('show');
            this.editOptionForm = useForm(option);
            this.editOptionForm.formState = '';
        },

        submitEditOption() {
            this.formState = '';
            this.editOptionForm.put('/ministry/maintenance/demographics/options/' + this.editOptionForm.id, {
                onSuccess: () => {
                    $("#editOptionModal").modal('hide');
                    this.formState = true;
                },
                onError: () => {
                    this.formState = false;
                },
                preserveState: true,
                preserveScroll: true
            });
        },

        deleteOption(option) {
            if (confirm('Are you sure you want to delete this option?')) {
                this.formState = '';
                this.$inertia.delete('/ministry/maintenance/demographics/options/' + option.id, {
                    onSuccess: () => {
                        this.formState = true;
                    },
                    onError: () => {
                        this.formState = false;
                    },
                    preserveState: true,
                    preserveScroll: true
                });
            }
        }
    }
}
</script>
