<template>
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
</template>

<script>
  export default {
    name: "AppBar",
    props: {
      value: {type: Boolean, default: null}
    },
    data: () => ({
      showMenu: null
    }),
    created() {
      this.showMenu = this.value;
    },
    watch: {
      showMenu: (v) => {this.$emit('input', v)},
    }
  }
</script>

<style scoped>

</style>