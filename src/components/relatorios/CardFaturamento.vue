<template>
  <v-card color="success" dark style="height: 100%" class="d-flex">
    <v-card-text class="text-center d-flex flex-grow-1 flex-column">
      <p class="text-h6 ma-0">Faturamento</p>
      <v-progress-linear
        v-if="loading"
        indeterminate
        color="white"
        class="my-5"
      ></v-progress-linear>
      <p class="text-h4 ma-0" v-else>R$ {{faturamento ? faturamento.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ".") : '00,00'}}</p>
      <p class="text-caption ma-0">VALOR RECEBIDO DOS SERVIÇOS</p>
      <div class="d-flex flex-grow-1 justify-center align-end mt-3">
        <v-select :items="options" dense hide-details solo-inverted v-model="periodo"></v-select>
      </div>
    </v-card-text>
  </v-card>
</template>

<script>
  export default {
    name: "CardFaturamento",
    data: () => ({
      loading: true,
      valores: null,
      options: [
        {text: 'do dia', value: 'd'},
        {text: 'do mês', value: 'm'},
        {text: 'do ano', value: 'a'},
        {text: 'de tudo', value: 't'}
      ],
      periodo: 'm'
    }),
    computed: {
      faturamento() {
        if (this.valores) {
          return this.valores[this.periodo];
        }
        return 0;
      }
    },
    methods: {
      async loadData() {
        this.loading = true;
        try {
          const {data} = await this.$http.get('/relatorios/faturamento');
          this.valores = data;
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
