<template>
  <v-container>
    <v-card class="elevation-3">
      <v-card-title class="grey lighten-3">
        Resumo
        <p class="ma-0 text-caption w-100">Informações básicas do negócio</p>
      </v-card-title>
      <v-card-text class="pa-3 pa-lg-4">
        <v-row :dense="$vuetify.breakpoint.smAndDown">
          <v-col cols="12" sm="4">
            <card-ticket-medio></card-ticket-medio>
          </v-col>
          <v-col cols="12" sm="4">
            <card-faturamento></card-faturamento>
          </v-col>
          <v-col cols="12" sm="4">
            <card-servicos></card-servicos>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
    <card-faturamento-anual class="elevation-3 my-4"></card-faturamento-anual>
    <card-faturamento-socio class="elevation-3 my-4"></card-faturamento-socio>
    <v-card>
      <v-card-title>Aviso</v-card-title>
      <v-card-text>
        <p class="text-caption">Os calculos de faturamento não levam em consideração os "cheques" que foram adicionados de forma avulsa no sistema. Somente os cheques que foram registrados como pagamento de serviço entram no calculo.</p>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script>
  import CardTicketMedio from "../components/relatorios/CardTicketMedio";
  import CardFaturamento from "../components/relatorios/CardFaturamento";
  import CardServicos from "../components/relatorios/CardServicos";
  import CardFaturamentoAnual from "../components/relatorios/CardFaturamentoAnual";
  import CardFaturamentoSocio from "../components/relatorios/CardFaturamentoSocio";
  export default {
    name: "Relatorios",
    components: {CardFaturamentoSocio, CardFaturamentoAnual, CardServicos, CardFaturamento, CardTicketMedio}
  }
</script>

<style scoped>

</style>