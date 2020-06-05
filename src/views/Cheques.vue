<template>
  <v-container fluid>
    <table-cheques></table-cheques>
  </v-container>
</template>

<script>
  import TableCheques from "../components/TableCheques";
  export default {
    name: "Cheques",
    components: {TableCheques}
  }
</script>
