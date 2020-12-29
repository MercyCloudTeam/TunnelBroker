<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                ASN验证
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <create-a-s-n-form></create-a-s-n-form>
                <ASNManager :asn="asn"></ASNManager>
            </div>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout'
    import CreateASNForm from './CreateASNForm'
    import ASNManager from "@/Pages/ASN/ASNManager";

    export default {
        props: ['asn'],

        components: {
            ASNManager,
            AppLayout,
            CreateASNForm,
        },
    }
</script>
