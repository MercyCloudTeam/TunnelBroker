<script setup>
import ActionSection from '@/Components/ActionSection.vue';
import DialogModal from '@/Components/DialogModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import {ref} from "vue";
import {useForm} from "@inertiajs/inertia-vue3";
import Swal from "sweetalert2";

defineProps({
    bgp: Array,
})


const confirmBGPDeletionModal = ref(false);

const confirmBGPDeletion = (bgp) => {
    confirmBGPDeletionModal.value = true;
    delBGPForm.bgp = bgp;
}

const delBGPForm = useForm({
    bgp: null,
})


const deleteBGP = () => {
    confirmTunnelDeletionModal.value = false;
    delBGPForm.delete(route('BGP.destroy', delBGPForm.bgp.id), {
        preserveScroll: true,
        errorBag: 'deleteBGP',
        onSuccess: () => {
            delBGPForm.reset();
        },
        onError: () => {
            console.log(delBGPForm.errors.deleteBGP);
        },
    });

}

</script>


<template>
    <div v-if="bgp.length > 0">
        <SectionBorder/>
        <!-- Manage API Tokens -->
        <div class="mt-10 sm:mt-0">
            <ActionSection>
                <template #title>
                    Manage BGPs
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
                                    BGP
                                </th>
                                <th
                                    class="whitespace-nowrap px-4 py-2 text-left font-medium text-gray-900"
                                >
                                    Status
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
                            <tr v-for="item in bgp" :key="item.id">
                                <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">
                                    #{{ item.id }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">
                                    {{ item.bgp }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">
                                    <div v-if="item.validate === 1" class="badge badge-accent">Validate</div>
                                    <div v-if="item.validate === 0" class="badge badge-secondary">No Validate</div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-2 text-right">
                                    <button class="cursor-pointer ml-6 text-sm text-red-500"
                                            @click="confirmBGPDeletion(item)">
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

        <DialogModal :show="confirmBGPDeletionModal" @close="confirmBGPDeletionModal = false">
            <template #title>
                Remove BGP
            </template>

            <template #content>
                <div>
                    Are you sure you want to remove this BGP?
                </div>
            </template>

            <template #footer>
                <PrimaryButton @click="deleteBGP">
                    Remove
                </PrimaryButton>
                <SecondaryButton @click="confirmBGPDeletionModal = false">
                    Close
                </SecondaryButton>
            </template>
        </DialogModal>
    </div>
</template>
