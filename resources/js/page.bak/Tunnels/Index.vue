<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import List from "@/page.bak/Tunnels/Partials/List.vue";
import TunnelManager from "@/page.bak/Tunnels/Partials/TunnelManager.vue";

defineProps({
    tunnels: Array,
    nodes: Array,
    availableMode: Array,
});
</script>

<template>
    <AppLayout title="Tunnels">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tunnels
            </h2>
        </template>

        <div>
            <div v-if="tunnels.length > 0">
                <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                    <List :tunnels="tunnels"/>
                </div>
            </div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <TunnelManager  :tunnels="tunnels" :available-mode="availableMode" :nodes="nodes" ></TunnelManager>
            </div>
        </div>
    </AppLayout>
</template>
