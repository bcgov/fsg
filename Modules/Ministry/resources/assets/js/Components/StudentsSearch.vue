<template>


    <form @submit.prevent="searchFormSubmit" class="m-3">
        <div class="row mb-3">
            <BreezeLabel class="col-auto col-form-label" for="inputFirstName" value="Search term" />
            <div class="col-12">
                <BreezeInput type="text" id="inputFirstName" class="form-control" v-model="searchForm.filter_term" autocomplete="off" />
            </div>
        </div>
        <div class="row mb-3">
            <BreezeLabel class="col-auto col-form-label" for="inputLastName" value="Search type" />
            <div class="col-12">
                <BreezeSelect id="inputType" class="form-control" v-model="searchForm.filter_type">
                    <option value="sin">SIN</option>
                    <option value="fname">First Name</option>
                    <option value="lname">Last Name</option>
                    <option value="email">Email</option>
                </BreezeSelect>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-auto">
                <BreezeButton class="btn btn-success" :class="{ 'opacity-25': searchForm.processing }" :disabled="searchForm.processing">
                    Search
                </BreezeButton>
            </div>
        </div>
    </form>
</template>
<script setup>
import BreezeInput from '@/Components/Input.vue';
import BreezeLabel from '@/Components/Label.vue';
import BreezeButton from '@/Components/Button.vue';

import { ref, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3';
import BreezeSelect from "@/Components/Select.vue";

const props = defineProps({
    ftype: [String, null],
});


let searchType = ref('byEmail');

const searchFormTemplate = {
    filter_term: '',
    filter_type: 'snumber',
};
const searchForm = useForm(searchFormTemplate);
const searchFormSubmit = () => {
    searchForm.get('/ministry/students', {
        onFinish: () => searchForm.reset('filter_term', 'filter_type'),
    });
};


</script>
