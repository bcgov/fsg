<template>
    <tr>
        <th scope="col" class="text-nowrap">
            <span>Program</span>
        </th>
        <th scope="col" class="text-nowrap">
            <span>Institution</span>
        </th>
        <th scope="col" class="text-nowrap">
                <span>Est. Hold</span>
        </th>
        <th scope="col" class="text-nowrap">
            <span title="Program Fee">Prog. Fee</span>
        </th>
        <th scope="col" class="text-nowrap">
            <span title="Registration Fee">Reg. Fee</span>
        </th>
        <th scope="col" class="text-nowrap">
            <span title="Materials Fee">Mat. Fee</span>
        </th>
        <th scope="col" class="text-nowrap">
            <span>Status</span>
        </th>
    </tr>
</template>
<script>

import {Inertia} from "@inertiajs/inertia";

export default {
    name: 'StudentClaimsHeader',
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
            path: 'api/fetch/students/claims-by-student',
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
            axios.get('/ministry/api/fetch/students/claims-by-course?in=' + this.guid + '&page=' + this.page + '&direction=' + this.sortType + '&sort=' + this.sortClmn)
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
