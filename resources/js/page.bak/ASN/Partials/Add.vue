<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/inertia-vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const asnInput = ref(null);

const form = useForm({
    asn: '',
});

const updatePassword = () => {
    form.post(route('asn.validate'), {
        errorBag: 'updatePassword',
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.asn) {
                form.reset();
                asnInput.value.focus();
            }
        },
    });
};
</script>


<template>
    <FormSection @submitted="updatePassword">
        <template #title>
            Add ASN
        </template>

        <template #description>
            Please enter your ASN, (the recommended account email is the same as the ASN information email)
        </template>

        <template #form>
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="asn" value="Autonomous System Number(ASN)" />
                <TextInput
                    id="asn"
                    ref="asnInput"
                    v-model="form.asn"
                    type="text"
                    class="mt-1 block w-full"
                    autocomplete="asn"
                    oninput="value=value.replace(/[^\d]/g, '')"
                />
                <InputError :message="form.errors.asn" class="mt-2" />
            </div>
        </template>

        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="mr-3">
                Success.
            </ActionMessage>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Add
            </PrimaryButton>
        </template>
    </FormSection>
</template>
