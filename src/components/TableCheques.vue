<template>
  <v-card>
    <v-card-title class="justify-space-between">
      Cheques
      <v-dialog v-model="dialogAdd" max-width="30rem" scrollable>
        <template v-slot:activator="{on}">
          <v-btn color="success" v-on="on">Adicionar</v-btn>
        </template>
        <v-card :loading="loading">
          <v-card-title class="primary white--text elevation-3">Adicionar cheque</v-card-title>
          <v-card-text class="pt-3">
            <v-form ref="formadd">
              <v-text-field
                label="Nome do cliente"
                v-model="iptCliente"
                hint="Exemplo: João Appolinário"
                :rules="[v => !!v || 'Preencha este campo']"
                :disabled="loading"
              ></v-text-field>
              <v-text-field
                label="Nome do banco"
                v-model="iptBanco"
                hint="Exemplo: Banco do Brasil"
                :disabled="loading"
              ></v-text-field>
              <v-text-field
                label="Agencia"
                v-model="iptAgencia"
                hint="Exemplo: 5799-1"
                :disabled="loading"
              ></v-text-field>
              <v-text-field
                label="Conta"
                v-model="iptConta"
                hint="Exemplo: 6541-2"
                :disabled="loading"
              ></v-text-field>
              <v-radio-group
                label="Forma de resgate"
                v-model="iptTipo"
                :rules="[v => !!v || v === 0 || 'Selecione a forma de resgate']"
                :disabled="loading"
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
                :disabled="loading"
              ></v-text-field>
              <v-input class="v-text-field" ref="valor" :rules="[v => !!v || 'Preencha o valor']" v-model="iptValor">
                <v-label for="ipt-valor" absolute value :focused="iptValorFocused">Valor do cheque</v-label>
                <money
                  id="ipt-valor"
                  v-model="iptValor"
                  v-bind="{prefix: 'R$ ', precision: 2, thousands: '.', decimal: ',', masked: false}"
                  @focusin.native="$refs.valor.isFocused = $refs.valor.hasColor = iptValorFocused = true"
                  @focusout.native="$refs.valor.isFocused = iptValorFocused = false"
                  :disabled="loading"
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
                    prepend-inner-icon="mdi-calendar"
                    readonly
                    v-on="on"
                    hint="Data prevista para o resgate do valor"
                    persistent-hint
                    v-model="iptDataBr"
                    :disabled="loading"
                  ></v-text-field>
                </template>
                <v-date-picker @input="datepicker = false" no-title v-model="iptData"></v-date-picker>
              </v-menu>
            </v-form>
          </v-card-text>
          <v-card-actions class="justify-center elevation-3">
            <v-btn color="primary" @click="submit" :loading="loading" :disabled="loading">Salvar</v-btn>
            <v-btn color="primary" text @click="dialogAdd = false" :disabled="loading">Cancelar</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-card-title>
    <v-card-text>
      <v-data-table
        :items="cheques"
        :headers="headers"
        :loading="loading"
        no-data-text="Nenhum cheque encontrado"
        :custom-sort="customSort"
        dense
      >
        <template v-slot:item.tipo="{item}">
          <span v-if="item.tipo === 0" class="green--text">Saque em dinheiro</span>
          <span v-else-if="item.tipo === 1" class="blue--text">Deposito em conta</span>
        </template>
        <template v-slot:item.valor="{item}">R$ {{item.valor.toFixed(2).replace('.', ',')}}</template>
        <template v-slot:item.data_cheque="{item}">{{corrigeData(item.data_cheque)}}</template>
        <template v-slot:item.dias_restantes="{item}">
          <v-chip v-if="item.dias_restantes === null" color="success" small class="my-1">Compensado em {{corrigeData(item.data_compensado)}}</v-chip>
          <v-chip v-else-if="item.dias_restantes <= 0" color="error" small class="my-1">Compensar cheque</v-chip>
          <v-chip v-else-if="item.dias_restantes > 0" color="info" small class="my-1">{{item.dias_restantes === 1 ? 'Falta 1 dia' : 'Faltam ' + item.dias_restantes + ' dias'}}</v-chip>
        </template>
      </v-data-table>
    </v-card-text>
  </v-card>
</template>

<script>
  //TODO: Funcao para excluir um cheque, exceto se ele estiver interligado a um serviço.
  //TODO: Funcao alterar um cheque, exceto se ele ja foi compensado.
  //TODO: Exibir o total de dinheiro a resgatar dos cheques (soma do valor de todos os cheques nao compensados).
  //TODO: Subdividir o valor acima em "Deposito em conta" e "Saque em dinheiro".
  import {DateHelper} from 'eliaslazcano-helpers'
  export default {
    name: "TableCheques",
    data: () => ({
      loading: true,
      cheques: [],
      headers: [
        {text: 'Cliente', value: 'cliente'},
        {text: 'Tipo de resgate', value: 'tipo'},
        {text: 'Banco', value: 'banco'},
        {text: 'Agencia', value: 'agencia'},
        {text: 'Conta', value: 'conta'},
        {text: 'NºCheque', value: 'numcheque'},
        {text: 'Valor', value: 'valor'},
        {text: 'Data', value: 'data_cheque'},
        {text: 'Status', value: 'dias_restantes', align: 'center'}
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
      iptData: DateHelper.date_SQLagora(),
      iptValorFocused: false
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
        this.loading = true;
        try {
          await this.$http.post('/cheques', {
            cliente: this.iptCliente,
            banco: this.iptBanco,
            agencia: this.iptAgencia,
            conta: this.iptConta,
            tipo: this.iptTipo,
            numero: this.iptNumero,
            valor: this.iptValor,
            data: this.iptData
          });
          this.dialogAdd = false;
          this.limparCampos();
          await this.loadData();
        } finally {
          this.loading = false;
        }
      },
      limparCampos() {
        this.iptCliente = this.iptBanco = this.iptAgencia = this.iptConta = this.iptNumero = '';
        this.iptTipo = null;
        this.iptValor = 0;
        this.iptData = DateHelper.date_SQLagora();
      },
      corrigeData: (dataSql) => DateHelper.date_SQLparaBR(dataSql),
      customSort(items) { //index, isDesc
        items.sort((a, b) => {
            if (a.dias_restantes === null && b.dias_restantes === null) {
                if (a.data_compensado < b.data_compensado) return 1;
                if (a.data_compensado > b.data_compensado) return -1;
                if (a.data_compensado === b.data_compensado) return 0;
            }
            if (a.dias_restantes === null && b.dias_restantes !== null) return 1;
            if (a.dias_restantes !== null && b.dias_restantes === null) return -1;
            if (a.dias_restantes < b.dias_restantes) return -1;
            if (a.dias_restantes > b.dias_restantes) return 1;
            return 0;
        });
        return items;
      }
    },
    created() {
      this.loadData();
    }
  }
</script>
