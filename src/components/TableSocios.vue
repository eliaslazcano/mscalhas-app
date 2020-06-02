<template>
  <v-card>
    <v-card-title class="justify-space-between">
      Sócios
      <v-btn color="success" @click="addSocio" :loading="loadingAddbtn" :disabled="loadingAddbtn || loading">Adicionar</v-btn>
    </v-card-title>
    <v-card-text>
      <v-data-table
        :items="socios"
        :headers="[{value: 'nome', text: 'Nome'}, {value: 'actions', text: 'Ações', sortable: false}]"
        :loading="loading"
        sort-by="nome"
        no-data-text="Nenhum sócio"
      >
        <template v-slot:item.actions="{ item }">
          <v-tooltip top>
            <template v-slot:activator="{ on }">
              <v-btn icon v-on="on" :disabled="loading" color="amber" @click="renomearSocio(item)">
                <v-icon>mdi-pencil</v-icon>
              </v-btn>
            </template>
            <span>Renomear</span>
          </v-tooltip>
          <v-tooltip top>
            <template v-slot:activator="{ on }">
              <v-btn icon v-on="on" :disabled="loading" color="red" @click="removerSocio(item)">
                <v-icon>mdi-delete</v-icon>
              </v-btn>
            </template>
            <span>Remover</span>
          </v-tooltip>
        </template>
      </v-data-table>
    </v-card-text>
  </v-card>
</template>

<script>
  export default {
    name: "TableSocios",
    data: () => ({
      loading: true,
      loadingAddbtn: false,
      socios: [],
    }),
    methods: {
      async loadData() {
        this.loading = true;
        try {
          const {data: socios} = await this.$http.get('/socios');
          this.socios = socios;
        } finally {
          this.loading = false;
        }
      },
      async addSocio() {
        const nome = prompt("Digite o nome completo do sócio");
        if (!nome || !nome.trim()) return;
        this.loadingAddbtn = true;
        try {
          await this.$http.post('/socios', {nome});
          await this.loadData();
        } finally {
          this.loadingAddbtn = false;
        }
      },
      async renomearSocio(socio) {
        const nome = prompt("Digite o nome completo do sócio", socio.nome);
        if (!nome || !nome.trim()) return;
        this.loading = true;
        try {
          await this.$http.put('/socios', {id: socio.id, nome});
          await this.loadData();
        } finally {
          this.loading = false;
        }
      },
      async removerSocio(socio) {
        if (!confirm(`Tem certeza que deseja remover "${socio.nome}" do quadro societário?`)) return;
        this.loading = true;
        try {
          await this.$http.delete('/socios', {params: {id: socio.id}});
          await this.loadData();
        } finally {
          this.loading = false;
        }
      }
    },
    created() {this.loadData()}
  }
</script>

<style scoped>

</style>