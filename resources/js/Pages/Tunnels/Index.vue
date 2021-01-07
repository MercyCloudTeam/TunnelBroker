<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                隧道(Tunnels)
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <tunnel-manager  :tunnels="tunnels" :available-mode="availableMode" :display-a-s-n-select="displayASNSelect"  :asn="asn" :defaultASN="defaultASN" :defaultNode="defaultNode" :nodes="nodes" :default-mode="defaultMode"></tunnel-manager>
            </div>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout'
    import TunnelManager from "@/Pages/Tunnels/TunnelManager";

    export default {
        props: [
            'tunnels',
            'asn',
            'nodes',
            'availableMode',
            'defaultMode',
            'defaultNode',
            'displayASNSelect',
            'defaultASN',
        ],

        components: {
            TunnelManager,
            AppLayout,
        },
    }
</script>
