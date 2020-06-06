<template>
  <v-dialog
    fullscreen
    hide-overlay
    transition="dialog-bottom-transition"
    v-model="showDialog"
  ></v-dialog>
</template>

<script>
  export default {
    name: "DialogPagamentos",
    props: {
      servico: {required: true}, //Objeto fornecido pela API GET /servicos?id=x
      value: {default: false}
    },
    data: () => ({
      showDialog: false
    }),
    mounted() {
      this.showDialog = this.value;
    },
    watch: {
      value(x) {
        console.log(`value mudou para ` + x);
      }
    }
  }
</script>

<style scoped>

</style>