<template>
    <tr>
        <th scope="col" style="min-width: 120px;">
            <span>Start Date</span>
        </th>
        <th scope="col" style="min-width: 120px;">
            <span>End Date</span>
        </th>
        <th scope="col" style="min-width: 80px;">
            <span>Total Allocation</span>
        </th>
        <th scope="col" style="min-width: 80px;">
            <span>Used Allocation</span>
        </th>
        <th scope="col">
            <span>Status</span>
        </th>
        <th scope="col" style="min-width: 120px;">
            <span>Created At</span>
        </th>
    </tr>
</template>
<script>

import {Inertia} from "@inertiajs/inertia";

export default {
    name: 'InstitutionAllocationsHeader',
    components: {},
    props: {},
    data() {
        return {
            sortClmn: 'start_date',
            sortType: 'asc',
            url: '',
            path: 'allocations',
        }
    },
    mounted() {

    },
    methods: {

    }
};
</script>
