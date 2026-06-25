<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout v-bind="$attrs">



        <div class="container">
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="display-5">{{ results.name }}</div>
                            <p>Welcome {{$attrs.auth.user.first_name}} {{$attrs.auth.user.last_name}}</p>
                            <br/>
                            <h4 class="fw-light">Active Program Year: <strong>{{ programYear.start_date }} to {{ programYear.end_date }}</strong></h4>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="!allocationSummaries || allocationSummaries.length === 0" class="row">
                <div class="col-12 mb-3">
                    <div class="text-center">
                        You have no active allocations for this program year.
                    </div>
                </div>

            </div>
            <div v-else>
                <div v-for="alloc in allocationSummaries" :key="alloc.guid" class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Total Allocation (includes Admin)</span>
                        <span class="fw-bold">${{ $formatNumberWithCommas(Number(alloc.total_amount).toFixed(2)) }}</span>
                    </div>
                    <div class="card-body">
                        <!-- New per funding type cards -->
                        <div v-if="alloc.has_funding_types" class="row">
                            <div v-for="ft in alloc.funding_types" :key="ft.funding_type" class="col-md-4 mb-3">
                                <div class="card h-100 text-center">
                                    <div class="card-header fw-semibold">{{ ft.funding_type }}</div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between"><span>Allocation</span><strong>${{ $formatNumberWithCommas(Number(ft.allocated).toFixed(2)) }}</strong></div>
                                        <div class="d-flex justify-content-between"><span>Holds</span><strong>${{ $formatNumberWithCommas(Number(ft.hold).toFixed(2)) }}</strong></div>
                                        <div class="d-flex justify-content-between"><span>Claimed</span><strong>${{ $formatNumberWithCommas(Number(ft.claimed).toFixed(2)) }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Legacy allocation without funding types -->
                        <div v-else class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card text-center">
                                    <div class="card-header">Hold (includes Admin)</div>
                                    <div class="card-body display-6 m-3">${{ $formatNumberWithCommas(Number(alloc.legacy_hold || 0).toFixed(2)) }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card text-center">
                                    <div class="card-header">Claimed (includes Admin)</div>
                                    <div class="card-body display-6 m-3">${{ $formatNumberWithCommas(Number(alloc.legacy_claimed || 0).toFixed(2)) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card text-center">
                            <div class="card-header">Committed Amount (Claimed + Hold)</div>
                            <div class="card-body display-5 m-4">${{ $formatNumberWithCommas(Number(committedAmount).toFixed(2)) }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card text-center">
                            <div class="card-header">Claims Waiting for Outcome Reporting</div>
                            <div class="card-body display-5 m-4">{{ $formatNumberWithCommas((Number(waitingOutcome)).toFixed(2)) }}</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </AuthenticatedLayout>

</template>
<script>
import AuthenticatedLayout from '../Layouts/Authenticated.vue';
import { Link, Head } from '@inertiajs/vue3';
import DashboardProfile from "../Components/DashboardProfile.vue";
import DashboardApplications from "../Components/DashboardApplications.vue";
import DashboardMenu from "../Components/DashboardMenu.vue";

export default {
    name: 'Dashboard',
    components: {
        DashboardMenu, DashboardProfile, DashboardApplications,
        AuthenticatedLayout, Head, Link
    },
    props: {
        results: Object,
        programYear: Object,
        allocationSummaries: Array,
        waitingOutcome: Number
    },
    computed: {
        committedAmount() {
            return (this.allocationSummaries || []).reduce((total, alloc) => {
                if (alloc.has_funding_types) {
                    return total + (alloc.funding_types || []).reduce((sum, ft) => {
                        return sum + Number(ft.hold || 0) + Number(ft.claimed || 0);
                    }, 0);
                }
                return total + Number(alloc.legacy_hold || 0) + Number(alloc.legacy_claimed || 0);
            }, 0);
        }
    }
}
</script>
