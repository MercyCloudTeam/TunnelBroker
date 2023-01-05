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

defineProps({
    tunnels: Array,
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
                        <div v-for="tunnel in tunnels" :key="tunnel.id" class="flex items-center justify-between">
                            <div class="break-all">
                                #{{ tunnel.id }}
                            </div>
                            <div class="break-all">
                                {{ tunnel.remote }}
                            </div>
                            <div  class="text-sm text-gray-400">

                            </div>
                            <div class="flex items-center ml-2">
                                <div  class="text-sm text-gray-400">
                                    {{ tunnel.mode }}
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </ActionSection>
        </div>
    </div>
</template>
