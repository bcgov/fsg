<template>
    <tr>

        <th scope="col" class="text-nowrap">
                <span>Program Name</span>
        </th>
        <th scope="col" class="text-nowrap">
                <span>Institution Name</span>
        </th>
        <th scope="col" class="text-nowrap">
            <span>Estimated Hold</span>
        </th>
        <th scope="col" class="text-nowrap">
            <span>Claim Total</span>
        </th>
<!--        <th scope="col" class="text-nowrap">-->
<!--            <span>Claims</span>-->
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
    name: 'StudentApplicationsHeader',
    components: {},
    props: {
        page: Number,
        guid: String
    },

    data() {
        return {
            sortClmn: 'active_status',
            sortType: 'asc',
            url: '',
            path: 'api/fetch/students/applications',
        }
    },
    mounted() {
        this.url = new URL(document.location);
        this.sortClmn = this.url.searchParams.get("sort");
        this.sortType = this.url.searchParams.get("direction");


        let search = this.url.pathname.split('student-search/');
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
            axios.get('/student/api/fetch/students/applications?page=' + this.page + '&direction=' + this.sortType + '&sort=' + this.sortClmn)
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
