<style scoped>
[type='checkbox']:checked, [type='radio']:checked {
    background-size: initial;
}
</style>
<template>
    <Head title="Students" />

    <AuthenticatedLayout v-bind="$attrs">

            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-header">
                                Student Search
                            </div>
                            <div class="card-body">
                                <StudentsSearchBox />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="card mb-3">
                            <div class="card-header">
                                Students
                            </div>
                            <div class="card-body">
                                <div v-if="results != null && results.data.length > 0" class="table-responsive pb-3">
                                    <table class="table table-striped">
                                        <thead>
                                            <StudentsHeader></StudentsHeader>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(row, i) in results.data">
                                            <td><Link :href="'/ministry/students/' + row.id">{{ row.last_name }}</Link></td>
                                            <td>{{ row.first_name }}</td>
                                            <td>{{ row.dob }}</td>
                                            <td>{{ row.sin }}</td>
                                            <td>{{ row.email }}</td>
                                            <td>${{ row.total_grant }}</td>
                                            <td>
                                                <span v-if="row.claims != null && row.claims.length > 0">{{ row.claims.length }}</span>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <Pagination :links="results.links" :active-page="results.current_page" />
                                </div>
                                <h1 v-else class="lead">No results</h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

    </AuthenticatedLayout>

</template>
<script>
import AuthenticatedLayout from '../Layouts/Authenticated.vue';
import StudentsSearchBox from '../Components/StudentsSearch.vue';
import StudentsHeader from '../Components/StudentsHeader.vue';
import Pagination from "@/Components/Pagination";
import { Link, Head } from '@inertiajs/vue3';

export default {
    name: 'Students',
    components: {
        AuthenticatedLayout, StudentsSearchBox, StudentsHeader, Head, Link, Pagination
    },
    props: {
        results: Object,
    }
}
</script>
