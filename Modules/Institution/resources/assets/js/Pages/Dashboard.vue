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

            <div v-if="activeAllocation == null" class="row">
                <div class="col-12 mb-3">
                    <div class="text-center">
                        You have no active allocations for this program year.
                    </div>
                </div>

            </div>
            <div v-else>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card text-center">
                            <div class="card-header">Total Gov Allocation (includes Admin)</div>
                            <div class="card-body display-5 m-4">${{ $formatNumberWithCommas(((activeAllocation && activeAllocation.ts_percent !== undefined ? (100 - activeAllocation.ts_percent) : 20) / 100 * (activeAllocation ? activeAllocation.total_amount : 0)).toFixed(2)) }}</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-center">
                            <div class="card-header">Total Transferrable (includes Admin)</div>
                            <div class="card-body display-5 m-4">${{ $formatNumberWithCommas(((activeAllocation && activeAllocation.ts_percent !== undefined ? activeAllocation.ts_percent : 20) / 100 * (activeAllocation ? activeAllocation.total_amount : 0)).toFixed(2)) }}</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-center">
                            <div class="card-header">Total Allocation (includes Admin)</div>
                            <div class="card-body display-5 m-4">${{ $formatNumberWithCommas(activeAllocation ? activeAllocation.total_amount : 0) }}</div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card text-center">
                            <div class="card-header">Government Hold (includes Admin)</div>
                            <div class="card-body display-5 m-4">${{ $formatNumberWithCommas(holdApps) }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card text-center">
                            <div class="card-header">Government Claimed (includes Admin)</div>
                            <div class="card-body display-5 m-4">${{ $formatNumberWithCommas(claimedApps) }}</div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card text-center">
                            <div class="card-header">Transferable Skills Hold (includes Admin)</div>
                            <div class="card-body display-5 m-4">${{ $formatNumberWithCommas(tsHoldAmount) }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card text-center">
                            <div class="card-header">Transferable Skills Claimed (includes Admin)</div>
                            <div class="card-body display-5 m-4">${{ $formatNumberWithCommas(tsClaimedAmount) }}</div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="card text-center">
                            <div class="card-header">Committed Amount (Claimed + Total)</div>
                            <div class="card-body display-5 m-4">${{ $formatNumberWithCommas((Number(claimedApps) + Number(holdApps) + Number(tsHoldAmount) + Number(tsClaimedAmount)).toFixed(2)) }}</div>
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
        activeAllocation: Object,
        programYear: Object,
        holdApps: Number,
        claimedApps: Number,
        tsHoldAmount: Number,
        tsClaimedAmount: Number
    }
}
</script>
