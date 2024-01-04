<template >
    <header class="flex flex-row" >
        <div class="navbar bg-base-100">
            <div class="flex-1">
                <a class="btn btn-ghost text-xl">TunnelBroker.IO</a>
            </div>
            <div class="flex-none">
                <ul class="menu menu-horizontal px-1">
                    <li><Link :href="route('login')">Login</Link></li>
                    <li><Link :href="route('register')">Register</Link></li>
                    <li class="hidden md:block">
                        <label class="cursor-pointer grid place-items-center">
                            <input type="checkbox"  value="synthwave"  v-model="darkModel" @click="toggleDarkMode" class="toggle theme-controller bg-base-content row-start-1 col-start-1 col-span-2"/>
                            <svg class="col-start-1 row-start-1 stroke-base-100 fill-base-100" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/></svg>
                            <svg class="col-start-2 row-start-1 stroke-base-100 fill-base-100" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                        </label>
                    </li>

                </ul>
            </div>
        </div>
    </header>

</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import {onMounted, ref} from "vue";

const darkModel = ref(false)

onMounted(() => {
    darkModel.value = localStorage.getItem('darkMode') === 'true'
})

const toggleDarkMode = () => {
    const darkMode = localStorage.getItem('darkMode') === 'true'
    localStorage.setItem('darkMode', !darkMode)
    //Set the class on the body
    if (!darkMode) {
        document.body.classList.add('dark')
        document.body.classList.remove('light')
        document.querySelector("html").setAttribute("data-theme", "dark");
    } else {
        document.body.classList.add('light')
        document.body.classList.remove('dark')
        document.querySelector("html").setAttribute("data-theme", "light");
    }
}

</script>
