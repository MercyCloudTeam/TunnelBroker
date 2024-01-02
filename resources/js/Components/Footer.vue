<template>
    <footer class="footer footer-center p-4 bg-base-300 text-base-content">
        <aside>
            <p>Copyright © 2020 ~ {{new Date().getFullYear()}} - All right reserved by <a href="https://tunnelbroker.io">TunnelBroker.IO</a></p>
            <p>
                <BeakerIcon class="inline-block w-4 h-4 align-text-bottom" />
                <a href="https://github.com/MercyCloudTeam/TunnelBroker/tree/dev">Github</a>
            </p>
        </aside>
    </footer>
</template>

<script setup>
import { BeakerIcon } from '@heroicons/vue/24/solid'
</script>
