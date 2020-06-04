<template>
  <v-container>
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
