<script>
import GuestLayout from '@/Layouts/Guest.vue';
import {Head, Link} from '@inertiajs/vue3';
import BreezeNavLink from "@/Components/NavLink.vue";
import BreezeResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import BreezeApplicationLogo from "@/Components/ApplicationLogo.vue";

export default {
    name: 'Login',
    components: {
        BreezeApplicationLogo, Link, BreezeResponsiveNavLink, BreezeNavLink,
        GuestLayout, Head
    },
    props: {
        loginAttempt: Boolean,
        hasAccess: Boolean,
        status: String,
    }
}
</script>

<style scoped>
.btn-bc-gov{
    background-image: none;
    background-color: #003366;
    border: none;
    border-radius: 4px;
    color: white;
    text-align: center;
    text-decoration: none;
    display: block;
    font-weight: 700;
    letter-spacing: 1px;
    cursor: pointer;
    margin-left: auto;
    margin-right: auto;
}
.btn-bc-gov:hover{
    text-decoration: underline !important;
    opacity: 0.80 !important;
}
img.bceid_rev{
    width: 50px;
    display: inline;
}
</style>
<template>
    <GuestLayout>


        <Head title="Log in" />

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>


        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <p class="text-start fw-bold">Student: Login with your BC Services Card</p>
                        <p class="text-start">Applying for StrongBC Future Skills Grant? Click here.</p>
                        <a href="/bcsc-login" class="btn btn-bc-gov mb-3 w-100 fw-light">Student Login with BC Services Card</a>
                        <a class="btn btn-link" href="https://id.gov.bc.ca/account/">Learn how to use your BC Services Card to log in</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <p class="text-start fw-bold">Institution: Login with your BCeID</p>
                        <p class="text-start">BCeID is an online service that makes it possible for you to access government services using a single identifier and password.</p>
                        <a href="/bceid-login" class="btn btn-bc-gov mb-3 w-100 fw-light">Institution Login with
                            <img src="https://www.bceid.ca/assets/images/bceid_rev.png" class="bceid_rev">
                        </a>
                        <div class="text-center">
                            Don't have an account? <a class="btn btn-link" href="https://www.bceid.ca/register/">Register for a Business BCeID</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <p class="text-start fw-bold">PSFS Ministry: Admin staff login only</p>
                        <p class="text-start">Only available to PSFS Ministry admin staff only. Click on Student Login to apply for the grant.</p>
                        <a href="/applogin" class="btn btn-bc-gov mb-3 w-100 fw-light">PSFS Ministry Staff Login</a>
                    </div>
                </div>
            </div>
        </div>


    </GuestLayout>
</template>
