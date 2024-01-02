<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import List from "@/Pages/Tunnels/Partials/List.vue";
import TunnelManager from "@/Pages/Tunnels/Partials/TunnelManager.vue";

defineProps({
    tunnels: Array,
    nodes: Array,
    availableMode: Array,
});
</script>

<template>
    <AuthenticatedLayout title="Tunnels">
        <template #header>
            <h2 class="font-semibold text-xl text-base-content leading-tight">
                Tunnels
            </h2>
        </template>

        <div>
            <div v-if="tunnels.length > 0">
                <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                    <div class="card shadow bg-base-100">
                        <div class="card-body">
                            <List :tunnels="tunnels"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <div class="card shadow bg-base-100">
                    <div class="card-body">
                        <TunnelManager  :tunnels="tunnels" :available-mode="availableMode" :nodes="nodes" ></TunnelManager>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
