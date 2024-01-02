<script setup>

import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Swal from "sweetalert2";

const props = defineProps({
    nodes: Array,
    availableMode: Array
})

const createTunnel = () => {
    createTunnelForm.post(route('tunnels.store'), {
        preserveScroll: true,
        errorBag: 'createTunnel',
        onSuccess: () => {
            createTunnelForm.reset('remote', 'mode', 'node','pubkey','port');
            displayingToken.value = true;
            createTunnelForm.reset();
            displayPubKeyInput.value = false;
            displayPortInput.value = false;
            Swal.fire({
                icon: 'success',
                title: 'Tunnel created',
                text: 'Tunnel created successfully, places wait 1 minute for the tunnel to be created',
            })
        },
        onError: () => {
            if (createTunnelForm.errors.tunnel)
            {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: createTunnelForm.errors.tunnel,
                })
            }
        },
    });
}

const displayPortInput = ref(false);
const displayPubKeyInput = ref(false);
const displayIpAssignInput = ref({
    'ip4': false,
    'ip6': false
});


const createTunnelForm = useForm({
    remote: '',
    mode: '',
    node:'',
    pubkey: null,
    port: null
})

const modeChange = () => {
    // console.log(createTunnelForm.mode);
    switch (createTunnelForm.mode){
        case 'wireguard':
            createTunnelForm.port = null;
            displayPortInput.value = true;
            displayPubKeyInput.value  = true;
            displayIpAssignInput.value = {
                'ip4': true,
                'ip6': true
            };
            break
        case 'vxlan':
            createTunnelForm.port = 4789;
            displayPortInput.value = true;
            displayPubKeyInput.value  = false;
            displayIpAssignInput.value = {
                'ip4': true,
                'ip6': true
            };
            break
        case 'gre':
        case 'ip6gre':
        case 'sit':
            displayPortInput.value = false;
            displayPubKeyInput.value  = false;
            displayIpAssignInput.value = {
                'ip4': true,
                'ip6': true
            };
            break;
        default:
            displayPortInput.value = false;
            displayPubKeyInput.value  = false;
            displayIpAssignInput.value = {
                'ip4': false,
                'ip6': false
            };
            break
    }
}

</script>


<template>
    <section @submitted="createTunnel">

        <header>
            <h2 class="text-2xl font-medium text-base-content">Create Tunnel</h2>

            <p class="mt-2 text-sm text-base-content">
                Please enter your server IP and information for creating the interface.
            </p>
        </header>


        <form  @submit.prevent="createTunnelForm.post(route('tunnels.store'))" class="mt-6 space-y-6">

            <!-- Remote IP -->
            <div class="col-span-6">
                <InputLabel for="remote" value="Remote IP" />
                <TextInput
                    id="remote"
                    v-model.trim="createTunnelForm.remote"
                    type="text"
                    class="mt-1 block w-full"
                    autofocus
                />
                <InputError :message="createTunnelForm.errors.remote" class="mt-2" />
            </div>
            <!-- Pubkey -->
            <div class="col-span-6" v-if="displayPubKeyInput">
                <InputLabel for="pubkey" value="Pubkey" />
                <TextInput
                    id="pubkey"
                    v-model.trim="createTunnelForm.pubkey"
                    type="text"
                    class="mt-1 block w-full"
                    autofocus
                />
                <InputError :message="createTunnelForm.errors.pubkey" class="mt-2" />
            </div>

            <!-- port -->
            <div class="col-span-6" v-if="displayPortInput">
                <InputLabel for="port" value="Port" />
                <TextInput
                    id="port"
                    v-model.number="createTunnelForm.port"
                    type="text"
                    class="mt-1 block w-full"
                    autofocus
                />
                <InputError :message="createTunnelForm.errors.port" class="mt-2" />
            </div>

            <!-- Node -->
            <div class="col-span-6">
                <InputLabel for="node" value="Node" />
                    <select
                        class="select select-bordered  w-full"
                        v-model="createTunnelForm.node">
                        <option v-for="node in nodes" :value="node.id">
                            {{ node.title }}
                        </option>
                    </select>
                <InputError :message="createTunnelForm.errors.node" class="mt-2" />
            </div>
            <!-- Mode -->
            <div class="col-span-6">
                <InputLabel for="mode" value="Mode" />
                <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="mode in availableMode" :key="mode">
                        <label class="flex items-center">
                            <input type="radio"  class="radio radio-primary"  @change="modeChange" v-model="createTunnelForm.mode" :value="mode" >
                            <span class="ml-2 text-lg text-gray-600">{{ mode }}</span>
                        </label>
                    </div>
                </div>
                <InputError :message="createTunnelForm.errors.mode" class="mt-2" />
            </div>

            <p v-if="createTunnelForm.recentlySuccessful" class="mr-3">
                Created.
            </p>
            <p v-if="createTunnelForm.hasErrors">
                {{ createTunnelForm.errors.toString()}}
            </p>

            <button class="btn btn-primary " :class="{ 'opacity-25': createTunnelForm.processing }" :disabled="createTunnelForm.processing">
                Create
            </button>
        </form>

    </section>

</template>
