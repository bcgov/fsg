<style scoped>
[type='checkbox']:checked, [type='radio']:checked {
    background-size: initial;
}
</style>
<template>
    <Head title="Maintenance" />

    <BreezeAuthenticatedLayout v-bind="$attrs">

            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="card">
                            <div class="card-header">
                                Maintenance Menu
                            </div>
                            <div class="card-body">
                                <MaintenanceMenu :page="page" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9 mb-3">
                        <MaintenanceStaff v-if="page === 'staff'" :results="results"></MaintenanceStaff>
                        <MaintenanceStaffEdit v-if="page === 'staff-edit'" :results="results"></MaintenanceStaffEdit>
                        <MaintenanceUtils v-if="page === 'utils'" :results="results" :categories="categories"></MaintenanceUtils>
                        <MaintenanceProgramYears v-if="page === 'program_years'" :results="results"></MaintenanceProgramYears>
                        <MaintenanceFaq v-if="page === 'faqs'" :results="results"></MaintenanceFaq>
                    </div>
                </div>
            </div>
    </BreezeAuthenticatedLayout>

</template>
<script>
import BreezeAuthenticatedLayout from '../Layouts/Authenticated.vue';
import { Head, Link } from '@inertiajs/vue3';
import MaintenanceMenu from "../Components/MaintenanceMenu";
import MaintenanceStaff from "../Components/MaintenanceStaff";
import MaintenanceStaffEdit from "../Components/MaintenanceStaffEdit";
import MaintenanceUtils from "../Components/MaintenanceUtils";
import MaintenanceProgramYears from "../Components/MaintenanceProgramYears";
import MaintenanceFaq from "../Components/MaintenanceFaq";

export default {
    name: 'Maintenance',
    components: {
        MaintenanceFaq,
        MaintenanceUtils,
        MaintenanceMenu,
        BreezeAuthenticatedLayout, Head, Link, MaintenanceStaff, MaintenanceStaffEdit, MaintenanceProgramYears
    },
    props: {
        results: Object,
        categories: Object|null,
        page: String,
    },
    data() {
        return {
        }
    },
}
</script>
