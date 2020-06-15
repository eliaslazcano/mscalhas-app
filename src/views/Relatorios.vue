<template>
  <v-container>
    <ticket-medio></ticket-medio>
    <div>
      <h1 class="text-center text-h6">Em desenvolvimento...</h1>
      <ul>
        <li>Ticket médio (faturamento dividido pelo número de serviços, dia/mes/semana/ano ou então os últimos 100 serviços)</li>
        <li>Faturamento total (de todos os sócios dia/mes/ano) com indicadores do dia/mes/ano passados</li>
        <li>Performance dos sócios (quantidade de serviços e respectivo faturamento, por dia/semana/mes/ano e data especifica) [subtela]</li>
      </ul>
    </div>
  </v-container>
</template>

<script>
  import TicketMedio from "../components/TicketMedio";
  export default {
    name: "Relatorios",
    components: {TicketMedio}
  }
</script>

<style scoped>

</style>