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

defineProps({
    tunnels: Array,
})



const confirmTunnelDeletionModal = ref(false);

const confirmTunnelDeletion = (tunnel) => {
    confirmTunnelDeletionModal.value = true;
    delTunnelForm.tunnel = tunnel;
}

const deleteTunnel = () => {
    confirmTunnelDeletionModal.value = false;
    delTunnelForm.delete(route('tunnels.destroy', delTunnelForm.tunnel.id), {
        preserveScroll: true,
        errorBag: 'deleteTunnel',
        onSuccess: () => {
            delTunnelForm.reset();
        },
        onError: () => {
            console.log(delTunnelForm.errors.deleteTunnel);
        },
    });

}

const delTunnelForm = useForm({
    tunnel: null,

})

</script>


<template>
    <div v-if="tunnels.length > 0">
        <SectionBorder />
        <!-- Manage API Tokens -->
        <div class="mt-10 sm:mt-0">
            <ActionSection>
                <template #title>
                    Manage Tunnels
                </template>

                <template #description>
                    You
                </template>

                <!-- API Token List -->
                <template #content>
                    <div class="space-y-6">
                        <div v-for="tunnel in tunnels" :key="tunnel.id" class="flex justify-between">
                            <div class="break-all">
                                #{{ tunnel.id }}
                            </div>
                            <div class="text-right">
                                {{ tunnel.remote }}
                            </div>
                            <div  class="text-sm ext-gray-400">
                                test
                            </div>
                            <div class="flex items-center ml-2">
                                <div  class="text-sm text-gray-400">
                                    {{ tunnel.mode }}
                                </div>
                            </div>
                            <div class="flex">
                                <button class="cursor-pointer ml-6 text-sm text-red-500" @click="confirmTunnelDeletion(tunnel)">
                                    Delete
                                </button>
                            </div>
                        </div>
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
    </div>
</template>
