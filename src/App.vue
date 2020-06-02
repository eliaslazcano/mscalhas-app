<template>
  <v-app>
    <!-- Menu lateral -->
    <v-navigation-drawer
      app
      clipped
      v-if="$store.state.token"
      v-model="showMenu"
    >
      <template v-slot:prepend>
        <v-list></v-list>
      </template>
    </v-navigation-drawer>
    <!-- Barra de ferramentas -->
    <v-app-bar
      app
      clipped-left
      dark
      color="primary"
      v-if="$store.state.token"
    >
      <v-app-bar-nav-icon @click.stop="showMenu = !showMenu" />
      <v-toolbar-title class="ml-0 pl-4">MS Calhas</v-toolbar-title>
    </v-app-bar>
    <!-- Conteúdo -->
    <v-content>
      <transition name="fade" mode="out-in" @after-leave="scrollTop">
        <router-view/>
      </transition>
    </v-content>
  </v-app>
</template>

<script>
export default {
  name: 'App',
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
