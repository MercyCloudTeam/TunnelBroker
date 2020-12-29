<template>
    <div>
        <!-- Generate API Token -->
        <div v-if="asn.length > 0">
            <jet-section-border />
            <!-- Manage API Tunnels -->
            <div class="mt-10 sm:mt-0">
                <jet-action-section>
                    <template #title>
                        ASN列表
                    </template>

                    <template #description>
                        ASN仅限当前账户使用 <br>
                        每个用户目前最多验证5个ASN <br>
                        ASN只能归属一个账户 <br>
                        如需验证更多或其他配置请联系: ipv6@tunnelbroker.io
                    </template>

                    <!-- API Token List -->
                    <template #content>
                        <div class="space-y-6">
                            <div class="flex items-center justify-between" v-for="item in asn" :key="item.id">
                                <div>
                                    AS{{ item.asn }}
                                </div>

                                <div class="flex items-center">
                                    <div class="text-sm text-gray-400" v-if="item.validate">
                                         已验证
                                    </div>
                                    <div class="text-sm text-gray-400" v-else>
                                         未验证
                                    </div>

                                </div>
                            </div>
                        </div>
                    </template>
                </jet-action-section>
            </div>
        </div>
    </div>
</template>

<script>
    import JetActionMessage from '@/Jetstream/ActionMessage'
    import JetActionSection from '@/Jetstream/ActionSection'
    import JetButton from '@/Jetstream/Button'
    import JetConfirmationModal from '@/Jetstream/ConfirmationModal'
    import JetDangerButton from '@/Jetstream/DangerButton'
    import JetDialogModal from '@/Jetstream/DialogModal'
    import JetFormSection from '@/Jetstream/FormSection'
    import JetInput from '@/Jetstream/Input'
    import JetInputError from '@/Jetstream/InputError'
    import JetLabel from '@/Jetstream/Label'
    import SelectInput from '@/Share/SelectInput'
    import JetSecondaryButton from '@/Jetstream/SecondaryButton'
    import JetSectionBorder from '@/Jetstream/SectionBorder'

    export default {
        components: {
            JetActionMessage,
            JetActionSection,
            JetButton,
            JetConfirmationModal,
            JetDangerButton,
            JetDialogModal,
            JetFormSection,
            JetInput,
            SelectInput,
            JetInputError,
            JetLabel,
            JetSecondaryButton,
            JetSectionBorder,
        },

        props: [
            'asn',
        ],

        data() {
            return {

                managingmodeFor: null,
                TunnelBeingDeleted: null,
            }
        },

        methods: {
            fromNow(timestamp) {
                return moment(timestamp).local().fromNow()
            },
        },
    }
</script>
