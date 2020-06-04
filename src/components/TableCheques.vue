<template>
  <v-card>
    <v-card-title class="justify-space-between">
      Cheques
      <v-dialog v-model="dialogAdd" max-width="30rem" scrollable>
        <template v-slot:activator="{on}">
          <v-btn color="success" v-on="on">Adicionar</v-btn>
        </template>
        <v-card>
          <v-card-title class="primary white--text elevation-3">Adicionar cheque</v-card-title>
          <v-card-text class="pt-3">
            <v-form ref="formadd">
              <v-text-field
                label="Nome do cliente"
                v-model="iptCliente"
                hint="Exemplo: João Appolinário"
                :rules="requiredRule"
              ></v-text-field>
              <v-text-field
                label="Nome do banco"
                v-model="iptBanco"
                hint="Exemplo: Banco do Brasil"
              ></v-text-field>
              <v-text-field
                label="Agencia"
                v-model="iptAgencia"
                hint="Exemplo: 5799-1"
              ></v-text-field>
              <v-text-field
                label="Conta"
                v-model="iptConta"
                hint="Exemplo: 6541-2"
              ></v-text-field>
              <v-radio-group
                label="Forma de resgate"
                v-model="iptTipo"
                :rules="requiredRule"
              >
                <v-radio
                  label="Saque em dinheiro"
                  :value="0"
                ></v-radio>
                <v-radio
                  label="Deposito em conta (cheque cruzado/riscado)"
                  :value="1"
                ></v-radio>
              </v-radio-group>
              <v-text-field
                label="Número do cheque"
                v-model="iptNumero"
                hint="Exemplo: 123456"
              ></v-text-field>
              <v-input class="v-text-field" ref="valor">
                <v-label for="ipt-valor" absolute value :focused="iptValorFocused">Valor do cheque</v-label>
                <money
                  id="ipt-valor"
                  v-model="iptValor"
                  v-bind="{prefix: 'R$ ', precision: 2, thousands: '.', decimal: ',', masked: false}"
                  @focusin.native="$refs.valor.isFocused = $refs.valor.hasColor = iptValorFocused = true"
                  @focusout.native="$refs.valor.isFocused = iptValorFocused = false"
                ></money>
              </v-input>
              <v-menu
                v-model="datepicker"
                :close-on-content-click="false"
                :nudge-right="40"
                transition="scale-transition"
                offset-y
                top
                min-width="290px"
              >
                <template v-slot:activator="{ on }">
                  <v-text-field
                    label="Data do cheque"
                    prepend-icon="mdi-calendar"
                    readonly
                    v-on="on"
                    clearable
                    hint="Data prevista para o resgate do valor"
                    persistent-hint
                    v-model="iptDataBr"
                  ></v-text-field>
                </template>
                <v-date-picker @input="datepicker = false" no-title v-model="iptData"></v-date-picker>
              </v-menu>
            </v-form>
          </v-card-text>
          <v-card-actions class="justify-center elevation-3">
            <v-btn color="primary" @click="submit">Salvar</v-btn>
            <v-btn color="primary" text @click="dialogAdd = false">Cancelar</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
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
  //TODO - Fazer um Rule para o money
  import {DateHelper} from 'eliaslazcano-helpers'
  export default {
    name: "TableCheques",
    data: () => ({
      loading: true,
      cheques: [],
      headers: [
        {text: 'Cod.', value: 'id'}
      ],
      dialogAdd: false,
      datepicker: false,
      //Inputs
      iptCliente: '',
      iptBanco: '',
      iptAgencia: '',
      iptConta: '',
      iptTipo: null,
      iptNumero: '',
      iptValor: 0,
      iptData: '',
      iptValorFocused: false,
      requiredRule: [
        v => !!v || v === 0 || 'Preencha este campo'
      ]
    }),
    computed: {
      iptDataBr() {return this.iptData ? DateHelper.date_SQLparaBR(this.iptData) : ''}
    },
    methods: {
      async loadData() {
        this.loading = true;
        try {
          const {data: cheques} = await this.$http.get('/cheques');
          this.cheques = cheques;
        } finally {
          this.loading = false;
        }
      },
      async submit() {
        if (!this.$refs.formadd.validate()) return;
        alert('Enviar dados...')
      }
    },
    created() {
      this.loadData();
    }
  }
</script>
