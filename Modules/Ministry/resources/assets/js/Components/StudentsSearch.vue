<template>


    <form @submit.prevent="nameFormSubmit" class="m-3">
                <div class="row mb-3">
                    <BreezeLabel class="col-auto col-form-label" for="inputEmail" value="Email" />
                    <div class="col-auto">
                        <BreezeInput type="email" id="inputEmail" class="form-control" v-model="emailForm.filter_email" />
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-auto">
                        <BreezeButton class="btn btn-success" :class="{ 'opacity-25': emailForm.processing }" :disabled="emailForm.processing">
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

const props = defineProps({
    ftype: [String, null],
});


let searchType = ref('byEmail');

const nameFormTemplate = {
    filter_email: '',
    filter_type: props.ftype ?? 'active',
};
const emailForm = useForm(nameFormTemplate);
const nameFormSubmit = () => {
    emailForm.get('/ministry/students', {
        onFinish: () => emailForm.reset('inputEmail'),
    });
};


</script>
