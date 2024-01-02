<script setup>
import { Link } from '@inertiajs/vue3';

</script>


<template>

<div class="mt-4 mb-4">
    <p class="text-2xl text-black mb-2">Links</p>
    <div class="flex gap-4 justify-items-center items-center text-center">
        <Link :href="route('login')" class="underline text-sm text-gray-600 hover:text-gray-900">
            Login
        </Link>
        <Link :href="route('register')" class="underline text-sm text-gray-600 hover:text-gray-900">
            Register
        </Link>
        <Link :href="route('password.request')" class="underline text-sm text-gray-600 hover:text-gray-900">
            Forgot Password
        </Link>

    </div>
</div>

</template>
