<template>
    <form @submit.prevent="nameFormSubmit" class="m-3">

        <!-- Dropdown for Programs -->
        <div v-if="nameForm.filter_type === 'program'" class="row mb-3">
            <BreezeLabel class="col-auto col-form-label" for="programDropdown" value="Programs" />
            <div class="col-12">
                <BreezeSelect id="programDropdown" class="form-control" v-model="nameForm.filter_term">
                    <option v-for="(programName, programGuid) in programYearsData.programs" :key="programGuid" :value="programGuid">
                        {{ programName }}
                    </option>
                </BreezeSelect>
            </div>
        </div>

        <div v-else class="row mb-3">
            <BreezeLabel class="col-auto col-form-label" for="inputFirstName" value="Search term" />
            <div class="col-12">
                <BreezeInput type="text" id="inputFirstName" class="form-control" v-model="nameForm.filter_term" autocomplete="off" />
            </div>
        </div>

        <div class="row mb-3">
            <BreezeLabel class="col-auto col-form-label" for="inputLastName" value="Search type" />
            <div class="col-12">
                <BreezeSelect id="inputType" class="form-control" v-model="nameForm.filter_type">
                    <option value="sin">SIN</option>
                    <option value="fname">First Name</option>
                    <option value="lname">Last Name</option>
                    <option value="email">Email</option>
                    <option value="status">Status</option>
                    <option value="program">Program Name</option>
                </BreezeSelect>
            </div>
        </div>


        <div class="row mb-3">
            <div class="col-12">
                <BreezeButton class="btn btn-success" :class="{ 'opacity-25': nameForm.processing }" :disabled="nameForm.processing">
                    Search
                </BreezeButton>
                <button type="button" @click="clearSearch" v-if="nameForm.form_submitted" class="btn btn-warning float-end text-xs text-white uppercase" :class="{ 'opacity-25': nameForm.processing }" :disabled="nameForm.processing">
                    Clear
                </button>
            </div>
        </div>
    </form>
</template>
<script setup>
import BreezeInput from '@/Components/Input.vue';
import BreezeSelect from '@/Components/Select.vue';
import BreezeLabel from '@/Components/Label.vue';
import BreezeButton from '@/Components/Button.vue';

import {ref, onMounted, useAttrs} from 'vue'
import { useForm } from '@inertiajs/vue3';

// Get the query parameters from the URL using window.location
const getQueryParams = () => {
    const urlParams = new URLSearchParams(window.location.search);
    return {
        filter_term: urlParams.get('filter_term') || '',
        filter_type: urlParams.get('filter_type') || 'snumber',
    };
};

const props = defineProps({
    ftype: [String, null],
    attrs: Object, // Assuming attrs is passed as a prop

});


let searchType = ref('byName');

const blankForm = useForm({});
const nameFormTemplate = {
    filter_term: '',
    filter_type: 'snumber',
    form_submitted: false,
};
const nameForm = useForm(nameFormTemplate);
// const selectedProgram = ref(null); // Track selected program
const programYearsData = useAttrs().programYearsData;

const nameFormSubmit = () => {
    nameForm.get('/institution/claims', {
        onFinish: function() {
            nameForm.reset('filter_term', 'filter_type');
            nameForm.form_submitted = true;
        },
    });
};
const clearSearch = () => {
    blankForm.get('/institution/claims');
};

// Update the form based on the query parameters from the URL
onMounted(() => {
    const queryParams = getQueryParams();
    nameForm.filter_term = queryParams.filter_term;
    nameForm.filter_type = queryParams.filter_type;

    if(nameForm.filter_term != ''){
        nameForm.form_submitted = true;
    }
});

</script>
