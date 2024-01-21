<script setup>
import { Head, Link } from '@inertiajs/vue3';
import LoadingLayout from "@/Layouts/LoadingLayout.vue";
import { BoltIcon, CubeIcon, GlobeAltIcon, ScaleIcon } from '@heroicons/vue/24/outline'
import * as echarts from 'echarts/core';
import { TooltipComponent, GeoComponent } from 'echarts/components';
import { EffectScatterChart } from 'echarts/charts';
import { UniversalTransition } from 'echarts/features';
import { CanvasRenderer } from 'echarts/renderers';

echarts.use([
    TooltipComponent,
    GeoComponent,
    EffectScatterChart,
    CanvasRenderer,
    UniversalTransition
]);

import WorldMap from "../../assets/svg/map.svg";
import { onMounted } from "vue";

onMounted(() => {
    initEcharts()
})

function initEcharts() {
    // 基于准备好的dom，初始化echarts实例
    let Chart = echarts.init(document.getElementById("echarts"));

    axios(WorldMap).then(res => {
        let svg = res.data;
        echarts.registerMap('world-map', { svg: svg })
        // 绘制图表
        let option = {
            tooltip: {},
            geo: {
                // tooltip: {
                //     show: true
                // },
                map: 'world-map',
                roam: false,
                color: '#fff',
            },
            series: {
                type: 'effectScatter',
                coordinateSystem: 'geo',
                geoIndex: 0,
                itemStyle: {
                    color: '#ec4899'
                },
                encode: {
                    tooltip: 2
                },
                data: [
                    [1574, 785, "China Hong Kong"],
                    [1720, 682, "Japan"],
                    [555, 679, "United States"],
                    [315, 664, "United States"],
                    [1019, 570, "Germany"],
                    [959, 616, "France"],
                    [544, 632, "Canada"],
                    [1517, 888, "Singapore"],
                ]
            }
        };
        // 渲染图表
        Chart.setOption(option);
    })

}

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
</script>

<template>

    <LoadingLayout >
        <Head title="Index" />
        <div class="container mx-auto">
            <div class="py-24 sm:py-32 lg:py-40">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="sm:text-center">
                        <h2 class="text-lg font-semibold leading-8 text-primary">TunnelBroker</h2>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl animate__animated animate__fadeInDown">A better open source TunnelBroker project</p>
                        <p class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-gray-600 animate__animated animate__fadeIn">An open source, support for multiple access protocols, feature-rich TunnelBroker project.</p>
                    </div>

                    <div class="mx-auto max-w-5xl mt-4 md:mt-16">
                        <div class="flex flex-col align-items-center gap-8 md:flex-row">
                            <div class="md:basis-1/3 animate__animated animate__fadeInLeft animate__fast">
                                <div class="card shadow-xl m-4 md:m-8 mt-12">
                                    <div class="card-body">
                                        <p class="text-primary text-3xl font-bold font-mono">PoPs</p>
                                        <p class="text-gray-600 dark:text-gray-300 text-md font-semibold">Global PoPs</p>
                                        <p class="text-md text-secondary font-semibold">Europe</p>
                                        <ul class="">
                                            <li>Germany</li>
                                            <li>France</li>
                                            <li>Netherlands</li>
                                        </ul>
                                        <p class="text-md text-secondary font-semibold">Asia</p>
                                        <ul>
                                            <li>Japan</li>
                                            <li>Singapore</li>
                                            <li>China</li>
                                        </ul>
                                        <p class="text-md text-secondary font-semibold">North America</p>
                                        <ul>
                                            <li>United States</li>
                                            <li>Canada</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="md:basis-2/3 hidden md:block">
                                <div id="echarts" class="w-full h-[20rem] md:h-[35rem]"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-16 max-w-lg sm:mx-auto md:max-w-none">
                        <div class="grid grid-cols-1 gap-y-16 md:grid-cols-2 md:gap-x-12 md:gap-y-16 animate__animated animate__fadeInLeft animate__slow">
                            <div v-for="feature in features" :key="feature.name" class="relative flex flex-col gap-6 sm:flex-row md:flex-col lg:flex-row">
                                <div class="wow bounceInUp flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500 text-white sm:shrink-0">
                                    <component :is="feature.icon" class="h-8 w-8 " aria-hidden="true" />
                                </div>
                                <div class="sm:min-w-0 sm:flex-1">
                                    <p class="text-lg font-semibold leading-8 dark:text-white text-gray-900">{{ feature.name }}</p>
                                    <p class="mt-2 text-base leading-7 text-gray-600">{{ feature.description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </LoadingLayout>
</template>

<style>

</style>
