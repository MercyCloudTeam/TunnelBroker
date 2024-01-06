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
            <div>
                <div class="max-w-7xl mx-auto py-4 sm:px-6 lg:px-8">
                    <div class="card shadow bg-base-100">
                        <div class="card-body">
                            <p class="text-2xl font-bold">Note:</p>
                            <p>
                                If creation fails, test if your IP quota is sufficient. <br>

                                Except for SIT, ip6ip6,ip6gre all others will be assigned IPV4 interconnect addresses.
                                <br>
                                If there is still an exception, please contact the staff.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="tunnels.length > 0">
                <div class="max-w-7xl mx-auto py-4 sm:px-6 lg:px-8">
                    <div class="card shadow bg-base-100">
                        <div class="card-body">
                            <List :tunnels="tunnels"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="max-w-7xl mx-auto py-4 sm:px-6 lg:px-8">
                <div class="card shadow bg-base-100">
                    <div class="card-body">
                        <TunnelManager  :tunnels="tunnels" :available-mode="availableMode" :nodes="nodes" ></TunnelManager>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
