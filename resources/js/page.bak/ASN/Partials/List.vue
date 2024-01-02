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
    asn: Array,
})


const confirmASNDeletionModal = ref(false);

const confirmASNDeletion = (ASN) => {
    confirmASNDeletionModal.value = true;
    delASNForm.asn = ASN;
}

const delASNForm = useForm({
    asn: null,
})


const deleteASN = () => {
    confirmASNDeletionModal.value = false;
    delASNForm.delete(route('asn.destroy', delASNForm.asn.id), {
        preserveScroll: true,
        errorBag: 'deleteASN',
        onSuccess: () => {
            delASNForm.reset();
        },
        onError: () => {
            console.log(delASNForm.errors.deleteASN);
        },
    });

}

</script>


<template>
    <div v-if="asn.length > 0">
        <SectionBorder/>
        <!-- Manage API Tokens -->
        <div class="mt-10 sm:mt-0">
            <ActionSection>
                <template #title>
                    Manage ASNs
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
                                    ASN
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
                            <tr v-for="item in asn" :key="item.id">
                                <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">
                                    #{{ item.id }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">
                                    {{ item.asn }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">
                                    <div v-if="item.validate === 1" class="badge badge-accent">Validate</div>
                                    <div v-if="item.validate === 0" class="badge badge-secondary">No Validate</div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-2 text-right">
                                    <button class="cursor-pointer ml-6 text-sm text-red-500"
                                            @click="confirmASNDeletion(item)">
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
                Remove ASN
            </template>

            <template #content>
                <div>
                    Are you sure you want to remove this ASN?
                </div>
            </template>

            <template #footer>
                <PrimaryButton @click="deleteASN">
                    Remove
                </PrimaryButton>
                <SecondaryButton @click="confirmASNDeletionModal = false">
                    Close
                </SecondaryButton>
            </template>
        </DialogModal>
    </div>
</template>
