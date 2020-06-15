<template>
  <v-container>
    <v-card class="elevation-4">
      <v-card-title class="grey lighten-3">Resumo</v-card-title>
      <v-card-text>
        <v-row>
          <v-col cols="12" md="4">
            <card-ticket-medio></card-ticket-medio>
          </v-col>
          <v-col cols="12" md="4">
            <card-faturamento></card-faturamento>
          </v-col>
          <v-col cols="12" md="4">
            <card-servicos></card-servicos>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script>
  import CardTicketMedio from "../components/relatorios/CardTicketMedio";
  import CardFaturamento from "../components/relatorios/CardFaturamento";
  import CardServicos from "../components/relatorios/CardServicos";
  export default {
    name: "Relatorios",
    components: {CardServicos, CardFaturamento, CardTicketMedio}
  }
</script>

<style scoped>

</style>