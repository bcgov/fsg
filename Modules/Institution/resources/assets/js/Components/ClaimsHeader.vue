<template>
    <tr>
        <th scope="col" class="text-nowrap">
            <a href="#" @click="switchSort('sin')">
                <span>SIN</span>
                <em v-if="sortClmn === 'sin' && sortType === 'desc'" class="bi bi-sort-numeric-up"></em>
                <em v-else class="bi bi-sort-numeric-down"></em>
            </a>
        </th>
        <th scope="col" class="text-nowrap">
            <a href="#" @click="switchSort('first_name')">
                <span>First Name</span>
                <em v-if="sortClmn === 'first_name' && sortType === 'desc'" class="bi bi-sort-alpha-up"></em>
                <em v-else class="bi bi-sort-alpha-down"></em>
            </a>
        </th>
        <th scope="col" class="text-nowrap">
            <a href="#" @click="switchSort('last_name')">
                <span>Last Name</span>
                <em v-if="sortClmn === 'last_name' && sortType === 'desc'" class="bi bi-sort-alpha-up"></em>
                <em v-else class="bi bi-sort-alpha-down"></em>
            </a>
        </th>
        <th scope="col" class="text-nowrap">
                <span>Program Name</span>
        </th>
        <th scope="col" class="text-nowrap">
            <a href="#" @click="switchSort('estimated_hold_amount')">
                <span title="Estimated Hold">* Est. Hold</span>
                <em v-if="sortClmn === 'estimated_hold_amount' && sortType === 'desc'" class="bi bi-sort-numeric-up"></em>
                <em v-else class="bi bi bi-sort-numeric-down"></em>
            </a>
        </th>
        <th scope="col" class="text-nowrap">
            <a href="#" @click="switchSort('total_claim_amount')">
                <span>* Claim Total</span>
                <em v-if="sortClmn === 'total_claim_amount' && sortType === 'desc'" class="bi bi-sort-numeric-up"></em>
                <em v-else class="bi bi bi-sort-numeric-down"></em>
            </a>
        </th>

        <th scope="col" class="text-nowrap">
            <a href="#" @click="switchSort('claim_status')">
                <span>Status</span>
                <em v-if="sortClmn === 'claim_status' && sortType === 'desc'" class="bi bi-sort-alpha-up"></em>
                <em v-else class="bi bi-sort-alpha-down"></em>
            </a>
        </th>
        <th scope="col" class="text-nowrap">
            <a href="#" @click="switchSort('outcome_status')">
                <span>Outcome</span>
                <em v-if="sortClmn === 'outcome_status' && sortType === 'desc'" class="bi bi-sort-alpha-up"></em>
                <em v-else class="bi bi-sort-alpha-down"></em>
            </a>
        </th>
        <th scope="col" class="text-nowrap">
            <a href="#" @click="switchSort('created_at')">
                <span>Created At</span>
                <em v-if="sortClmn === 'created_at' && sortType === 'desc'" class="bi bi-sort-numeric-up"></em>
                <em v-else class="bi bi-sort-numeric-down"></em>
            </a>
        </th>
    </tr>
</template>
<script>

import {Inertia} from "@inertiajs/inertia";

export default {
    name: 'ClaimsHeader',
    components: {},
    props: {
        page: Number,
        guid: String
    },

    data() {
        return {
            sortClmn: 'first_name',
            sortType: 'asc',
            url: '',
            path: 'api/fetch/institutions/claims',
        }
    },
    mounted() {
        this.url = new URL(document.location);
        this.sortClmn = this.url.searchParams.get("sort");
        this.sortType = this.url.searchParams.get("direction");

        // if (this.url.pathname === '/dashboard') {
        //     this.path = 'dashboard';
        // }

        let search = this.url.pathname.split('institution-search/');
        if (search.length > 1) {
            this.path = search[1];
        }
    },
    methods: {
        switchSort: function (clmn) {
            if (clmn === this.sortClmn) {
                if (this.sortType === 'asc') {
                    this.sortType = 'desc';
                } else {
                    this.sortType = 'asc';
                }
            } else {
                this.sortClmn = clmn;
                this.sortType = 'asc';
            }
            let data = {
                'direction': this.sortType,
                'sort': this.sortClmn
            };

            // Create a new URLSearchParams object from the current URL
            let params = new URLSearchParams(window.location.search);

            // Append all filter_ parameters to the data object
            params.forEach((value, key) => {
                if (key.startsWith('filter_')) {
                    data[key] = value;
                }
            });

            // Use the `data` object to construct the query string for the request
            let queryString = new URLSearchParams(data).toString();

            let vm = this;
            axios.get('/institution/api/fetch/claims?page=' + this.page + '&direction=' + this.sortType + '&sort=' + this.sortClmn + '&' + queryString)
                .then(function (response) {
                    // vm.claims = response.data.body;
                    vm.$emit('update', {data: response.data.body.data, sortDir: vm.sortType, sortBy: vm.sortClmn});
                })
                .catch(function (error) {
                    // handle error
                    console.log(error);
                });

        },
    }
};
</script>
