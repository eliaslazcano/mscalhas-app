<template>
  <v-card>
    <v-card-title class="grey lighten-3">Faturamento Anual</v-card-title>
    <v-card-text>
      <line-chart
        v-if="chartData"
        :chart-data="chartData"
        style="height: 22rem"
      ></line-chart>
    </v-card-text>
  </v-card>
</template>

<script>
  import LineChart from "../chart/LineChart";
  export default {
    name: "CardFaturamentoAnual",
    components: {LineChart},
    data: () => ({
      loading: true,
      chartData: null
    }),
    methods: {
      async loadData() {
        const anoAtual = (new Date()).getFullYear();
        // const anos = [
        //   anoAtual,
        //   anoAtual -1,
        //   anoAtual -2
        // ];
        this.loading = true;
        try {
          const requests = await Promise.all([
            this.$http.post('/relatorios/faturamento', {ano: anoAtual}),
            this.$http.post('/relatorios/faturamento', {ano: anoAtual -1}),
            this.$http.post('/relatorios/faturamento', {ano: anoAtual -2})
          ])
          this.chartData = {
            labels: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
            datasets: [
              {
                label: anoAtual -2,
                backgroundColor: 'rgb(255, 99, 132)',
                borderColor: 'rgb(255, 99, 132)',
                data: requests[2].data,
                fill: false
              },
              {
                label: anoAtual -1,
                backgroundColor: 'rgb(255, 159, 64)',
                borderColor: 'rgb(255, 159, 64)',
                data: requests[1].data,
                fill: false
              },
              {
                label: anoAtual,
                backgroundColor: 'rgb(54, 162, 235)',
                borderColor: 'rgb(54, 162, 235)',
                data: requests[0].data,
                fill: false
              }
            ]
          }
        } finally {
          this.loading = false;
        }
      }
    },
    created() {
      this.loadData();
    }
  }
</script>

<style scoped>

</style>