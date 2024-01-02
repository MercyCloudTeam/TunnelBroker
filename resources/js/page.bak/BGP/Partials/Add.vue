<script setup>
import {ref} from 'vue';
import {useForm} from '@inertiajs/inertia-vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const asnInput = ref(null);

defineProps({
    asn: Array,
    tunnels: Array,
});

const form = useForm({
    asn: '',
    tunnel: '',
});

const addBGP = () => {
    form.post(route('bgp.store'), {
        errorBag: 'CreateBGP',
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
    <FormSection @submitted="addBGP">
        <template #title>
            Add BGP Session
        </template>

        <template #description>

        </template>

        <template #form>
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="asn" value="ASN"/>
                <select
                    class="border-gray-300 w-full focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                    v-model="form.asn">
                    <option v-for="item in asn" :value="item.id">
                        AS{{ item.asn }}
                    </option>
                </select>
                <InputError :message="form.errors.asn" class="mt-2"/>
            </div>
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="tunnel" value="Tunnel"/>
                <select
                    class="border-gray-300 w-full focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                    v-model="form.tunnel">
                    <option v-for="item in tunnels" :value="item.tunnel_id">
                        #{{ item.tunnel_id }}  {{ item.remote }}
                    </option>
                </select>
                <InputError :message="form.errors.tunnel" class="mt-2"/>
            </div>
        </template>

        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="mr-3">
                Success.
            </ActionMessage>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Create
            </PrimaryButton>
        </template>
    </FormSection>
</template>
