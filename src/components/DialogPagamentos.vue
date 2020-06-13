<template>
  <v-dialog
    fullscreen
    hide-overlay
    transition="dialog-bottom-transition"
    v-model="showDialog"
  >
    <v-card>
      <v-toolbar dark color="primary" tile>
        <v-btn icon dark @click="showDialog = false">
          <v-icon>mdi-close</v-icon>
        </v-btn>
        <v-toolbar-title>Pagamento</v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-dialog max-width="25rem" v-model="dialogPagar">
            <template v-slot:activator="{on}">
              <v-btn dark text v-on="on">
                <v-icon class="mr-1">mdi-cash-plus</v-icon>Adicionar
              </v-btn>
            </template>
            <v-card>
              <v-card-title>Registrar pagamento</v-card-title>
              <v-card-text>
                <v-form ref="form-addpayment">
                  <!-- Forma de pagamento -->
                  <div>
                    <p class="mb-0">Forma de pagamento</p>
                    <v-select
                      label="Forma de pagamento"
                      :items="formasPagamento"
                      placeholder="clique para escolher"
                      :rules="[v => (v !== false && v !== null) || 'Selecione a forma de pagamento']"
                      v-model="inputFormaPagamento"
                      solo
                      dense
                    ></v-select>
                  </div>
                  <!-- Data do pagamento -->
                  <v-menu
                    v-model="showDatepickerNewCheque"
                    :close-on-content-click="false"
                    :nudge-right="40"
                    transition="scale-transition"
                    offset-y
                    top
                    min-width="290px"
                  >
                    <template v-slot:activator="{ on }">
                      <div>
                        <p class="mb-0">Data do recebimento</p>
                        <v-text-field
                          label="Data do recebimento"
                          prepend-inner-icon="mdi-calendar"
                          readonly
                          v-on="on"
                          persistent-hint
                          v-model="inputDataBr"
                          :disabled="loading"
                          solo
                          dense
                        ></v-text-field>
                      </div>
                    </template>
                    <v-date-picker @input="showDatepickerNewCheque = false" no-title v-model="inputData"></v-date-picker>
                  </v-menu>
                  <!-- Parcelas -->
                  <div v-if="inputFormaPagamento === 3">
                    <p class="mb-0">Nº de parcelas</p>
                    <v-text-field
                      label="Nº de parcelas"
                      v-model.number="inputParcelas"
                      solo
                      v-money="{prefix: '', precision: 0, thousands: ''}"
                      :rules="[v => !!v || 'Digite a quantidade de parcelas']"
                    ></v-text-field>
                  </div>
                  <!-- Valor -->
                  <div>
                    <p class="mb-0">Valor do pagamento</p>
                    <v-input
                      v-model="inputValor"
                      :rules="[v => !!v || 'Digite o valor']">
                      <money
                        class="form-control font-weight-bold w-100"
                        v-bind="{prefix: 'R$ ', precision: 2, thousands: '.', decimal: ',', masked: false}"
                        :disabled="loading"
                        v-model="inputValor"
                      ></money>
                    </v-input>
                  </div>
                </v-form>
              </v-card-text>
              <v-card-actions class="justify-center">
                <v-btn outlined color="primary" @click="dialogPagar = false" :disabled="loading">Cancelar</v-btn>
                <v-btn color="primary" :disabled="loading" :loading="loading" @click="insertPayment">Salvar</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-toolbar-items>
      </v-toolbar>
      <div class="pa-3">
        <v-card outlined class="mb-3" :loading="loading">
          <v-card-text>
            <div class="d-flex justify-space-between align-center">
              <div>
                <p class="mb-1 font-weight-bold">Valor do serviço</p>
                <v-input hide-details>
                  <money
                    class="form-control font-weight-bold w-100"
                    v-bind="{prefix: 'R$ ', precision: 2, thousands: '.', decimal: ',', masked: false}"
                    :disabled="loading"
                    v-model="service.valor"
                  ></money>
                </v-input>
              </div>
              <div class="font-weight-bold text-sm-left text-right">
                <p class="mb-0">Valor pago até o momento:</p>
                <p class="mb-0">R$ {{service.pagamentos ? service.pagamentos.reduce((accumulator, currentValue) => accumulator + currentValue.valor, 0).toFixed(2).replace('.', ',') : '0,00'}}</p>
              </div>
            </div>
          </v-card-text>
        </v-card>
        <v-card>
          <v-card-text>
            <v-data-table
              :items="service.pagamentos"
              :headers="headers"
              no-data-text="Nenhum pagamento registrado até agora"
              :items-per-page="999"
              hide-default-footer
              :loading="loading"
            >
              <template v-slot:item.tipo="{item}">
                {{formasPagamento.find(x => x.value === item.tipo).text}}
              </template>
              <template v-slot:item.valor="{item}">
                R$ {{item.valor ? item.valor.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ".") : 'ZERO'}}
              </template>
              <template v-slot:item.data_pagamento="{item}">
                {{ajustaData(item.data_pagamento)}}
              </template>
              <template v-slot:item.obs="{item}">
                <v-chip v-if="item.tipo === 3" small color="primary">{{item.parcelas}}x</v-chip>
                <span v-else-if="item.tipo === 4">
                  <v-chip small color="success" v-if="item.data_compensado">Compensado em {{item.data_compensado}}</v-chip>
                  <v-chip small color="warning" else>Aguardando compensação</v-chip>
                </span>
              </template>
              <template v-slot:item.acoes="{item}">
                <v-tooltip top>
                  <template v-slot:activator="{ on }">
                    <v-btn icon color="error" @click="deletePayment(item)" :disabled="loading" v-on="on">
                      <v-icon>mdi-delete</v-icon>
                    </v-btn>
                  </template>
                  <span>Remover</span>
                </v-tooltip>
              </template>
            </v-data-table>
          </v-card-text>
        </v-card>
      </div>
    </v-card>
  </v-dialog>
