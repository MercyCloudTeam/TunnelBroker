<template>
    <jet-form-section @submitted="createASN">
        <template #title>
            添加ASN
        </template>

        <template #description>
            请输入您的ASN号码(例如:59598 不包含"AS"),默认咱们会通过您的注册邮箱与ASN维护者邮箱进行自动验证,若无法通过自动验证。 <br>
            请使用维护者邮箱将LOA授权书发送至 ipv6@tunnelbroker.io
        </template>
        <template #form>
            <div class="col-span-6 sm:col-span-4">
                <jet-label for="asn" value="ASN" />
                <jet-input id="asn"  type="text" class="mt-1 block w-full" v-model="form.asn" autofocus />
                <jet-input-error :message="form.error('asn')" class="mt-2" />
            </div>
        </template>

        <template #actions>
            <jet-action-message :on="form.recentlySuccessful" class="mr-3">
                添加成功.
            </jet-action-message>

            <jet-button :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                提交
            </jet-button>
        </template>
    </jet-form-section>
</template>

<script>
    import JetActionMessage from '@/Jetstream/ActionMessage'
    import JetButton from '@/Jetstream/Button'
    import JetFormSection from '@/Jetstream/FormSection'
    import JetInput from '@/Jetstream/Input'
    import JetInputError from '@/Jetstream/InputError'
    import JetLabel from '@/Jetstream/Label'

    export default {
        components: {
            JetActionMessage,
            JetButton,
            JetFormSection,
            JetInput,
            JetInputError,
            JetLabel,
        },

        data() {
            return {
                form: this.$inertia.form({
                    asn: '',
                }, {
                    bag: 'createASN',
                    resetOnSuccess: false,
                })
            }
        },

        methods: {
            createASN() {
                this.form.post(route('bgp.validate'), {
                    preserveScroll: true
                });
            },
        },
    }
</script>
