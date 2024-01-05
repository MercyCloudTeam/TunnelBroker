<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    my: Array,
    plan: Array,
    userPlan: Array,
    usage: Array,
});


const humanTrafficDisplay = (bytes) => {
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB']
    if (bytes === 0) return '0 Byte'
    const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)), 10)
    if (i === 0) return `${bytes} ${sizes[i]}`
    return `${(bytes / (1024 ** i)).toFixed(1)} ${sizes[i]}`
}

</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-base-content  leading-tight">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-base-200 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="overflow-hidden  shadow sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6">
                            <h3 class="text-lg font-medium leading-6 ">Welcome to back {{my.name}} !</h3>
                            <p class="mt-1 max-w-2xl text-sm ">Personal .</p>
                        </div>
                        <div class="border-t border-base-200 ">
                            <div>
                                <div class="bg-base-300 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6" v-if="plan !== undefined">
                                    <dt class="text-sm font-medium ">Plan</dt>
                                    <dd class="mt-1 text-sm  sm:col-span-2 sm:mt-0">{{plan.name ? plan.name : ''}} ({{plan.slug ? plan.slug : ''}})</dd>
                                </div>
                                <div class="bg-base-300 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium ">Plan Traffic Usage</dt>
                                    <dd class="mt-1 text-sm  sm:col-span-2 sm:mt-0">
                                        {{humanTrafficDisplay(usage.total)}} / {{humanTrafficDisplay(plan.traffic)}}
                                    </dd>
                                </div>
                                <div class="bg-base-300 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium ">Plan Speed Limit</dt>
                                    <dd class="mt-1 text-sm  sm:col-span-2 sm:mt-0">
                                        {{plan.speed}} Mbits/s
                                    </dd>
                                </div>
                                <div class="bg-base-300 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium ">Reset Day</dt>
                                    <dd class="mt-1 text-sm  sm:col-span-2 sm:mt-0">The {{userPlan.reset_day}}th day of each month</dd>
                                </div>
                                <div class="bg-base-300 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium ">IPV4 Address Limit</dt>
                                    <dd class="mt-1 text-sm  sm:col-span-2 sm:mt-0">
                                        {{usage.ipv4}} / {{plan.ipv4_num}}
                                    </dd>
                                </div>
                                <div class="bg-base-300 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium ">IPV6 Address Limit</dt>
                                    <dd class="mt-1 text-sm  sm:col-span-2 sm:mt-0">
                                        {{usage.ipv6}} / {{plan.ipv6_num}}
                                    </dd>
                                </div>
                                <div class="bg-base-300 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium ">Tunnel Limit</dt>
                                    <dd class="mt-1 text-sm  sm:col-span-2 sm:mt-0">
                                        {{usage.tunnel}} / {{plan.limit}}
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
