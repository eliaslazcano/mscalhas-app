<template>
  <v-card>
    <v-card-title class="grey lighten-3">
      Faturamento por sócio
      <p class="ma-0 text-caption w-100">Veja a produtividade dos sócios</p>
    </v-card-title>
    <v-card-text>
      <bar-chart
        v-if="chartData"
        :chart-data="chartData"
        :options="chartOptions"
        style="height: 22rem"
      ></bar-chart>
      <div v-else class="d-flex justify-center align-center flex-column" style="height: 22rem">
        <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
        <p class="caption mb-0 mt-2">CALCULANDO</p>
      </div>
    </v-card-text>
  </v-card>
</template>

<script>
  import BarChart from "../chart/BarChart";
  export default {
    name: "CardFaturamentoSocio",
    components: {BarChart},
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
          text: 'Clique no sócio para remove-lo ou exibi-lo'
        }
      },
      colors: [
        'rgb(54, 162, 235)',
        'rgb(255, 159, 64)',
        'rgb(255, 205, 86)',
        'rgb(255, 99, 132)',
        'rgb(75, 192, 192)',
        'rgb(153, 102, 255)',
        'rgb(201, 203, 207)'
      ]
    }),
    methods: {
      async loadData() {
        this.loading = true;
        try {
          const {data} = await this.$http.post('/relatorios/rentabilidade_socio', {ano: 2020});
          let dataset = data.map((item, index) => ({
            label: item.nome,
            data: item.faturamento,
            backgroundColor: this.getColor(index),
            borderColor: this.getColor(index)
          }));
          this.chartData = {
            labels: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
            datasets: dataset
          };

        } finally {
          this.loading = false;
        }
      },
      getColor(i = 0) {
        //7 cores, de 0 a 6.
        if (i < this.colors.length) return this.colors[i];
        let x = Math.trunc(i / this.colors.length);
        return this.colors[i - (this.colors.length * x)];
      }
    },
    mounted() {
      this.loadData();
    }
  }
</script>

<style scoped>

</style>