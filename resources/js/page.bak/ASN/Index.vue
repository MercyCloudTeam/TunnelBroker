<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import TunnelManager from "@/page.bak/Tunnels/Partials/TunnelManager.vue";
import List from "@/page.bak/ASN/Partials/List.vue";
import Add from "@/page.bak/ASN/Partials/Add.vue";

defineProps({
    asn: Array,
    nodes: Array,
    availableMode: Array,
});
</script>

<template>
    <AppLayout title="ASN">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                ASN
            </h2>
        </template>

        <div>
            <div v-if="asn.length > 0">
                <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                    <List :asn="asn"/>
                </div>
            </div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <Add />
            </div>
        </div>
    </AppLayout>
</template>
