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
                <p class="mb-0">Forma de pagamento</p>
                <v-select
                  label="Forma de pagamento"
                  :items="formasPagamento"
                  placeholder="clique para escolher"
                  v-model="inputFormaPagamento"
                  solo
                  dense
                ></v-select>

                <p class="mb-0">Valor do pagamento</p>
                <v-input hide-details>
                  <money
                    class="form-control font-weight-bold w-100"
                    v-bind="{prefix: 'R$ ', precision: 2, thousands: '.', decimal: ',', masked: false}"
                    :disabled="loading"
                    v-model="inputValor"
                  ></money>
                </v-input>
              </v-card-text>
              <v-card-actions class="justify-center">
                <v-btn outlined color="primary" @click="dialogPagar = false">Cancelar</v-btn>
                <v-btn color="primary">Salvar</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-toolbar-items>
      </v-toolbar>
      <div class="pa-3">
        <v-card outlined class="mb-3">
          <v-card-text>
            <div class="d-flex justify-space-between align-center">
              <div>
                <p class="mb-1 font-weight-bold">Valor do serviço</p>
                <v-input hide-details>
                  <money
                    class="form-control font-weight-bold w-100"
                    v-bind="{prefix: 'R$ ', precision: 2, thousands: '.', decimal: ',', masked: false}"
                    :disabled="loading"
                    v-model="servico.valor"
                  ></money>
                </v-input>
              </div>
              <div class="font-weight-bold">
                <p class="mb-0">Valor pago até o momento:</p>
                <p class="mb-0">R$ 0,00</p>
              </div>
            </div>
          </v-card-text>
        </v-card>
        <v-card>
          <v-card-text>
            <v-data-table
              :items="servico.pagamentos"
              :headers="headers"
              no-data-text="Nenhum pagamento registrado até agora"
              :items-per-page="999"
              hide-default-footer
            >
            </v-data-table>
          </v-card-text>
        </v-card>
      </div>
    </v-card>
  </v-dialog>
</template>

<script>
  //TODO -> Criar um $emit('updateServico') para forçar o componente Pai a atualizar a propriedade serviço? Senão, mudar o comportamento do componente para ID do serviço, carregando os dados via API.
  export default {
    name: "DialogPagamentos",
    props: {
      servico: {required: true}, //Objeto fornecido pela API GET /servicos?id=x
      value: {default: false}
    },
    data: () => ({
      showDialog: false,
      loading: false,
      headers: [
        {value: 'tipo', text: 'Forma de pagamento'},
        {value: 'valor', text: 'Valor'},
        {value: 'acoes', text: 'Ações'}
      ],
      formasPagamento: [
        {value: 0, text: 'Dinheiro'},
        {value: 1, text: 'Débito'},
        {value: 2, text: 'Crédito à vista'},
        {value: 3, text: 'Crédito parcelado'},
        {value: 4, text: 'Cheque'},
        {value: 5, text: 'Outro'}
      ],
      dialogPagar: false,
      inputFormaPagamento: null,
      inputValor: 0
    }),
    mounted() {
      this.showDialog = this.value;
    },
    watch: {
      showDialog(x) {
        if (x !== this.value) this.$emit('input', x)
      },
      value(x) {
        if (x !== this.showDialog) this.showDialog = x;
      }
    }
  }
</script>

<style scoped>

</style>