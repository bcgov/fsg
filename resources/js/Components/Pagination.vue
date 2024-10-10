<template>
    <div v-if="links.length > 3">
        <div class="d-flex flex-row justify-content-center -mb-1">
            <template v-for="(link, key) in links">
                <div v-if="link.url === null" :key="key" class="btn btn-light border m-1" v-html="link.label" />
                <template v-else :key="`link-${key}`">
                    <Link v-if="Number(sortBy) === 0 && Number(sortDir) === 0" class="btn btn-light btn-link border m-1"
                          :class="{ 'disabled': (link.label == activePage) }" :href="link.url" v-html="link.label" />
                    <Link v-else class="btn btn-light btn-link border m-1"
                          :class="{ 'disabled': (link.label == activePage) }" :href="link.url + '&direction=' + sortDir + '&sort=' + sortBy" v-html="link.label" />
                </template>
            </template>
        </div>
    </div>
</template>

<script>
import { Link } from '@inertiajs/vue3';

export default {
    components: {
        Link,
    },
    props: {
        links: Array,
        activePage: Number,
        sortBy: String|null,
        sortDir: String|null,
    },
    data() {
        return {
        }
    },
    mounted() {
        // let data = {
        //     'direction': this.sortType,
        //     'sort': this.sortClmn
        // };
        //
        // // Create a new URLSearchParams object from the current URL
        // let params = new URLSearchParams(window.location.search);
        //
        // // Append all filter_ parameters to the data object
        // params.forEach((value, key) => {
        //     if (key.startsWith('filter_')) {
        //         data[key] = value;
        //     }
        // });
        //
        // // Use the `data` object to construct the query string for the request
        // this.queryString = new URLSearchParams(data).toString();

    },
}
</script>
