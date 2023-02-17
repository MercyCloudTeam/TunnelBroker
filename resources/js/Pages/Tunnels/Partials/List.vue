<script setup>
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
import {ref} from "vue";
import {useForm} from "@inertiajs/inertia-vue3";
import Swal from "sweetalert2";

defineProps({
    tunnels: Array,
})


const confirmTunnelDeletionModal = ref(false);
const displayTunnelInfoModal = ref(false);

const confirmTunnelDeletion = (tunnel) => {
    confirmTunnelDeletionModal.value = true;
    delTunnelForm.tunnel = tunnel;
}

const displayTunnelInfo = (tunnel) => {
    displayTunnelInfoModal.value = true;
    detailTunnel.tunnel = tunnel;
}

const deleteTunnel = () => {
    confirmTunnelDeletionModal.value = false;
    delTunnelForm.delete(route('tunnels.destroy', delTunnelForm.tunnel.id), {
        preserveScroll: true,
        errorBag: 'deleteTunnel',
        onSuccess: () => {
            delTunnelForm.reset();
            Swal.fire({
                icon: 'success',
                title: 'Tunnel deleted',
                text: 'Tunnel deleted successfully,Tunnel will be deleted in 1 minute',
            })
        },
        onError: () => {
            console.log(delTunnelForm.errors.deleteTunnel);
        },
    });

}

const delTunnelForm = useForm({
    tunnel: null,
})

const detailTunnel = useForm({
    tunnel: null,
})

const getStatusDisplay = (status) => {
    switch (status) {
        case 1:
            return {
                'class': 'text-green-600',
                'text': 'Active'
            };
        case 2:
            return {
                'class': 'text-yellow-600',
                'text': 'Waiting Create'
            };
        case 3:
            return {
                'class': 'text-yellow-600',
                'text': 'Rebuilding'
            };
        case 4:
            return {
                'class': 'text-yellow-600',
                'text': 'Create Error(Waiting Retry)'
            }
        case 5:
            return {
                'class': 'text-red-600',
                'text': 'IP Changed'
            };
        case 6:
            return {
                'class': 'text-red-600',
                'text': 'Error'
            };
        case 7:
            return {
                'class': 'text-red-600',
                'text': 'Deleting'
            };
    }
}

</script>


<template>
    <div v-if="tunnels.length > 0">
        <SectionBorder/>
        <!-- Manage API Tokens -->
        <div class="mt-10 sm:mt-0">
            <ActionSection>
                <template #title>
                    Manage Tunnels
                </template>

                <template #description>
                </template>

                <template #content>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y-2 divide-gray-200 text-sm">
                            <thead>
                            <tr>
                                <th
                                    class="whitespace-nowrap px-4 py-2 text-left font-medium text-gray-900"
                                >
                                    ID
                                </th>
                                <th
                                    class="whitespace-nowrap px-4 py-2 text-left font-medium text-gray-900"
                                >
                                    Remote Address
                                </th>
                                <th
                                    class="whitespace-nowrap px-4 py-2 text-left font-medium text-gray-900"
                                >
                                    Status
                                </th>
                                <th
                                    class="whitespace-nowrap px-4 py-2 text-left font-medium text-gray-900"
                                >
                                    Protocol
                                </th>
                                <th
                                    class="whitespace-nowrap px-4 py-2 text-left font-medium text-gray-900 text-right"
                                >
                                    Action
                                </th>
                                <th class="px-4 py-2"></th>
                            </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200">
                            <tr v-for="tunnel in tunnels" :key="tunnel.id">
                                <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">
                                    {{ tunnel.id }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-2 text-gray-700">{{ tunnel.remote }}</td>
                                <td class="whitespace-nowrap px-4 py-2" :class="getStatusDisplay(tunnel.status).class">
                                    {{ getStatusDisplay(tunnel.status).text }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-2 text-gray-700"> {{ tunnel.mode }}</td>
                                <td class="whitespace-nowrap px-4 py-2 text-right">
                                    <button class="cursor-pointer ml-6 text-sm text-blue-500"
                                            @click="displayTunnelInfo(tunnel)">
                                        Detail
                                    </button>
                                    <button v-if="tunnel.status !== 7" class="cursor-pointer ml-6 text-sm text-red-500"
                                            @click="confirmTunnelDeletion(tunnel)">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </template>
            </ActionSection>
        </div>

        <DialogModal :show="confirmTunnelDeletionModal" @close="confirmTunnelDeletionModal = false">
            <template #title>
                Remove Tunnel
            </template>

            <template #content>
                <div>
                    Are you sure you want to remove this tunnel?
                </div>
            </template>

            <template #footer>
                <PrimaryButton @click="deleteTunnel">
                    Remove
                </PrimaryButton>
                <SecondaryButton @click="confirmTunnelDeletionModal = false">
                    Close
                </SecondaryButton>
            </template>
        </DialogModal>

        <DialogModal :show="displayTunnelInfoModal" @close="displayTunnelInfoModal = false">
            <template #title>
                Tunnel #{{ detailTunnel.tunnel.id }}
            </template>

            <template #content>
                <div>

                    <p>Remote Address: {{ detailTunnel.tunnel.remote }} </p>
                    <p>IPV4 Address: {{ detailTunnel.tunnel.ip4 }}</p>
                    <p>IPV6 Address: {{ detailTunnel.tunnel.ip6 }}</p>
                    <p>Server Port: {{ detailTunnel.tunnel.srcport }}</p>
                    <p>Local Port: {{ detailTunnel.tunnel.dstport }}</p>
                    <p>Protocol: {{ detailTunnel.tunnel.mode }}</p>
                    <p>Status: <span
                        :class="getStatusDisplay(detailTunnel.tunnel.status).class">{{ getStatusDisplay(detailTunnel.tunnel.status).text }}</span>
                    </p>
                    <p>Created At: {{ detailTunnel.tunnel.created_at }}</p>
                    <div v-if="detailTunnel.tunnel.config">
                        <p v-if="detailTunnel.tunnel.config.local.pubkey">Local Public Key: {{detailTunnel.tunnel.config.remote.pubkey}}</p>
<!--                        <p v-if="detailTunnel.tunnel.config.local.privkey">Local Privacy Key: {{detailTunnel.tunnel.config.local.privkey}}</p>-->
                        <p v-if="detailTunnel.tunnel.config.remote.pubkey">Local Privacy Key: {{detailTunnel.tunnel.config.remote.privkey}}</p>
                        <p v-if="detailTunnel.tunnel.config.local.pubkey">Server Public Key: {{detailTunnel.tunnel.config.local.pubkey}}</p>
                    </div>
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="displayTunnelInfoModal = false">
                    Close
                </SecondaryButton>
            </template>
        </DialogModal>
    </div>
</template>
