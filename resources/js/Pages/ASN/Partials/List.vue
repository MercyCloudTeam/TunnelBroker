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
    asn: Array,
})


const confirmASNDeletionModal = ref(false);
const displayASNInfoModal = ref(false);

const confirmTunnelDeletion = (tunnel) => {
    confirmASNDeletionModal.value = true;
    delTunnelForm.tunnel = tunnel;
}

const displayTunnelInfo = (tunnel) => {
    displayASNInfoModal.value = true;
    detailTunnel.tunnel = tunnel;
}

const delTunnelForm = useForm({
    tunnel: null,
})

const detailTunnel = useForm({
    tunnel: null,
})


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
                            <tr v-for="item in asn" :key="item.id">
                                <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">
                                    {{ item.id }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-2 text-right">
                                    <button class="cursor-pointer ml-6 text-sm text-blue-500"
                                            @click="displayTunnelInfo(item)">
                                        Detail
                                    </button>
                                    <button v-if="item.status !== 7" class="cursor-pointer ml-6 text-sm text-red-500"
                                            @click="confirmTunnelDeletion(item)">
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

        <DialogModal :show="confirmASNDeletionModal" @close="confirmASNDeletionModal = false">
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
                <SecondaryButton @click="confirmASNDeletionModal = false">
                    Close
                </SecondaryButton>
            </template>
        </DialogModal>

        <DialogModal :show="displayASNInfoModal" @close="displayASNInfoModal = false">
            <template #title>
                Tunnel #{{ detailTunnel.tunnel.id }}
            </template>

            <template #content>
                <div>

                    <p> Remote Address: {{ detailTunnel.tunnel.remote }} </p>
                    <p>Protocol: {{ detailTunnel.tunnel.mode }}</p>
                    <p>Status: <span
                        :class="getStatusDisplay(detailTunnel.tunnel.status).class">{{ getStatusDisplay(detailTunnel.tunnel.status).text }}</span>
                    </p>
                    <p>IPV4 Address: {{ detailTunnel.tunnel.ip4}}</p>
                    <p>IPV6 Address: {{ detailTunnel.tunnel.ip6}}</p>
                    <p>Created At: {{ detailTunnel.tunnel.created_at }}</p>
                    <div v-if="detailTunnel.tunnel.config">
                        <p v-if="detailTunnel.tunnel.config.local.pubkey">Local Public Key: {{detailTunnel.tunnel.config.local.pubkey}}</p>
                        <p v-if="detailTunnel.tunnel.config.local.privkey">Local Privacy Key: {{detailTunnel.tunnel.config.local.privkey}}</p>
                        <p v-if="detailTunnel.tunnel.config.remote.pubkey">Server Public Key: {{detailTunnel.tunnel.config.remote.pubkey}}</p>
                    </div>
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="displayASNInfoModal = false">
                    Close
                </SecondaryButton>
            </template>
        </DialogModal>
    </div>
</template>
