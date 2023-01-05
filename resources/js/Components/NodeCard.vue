<script setup>

defineProps({
    node: Object,
})

function getStatus(status)
{
    switch (status){
        case 1:
            return 'Online';
            break;
    }
}

</script>

<template>
    <a href="#" class="block max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow-md hover:bg-gray-100">
        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 ">{{ node.title }}</h5>
        <p class="font-normal text-gray-700 ">{{getStatus(node.status)}}</p>
    </a>
</template>
