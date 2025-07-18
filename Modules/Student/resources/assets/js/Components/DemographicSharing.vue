<template>
    <div class="demographic-sharing-container">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-share-alt me-2"></i>
                    Demographics Sharing Preferences
                </h4>
                <p class="text-muted mb-0">
                    Control which demographic information you want to share with external entities
                </p>
            </div>
            <div class="card-body">
                <div v-if="demographics.length === 0" class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No demographics available for sharing at this time.
                </div>
                
                <div v-else>
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Privacy Notice:</strong> By sharing your demographic information, you consent to 
                                sharing specific data with the selected entities. You can revoke sharing permissions at any time.
                            </div>
                        </div>
                    </div>

                    <div class="accordion" id="demographicSharingAccordion">
                        <div v-for="demographic in demographics" :key="demographic.id" class="accordion-item">
                            <h2 class="accordion-header" :id="'heading-' + demographic.id">
                                <button 
                                    class="accordion-button collapsed" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    :data-bs-target="'#collapse-' + demographic.id"
                                    :aria-expanded="false" 
                                    :aria-controls="'collapse-' + demographic.id"
                                >
                                    <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                        <div>
                                            <strong>{{ demographic.question }}</strong>
                                            <span v-if="hasAnswered(demographic)" class="badge bg-success ms-2">
                                                <i class="fas fa-check me-1"></i> Answered
                                            </span>
                                            <span v-else class="badge bg-secondary ms-2">
                                                <i class="fas fa-question me-1"></i> Not Answered
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-muted">
                                                {{ getSharedCount(demographic.id) }} of {{ shareableEntities.length }} entities
                                            </span>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div 
                                :id="'collapse-' + demographic.id" 
                                class="accordion-collapse collapse" 
                                :aria-labelledby="'heading-' + demographic.id" 
                                data-bs-parent="#demographicSharingAccordion"
                            >
                                <div class="accordion-body">
                                    <div v-if="!hasAnswered(demographic)" class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        You must answer this demographic question before you can share it with entities.
                                        <a href="/student/applications" class="alert-link">Complete your profile</a> to enable sharing.
                                    </div>
                                    
                                    <div v-else>
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <h6>Your Answer:</h6>
                                                <div class="p-3 bg-light rounded">
                                                    <span class="fw-bold">{{ getDemographicAnswer(demographic) }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <h6>Select entities to share with:</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>Entity</th>
                                                                <th>Contact</th>
                                                                <th>Privacy Policy</th>
                                                                <th>Share</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr v-for="entity in shareableEntities" :key="entity.id">
                                                                <td>
                                                                    <div>
                                                                        <strong>{{ entity.name }}</strong>
                                                                        <br>
                                                                        <small class="text-muted">{{ entity.description }}</small>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div v-if="entity.contact_email || entity.contact_phone">
                                                                        <div v-if="entity.contact_email">
                                                                            <i class="fas fa-envelope me-1"></i>
                                                                            <small>{{ entity.contact_email }}</small>
                                                                        </div>
                                                                        <div v-if="entity.contact_phone">
                                                                            <i class="fas fa-phone me-1"></i>
                                                                            <small>{{ entity.contact_phone }}</small>
                                                                        </div>
                                                                    </div>
                                                                    <span v-else class="text-muted">
                                                                        <small>No contact info</small>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <a 
                                                                        v-if="entity.privacy_policy_url" 
                                                                        :href="entity.privacy_policy_url" 
                                                                        target="_blank" 
                                                                        class="btn btn-sm btn-outline-info"
                                                                    >
                                                                        <i class="fas fa-external-link-alt me-1"></i>
                                                                        View Policy
                                                                    </a>
                                                                    <span v-else class="text-muted">
                                                                        <small>No policy available</small>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <div class="form-check form-switch">
                                                                        <input 
                                                                            class="form-check-input" 
                                                                            type="checkbox" 
                                                                            role="switch" 
                                                                            :id="'share-' + demographic.id + '-' + entity.id"
                                                                            :checked="isShared(demographic.id, entity.id)"
                                                                            @change="toggleSharing(demographic.id, entity.id, $event.target.checked)"
                                                                        >
                                                                        <label 
                                                                            class="form-check-label" 
                                                                            :for="'share-' + demographic.id + '-' + entity.id"
                                                                        >
                                                                            Share
                                                                        </label>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div v-if="isShared(demographic.id, entity.id)">
                                                                        <span class="badge bg-success">
                                                                            <i class="fas fa-check me-1"></i> Shared
                                                                        </span>
                                                                        <br>
                                                                        <small class="text-muted">
                                                                            {{ getSharedDate(demographic.id, entity.id) }}
                                                                        </small>
                                                                    </div>
                                                                    <span v-else class="badge bg-secondary">
                                                                        <i class="fas fa-times me-1"></i> Not Shared
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'DemographicSharing',
    props: {
        demographics: {
            type: Array,
            required: true
        },
        shareableEntities: {
            type: Array,
            required: true
        },
        existingShares: {
            type: Object,
            required: true
        },
        student: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            shares: { ...this.existingShares },
            loading: false
        }
    },
    methods: {
        hasAnswered(demographic) {
            return demographic.student_demographics && demographic.student_demographics.length > 0;
        },

        getDemographicAnswer(demographic) {
            if (!this.hasAnswered(demographic)) {
                return 'Not answered';
            }
            
            const studentDemo = demographic.student_demographics[0];
            if (studentDemo.answers && studentDemo.answers.length > 0) {
                return studentDemo.answers.map(answer => answer.value).join(', ');
            }
            
            return 'No answer recorded';
        },

        isShared(demographicId, entityId) {
            return this.shares[demographicId] && 
                   this.shares[demographicId][entityId] && 
                   this.shares[demographicId][entityId].is_shared;
        },

        getSharedCount(demographicId) {
            if (!this.shares[demographicId]) return 0;
            
            return Object.values(this.shares[demographicId])
                .filter(share => share.is_shared).length;
        },

        getSharedDate(demographicId, entityId) {
            const share = this.shares[demographicId] && this.shares[demographicId][entityId];
            if (share && share.shared_at) {
                return new Date(share.shared_at).toLocaleDateString();
            }
            return '';
        },

        async toggleSharing(demographicId, entityId, isShared) {
            this.loading = true;
            
            try {
                const response = await axios.post('/demographic-sharing/update', {
                    demographic_id: demographicId,
                    entity_id: entityId,
                    is_shared: isShared
                });

                if (response.data.success) {
                    // Update local state
                    if (!this.shares[demographicId]) {
                        this.shares[demographicId] = {};
                    }
                    
                    this.shares[demographicId][entityId] = {
                        entity_id: entityId,
                        is_shared: isShared,
                        shared_at: isShared ? new Date().toISOString() : null,
                        revoked_at: isShared ? null : new Date().toISOString()
                    };

                    // Show success message
                    this.$toast.success(response.data.message);
                } else {
                    throw new Error(response.data.message || 'Failed to update sharing preference');
                }
            } catch (error) {
                console.error('Error updating sharing preference:', error);
                this.$toast.error(error.response?.data?.error || 'Failed to update sharing preference');
                
                // Revert the checkbox state
                this.$nextTick(() => {
                    const checkbox = document.getElementById(`share-${demographicId}-${entityId}`);
                    if (checkbox) {
                        checkbox.checked = !isShared;
                    }
                });
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>

<style scoped>
.demographic-sharing-container {
    max-width: 1200px;
    margin: 0 auto;
}

.accordion-button {
    font-size: 1rem;
}
/* Custom switch styling */
.form-switch .form-check-input {
    width: 3em;
    height: 1.5em;
    cursor: pointer;
}

/* Position the white circle (thumb) to the right when checked */
.form-switch .form-check-input:checked::before {
    transform: translateX(1.5em);
}

/* Alternative method using background positioning */
.form-switch .form-check-input:checked {
    background-position: right center;
}

/* Add spacing between switch and label */
.form-switch .form-check-label {
    margin-left: 0.5rem;
    cursor: pointer;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.badge {
    font-size: 0.75em;
}

.alert {
    border-left: 4px solid;
}

.alert-info {
    border-left-color: #0dcaf0;
}

.alert-warning {
    border-left-color: #ffc107;
}

.bg-light {
    background-color: #f8f9fa !important;
}
</style>
