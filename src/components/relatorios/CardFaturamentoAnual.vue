<template>
  <v-card>
    <v-card-title class="grey lighten-3">
      Faturamento Anual
      <p class="ma-0 text-caption w-100">Veja se a empresa está evoluindo</p>
    </v-card-title>
    <v-card-text>
      <line-chart
        v-if="chartData"
        :chart-data="chartData"
        :options="chartOptions"
        style="height: 22rem"
      ></line-chart>
      <div v-else class="d-flex justify-center align-center flex-column" style="height: 22rem">
        <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
        <p class="caption mb-0 mt-2">CALCULANDO</p>
      </div>
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
      chartData: null,
      chartOptions: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true,
              callback: function(value) { //, index, values
                return 'R$ ' + value.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ".");
              }
            }
          }]
        },
        tooltips: {
          enabled: true,
          mode: 'single',
          callbacks: {
            label: function(tooltipItems) {
              return 'R$ ' + tooltipItems.yLabel.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
          }
        },
        title: {
          display: true,
          text: 'Clique no ano para remove-lo ou exibi-lo'
        }
      }
    }),
    methods: {
      async loadData() {
        const date = new Date();
        const anoAtual = date.getFullYear();
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
                fill: false,
                hidden: true
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
                data: requests[0].data.filter((x, i) => i <= date.getMonth()),
                fill: false
              }
            ]
          }
        } finally {
          this.loading = false;
        }
      }
    },
    mounted() {
      this.loadData();
    }
  }
</script>

<style scoped>

</style>