<script setup>
import { Head, Link } from '@inertiajs/inertia-vue3';
import Header from "@/Components/Header.vue";
import NodeCard from "@/Components/NodeCard.vue";
import { BoltIcon, CubeIcon, GlobeAltIcon, ScaleIcon } from '@heroicons/vue/24/outline'
const features = [
    {
        name: 'Global Nodes',
        description:
            'Multiple network nodes around the world for comfortable access to the Internet.',
        icon: GlobeAltIcon,
    },
    {
        name: 'Fair price',
        description:
            'Our free plans are basically enough to satisfy most of our clients.\n' +
            'At the same time we offer paid plans in a very cost effective way',
        icon: ScaleIcon,
    },
    {
        name: 'Fast network',
        description:
            'High-speed network, fast, very fast, ultra-fast access to server nodes',
        icon: BoltIcon,
    },
    {
        name: 'Multi-protocol support',
        description:
            'We support WireGuard, SIT, GRE, VXLAN, IPIP and other access protocols',
        icon: CubeIcon,
    },
]
defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    nodes: Array,
});

</script>

<template>
    <Head title="Welcome" />
    <Header />

    <div class="bg-white py-24 sm:py-32 lg:py-40">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="sm:text-center">
                <h2 class="text-lg font-semibold leading-8 text-indigo-600">TunnelBroker</h2>
                <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">A better open source TunnelBroker project</p>
                <p class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-gray-600">An open source, support for multiple access protocols, feature-rich TunnelBroker project.</p>
            </div>

            <div class="mt-20 max-w-lg sm:mx-auto md:max-w-none">
                <div class="grid grid-cols-1 gap-y-16 md:grid-cols-2 md:gap-x-12 md:gap-y-16">
                    <div v-for="feature in features" :key="feature.name" class="relative flex flex-col gap-6 sm:flex-row md:flex-col lg:flex-row">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500 text-white sm:shrink-0">
                            <component :is="feature.icon" class="h-8 w-8" aria-hidden="true" />
                        </div>
                        <div class="sm:min-w-0 sm:flex-1">
                            <p class="text-lg font-semibold leading-8 text-gray-900">{{ feature.name }}</p>
                            <p class="mt-2 text-base leading-7 text-gray-600">{{ feature.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="bg-gray-100 py-24 sm:py-32 lg:py-40">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <NodeCard v-for="node in nodes" :key="node.id" :node="node" />
        </div>
    </div>

</template>
