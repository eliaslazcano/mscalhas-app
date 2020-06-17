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
        :search="search"
        dense
      >
        <template v-slot:top>
          <v-text-field
            v-model="search"
            prepend-inner-icon="mdi-magnify"
            label="Pesquisar"
            single-line
            hide-details
          ></v-text-field>
        </template>
        <template v-slot:loading>
          <p class="my-5">Carregando serviços...</p>
        </template>
        <template v-slot:item.data_criacao="{item}">{{ajustaData(item.data_criacao)}}</template>
        <template v-slot:item.status="{item}">
          <v-chip color="success" v-if="item.data_finalizacao" small>FINALIZADO</v-chip>
          <v-chip color="warning" v-else small>EM ANDAMENTO</v-chip>
        </template>
        <template v-slot:item.valor="{item}">
          <span :class="{'green--text': (item.pago >= item.valor), 'red--text': item.pago === 0}">R$ {{item.pago >= item.valor ? formataValor(item.pago) : formataValor(item.valor)}}</span>
        </template>
      </v-data-table>
    </v-card-text>
  </v-card>
</template>

<script>
  import {DateHelper} from 'eliaslazcano-helpers'
  export default {
    name: "TableServicos",
    data: () => ({
      loading: true,
      servicos: [],
      headers: [
        {text: 'Cod.', value: 'id'},
        {text: 'Cliente', value: 'cliente_nome'},
        {text: 'Data de criação', value: 'data_criacao'},
        {text: 'Responsável', value: 'socio_responsavel_nome'},
        {text: 'Status', value: 'status'},
        {text: 'Valor', value: 'valor'}
      ],
      search: ''
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
      abrirServico(a) {this.$router.push('/servico/' + a.id)},
      ajustaData(datetime) {return DateHelper.date_SQLparaBR(datetime)},
      formataValor(valor = 0) {
        return valor.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ".")
      }
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
