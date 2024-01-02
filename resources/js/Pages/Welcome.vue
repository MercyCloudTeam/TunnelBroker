<script setup>
import { Head, Link } from '@inertiajs/vue3';
import LoadingLayout from "@/Layouts/LoadingLayout.vue";

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>

    <LoadingLayout >
        <div class="container mx-auto">
            <p class="text-6xl text-center text-primary">
                Welcome To TunnelBroker

            </p>
        </div>
    </LoadingLayout>
</template>

<style>

</style>
