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
            displayingToken.value = true;
            createTunnelForm.reset();
        },
        onError: () => {
            console.log(createTunnelForm.errors.createTunnel.remote);
        },
    });
}

const createTunnelForm = useForm({
    remote: '',
    mode: '',
    node:''
})

</script>


<template>
    <!-- Generate API Token -->
    <FormSection @submitted="createTunnel">
        <template #title>
            Create API Token
        </template>

        <template #description>
            API tokens allow third-party services to authenticate with our application on your behalf.
        </template>

        <template #form>
            <!-- Token Name -->
            <div class="col-span-6">
                <InputLabel for="remote" value="Remote IP" />
                <TextInput
                    id="remote"
                    v-model="createTunnelForm.remote"
                    type="text"
                    class="mt-1 block w-full"
                    autofocus
                />
                <InputError :message="createTunnelForm.errors.remote" class="mt-2" />
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
                            <input type="radio" v-model="createTunnelForm.mode" :value="mode" >
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

            <PrimaryButton :class="{ 'opacity-25': createTunnelForm.processing }" :disabled="createTunnelForm.processing">
                Create
            </PrimaryButton>
        </template>
    </FormSection>

</template>
