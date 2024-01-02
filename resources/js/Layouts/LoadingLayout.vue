<script setup>
import { Link } from '@inertiajs/vue3';
import Footer from "@/Components/Footer.vue";
</script>

<template>
    <div>
    </div>

    <div class="flex flex-col items-center pt-6">
        <div>
            <slot />
        </div>
    </div>
    <Footer />
</template>
