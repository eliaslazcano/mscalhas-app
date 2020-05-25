<template>
  <v-app>
    <!-- Menu lateral -->
    <SideNav v-model="showMenu"></SideNav>
    <!-- Barra de ferramentas -->
    <AppBar></AppBar>
    <!-- Conteúdo -->
    <v-content>
      <transition name="fade" mode="out-in" @after-leave="scrollTop">
        <router-view/>
      </transition>
    </v-content>
  </v-app>
</template>

<script>
import SideNav from "./components/SideNav";
import AppBar from "./components/AppBar";
export default {
  name: 'App',
  components: {AppBar, SideNav},
  data: () => ({
    showMenu: null
  }),
  methods: {
    scrollTop() {
      window.scrollTo(0, 0);
    }
  }
};
</script>

<style>
  .fade-enter-active,
  .fade-leave-active {
    transition-duration: 300ms;
    transition-property: opacity;
    transition-timing-function: ease-out;
  }
  .fade-enter,
  .fade-leave-to {
    opacity: 0;
  }
  .fade-leave,
  .fade-enter-to {
    opacity: 1;
  }
</style>
