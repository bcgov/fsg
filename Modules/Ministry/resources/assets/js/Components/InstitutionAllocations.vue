<template>
    <div class="card">
        <div class="card-header">
            Institution Allocations
            <!--            <template>-->
<!--                <span class="badge rounded-pill text-bg-primary me-1">Active Allocation Total: {{ results.active_allocation.total_amount_formatted}}</span>-->
<!--                <span class="badge rounded-pill text-bg-primary me-1">Used Allocation: {{ results.active_allocation.used_amount }}</span>-->
<!--            </template>-->
            <button type="button" class="btn btn-success btn-sm float-end" @click="openNewForm">New Allocation</button>
        </div>
        <div class="card-body">
            <div v-if="results.allocations != null && results.allocations.length > 0" class="table-responsive pb-3">
                <table class="table table-striped">
                    <thead>
                    <InstitutionAllocationsHeader></InstitutionAllocationsHeader>
                    </thead>
                    <tbody>
                    <template v-for="(row, i) in results.allocations">
                        <tr v-if="row.py !== null">
                            <td v-if="row.status === 'active' || row.status === 'new'"><a href="#" @click="openEditForm(row)">{{ row.py.start_date }}</a></td>
                            <td v-else>{{ row.py.start_date }}</td>
                            <td>{{ row.py.end_date }}</td>
                            <td>${{ $formatNumberWithCommas(row.total_amount_formatted) }}</td>
                            <td>${{ $formatNumberWithCommas(((row.ts_percent ?? 20) / 100 * row.total_amount).toFixed(2)) }}</td>
                            <td>${{ $formatNumberWithCommas(row.claimed) }}</td>
                            <td>
                                <span v-if="row.status === 'new'" class="badge rounded-pill text-bg-info">New</span>
                                <span v-else-if="row.status === 'active'" class="badge rounded-pill text-bg-success">Active</span>
                                <span v-else class="badge rounded-pill text-bg-danger">Inactive</span>
                            </td>
                            <td>{{ formatDate(row.created_at) }}</td>
                        </tr>
                    </template>
                    </tbody>
                </table>
            </div>
            <h1 v-else class="lead">No results</h1>
        </div>
    </div>

    <div v-if="editAllocation == ''" class="modal modal-lg fade" id="newInstAllocationModal" tabindex="-1"
         aria-labelledby="newInstAllocationModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newInstAllocationModalLabel">New Institution Allocation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <InstitutionAllocationCreate v-bind="$attrs" :program-years="programYears" :results="results" :activeInstAllocation="activeInstAllocation"/>
            </div>
        </div>
    </div>
    <div v-if="editAllocation != ''" class="modal modal-lg fade" id="editInstAllocationModal" tabindex="0"
         aria-labelledby="editInstAllocationModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editInstAllocationModalLabel">Edit Institution Allocation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <InstitutionAllocationEdit v-bind="$attrs" :program-years="programYears" @close="closeEditForm"
                                           :allocation="editAllocation"
                                    :results="results" :activeInstAllocation="activeInstAllocation"/>
            </div>
        </div>
    </div>
</template>
<script>
import {Link} from '@inertiajs/vue3';
import InstitutionAllocationsHeader from "./InstitutionAllocationsHeader";
import InstitutionAllocationCreate from "./InstitutionAllocationCreate";
import InstitutionAllocationEdit from "./InstitutionAllocationEdit";

export default {
    name: 'InstitutionAllocations',
    components: {
        Link, InstitutionAllocationsHeader,
        InstitutionAllocationCreate, InstitutionAllocationEdit
    },
    props: {
        results: Object,
        programYears: Object
    },
    data() {
        return {
            editAllocation: '',
            allocationStat: '',
            activeInstAllocation: null,
            allowProgramAllocation: false
        }
    },
    methods: {
        openNewForm: function () {
            setTimeout(function () {
                $("#newInstAllocationModal").modal('show');
            }, 10);
        },

        openEditForm: function (allocation) {
            this.editAllocation = allocation;
            setTimeout(function () {
                $("#editInstAllocationModal").modal('show');
            }, 10);
        },
        closeEditForm: function () {
            $("#editInstAllocationModal").modal('hide');
            this.editAllocation = '';
        },
        formatDate: function (value) {
            if (value !== undefined && value !== '') {
                let date = value.split("T");

                return date[0];
            }
            return value;
        },
        // fetchAllocationStats: function () {
        //     let vm = this;
        //     let data = {
        //         institution_guid: this.results.guid,
        //     }
        //     axios.post('/ministry/api/fetch/allocationStats', data)
        //         .then(function (response) {
        //             vm.allocationStat = response.data.body;
        //         })
        //         .catch(function (error) {
        //             // handle error
        //             console.log(error);
        //         });
        // }
    },
    mounted() {
        // this.fetchAllocationStats();
        //
        // //look for inst active allocation
        // for (const allocation of this.results.allocations) {
        //     if (allocation.program_guid === null && allocation.status) {
        //         this.activeInstAllocation = allocation;
        //         break;
        //     }
        // }
    }
}
</script>
