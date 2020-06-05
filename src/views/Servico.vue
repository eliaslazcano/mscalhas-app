<template>
  <v-container>
    <!-- Informações do cliente -->
    <v-card>
      <v-card-title class="grey lighten-3">
        <v-icon class="mr-2">mdi-account-tie</v-icon>
        Informações do cliente
      </v-card-title>
      <v-card-text>
        <!-- Dados pessoais -->
        <div>
          <p class="mb-0 mt-3">Dados pessoais</p>
          <v-row>
            <v-col cols="12" sm="6">
              <v-text-field
                outlined dense hide-details
                label="Nome do cliente"
                v-model="servico.cliente_nome"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                outlined dense hide-details
                label="CPF/CNPJ"
                v-model="servico.cliente_cpfcnpj"
              ></v-text-field>
            </v-col>
          </v-row>
        </div>
        <v-divider class="py-2"></v-divider>
        <!-- Dados para contato -->
        <div>
          <p class="mb-0">Dados para contato</p>
          <v-row>
            <v-col cols="12" sm="6" lg="4">
              <v-text-field
                outlined dense hide-details
                label="Telefone 1"
                v-model="servico.contato_fone"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6" lg="4">
              <v-text-field
                outlined dense hide-details
                label="Telefone 2"
                v-model="servico.contato_fone2"
              ></v-text-field>
            </v-col>
            <v-col cols="12" lg="4">
              <v-text-field
                outlined dense hide-details
                label="E-mail"
                v-model="servico.contato_email"
              ></v-text-field>
            </v-col>
          </v-row>
        </div>
        <v-divider class="py-2"></v-divider>
        <!-- Local do serviço -->
        <div>
          <p class="mb-0 mt-3">Local do serviço</p>
          <v-row>
            <v-col cols="12" sm="9" lg="5">
              <v-text-field
                outlined dense hide-details
                label="Rua/Logradouro"
                v-model="servico.endereco_logradouro"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="3" lg="2">
              <v-text-field
                outlined dense hide-details
                label="Número"
                v-model="servico.endereco_numero"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6" lg="5">
              <v-text-field
                outlined dense hide-details
                label="Bairro/Distrito"
                v-model="servico.endereco_bairro"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6" lg="5">
              <v-text-field
                outlined dense hide-details
                label="Complemento"
                v-model="servico.endereco_complemento"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="9" lg="5">
              <v-text-field
                outlined dense hide-details
                label="Cidade"
                v-model="servico.endereco_cidade"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="3" lg="2">
              <v-text-field
                outlined dense hide-details
                label="Estado/UF"
                v-model="servico.endereco_uf"
              ></v-text-field>
            </v-col>
          </v-row>
        </div>
      </v-card-text>
    </v-card>
    <!-- Informações da obra -->
    <v-card class="mt-4">
      <v-card-title class="grey lighten-3">
        <v-icon class="mr-2">mdi-hammer-screwdriver</v-icon>
        Informações do serviço
      </v-card-title>
      <v-card-text>
        <p class="mb-1 mt-3">Descrição do serviço</p>
        <v-textarea
          outlined
          auto-grow
          hide-details
          placeholder="Escreva aqui o serviço que deve ser realizado"
          v-model="servico.descricao"
        ></v-textarea>
        <v-divider></v-divider>
        <p class="mb-1 mt-3">Observações / Anotações extras</p>
        <v-textarea
          outlined
          auto-grow
          hide-details
          placeholder="Escreva aqui anotações extras"
          v-model="servico.observacao"
        ></v-textarea>
      </v-card-text>
    </v-card>
    <!-- DEBUG -->
    <div class="pa-1 mb-3" style="border: 1px solid black">
      <p class="title">ID</p>
      <pre>{{id === null ? 'null' : id}}</pre>
      <hr>
      <p class="title">servico</p>
      <pre>{{servico}}</pre>
    </div>
  </v-container>
</template>

<script>
  export default {
    name: "Servico",
    props: {
      id: {default: null}
    },
    data: () => ({
      loading: false,
      servico: {}
    }),
    methods: {
      async loadData() {
        this.loading = true;
        try {
          const {data: servico} = await this.$http.get('/servicos', {params: {id: this.id}})
          this.servico = servico;
        } finally {
          this.loading = false;
        }
      }
    },
    created() {
      if (this.id) this.loadData();
    },
    watch: {
      id(v) {
        if (v) this.loadData();
      }
    }
  }
</script>

<style scoped>

</style>