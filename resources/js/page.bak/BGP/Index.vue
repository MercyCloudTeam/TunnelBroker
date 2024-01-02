<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Add from "@/page.bak/BGP/Partials/Add.vue";
import List from "@/page.bak/BGP/Partials/List.vue";

defineProps({
    asn: Array,
    tunnels: Array,
    availableMode: Array,
    bgp: Array,
});
</script>

<template>
    <AppLayout title="Tunnels">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                BGP
            </h2>
        </template>

        <div>
            <div v-if="bgp.length > 0">
                <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                    <List :bgp="bgp"/>
                </div>
            </div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <Add :tunnels="tunnels" :asn="asn" />
<!--                <TunnelManager  :tunnels="tunnels" :available-mode="availableMode" :nodes="nodes" ></TunnelManager>-->
            </div>
        </div>
    </AppLayout>
</template>
