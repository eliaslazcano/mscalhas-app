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
                v-model="showDatepickerNewCheque"
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
                <v-date-picker @input="showDatepickerNewCheque = false" no-title v-model="iptData"></v-date-picker>
              </v-menu>
            </v-form>
          </v-card-text>
          <v-card-actions class="justify-center elevation-3">
            <v-btn color="primary" @click="addCheque" :loading="loading" :disabled="loading">Salvar</v-btn>
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
        :search="search"
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
          <v-skeleton-loader
            class="mt-1"
            type="table-tbody"
          ></v-skeleton-loader>
        </template>
        <template v-slot:item.tipo="{item}">
          <span v-if="item.tipo === 0" class="green--text">Saque</span> <!-- em dinheiro -->
          <span v-else-if="item.tipo === 1" class="blue--text">Deposito</span> <!-- em conta -->
        </template>
        <template v-slot:item.valor="{item}">
          <span class="text-no-wrap">R$ {{item.valor.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ".")}}</span>
        </template>
        <template v-slot:item.data_cheque="{item}">{{corrigeData(item.data_cheque)}}</template>
        <template v-slot:item.dias_restantes="{item}">
          <v-chip v-if="item.dias_restantes === null" color="success" small class="my-1">Compensado {{item.data_compensado === hoje ? 'hoje' : 'em ' + corrigeData(item.data_compensado)}}</v-chip>
          <v-chip v-else-if="item.dias_restantes <= 0" color="error" small class="my-1">Compensar cheque</v-chip>
          <v-chip v-else-if="item.dias_restantes > 0" color="info" small class="my-1">{{item.dias_restantes === 1 ? 'Falta 1 dia' : 'Faltam ' + item.dias_restantes + ' dias'}}</v-chip>
        </template>
        <template v-slot:item.actions="{item}">
          <div class="d-flex flex-nowrap">
            <v-tooltip top>
              <template v-slot:activator="{ on }">
                <v-btn icon color="error" @click="deleteCheque(item)" :disabled="loading" v-on="on">
                  <v-icon>mdi-delete</v-icon>
                </v-btn>
              </template>
              <span>Remover</span>
            </v-tooltip>
            <v-tooltip top v-if="!item.data_compensado">
              <template v-slot:activator="{on}">
                <v-btn icon color="success" :disabled="loading || (hoje < item.data_cheque)" v-on="on" @click="() => {dialogCompensarCheque = item; dialogCompensarShow = true}">
                  <v-icon>mdi-checkbox-marked-circle-outline</v-icon>
                </v-btn>
              </template>
              <span>Marcar como compensado</span>
            </v-tooltip>
            <v-tooltip top v-if="item.servico">
              <template v-slot:activator="{ on }">
                <v-btn icon color="primary" :disabled="loading" :to="'/servico/' + item.servico" v-on="on">
                  <v-icon>mdi-tools</v-icon>
                </v-btn>
              </template>
              <span>Visualizar serviço</span>
            </v-tooltip>
          </div>
        </template>
      </v-data-table>
    </v-card-text>
    <!-- Dialog: Compensar Cheque -->
    <v-dialog
      ref="dialog"
      v-model="dialogCompensarShow"
      persistent
      width="290px"
    >
      <v-date-picker v-model="iptData" scrollable :min="dialogCompensarCheque ? dialogCompensarCheque.data_cheque : undefined" :max="hoje">
        <div class="d-flex flex-column" style="width: 100%">
          <p class="text-center text-body-1 green--text">Data que resgatou o dinheiro</p>
          <div class="d-flex justify-space-around">
            <v-btn outlined color="primary" @click="dialogCompensarShow = false" :disabled="loading">CANCELAR</v-btn>
            <v-btn color="primary" @click="updateCheque({...dialogCompensarCheque, data_compensado: iptData})" :loading="loading" :disabled="loading">CONFIRMAR</v-btn>
          </div>
        </div>
      </v-date-picker>
    </v-dialog>
  </v-card>
</template>

<script>
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
        {text: 'Cliente', value: 'cliente', sortable: false},
        {text: 'Tipo de resgate', value: 'tipo', sortable: false},
        {text: 'Banco', value: 'banco', sortable: false},
        {text: 'Agencia', value: 'agencia', sortable: false},
        {text: 'Conta', value: 'conta', sortable: false},
        {text: 'NºCheque', value: 'numcheque', sortable: false},
        {text: 'Valor', value: 'valor', sortable: false},
        {text: 'Data', value: 'data_cheque', sortable: false},
        {text: 'Status', value: 'dias_restantes', align: 'center', sortable: false},
        {text: 'Ações', value: 'actions', sortable: false}
      ],
      search: '',
      dialogAdd: false,
      dialogCompensarShow: false,
      dialogCompensarCheque: null,
      showDatepickerNewCheque: false,
      hoje: DateHelper.date_SQLagora(),
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
      },
      //Comunica com o backend
      async loadData() {
        this.loading = true;
        try {
          const {data: cheques} = await this.$http.get('/cheques');
          this.cheques = cheques;
        } finally {
          this.loading = false;
        }
      },
      async addCheque() {
        if (!this.$refs.formadd.validate()) return;
        this.loading = true;
        try {
          await this.$http.post('/cheques', {
            cliente: this.iptCliente,
            banco: this.iptBanco,
            agencia: this.iptAgencia,
            conta: this.iptConta,
            tipo: this.iptTipo,
            numcheque: this.iptNumero,
            valor: this.iptValor,
            data_cheque: this.iptData
          });
          this.dialogAdd = false;
          this.limparCampos();
          await this.loadData();
          this.$store.commit('snackbar', {color: 'success', text: 'Cheque registrado'});
        } finally {
          this.loading = false;
        }
      },
      async deleteCheque(cheque) {
        if (cheque.servico) {
          alert(`Este cheque não pode ser apagado por aqui. Ele está vinculado a uma ordem de serviço. Se deseja apagar mesmo assim, vá até a ficha do serviço número ${cheque.servico} e modifique o pagamento por lá.`);
          return;
        }
        if (!confirm(`Tem certeza que deseja remover este cheque de ${cheque.cliente.toUpperCase()} no valor de R$ ${cheque.valor.toFixed(2).replace('.', ',')} ?`)) return;
        this.loading = true;
        try {
          await this.$http.delete('/cheques', {params: {id: cheque.id}});
          await this.loadData();
          this.$store.commit('snackbar', {color: 'success', text: 'Cheque removido'})
        } finally {
          this.loading = false;
        }
      },
      async updateCheque(cheque) {
        this.loading = true;
        try {
          await this.$http.put('/cheques', cheque);
          await this.loadData();
          this.dialogCompensarShow = false;
          this.limparCampos();
          this.$store.commit('snackbar', {color: 'success', text: 'Cheque atualizado'});
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
