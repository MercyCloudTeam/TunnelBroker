<script setup>
import { Head, Link } from '@inertiajs/inertia-vue3';
import Header from "@/Components/Header.vue";
import NodeCard from "@/Components/NodeCard.vue";

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    nodes: Array,
});

</script>

<template>
    <Head title="Welcome" />
<!--    <Header />-->
    <NodeCard v-for="node in nodes" :key="node.id" :node="node" />
<!--    <div class="bg-gray-200">-->
<!--        <div v-if="canLogin" class="hidden fixed top-0 right-0 px-6 py-4 sm:block">-->
<!--            <Link v-if="$page.props.user" :href="route('dashboard')" class="text-sm text-gray-700 dark:text-gray-500 ">Dashboard</Link>-->
<!--            <template v-else>-->
<!--                <Link :href="route('login')" class="text-sm text-gray-700 dark:text-gray-500 ">Log in</Link>-->
<!--                <Link v-if="canRegister" :href="route('register')" class="ml-4 text-sm text-gray-700 dark:text-gray-500 ">Register</Link>-->
<!--            </template>-->
<!--        </div>-->
<!--        <div class="max-w-8xl mx-auto sm:px-8 lg:px-8">-->
<!--            <div class="flex justify-center mt-4 sm:items-center sm:justify-between">-->
<!--                <div class="ml-4 text-center text-sm text-gray-500 sm:text-right sm:ml-0">-->
<!--                    TunnelBroker-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
</template>
