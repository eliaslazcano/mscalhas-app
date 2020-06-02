<template>
  <v-container>
    <table-socios></table-socios>
    <table-usuarios class="my-5"></table-usuarios>
  </v-container>
</template>

<script>
  import TableSocios from "../components/TableSocios";
  import TableUsuarios from "../components/TableUsuarios";
  export default {
    name: "Config",
    components: {TableUsuarios, TableSocios}
  }
</script>

<style scoped>

</style>