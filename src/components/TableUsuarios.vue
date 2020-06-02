<template>
  <v-card>
    <v-card-title class="justify-space-between">
      Usuários
      <v-dialog max-width="30rem" v-model="showDialogAddUser">
        <template v-slot:activator="{on}">
          <v-btn color="success" :loading="loadingAddbtn" :disabled="loadingAddbtn || loading" v-on="on">Cadastrar</v-btn>
        </template>
        <v-card :loading="loading">
          <v-card-title>Nova conta de usuário</v-card-title>
          <v-card-text>
            <v-form ref="form-adduser">
              <v-text-field
                label="Login"
                v-model="iptLogin"
                :rules="loginRules"
              ></v-text-field>
              <v-text-field
                label="Nome completo"
                v-model="iptNome"
                :rules="nameRules"
              ></v-text-field>
              <v-text-field
                label="Email"
                v-model="iptEmail"
                :rules="emailRules"
                hint="Ele receberá a senha por e-mail"
                persistent-hint
              ></v-text-field>
            </v-form>
          </v-card-text>
          <v-card-actions class="justify-center">
            <v-btn @click="showDialogAddUser = false" :disabled="loading">Cancelar</v-btn>
            <v-btn @click="addUsuario" color="primary" :disabled="loading" :loading="loading">Gravar</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-card-title>
    <v-card-text>
      <v-data-table
        :items="usuarios"
        :headers="headers"
        :loading="loading"
        sort-by="nome"
        no-data-text="Nenhum usuário"
      >
        <template v-slot:item.login="{ item }">
          <template v-if="item.ativo">{{item.login}}</template>
          <s v-else>{{item.login}}</s>
        </template>
        <template v-slot:item.desde="{ item }">{{corrigeData(item.desde)}}</template>
        <template v-slot:item.actions="{ item }">
          <!-- Editar -->
          <v-tooltip top>
            <template v-slot:activator="{ on }">
              <v-btn icon v-on="on" :disabled="loading" color="amber" small @click="iptId = item.id">
                <v-icon>mdi-pencil</v-icon>
              </v-btn>
            </template>
            <span>Editar usuário</span>
          </v-tooltip>
          <!-- Reset de senha -->
          <v-tooltip top>
            <template v-slot:activator="{ on }">
              <v-btn icon v-on="on" :disabled="loading" color="blue-grey" @click="resetarSenha(item)" small>
                <v-icon>mdi-lock</v-icon>
              </v-btn>
            </template>
            <span>Alterar senha</span>
          </v-tooltip>
          <!-- Desativar/Reativar -->
          <v-tooltip top v-if="item.ativo">
            <template v-slot:activator="{ on }">
              <v-btn icon v-on="on" :disabled="loading" color="success" @click="desativarUsuario(item)" small>
                <v-icon>mdi-power</v-icon>
              </v-btn>
            </template>
            <span>Desativar acesso</span>
          </v-tooltip>
          <v-tooltip top v-else>
            <template v-slot:activator="{ on }">
              <v-btn icon v-on="on" :disabled="loading" color="error" @click="reativarUsuario(item)" small>
                <v-icon>mdi-power</v-icon>
              </v-btn>
            </template>
            <span>Reativar acesso</span>
          </v-tooltip>
        </template>
      </v-data-table>
    </v-card-text>
  </v-card>
</template>

<script>
  import {DateHelper} from 'eliaslazcano-helpers'
  export default {
    name: "TableUsuarios",
    data: () => ({
      loading: true,
      loadingAddbtn: false,
      usuarios: [],
      headers: [
        {value: 'nome', text: 'Nome'},
        {value: 'login', text: 'Login'},
        {value: 'email', text: 'E-mail'},
        {value: 'desde', text: 'Data do cadastro'},
        {value: 'actions', text: 'Ações', sortable: false}
      ],
      iptId: null,
      iptLogin: '',
      iptNome: '',
      iptEmail: '',
      showDialogAddUser: false,
      loginRules: [
        v => !!v || 'O login é obrigatório',
        v => (v && v.length > 3) || 'O login está pequeno demais'
      ],
      nameRules: [
        v => !!v || 'O nome é obrigatório',
        v => (v && v.length > 3) || 'O nome está pequeno demais'
      ],
      emailRules: [
        v => !!v || 'E-mail é obrigatório',
        v => /.+@.+\..+/.test(v) || 'E-mail inválido',
      ]
    }),
    methods: {
      async loadData() {
        this.loading = true;
        try {
          const {data: usuarios} = await this.$http.get('/usuarios');
          this.usuarios = usuarios;
        } finally {
          this.loading = false;
        }
      },
      async addUsuario() {
        if (!this.$refs['form-adduser'].validate()) return;
        if (!this.iptLogin.trim() || !this.iptNome.trim() || !this.iptEmail.trim()) {
          alert('Preencha tudo');
          return;
        }
        this.loading = true;
        try {
          if (this.iptId) await this.$http.put('/usuarios', {id: this.iptId, login: this.iptLogin.trim(), nome: this.iptNome.trim(), email: this.iptEmail.trim()});
          else await this.$http.post('/usuarios', {login: this.iptLogin.trim(), nome: this.iptNome.trim(), email: this.iptEmail.trim()});
          await this.loadData();
          this.showDialogAddUser = false;
        } finally {
          this.loading = false;
        }
      },
      async desativarUsuario(usuario) {
        if (!confirm(`Tem certeza que deseja desativar "${usuario.nome}" do sistema?`)) return;
        this.loading = true;
        try {
          await this.$http.delete('/usuarios', { params: {id: usuario.id} });
          await this.loadData();
        } finally {
          this.loading = false;
        }
      },
      async reativarUsuario(usuario) {
        this.loading = true;
        try {
          await this.$http.delete('/usuarios', { params: {id: usuario.id, reativar: 'S'} });
          await this.loadData();
        } finally {
          this.loading = false;
        }
      },
      async resetarSenha(usuario) {
        if (!confirm(`Tem certeza? O usuário irá receber a nova senha no e-mail "${usuario.email}".`)) return;
        this.loading = true;
        try {
          await this.$http.put('/usuarios', {reset: true, id: usuario.id});
        } finally {
          this.loading = false;
        }
      },
      corrigeData(datetimeSql) {
        return DateHelper.datetime_SQLparaBR(datetimeSql);
      },
      preencherCampos(usuario = null) {
        if (usuario) {
          this.iptLogin = usuario.login;
          this.iptNome = usuario.nome;
          this.iptEmail = usuario.email;
        }
        else this.iptLogin = this.iptNome = this.iptEmail = '';
      }
    },
    watch: {
      iptId(v) {
        if (v) {
          const usuario = this.usuarios.find(u => u.id === v);
          this.preencherCampos(usuario);
          this.showDialogAddUser = true;
        } else this.preencherCampos();
      },
      showDialogAddUser(v) { if (!v) this.iptId = null; }
    },
    created() {this.loadData()}
  }
</script>
