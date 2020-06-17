<template>
  <v-card>
    <v-card-title class="grey lighten-3">
      Faturamento por sócio
      <p class="ma-0 text-caption w-100">Veja a produtividade dos sócios</p>
    </v-card-title>
    <v-card-text>
      <v-autocomplete
        dense
        :items="years"
        v-model="yearSelected"
        label="Ano"
        class="mt-3 mb-0"
        hide-details
        outlined
        no-data-text="Não há dados no valor digitado"
        :loading="loading"
        :disabled="loading"
      ></v-autocomplete>
      <v-checkbox
        v-model="unify"
        label="Unificar o gráfico"
        class="my-0"
        hide-details
        :disabled="loading"
      ></v-checkbox>
      <div v-if="chartData">
        <bar-chart
          v-show="!unify"
          :chart-data="chartData"
          :options="chartOptions"
          style="height: 22rem"
        ></bar-chart>
        <bar-chart
          v-show="unify"
          :chart-data="chartData"
          :options="chartOptions2"
          style="height: 22rem"
        ></bar-chart>
      </div>
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
      chartOptions2: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true,
              callback: function(value) { //, index, values
                return 'R$ ' + value.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ".");
              }
            },
            stacked: true
          }],
          xAxes: [{
            stacked: true
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
      ],
      yearSelected: (new Date()).getFullYear(),
      unify: false //Mostra o gráfico que as barras estão em uma só
    }),
    methods: {
      async loadData() {
        this.loading = true;
        try {
          const {data} = await this.$http.post('/relatorios/rentabilidade_socio', {ano: this.yearSelected});
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
        if (i < this.colors.length) return this.colors[i];
        let x = Math.trunc(i / this.colors.length);
        return this.colors[i - (this.colors.length * x)];
      },
    },
    computed: {
      years() {
        const anoAtual = new Date().getFullYear();
        let list = [];
        for (let i = 2018; i <= anoAtual; i++) list.push(i);
        return list;
      }
    },
    watch: {
      yearSelected(v) {
        if (v) this.loadData();
      }
    },
    mounted() {
      this.loadData();
    }
  }
</script>

<style scoped>

</style>