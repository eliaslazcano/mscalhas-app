<template>
  <v-card>
    <v-card-title class="grey lighten-3">Faturamento Anual</v-card-title>
    <v-card-text>
      GRAFICO AQUI
    </v-card-text>
  </v-card>
</template>

<script>
  export default {
    name: "CardFaturamentoAnual",
    data: () => ({
      loading: true,
      dados: null
    }),
    methods: {
      async loadData() {
        this.loading = true;
        try {
          const {data} = await this.$http.post('/relatorios/faturamento', {ano: 2020});
          this.dados = data;
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