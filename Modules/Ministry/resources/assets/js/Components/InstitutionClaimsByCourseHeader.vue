<template>
    <tr>
        <th scope="col" class="text-nowrap">
            <a href="#" @click="switchSort('id')">
                <span>ID</span>
                <em v-if="sortClmn === 'id' && sortType === 'desc'" class="bi bi-sort-numeric-up"></em>
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
            <a href="#" @click="switchSort('course_name')">
                <span>Program Name</span>
                <em v-if="sortClmn === 'course_name' && sortType === 'desc'" class="bi bi-sort-alpha-up"></em>
                <em v-else class="bi bi-sort-alpha-down"></em>
            </a>
        </th>
        <th scope="col" class="text-nowrap">
            <a href="#" @click="switchSort('estimated_hold_amount')">
                <span title="Estimated Hold + Admin Fee">Est. Hold incl. fee</span>
                <em v-if="sortClmn === 'estimated_hold_amount' && sortType === 'desc'" class="bi bi-sort-numeric-up"></em>
                <em v-else class="bi bi bi-sort-numeric-down"></em>
            </a>
        </th>
        <th scope="col" class="text-nowrap">
            <a href="#" @click="switchSort('total_claim_amount')">
                <span>Claim Total</span>
                <em v-if="sortClmn === 'total_claim_amount' && sortType === 'desc'" class="bi bi-sort-numeric-up"></em>
                <em v-else class="bi bi bi-sort-numeric-down"></em>
            </a>
        </th>
<!--        <th scope="col" class="text-nowrap">-->
<!--            <span>Student Claims</span>-->
<!--        </th>-->
        <th scope="col" class="text-nowrap">
            <a href="#" @click="switchSort('claim_status')">
                <span>Status</span>
                <em v-if="sortClmn === 'claim_status' && sortType === 'desc'" class="bi bi-sort-alpha-up"></em>
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
    name: 'InstitutionClaimsByCourseHeader',
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
            path: 'api/fetch/institutions/claims-by-course',
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

            //if the url has filter_x params then append them all
            this.url.searchParams.forEach((value, key) => {
                let filter = key.split('filter_');
                if(filter.length > 1) {
                    data[key] = value;
                }
            });

            // Inertia.get('/ministry/' + this.path, data, {
            //     preserveState: true
            // });

            let vm = this;
            axios.get('/ministry/api/fetch/institutions/claims-by-course?in=' + this.guid + '&page=' + this.page + '&direction=' + this.sortType + '&sort=' + this.sortClmn)
                .then(function (response) {
                    // vm.claims = response.data.body;
                    vm.$emit('update', response.data.body);
                })
                .catch(function (error) {
                    // handle error
                    console.log(error);
                });

        },
    }
};
</script>
