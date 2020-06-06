<template>
  <v-card>
    <v-card-title class="justify-space-between">
      Serviços
      <v-btn color="success" to="/servico">Adicionar</v-btn>
    </v-card-title>
    <v-card-text>
      <v-data-table
        :items="servicos"
        :headers="headers"
        :loading="loading"
        sort-by="id"
        sort-desc
        no-data-text="Nenhum serviço encontrado"
        @click:row="abrirServico"
      >
        <template v-slot:item.status="{item}">
          <span v-if="item.data_finalizacao" class="green--text">FINALIZADO</span>
          <span v-else class="amber--text">EM ANDAMENTO</span>
        </template>
        <template v-slot:item.valor="{item}">R$ {{item.valor.toFixed(2).replace('.', ',')}}</template>
      </v-data-table>
    </v-card-text>
  </v-card>
</template>

<script>
  export default {
    name: "TableServicos",
    data: () => ({
      loading: true,
      servicos: [],
      headers: [
        {text: 'Cod.', value: 'id'},
        {text: 'Cliente', value: 'cliente_nome'},
        {text: 'Status', value: 'status'},
        {text: 'Valor', value: 'valor'},
        {text: 'Responsável', value: 'socio_responsavel_nome'}
      ]
    }),
    methods: {
      async loadData() {
        this.loading = true;
        try {
          const {data: servicos} = await this.$http.get('/servicos');
          this.servicos = servicos;
        } finally {
          this.loading = false;
        }
      },
      abrirServico(a) {this.$router.push('/servico/' + a.id)}
    },
    created() {
      this.loadData();
    }
  }
</script>

<style>
  .v-data-table tbody tr:hover {
    cursor: pointer;
  }
</style>
