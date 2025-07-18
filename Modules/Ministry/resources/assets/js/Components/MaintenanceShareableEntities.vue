<template>
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Shareable Entities Management</h5>
                <button @click="newEntity" type="button" class="btn btn-success">
                    <i class="fas fa-plus"></i> New Entity
                </button>
            </div>
        </div>

        <div class="card-body">
            <div v-if="results.length === 0" class="alert alert-info">
                <i class="fas fa-info-circle"></i> No shareable entities found. Create your first entity to get started.
            </div>

            <div v-else class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="entity in results" :key="entity.id">
                            <td>
                                <strong>{{ entity.name }}</strong>
                                <small v-if="entity.privacy_policy_url" class="d-block text-muted">
                                    <a :href="entity.privacy_policy_url" target="_blank" class="text-decoration-none">
                                        <i class="fas fa-external-link-alt"></i> Privacy Policy
                                    </a>
                                </small>
                            </td>
                            <td>
                                <span v-if="entity.description">{{ entity.description }}</span>
                                <span v-else class="text-muted">No description</span>
                            </td>
                            <td>
                                <div v-if="entity.contact_email || entity.contact_phone">
                                    <div v-if="entity.contact_email">
                                        <i class="fas fa-envelope"></i> {{ entity.contact_email }}
                                    </div>
                                    <div v-if="entity.contact_phone">
                                        <i class="fas fa-phone"></i> {{ entity.contact_phone }}
                                    </div>
                                </div>
                                <span v-else class="text-muted">No contact info</span>
                            </td>
                            <td>
                                <span v-if="entity.active" class="badge bg-success">Active</span>
                                <span v-else class="badge bg-danger">Inactive</span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button @click="editEntity(entity)" type="button" class="btn btn-sm btn-outline-primary">Edit
                                    </button>
                                    <!-- <button @click="deleteEntity(entity)" type="button" class="btn btn-sm btn-outline-danger">Delete
                                    </button> -->
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- New Entity Modal -->
    <div class="modal fade" id="newEntityModal" tabindex="-1" aria-labelledby="newEntityModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newEntityModalLabel">New Shareable Entity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form v-if="newEntityForm != null" @submit.prevent="submitNewEntity">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <BreezeLabel for="newEntityName" class="form-label" value="Name *" />
                                <BreezeInput type="text" class="form-control" id="newEntityName" v-model="newEntityForm.name" />
                                <div v-if="newEntityForm.errors.name" class="text-danger">
                                    {{ newEntityForm.errors.name }}
                                </div>
                            </div>
                            <div class="col-12">
                                <BreezeLabel for="newEntityDescription" class="form-label" value="Description" />
                                <textarea class="form-control" id="newEntityDescription" v-model="newEntityForm.description" rows="3"></textarea>
                                <div v-if="newEntityForm.errors.description" class="text-danger">
                                    {{ newEntityForm.errors.description }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <BreezeLabel for="newEntityEmail" class="form-label" value="Contact Email" />
                                <BreezeInput type="email" class="form-control" id="newEntityEmail" v-model="newEntityForm.contact_email" />
                                <div v-if="newEntityForm.errors.contact_email" class="text-danger">
                                    {{ newEntityForm.errors.contact_email }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <BreezeLabel for="newEntityPhone" class="form-label" value="Contact Phone" />
                                <BreezeInput type="tel" class="form-control" id="newEntityPhone" v-model="newEntityForm.contact_phone" />
                                <div v-if="newEntityForm.errors.contact_phone" class="text-danger">
                                    {{ newEntityForm.errors.contact_phone }}
                                </div>
                            </div>
                            <div class="col-12">
                                <BreezeLabel for="newEntityPrivacyUrl" class="form-label" value="Privacy Policy URL" />
                                <BreezeInput type="url" class="form-control" id="newEntityPrivacyUrl" v-model="newEntityForm.privacy_policy_url" />
                                <div v-if="newEntityForm.errors.privacy_policy_url" class="text-danger">
                                    {{ newEntityForm.errors.privacy_policy_url }}
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="newEntityActive" v-model="newEntityForm.active">
                                    <label class="form-check-label" for="newEntityActive">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="newEntityForm.processing">
                            <span v-if="newEntityForm.processing" class="spinner-border spinner-border-sm me-2"></span>
                            Create Entity
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Entity Modal -->
    <div class="modal fade" id="editEntityModal" tabindex="-1" aria-labelledby="editEntityModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editEntityModalLabel">Edit Shareable Entity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form v-if="editEntityForm != null" @submit.prevent="submitEditEntity">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <BreezeLabel for="editEntityName" class="form-label" value="Name *" />
                                <BreezeInput type="text" class="form-control" id="editEntityName" v-model="editEntityForm.name" />
                                <div v-if="editEntityForm.errors.name" class="text-danger">
                                    {{ editEntityForm.errors.name }}
                                </div>
                            </div>
                            <div class="col-12">
                                <BreezeLabel for="editEntityDescription" class="form-label" value="Description" />
                                <textarea class="form-control" id="editEntityDescription" v-model="editEntityForm.description" rows="3"></textarea>
                                <div v-if="editEntityForm.errors.description" class="text-danger">
                                    {{ editEntityForm.errors.description }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <BreezeLabel for="editEntityEmail" class="form-label" value="Contact Email" />
                                <BreezeInput type="email" class="form-control" id="editEntityEmail" v-model="editEntityForm.contact_email" />
                                <div v-if="editEntityForm.errors.contact_email" class="text-danger">
                                    {{ editEntityForm.errors.contact_email }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <BreezeLabel for="editEntityPhone" class="form-label" value="Contact Phone" />
                                <BreezeInput type="tel" class="form-control" id="editEntityPhone" v-model="editEntityForm.contact_phone" />
                                <div v-if="editEntityForm.errors.contact_phone" class="text-danger">
                                    {{ editEntityForm.errors.contact_phone }}
                                </div>
                            </div>
                            <div class="col-12">
                                <BreezeLabel for="editEntityPrivacyUrl" class="form-label" value="Privacy Policy URL" />
                                <BreezeInput type="url" class="form-control" id="editEntityPrivacyUrl" v-model="editEntityForm.privacy_policy_url" />
                                <div v-if="editEntityForm.errors.privacy_policy_url" class="text-danger">
                                    {{ editEntityForm.errors.privacy_policy_url }}
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="editEntityActive" v-model="editEntityForm.active">
                                    <label class="form-check-label" for="editEntityActive">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="editEntityForm.processing">
                            <span v-if="editEntityForm.processing" class="spinner-border spinner-border-sm me-2"></span>
                            Update Entity
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { useForm } from '@inertiajs/vue3';
import BreezeInput from '@/Components/Input.vue';
import BreezeLabel from '@/Components/Label.vue';

export default {
    name: 'MaintenanceShareableEntities',
    components: {
        BreezeInput,
        BreezeLabel
    },
    props: {
        results: {
            type: Array,
            required: true
        }
    },
    data() {
        return {
            newEntityForm: null,
            editEntityForm: null,
            newEntityFormData: {
                name: '',
                description: '',
                contact_email: '',
                contact_phone: '',
                privacy_policy_url: '',
                active: true
            }
        }
    },
    methods: {
        newEntity() {
            this.newEntityForm = useForm({ ...this.newEntityFormData });
            $('#newEntityModal').modal('show');
        },

        editEntity(entity) {
            this.editEntityForm = useForm({
                id: entity.id,
                name: entity.name,
                description: entity.description || '',
                contact_email: entity.contact_email || '',
                contact_phone: entity.contact_phone || '',
                privacy_policy_url: entity.privacy_policy_url || '',
                active: entity.active
            });
            $('#editEntityModal').modal('show');
        },

        submitNewEntity() {
            this.newEntityForm.post('/ministry/maintenance/shareable_entities', {
                onSuccess: () => {
                    $('#newEntityModal').modal('hide');
                    this.newEntityForm.reset();
                    this.$inertia.visit('/ministry/maintenance/shareable_entities');
                }
            });
        },

        submitEditEntity() {
            this.editEntityForm.put('/ministry/maintenance/shareable_entities/' + this.editEntityForm.id, {
                onSuccess: () => {
                    $('#editEntityModal').modal('hide');
                    this.editEntityForm.reset();
                    this.$inertia.visit('/ministry/maintenance/shareable_entities');
                }
            });
        },

        deleteEntity(entity) {
            if (confirm('Are you sure you want to delete this entity? This action cannot be undone.')) {
                this.$inertia.delete('/ministry/maintenance/shareable_entities/' + entity.id, {
                    onSuccess: () => {
                        this.$inertia.visit('/ministry/maintenance/shareable_entities');
                    }
                });
            }
        }
    }
}
</script>
