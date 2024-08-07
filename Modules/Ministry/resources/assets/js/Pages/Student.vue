<template>
    <Head title="Student" />

    <AuthenticatedLayout v-bind="$attrs">

            <div v-if="results != null" class="container-fluid">

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="card">
                            <div class="card-header">
                                Student Menu
                            </div>
                            <div class="card-body">
                                <StudentMenu :page="page" :id="results.id" :first_name="results.first_name" :last_name="results.last_name" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9 mb-3">
                        <StudentDetails v-bind="$attrs" v-if="page === 'details'" :results="results"></StudentDetails>
                        <StudentClaims v-bind="$attrs" v-if="page === 'claims'" :results="results"></StudentClaims>
                    </div>
                </div>
            </div>
    </AuthenticatedLayout>

</template>
<script>
import AuthenticatedLayout from '../Layouts/Authenticated.vue';
import { Head, Link } from '@inertiajs/vue3';
import StudentMenu from "../Components/StudentMenu";
import StudentDetails from "../Components/StudentDetails";
import StudentClaims from "../Components/StudentClaims";

export default {
    name: 'Student',
    components: {
        StudentMenu,
        AuthenticatedLayout, Head, Link, StudentDetails, StudentClaims
    },
    props: {
        results: Object,
        page: String,
        countries: Object,
        programYears: Object,
    },
    mounted() {
    }
}
</script>