</template>

<script>
  import {DateHelper} from 'eliaslazcano-helpers'
  export default {
    name: "DialogPagamentos",
    props: {
      servico: {required: true}, //Objeto fornecido pela API GET /servicos?id=x. Atualizado mediante this.$emit('update:servico', this.service)
      value: {default: false}    //Booleano que exibe o modal
    },
    data: () => ({
      service: {},
      showDialog: false,
      loading: false,
      headers: [
        {value: 'tipo', text: 'Forma de pagamento'},
        {value: 'valor', text: 'Valor'},
        {value: 'data_pagamento', text: 'Data'},
        {value: 'obs', text: 'Observação'},
        {value: 'acoes', text: 'Ações'}
      ],
      //Dialog pagar
      dialogPagar: false,
      formasPagamento: [
        {value: 0, text: 'Dinheiro'},
        {value: 1, text: 'Débito'},
        {value: 2, text: 'Crédito à vista'},
        {value: 3, text: 'Crédito parcelado'},
        {value: 4, text: 'Cheque'},
        {value: 5, text: 'Transferência bancária'},
        {value: 6, text: 'Depósito bancário'},
        {value: 7, text: 'Outro'}
      ],
      inputFormaPagamento: null,
      inputValor: 0,
      inputParcelas: 2,
      inputData: DateHelper.date_SQLagora(),
      showDatepickerNewCheque: false
    }),
    computed: {
      inputDataBr() {return this.inputData ? DateHelper.date_SQLparaBR(this.inputData) : ''}
    },
    methods: {
      ajustaData(date) {
        return DateHelper.date_SQLparaBR(date);
      },
      limparCampos() {
        this.inputFormaPagamento = null;
        this.inputValor = 0;
        this.inputParcelas = 2;
        this.inputData = DateHelper.date_SQLagora();
      },
      async reloadPayments(preserveLoading = false) {
        if (!preserveLoading) this.loading = true;
        try {
          const {data} = await this.$http.get(`/pagamentos?servico=${this.service.id}`);
          this.service.pagamentos = data;
          this.$emit('update:servico', this.service);
        } finally {
          if (!preserveLoading) this.loading = false;
        }
      },
      async insertPayment() {
        //Valições
        if (!this.$refs['form-addpayment'].validate()) return;
        if (this.inputFormaPagamento === 3 && !this.inputParcelas) {
          this.$store.commit('snackbar', {text: 'Digite a quantidade de parcelas', color: 'error'})
          return;
        }
        if (!this.inputValor) {
          this.$store.commit('snackbar', {text: 'Digite o valor', color: 'error'})
          return;
        }

        this.loading = true;
        try {
          await this.$http.post('/pagamentos', {
            tipo: this.inputFormaPagamento,
            valor: this.inputValor,
            parcelas: this.inputFormaPagamento === 3 ? this.inputParcelas : null,
            data_pagamento: this.inputData,
            servico: this.service.id
          });
          await this.reloadPayments(true);
          this.dialogPagar = false;
          this.limparCampos();
        } finally {
          this.loading = false;
        }
      },
      async deletePayment(payment) {
        this.loading = true;
        try {
          await this.$http.delete(`/pagamentos?id=${payment.id}`);
          await this.reloadPayments(true);
        } finally {
          this.loading = false;
        }
      }
    },
    mounted() {
      this.showDialog = this.value;
    },
    watch: {
      showDialog(x) {
        if (x !== this.value) {
          if (x === false) this.$emit('update:servico', this.service);
          this.$emit('input', x);
        }
      },
      value(x) {
        if (x !== this.showDialog) {
          if (x === true) this.service = this.servico;
          if (x === false) this.$emit('update:servico', this.service);
          this.showDialog = x;
        }
      }
    }
  }
</script>
