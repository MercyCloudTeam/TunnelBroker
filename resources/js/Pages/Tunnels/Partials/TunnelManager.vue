<script setup>

import { ref } from 'vue';
import { useForm } from '@inertiajs/inertia-vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import ActionSection from '@/Components/ActionSection.vue';
import Checkbox from '@/Components/Checkbox.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import DialogModal from '@/Components/DialogModal.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import TextInput from '@/Components/TextInput.vue';

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

        },
        onError: () => {
            console.log(createTunnelForm.errors.createTunnel.remote);
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
    console.log(createTunnelForm.mode);
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
    <!-- Generate API Token -->
    <FormSection @submitted="createTunnel">
        <template #title>
            Create Tunnel
        </template>

        <template #description>
            Please enter your server IP and information for creating the interface.
        </template>

        <template #form>
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
                        class="border-gray-300 w-full focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
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
                            <input type="radio" @change="modeChange" v-model="createTunnelForm.mode" :value="mode" >
                            <span class="ml-2 text-sm text-gray-600">{{ mode }}</span>
                        </label>
                    </div>
                </div>
                <InputError :message="createTunnelForm.errors.mode" class="mt-2" />
            </div>
        </template>

        <template #actions>
            <ActionMessage :on="createTunnelForm.recentlySuccessful" class="mr-3">
                Created.
            </ActionMessage>
            <ActionMessage v-if="createTunnelForm.hasErrors">
                {{ createTunnelForm.errors.toString()}}
            </ActionMessage>

            <PrimaryButton :class="{ 'opacity-25': createTunnelForm.processing }" :disabled="createTunnelForm.processing">
                Create
            </PrimaryButton>
        </template>
    </FormSection>

</template>
