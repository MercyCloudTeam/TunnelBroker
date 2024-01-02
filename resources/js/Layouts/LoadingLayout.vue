<script setup>
import { Link } from '@inertiajs/vue3';
import Header from "@/Components/Header.vue";
import Footer from "@/Components/Footer.vue";
import {onMounted} from "vue";

//Get Dark Mode localStorage
const darkMode = localStorage.getItem('darkMode');

onMounted(() => {
    if (darkMode === 'true') {
        document.body.classList.add('dark')
        document.body.classList.remove('light')
        document.querySelector("html").setAttribute("data-theme", "dark");
    } else {
        document.body.classList.add('light')
        document.body.classList.remove('dark')
        document.querySelector("html").setAttribute("data-theme", "light");
    }
})

</script>

<template>
  <div class="">
      <Header />
      <div class="flex flex-col items-center pt-6">
          <div>
              <slot />
          </div>
      </div>
      <Footer />
  </div>
</template>
