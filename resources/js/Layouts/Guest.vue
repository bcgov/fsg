<script>
import ApplicationLoginLogo from '@/Components/ApplicationLoginLogo.vue';
import {Link} from "@inertiajs/vue3";
import BreezeApplicationLogo from "@/Components/ApplicationLogo.vue";

export default {
    name: 'GuestLayout',
    components: {
        BreezeApplicationLogo, Link,
        ApplicationLoginLogo
    }
}
</script>
<style scoped>
.bg-bc-gov {
	background: none;
	background-color: #036;
	/*font-family: 'BCSans', 'Noto Sans', Verdana, Arial, sans-serif;*/
	color: #036;
}
</style>
<style scoped>
nav.navbar{
    background-color: #003366;
    border: none;
    border-bottom: 2px solid #fcba19;
    z-index: 99;
}
</style>
<template>
    <nav class="navbar navbar-expand-lg navbar-dark shadow">
        <div class="container-fluid">
            <Link class="navbar-brand" href="/">
                <BreezeApplicationLogo width="126" height="34" class="d-inline-block align-text-top me-3" />
                <span class="d-none d-lg-inline fw-light">StrongerBC Future Skills Grant</span>
            </Link>
        </div>
    </nav>
	<div class="min-h-screen bg-gray-100 text-center flex flex-col sm:justify-center items-center sm:pt-0">
		<div class="w-full sm:max-w-md py-4 overflow-hidden sm:rounded-lg">
			<div class="inline-flex mb-5">
				<a href="/login">
					<ApplicationLoginLogo class="inline-flex" />
				</a>
			</div>
            <slot />
		</div>
	</div>
</template>
