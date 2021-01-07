<template>
    <div>
        <!-- Generate API Token -->
        <jet-form-section @submitted="createTunnel" v-if="$page.user.limit > tunnels.length">
            <template #title>
                创建隧道
            </template>

            <template #description>
               SIT隧道（6in4）IPV6隧道 <br>
                BGP隧道请在验证ASN后在创建隧道界面选择你使用的ASN <br>
                隧道创建后除IP之外其他不允许变更 <br>
                支持DDNS API动态更新隧道对端IP
            </template>

            <template #form>


                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="remote" value="您的服务器IP" />
                    <jet-input id="remote" type="text" class="mt-1 block w-full" v-model="createTunnelForm.remote" autofocus />
                    <jet-input-error :message="createTunnelForm.error('remote')" class="mt-2" />
                </div>

                <div class="col-span-6" v-if="availableMode.length > 0">
                    <jet-label for="mode" value="隧道类型" />
                    <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <select-input v-model="createTunnelForm.mode" :error="createTunnelForm.error('mode')" class="mt-1 block w-full"   >
                            <option v-for="mode in availableMode" :key="mode" :value="mode">{{ mode }}</option>
                        </select-input>
                    </div>
                </div>

                <div class="col-span-6" v-if="nodes.length > 0">
                    <jet-label for="mode" value="接入节点" />
                    <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <select-input @change="canSelectASN" v-model="createTunnelForm.node" :error="createTunnelForm.error('node')" class="mt-1 block w-full"   >
                            <option v-for="node in nodes" :key="node.id" :value="node.id">{{ node.title }} {{node.ip}}</option>
                        </select-input>
                    </div>
                </div>

                <div class="col-span-6" v-if="asn.length > 0 && displayASNSelect">
                    <jet-label for="mode" value="ASN" />
                    <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <select-input v-model="createTunnelForm.asn" :error="createTunnelForm.error('asn')" class="mt-1 block w-full"   >
                            <option  value="">不配置</option>
                            <option v-for="item in asn" :key="item.id" :value="item.id">{{ item.asn }}</option>
                        </select-input>
                    </div>
                </div>


            </template>

            <template #actions>
                <jet-action-message :on="createTunnelForm.recentlySuccessful" class="mr-3">
                    Created.
                </jet-action-message>

                <jet-button :class="{ 'opacity-25': createTunnelForm.processing }" :disabled="createTunnelForm.processing">
                    Create
                </jet-button>
            </template>
        </jet-form-section>

        <div v-if="tunnels.length > 0">
            <jet-section-border />

            <!-- Manage API Tunnels -->
            <div class="mt-10 sm:mt-0">
                <jet-action-section>
                    <template #title>
                        隧道列表
                    </template>

                    <template #description>
                        数量限制：{{tunnels.length}}/{{$page.user.limit}} <br>
                        查看状态更新请刷新页面 <br>

                    </template>

                    <!-- API Token List -->
                    <template #content>
                        <div class="space-y-6">
                            <div class="flex items-center justify-between" v-for="tunnel in tunnels" :key="tunnel.id">
                                <div>
                                    tunnel{{ tunnel.id }}
                                </div>

                                <div class="text-sm text-gray-400" v-if="tunnel.status === 1">
                                    正常
                                </div>
                                <div class="text-sm text-gray-400" v-if="tunnel.status === 2">
                                    等待创建
                                </div>
                                <div class="text-sm text-gray-400" v-if="tunnel.status === 3">
                                    创建中
                                </div>
                                <div class="text-sm text-gray-400" v-if="tunnel.status === 4">
                                    创建失败
                                </div>
                                <div class="text-sm text-gray-400" v-if="tunnel.status === 5">
                                    等待更新
                                </div>
                                <div class="text-sm text-gray-400" v-if="tunnel.status === 6">
                                    异常
                                </div>



                                <div class="flex items-center">



                                    <div class="text-sm text-gray-400" v-if="tunnel.remote">
                                        对端IP: {{ tunnel.remote }}
                                    </div>


                                    <button class="cursor-pointer ml-6 text-sm text-gray-400 underline focus:outline-none"
                                                @click="manageTunnel(tunnel)"
                                                v-if="availableMode.length > 0">
                                        修改
                                    </button>

                                    <inertia-link v-show="tunnel.status !== 2" :href="route('tunnels.show',tunnel.id)" class="cursor-pointer ml-6 text-sm text-gray-400 underline focus:outline-none">
                                        隧道信息
                                    </inertia-link>

                                    <button class="cursor-pointer ml-6 text-sm text-red-500 focus:outline-none" @click="confirmTunnelDeletion(tunnel)">
                                        删除
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </jet-action-section>
            </div>
        </div>

        <jet-dialog-modal :show="managingmodeFor" @close="managingmodeFor = null">
            <template #title>
                隧道管理
            </template>

            <template #content>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <jet-label for="remote" value="您的服务器IP" />
                    <jet-input  type="text" class="mt-1 block w-full" v-model="updateTunnelForm.remote" autofocus />
                    <jet-input-error :message="updateTunnelForm.error('remote')" class="mt-2" />
                </div>
            </template>

            <template #footer>
                <jet-secondary-button @click.native="managingmodeFor = null">
                    Nevermind
                </jet-secondary-button>

                <jet-button class="ml-2" @click.native="updateTunnel" :class="{ 'opacity-25': updateTunnelForm.processing }" :disabled="updateTunnelForm.processing">
                    Save
                </jet-button>
            </template>
        </jet-dialog-modal>

        <!-- Delete Token Confirmation Modal -->
        <jet-confirmation-modal :show="TunnelBeingDeleted" @close="TunnelBeingDeleted = null">
            <template #title>
                删除隧道
            </template>

            <template #content>
               您确定要删除这个隧道嘛
            </template>

            <template #footer>
                <jet-secondary-button @click.native="TunnelBeingDeleted = null">
                    Nevermind
                </jet-secondary-button>

                <jet-danger-button class="ml-2" @click.native="deleteTunnel" :class="{ 'opacity-25': deleteTunnelForm.processing }" :disabled="deleteTunnelForm.processing">
                    删除
                </jet-danger-button>
            </template>
        </jet-confirmation-modal>
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
            'tunnels',
            'nodes',
            'asn',
            'availableMode',
            'defaultMode',
            'defaultNode',
            'defaultASN',
            'displayASNSelect',
        ],
        data() {
            return {
                createTunnelForm: this.$inertia.form({
                    remote: '',
                    mode: this.defaultMode,
                    node: this.defaultNode,
                    asn: this.defaultASN,
                }, {
                    bag: 'createTunnel',
                    resetOnSuccess: true,
                }),

                updateTunnelForm: this.$inertia.form({
                    remote: ''
                }, {
                    resetOnSuccess: false,
                    bag: 'updateTunnel',
                }),

                deleteTunnelForm: this.$inertia.form(),

                displayingToken: false,
                managingmodeFor: null,
                tunnelInfoFor: null,
                TunnelBeingDeleted: null,
            }
        },
        methods: {

            canSelectASN(node)
            {
                console.log(node)
                console.log(this.nodes)
            },

            createTunnel() {
                this.createTunnelForm.post(route('tunnels.store'), {
                    preserveScroll: true,
                }).then(response => {
                    if (! this.createTunnelForm.hasErrors()) {
                        this.displayingToken = true
                    }
                })
            },

            manageTunnel(tunnel) {
                this.updateTunnelForm.remote = tunnel.remote

                this.managingmodeFor = tunnel
            },

            tunnelInfo(tunnel){
                this.tunnelInfoFor = tunnel
            },

            changeTunnelIP(tunnel){
                // this.cha
            },

            updateTunnel() {
                this.updateTunnelForm.put(route('tunnels.update', this.managingmodeFor), {
                    preserveScroll: true,
                    preserveState: true,
                }).then(response => {
                    this.managingmodeFor = null
                })
            },

            confirmTunnelDeletion(tunnel) {
                this.TunnelBeingDeleted = tunnel
            },

            deleteTunnel() {
                this.deleteTunnelForm.delete(route('tunnels.destroy', this.TunnelBeingDeleted), {
                    preserveScroll: true,
                    preserveState: true,
                }).then(() => {
                    this.TunnelBeingDeleted = null
                })
            },

            fromNow(timestamp) {
                return moment(timestamp).local().fromNow()
            },
        },
    }
</script>
