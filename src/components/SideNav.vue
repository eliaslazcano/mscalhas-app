<template>
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
</template>

<script>
  export default {
    name: "SideNav",
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
      value: () => console.log('value mudou')
    }
  }
</script>

<style scoped>

</style>