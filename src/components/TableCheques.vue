<template>
  <v-card>
    <v-card-title class="justify-space-between">
      Cheques
      <v-btn color="success">Adicionar</v-btn>
    </v-card-title>
    <v-card-text>
      <v-data-table
        :items="cheques"
        :headers="headers"
        :loading="loading"
        sort-by="id"
        sort-desc
        no-data-text="Nenhum cheque encontrado"
      ></v-data-table>
    </v-card-text>
  </v-card>
</template>

<script>
  export default {
    name: "TableCheques",
    data: () => ({
      loading: true,
      cheques: [],
      headers: [
        {text: 'Cod.', value: 'id'}
      ]
    }),
    methods: {
      async loadData() {
        this.loading = true;
        try {
          const {data: cheques} = await this.$http.get('/cheques');
          this.cheques = cheques;
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
