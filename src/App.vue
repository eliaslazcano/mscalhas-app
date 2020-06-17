<template>
  <v-app>
    <!-- Menu lateral -->
    <v-navigation-drawer
      app
      clipped
      v-if="$store.state.token"
      v-model="showMenu"
    >
      <!-- Dados do usuario -->
      <template v-slot:prepend>
        <v-list>
          <v-list-item>
            <v-list-item-avatar>
              <v-avatar color="primary" class="font-weight-medium elevation-2 white--text">{{$store.getters.nameInitials}}</v-avatar>
            </v-list-item-avatar>
            <v-list-item-content>
              <v-list-item-title>{{$store.getters.nameShort}}</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list>
      </template>
      <v-divider></v-divider>
      <!-- Lista de links -->
      <v-list dense>
        <v-list-item link to="/">
          <v-list-item-action>
            <v-icon>mdi-home</v-icon>
          </v-list-item-action>
          <v-list-item-content>
            <v-list-item-title>Início</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
        <v-list-item link to="/servicos">
          <v-list-item-action>
            <v-icon>mdi-tools</v-icon>
          </v-list-item-action>
          <v-list-item-content>
            <v-list-item-title>Serviços</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
        <v-list-item link to="/cheques">
          <v-list-item-action>
            <v-icon>mdi-checkbook</v-icon>
          </v-list-item-action>
          <v-list-item-content>
            <v-list-item-title>Cheques</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
        <v-list-item link to="/relatorios">
          <v-list-item-action>
            <v-icon>mdi-finance</v-icon>
          </v-list-item-action>
          <v-list-item-content>
            <v-list-item-title>Relatórios</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
        <v-list-item link to="/config">
          <v-list-item-action>
            <v-icon>mdi-cog</v-icon>
          </v-list-item-action>
          <v-list-item-content>
            <v-list-item-title>Configurações</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
      </v-list>
      <!-- Rodapé do menu -->
      <template v-slot:append>
        <div class="px-2 pb-3">
          <v-btn color="primary" class="white--text" large block @click="() => {showMenu = false; $store.dispatch('signout')}">SAIR</v-btn>
        </div>
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
      <v-toolbar-title class="ml-0 pl-4">MS CALHAS</v-toolbar-title>
    </v-app-bar>
    <!-- Conteúdo -->
    <v-main>
      <transition name="fade" mode="out-in" @after-leave="scrollTop">
        <router-view/>
      </transition>
    </v-main>
    <!-- Snackbar Global -->
    <v-snackbar
      :top="$store.state.snackbarOptions.top"
      :bottom="$store.state.snackbarOptions.bottom"
      :left="$store.state.snackbarOptions.left"
      :right="$store.state.snackbarOptions.right"
      :timeout="$store.state.snackbarOptions.timeout"
      :vertical="$store.state.snackbarOptions.vertical"
      :multi-line="$store.state.snackbarOptions.multiLine"
      :color="$store.state.snackbarOptions.color"
      :absolute="$store.state.snackbarOptions.absolute"
      :value="$store.state.snackbar"
      @input="$store.commit('snackbar', false)"
    >
      {{$store.state.snackbarOptions.text}}
      <v-btn dark icon @click="$store.commit('snackbar', false)">
        <v-icon>mdi-close-circle</v-icon>
      </v-btn>
    </v-snackbar>
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
