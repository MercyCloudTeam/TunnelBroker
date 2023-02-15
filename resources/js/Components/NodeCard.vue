<script setup>
import CountryFlag from 'vue-country-flag-next'

defineProps({
    node: Object,
})

const getStatusDisplay = (status) => {
    switch (status) {
        case 1:
            return {
                text: 'Online',
                color: 'text-green-500'
            };

    }
}

</script>

<template>
    <div class="block max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow-md hover:bg-gray-100">
        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 ">{{ node.title }}</h5>
        <p class="font-normal text-gray-700">Status: <span :class="getStatusDisplay(node.status).color">{{ getStatusDisplay(node.status).text }}</span></p>
        <p class="font-normal text-gray-700">Country: <CountryFlag :country="node.country"/></p>
    </div>
</template>
