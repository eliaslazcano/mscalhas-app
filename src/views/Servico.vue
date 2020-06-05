<template>
  <v-container>
    <!-- Status e Pagamento -->
    <v-row>
      <v-col cols="12" sm="6">
        <v-card color="amber" dark height="8.5rem" class="d-flex flex-column">
          <v-card-subtitle class="pb-0 text-center"><v-icon class="mr-2">mdi-progress-wrench</v-icon>STATUS DO SERVIÇO</v-card-subtitle>
          <div class="flex-grow-1 d-flex align-center justify-center">
            <v-card-title class="py-0 justify-center">{{finalizado ? 'FINALIZADO' : 'EM ANDAMENTO'}}</v-card-title>
          </div>
          <div class="d-flex justify-center">
            <v-btn outlined class="mb-2">{{finalizado ? 'Voltar para "em andamento"' : 'Mudar para finalizado'}}</v-btn>
          </div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6">
        <v-card color="red" dark height="8.5rem" class="d-flex flex-column">
          <v-card-subtitle class="pb-0 text-center"><v-icon class="mr-2">mdi-cash-multiple</v-icon>PAGAMENTO</v-card-subtitle>
          <div class="flex-grow-1 d-flex align-center justify-center">
            <v-card-title class="py-0 justify-center">NÃO FOI PAGO</v-card-title>
          </div>
          <div class="d-flex justify-center">
            <v-btn outlined class="mb-2">Ajustar pagamentos</v-btn>
          </div>
        </v-card>
      </v-col>
    </v-row>
    <v-select
      label="Sócio responsável"
      v-model="servico.socio_responsavel"
      :items="socios"
      item-value="id"
      item-text="nome"
      no-data-text="Nenhum sócio disponível"
      placeholder="Clique para escolher o sócio responsável"
      outlined
      hide-details
      class="my-3"
    ></v-select>
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
                :disabled="loading"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                outlined dense hide-details
                label="CPF/CNPJ"
                v-model="servico.cliente_cpfcnpj"
                :disabled="loading"
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
                :disabled="loading"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6" lg="4">
              <v-text-field
                outlined dense hide-details
                label="Telefone 2"
                v-model="servico.contato_fone2"
                :disabled="loading"
              ></v-text-field>
            </v-col>
            <v-col cols="12" lg="4">
              <v-text-field
                outlined dense hide-details
                label="E-mail"
                v-model="servico.contato_email"
                :disabled="loading"
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
                :disabled="loading"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="3" lg="2">
              <v-text-field
                outlined dense hide-details
                label="Número"
                v-model="servico.endereco_numero"
                :disabled="loading"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6" lg="5">
              <v-text-field
                outlined dense hide-details
                label="Bairro/Distrito"
                v-model="servico.endereco_bairro"
                :disabled="loading"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6" lg="5">
              <v-text-field
                outlined dense hide-details
                label="Complemento"
                v-model="servico.endereco_complemento"
                :disabled="loading"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="9" lg="5">
              <v-text-field
                outlined dense hide-details
                label="Cidade"
                v-model="servico.endereco_cidade"
                :disabled="loading"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="3" lg="2">
              <v-text-field
                outlined dense hide-details
                label="Estado/UF"
                v-model="servico.endereco_uf"
                :disabled="loading"
              ></v-text-field>
            </v-col>
          </v-row>
        </div>
      </v-card-text>
    </v-card>
    <!-- Informações do serviço -->
    <v-card class="my-4">
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
          :disabled="loading"
        ></v-textarea>
        <v-divider></v-divider>
        <p class="mb-1 mt-3">Observações / Anotações extras</p>
        <v-textarea
          outlined
          auto-grow
          hide-details
          placeholder="Escreva aqui anotações extras"
          v-model="servico.observacao"
          :disabled="loading"
        ></v-textarea>
        <v-divider></v-divider>
        <p class="mb-1 mt-3">Valor do serviço</p>
        <v-input hide-details>
          <money
            id="ipt-valor"
            class="form-control font-weight-bold w-100"
            v-model="servico.valor"
            v-bind="{prefix: 'R$ ', precision: 2, thousands: '.', decimal: ',', masked: false}"
            @focusin.native="$refs.valor.isFocused = $refs.valor.hasColor = iptValorFocused = true"
            @focusout.native="$refs.valor.isFocused = iptValorFocused = false"
            :disabled="loading"
          ></money>
        </v-input>
      </v-card-text>
    </v-card>
    <!-- Botões -->
    <div class="d-flex justify-center">
      <v-btn color="primary" @click="saveData" class="mr-2">
        <v-icon class="mr-1">mdi-sd</v-icon>GRAVAR {{id ? 'ALTERAÇÕES' : 'SERVIÇO'}}
      </v-btn>
      <v-btn color="primary" outlined to="/servicos">{{id ? 'VOLTAR' : 'CANCELAR'}}</v-btn>
    </div>
    <!-- DEBUG -->
    <div class="pa-1 mb-1 ml-1" style="border: 1px solid black; position: fixed; left: 0; bottom: 0; background-color: rgba(255,255,255,.75); font-size: .6rem; z-index: 999">
      <pre>ID: {{id === null ? 'null' : id}}</pre>
      <hr>
      <p class="caption mb-1">servico</p>
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
      loading: true,
      socios: [],
      servico: {}
    }),
    computed: {
      finalizado() {return this.servico ? (!!this.servico.data_finalizacao) : null}
    },
    methods: {
      async loadData() {
        this.loading = true;
        try {
          const {data: servico} = await this.$http.get('/servicos', {params: {id: this.id}})
          this.servico = servico;
        } finally {
          this.loading = false;
        }
      },
      async saveData() {
        this.loading = true;
        try {
          if (!this.id) {
            //Insert
            const {data: id} = await this.$http.post('/servicos', this.servico)
            this.$router.push(`/servico/${id}`);
          } else {
            const {data: atualizado} = await this.$http.put('/servicos', this.servico);
            if (atualizado) await this.loadData();
          }
        } finally {
          this.loading = false;
        }
      }
    },
    async created() {
      const {data: socios} = await this.$http.get('/socios');
      this.socios = socios;
      if (this.id) await this.loadData();
      this.loading = false;
    },
    watch: {
      id(v) {
        if (v) this.loadData();
      }
    }
  }
</script>
