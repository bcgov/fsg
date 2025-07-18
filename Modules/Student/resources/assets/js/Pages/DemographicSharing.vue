<template>
    <Head title="Demographics Sharing" />

    <AuthenticatedLayout v-bind="$attrs">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <Link href="/" class="text-decoration-none">Dashboard</Link>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                Demographics Sharing
                            </li>
                        </ol>
                    </nav>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h4 class="card-title mb-2">
                                                <i class="fas fa-share-alt me-2"></i>
                                                Demographics Sharing
                                            </h4>
                                            <p class="card-text mb-0">
                                                Manage your demographic information sharing preferences with authorized entities.
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="text-center">
                                                        <h3 class="mb-0">{{ totalSharedCount }}</h3>
                                                        <small>Shared Items</small>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="text-center">
                                                        <h3 class="mb-0">{{ shareableEntities.length }}</h3>
                                                        <small>Available Entities</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <DemographicSharing 
                                :demographics="demographics"
                                :shareable-entities="shareableEntities"
                                :existing-shares="existingShares"
                                :student="student"
                            />
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Important Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Data Usage</h6>
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-check text-success me-2"></i>Your data is only shared with entities you explicitly approve</li>
                                                <li><i class="fas fa-check text-success me-2"></i>You can revoke sharing permissions at any time</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Shared data is used for research and program improvement purposes</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Your Rights</h6>
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-shield-alt text-info me-2"></i>Right to know what data is shared</li>
                                                <li><i class="fas fa-shield-alt text-info me-2"></i>Right to revoke sharing at any time</li>
                                                <li><i class="fas fa-shield-alt text-info me-2"></i>Right to request data deletion</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <div class="alert alert-info mb-0">
                                                <i class="fas fa-envelope me-2"></i>
                                                Questions about data sharing? Contact us at 
                                                <a href="mailto:privacy@gov.bc.ca" class="alert-link">privacy@gov.bc.ca</a>
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
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '../Layouts/Authenticated.vue';
import { Head, Link } from '@inertiajs/vue3';
import DemographicSharing from '../Components/DemographicSharing.vue';

export default {
    name: 'DemographicSharingPage',
    components: {
        AuthenticatedLayout,
        Head,
        Link,
        DemographicSharing
    },
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
    computed: {
        totalSharedCount() {
            let count = 0;
            Object.values(this.existingShares).forEach(demographicShares => {
                Object.values(demographicShares).forEach(share => {
                    if (share.is_shared) {
                        count++;
                    }
                });
            });
            return count;
        }
    }
}
</script>

<style scoped>
.breadcrumb {
    background-color: transparent;
    padding: 0;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.bg-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.alert-info {
    border-left: 4px solid #0dcaf0;
}

.list-unstyled li {
    padding: 0.25rem 0;
}
</style>
